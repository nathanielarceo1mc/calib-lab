<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\UnitUnderTest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReceivingController extends Controller
{
    /**
     * List Receiving work orders (like SMCS receiving list).
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $forApprovalCount = WorkOrder::whereHas('units', function ($q) {
            $q->where('status', 'for_approval')
              ->where('assigned_approver_id', optional(auth()->user())->id);
        })->count();

        $query = WorkOrder::orderBy('order_date', 'desc')
            ->orderBy('id', 'desc');

        if ($filter === 'for-approval') {
            $query->whereHas('units', function ($q) {
                $q->where('status', 'for_approval')
                  ->where('assigned_approver_id', optional(auth()->user())->id);
            });
        }

        $jobs = $query->get();

        return view('receiving.index', [
            'jobs'             => $jobs,
            'forApprovalCount' => $forApprovalCount,
            'activeFilter'     => $filter,
        ]);
    }

    /**
     * Show "New Order" form.
     */
    public function create()
    {
        // Generate next order number: 1MC-ORD-YYYY-#### (based on work_orders table)
        $year = Carbon::now()->year;

        $prefix = '1MC-ORD-' . $year . '-';

        $last = WorkOrder::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($last) {
            $lastSeq = (int) substr($last->order_number, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        $newOrderNumber = sprintf('%s%04d', $prefix, $nextSeq);

        // Customers with related contact persons and end-users
        $customers = Customer::with(['contactPersons', 'endUsers'])
            ->orderBy('business_name', 'asc')
            ->get();

        return view('receiving.create', [
            'newOrderNumber'   => $newOrderNumber,
            'customers'        => $customers,
        ]);
    }
    /**
     * Store new work order (header only for now) then redirect to edit.
     */
    public function store(Request $request)
    {
        $validated = $this->validateHeader($request);

        // Derive customer_name STRICTLY from company name
        $customer = Customer::findOrFail($validated['customer_id']);
        $customerName = $customer->business_name;   // <- company name only

        // Handle PO file upload (optional)
        $poPath = null;
        if ($request->hasFile('po_file')) {
            $poPath = $request->file('po_file')->store('po_files', 'public');
        }

        $uutRows = $validated['uut'] ?? $request->input('uut');

        $workOrder = DB::transaction(function () use ($validated, $customerName, $poPath, $uutRows) {
            $workOrder = WorkOrder::create([
                'customer_id'      => $validated['customer_id'] ?? null,
                'job_number'       => null, // reserved for future if needed
                'order_number'     => $validated['order_number'],
                'order_date'       => $validated['order_date'],
                'customer_name'    => $customerName,
                'address'          => $validated['address'] ?? null,
                'telephone'        => $validated['telephone'] ?? null,
                'email'            => $validated['email'] ?? null,
                'contact_person'   => $validated['contact_person'] ?? null,
                'designation'      => $validated['designation'] ?? null,
                'po_number'        => $validated['po_number'] ?? null,
                'po_file_path'     => $poPath,
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'quotation_number' => $validated['quotation_number'] ?? null,
                'end_user'         => $validated['end_user'] ?? null,
                'receiver_user_id' => optional(auth()->user())->id,
                'status'           => 'receiving',
                'for_approval'     => 0,
                'notes'            => $validated['notes'] ?? null,
            ]);

            $this->syncUutRows($workOrder, $uutRows);

            return $workOrder;
        });

        return redirect()
            ->route('receiving.edit', $workOrder->id)
            ->with('success', 'New order has been successfully created.');
    }

    /**
     * Show edit form.
     */
    public function edit(WorkOrder $workOrder)
    {
        $approvers = User::where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('receiving.edit', [
            'job'       => $workOrder->load(['units.approver', 'units.personnel', 'customer.contactPersons']),
            'customers' => Customer::with(['contactPersons', 'endUsers'])->orderBy('business_name')->get(),
            'approvers' => $approvers,
        ]);
    }

    /**
     * Update header fields.
     */
    public function update(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->status === 'posted') {
            $validated = $request->validate([
                'units'                                 => ['array'],
                'units.*.status'                        => ['nullable', 'in:for_approval,approved,in_progress,returned'],
                'units.*.action'                        => ['nullable', 'in:assign,return'],
                'units.*.assigned_approver_id'          => ['nullable', 'exists:users,id'],
                'units.*.assigned_personnel_id'         => ['nullable', 'exists:users,id'],
                'units.*.name'                          => ['nullable', 'string', 'max:255'],
                'units.*.method_procedure'              => ['nullable', 'string', 'max:255'],
                'units.*.manufacturer'                  => ['nullable', 'string', 'max:255'],
                'units.*.model'                         => ['nullable', 'string', 'max:255'],
                'units.*.serial_number'                 => ['nullable', 'string', 'max:255'],
                'units.*.identification'                => ['nullable', 'string', 'max:255'],
                'units.*.sub_contracted'                => ['nullable', 'boolean'],
                'units.*.type'                          => ['nullable', 'string', 'max:50'],
                'units.*.receiving_method'              => ['nullable', 'string', 'max:50'],
                'units.*.tracking_no'                   => ['nullable', 'string', 'max:255'],
                'units.*.lead_time'                     => ['nullable', 'string', 'max:100'],
                'units.*.received_date'                 => ['nullable', 'date'],
                'units.*.estimated_completion_date'     => ['nullable', 'date'],
                'units.*.onsite_date'                   => ['nullable', 'date'],
                'units.*.service_interval'              => ['nullable', 'integer', 'min:0'],
                'units.*.service_interval_type'         => ['nullable', 'string', 'max:20'],
                'units.*.accessories'                   => ['nullable', 'string', 'max:255'],
                'units.*.receiving_condition'           => ['nullable', 'string', 'max:500'],
                'units.*.require_conformance'           => ['nullable', 'boolean'],
                'units.*.require_opinion'               => ['nullable', 'boolean'],
                'units.*.deviate_methods'               => ['nullable', 'boolean'],
                'units.*.request_new_method'            => ['nullable', 'boolean'],
                'units.*.request_details'               => ['nullable', 'string'],
                'units.*.delivery_method'               => ['nullable', 'string', 'max:255'],
                'units.*.delivery_contact'              => ['nullable', 'string', 'max:255'],
                'units.*.special_instructions'          => ['nullable', 'string'],
                'units.*.test_points'                   => ['nullable', 'string'],
                'units.*.approver_remarks'              => ['nullable', 'string'],
            ]);

            $unitsInput = $request->input('units', []);

            DB::transaction(function () use ($unitsInput, $workOrder) {
                foreach ($unitsInput as $id => $payload) {
                    $uut = $workOrder->units()->where('id', $id)->first();
                    if (! $uut) {
                        continue;
                    }

                    $action = $payload['action'] ?? null;
                    $newStatus = $payload['status'] ?? $uut->status;
                    if ($action === 'assign') {
                        $newStatus = 'in_progress';
                    } elseif ($action === 'return') {
                        $newStatus = 'for_approval';
                    }

                    if (
                        $uut->assigned_approver_id &&
                        ($newStatus !== $uut->status) &&
                        optional(auth()->user())->id !== $uut->assigned_approver_id
                    ) {
                        continue; // silently skip if user not allowed to change status
                    }

                    $nameValue = $payload['name'] ?? null;
                    if ($nameValue === null || $nameValue === '') {
                        $nameValue = $uut->name ?? $uut->description;
                    }

                    $uut->update([
                        'status'                    => $newStatus,
                        'assigned_approver_id'      => $payload['assigned_approver_id'] ?? $uut->assigned_approver_id,
                        'assigned_personnel_id'     => $payload['assigned_personnel_id'] ?? $uut->assigned_personnel_id,
                        'name'                      => $nameValue,
                        'method_procedure'          => $payload['method_procedure'] ?? $uut->method_procedure,
                        'manufacturer'              => $payload['manufacturer'] ?? $uut->manufacturer,
                        'model'                     => $payload['model'] ?? $uut->model,
                        'serial_number'             => $payload['serial_number'] ?? $uut->serial_number,
                        'identification'            => $payload['identification'] ?? $uut->identification,
                        'sub_contracted'            => isset($payload['sub_contracted']) ? boolval($payload['sub_contracted']) : false,
                        'type'                      => $payload['type'] ?? $uut->type,
                        'receiving_method'          => $payload['receiving_method'] ?? $uut->receiving_method,
                        'tracking_no'               => ($payload['receiving_method'] ?? $uut->receiving_method) === 'Courier'
                            ? ($payload['tracking_no'] ?? $uut->tracking_no)
                            : null,
                        'lead_time'                 => $payload['lead_time'] ?? $uut->lead_time,
                        'received_date'             => $payload['received_date'] ?? $uut->received_date ?? $workOrder->order_date,
                        'estimated_completion_date' => $this->computeEstimatedDate(
                            $payload['received_date'] ?? $uut->received_date ?? $workOrder->order_date,
                            $payload['lead_time'] ?? $uut->lead_time
                        ) ?? $uut->estimated_completion_date,
                        'onsite_date'               => ($payload['type'] ?? $uut->type) === 'On-site'
                            ? ($payload['onsite_date'] ?? $uut->onsite_date)
                            : null,
                        'service_interval'          => $payload['service_interval'] ?? $uut->service_interval,
                        'service_interval_type'     => $payload['service_interval_type'] ?? $uut->service_interval_type,
                        'accessories'               => $payload['accessories'] ?? $uut->accessories,
                        'receiving_condition'       => $payload['receiving_condition'] ?? $uut->receiving_condition,
                        'require_conformance'       => isset($payload['require_conformance']) ? boolval($payload['require_conformance']) : false,
                        'require_opinion'           => isset($payload['require_opinion']) ? boolval($payload['require_opinion']) : false,
                        'deviate_methods'           => isset($payload['deviate_methods']) ? boolval($payload['deviate_methods']) : false,
                        'request_new_method'        => isset($payload['request_new_method']) ? boolval($payload['request_new_method']) : false,
                        'request_details'           => ($payload['request_new_method'] ?? $uut->request_new_method)
                            ? ($payload['request_details'] ?? $uut->request_details)
                            : null,
                        'delivery_method'           => $payload['delivery_method'] ?? $uut->delivery_method,
                        'delivery_contact'          => $payload['delivery_contact'] ?? $uut->delivery_contact,
                        'special_instructions'      => $payload['special_instructions'] ?? $uut->special_instructions,
                        'test_points'               => isset($payload['test_points_list']) && is_array($payload['test_points_list'])
                                                        ? implode("\n", array_filter($payload['test_points_list'], fn($v) => $v !== null && $v !== ''))
                                                        : ($payload['test_points'] ?? $uut->test_points),
                        'approver_remarks'          => $payload['approver_remarks'] ?? $uut->approver_remarks,
                    ]);
                }
            });

            return back()->with('success', 'Unit references updated.');
        }

        $validated = $this->validateHeader($request, $workOrder->id, $workOrder);

        $uutRows = $validated['uut'] ?? $request->input('uut');

        // Customer name should remain the stored text value (not FK), so keep incoming value
        $poPath = $workOrder->po_file_path;
        if ($request->hasFile('po_file')) {
            $poPath = $request->file('po_file')->store('po_files', 'public');
        }

        DB::transaction(function () use ($workOrder, $validated, $poPath, $uutRows) {
            $workOrder->update([
                'customer_id'      => $validated['customer_id'] ?? $workOrder->customer_id,
                'order_number'     => $validated['order_number'],
                'order_date'       => $validated['order_date'],
                'customer_name'    => $validated['customer_name'] ?? $workOrder->customer_name,
                'address'          => $validated['address'] ?? null,
                'telephone'        => $validated['telephone'] ?? null,
                'email'            => $validated['email'] ?? null,
                'contact_person'   => $validated['contact_person'] ?? null,
                'designation'      => $validated['designation'] ?? null,
                'po_number'        => $validated['po_number'] ?? null,
                'po_file_path'     => $poPath,
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'quotation_number' => $validated['quotation_number'] ?? null,
                'end_user'         => $validated['end_user'] ?? null,
                'notes'            => $validated['notes'] ?? null,
            ]);

            $this->syncUutRows($workOrder, $uutRows);
        });

        return back()->with('success', 'Order updated.');
    }

    /**
     * Post the order (lock in and mark posted).
     */
    public function post(WorkOrder $workOrder)
    {
        if ($workOrder->units()->count() === 0) {
            return back()->with('error', 'Add at least one Unit Under Test before posting.');
        }

        DB::transaction(function () use ($workOrder) {
            $lines = $workOrder->units()->get();
            $workOrder->units()->delete();

            $year = Carbon::now()->year;

            foreach ($lines as $line) {
                $qty = (int) ($line->qty ?? 1);
                $qty = $qty > 0 ? $qty : 1;

                for ($i = 0; $i < $qty; $i++) {
                    $refNo = $this->nextReferenceNumber($year);
                    $receivedDate = $workOrder->order_date ?? Carbon::now();
                    $estimatedDate = $this->computeEstimatedDate($receivedDate, $line->lead_time);

                    $workOrder->units()->create([
                        'reference_no'          => $refNo,
                        'status'                => 'for_approval',
                        'accreditation'         => $line->accreditation,
                        'service_type'          => $line->service_type,
                        'capability'            => $line->capability,
                        'description'           => $line->description,
                        'name'                  => $line->description,
                        'type'                  => $line->type,
                        'receiving_method'      => $line->receiving_method,
                        'qty'                   => 1,
                        'unit'                  => $line->unit,
                        'lead_time'             => $line->lead_time,
                        'rush_fee'              => null,
                        'discount'              => null,
                        'received_date'         => $receivedDate,
                        'estimated_completion_date' => $estimatedDate,
                    ]);
                }
            }

            $workOrder->update([
                'status'       => 'posted',
                'for_approval' => 0,
            ]);
        });

        return back()->with('success', 'Order has been successfully posted.');
    }

    /**
     * Submit the order for approval (flag only for now).
     */
    public function submitForApproval(WorkOrder $workOrder)
    {
        $workOrder->update([
            'for_approval' => 1,
        ]);

        return back()->with('success', 'Order submitted for approval.');
    }

    /**
     * Assign selected references to a TSRF Approver in batch.
     */
    public function assignApproverBatch(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate([
            'approver_id'   => ['required', 'exists:users,id'],
            'unit_ids'      => ['required', 'array'],
            'unit_ids.*'    => ['integer'],
        ]);

        $units = $workOrder->units()->whereIn('id', $data['unit_ids'])->get();

        if ($units->isEmpty()) {
            return back()->with('error', 'No references selected.');
        }

        // Optionally ensure approver has the TSRF role
        $approver = User::find($data['approver_id']);
        if (! $approver) {
            return back()->with('error', 'Approver not found.');
        }

        UnitUnderTest::where('work_order_id', $workOrder->id)
            ->whereIn('id', $data['unit_ids'])
            ->update([
                'assigned_approver_id' => $data['approver_id'],
                'status'               => 'for_approval',
            ]);

        // Flag the work order as having items for approval
        $workOrder->update(['for_approval' => 1]);

        return back()->with('success', 'Selected references submitted to the approver.');
    }

    /**
     * Assign selected references to a Technical Personnel (by TSRF Approver).
     */
    public function assignPersonnelBatch(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate([
            'personnel_id' => ['required', 'exists:users,id'],
            'unit_ids'     => ['required', 'array'],
            'unit_ids.*'   => ['integer'],
        ]);

        $currentUserId = optional(auth()->user())->id;

        $units = $workOrder->units()
            ->whereIn('id', $data['unit_ids'])
            ->where('assigned_approver_id', $currentUserId)
            ->get();

        if ($units->isEmpty()) {
            return back()->with('error', 'No references selected or not assigned to you.');
        }

        UnitUnderTest::where('work_order_id', $workOrder->id)
            ->whereIn('id', $units->pluck('id'))
            ->update([
                'assigned_personnel_id' => $data['personnel_id'],
                'status'                => 'in_progress',
            ]);

        return back()->with('success', 'Selected references assigned to personnel.');
    }

    /**
     * Shared header validation.
     */
    protected function validateHeader(Request $request, $workOrderId = null, WorkOrder $workOrder = null): array
    {
        $hasExistingPo = $workOrder && $workOrder->po_file_path;

        return $request->validate([
            'order_number'      => [
                'required',
                'string',
                'max:32',
                Rule::unique('work_orders', 'order_number')->ignore($workOrderId),
            ],
            'order_date'        => ['required', 'date'],
            'customer_id'       => ['nullable', 'exists:customers,id'], // only used on create to derive name
            'customer_name'     => ['nullable', 'string', 'max:255'],

            'address'           => ['nullable', 'string', 'max:500'],
            'telephone'         => ['nullable', 'string', 'max:50'],
            'email'             => ['nullable', 'string', 'max:255'],
            'contact_person'    => ['nullable', 'string', 'max:255'],
            'designation'       => ['nullable', 'string', 'max:255'],
            'use_details_from'  => ['nullable', 'string', 'max:64'],
            'end_user'          => ['nullable', 'string', 'max:255'],
            'to_follow_po'      => ['nullable', 'boolean'],

            'po_number'         => ['nullable', 'string', 'max:64'],
            'po_file'           => [
                Rule::requiredIf(! $request->boolean('to_follow_po') && ! $hasExistingPo),
                'file',
                'mimes:pdf,jpg,jpeg,png'
            ],
            'discount_percent'  => ['nullable', 'numeric', 'min:0', 'max:100'],

            'quotation_number'  => ['nullable', 'string', 'max:64'],
            'notes'             => ['nullable', 'string'],

            'uut'                       => ['array'],
            'uut.*.accreditation'       => ['nullable', 'string', 'max:255'],
            'uut.*.service_type'        => ['nullable', 'string', 'max:255'],
            'uut.*.capability'          => ['nullable', 'string'],
            'uut.*.description'         => ['nullable', 'string'],
            'uut.*.type'                => ['nullable', 'string', 'max:50'],
            'uut.*.receiving_method'    => ['nullable', 'string', 'max:50'],
            'uut.*.qty'                 => ['nullable', 'string', 'max:50'],
            'uut.*.unit'                => ['nullable', 'string', 'max:50'],
            'uut.*.lead_time'           => ['nullable', 'string', 'max:100'],
        ]);
    }

    protected function isUutRowFilled(array $row): bool
    {
        return collect($row)->filter(function ($v) {
            return $v !== null && $v !== '' && $v !== [];
        })->isNotEmpty();
    }

    protected function syncUutRows(WorkOrder $workOrder, $uutRows): void
    {
        if (! is_array($uutRows)) {
            return;
        }

        $workOrder->units()->delete();

        foreach ($uutRows as $row) {
            if ($this->isUutRowFilled($row)) {
                $workOrder->units()->create([
                    'accreditation'    => $row['accreditation'] ?? null,
                    'service_type'     => $row['service_type'] ?? null,
                    'capability'       => $row['capability'] ?? null,
                    'description'      => $row['description'] ?? null,
                    'name'             => $row['description'] ?? null,
                    'type'             => $row['type'] ?? null,
                    'receiving_method' => $row['receiving_method'] ?? null,
                    'qty'              => $row['qty'] ?? null,
                    'unit'             => $row['unit'] ?? null,
                    'lead_time'        => $row['lead_time'] ?? null,
                ]);
            }
        }
    }

    protected function nextReferenceNumber(int $year): string
    {
        $prefix = $year . '-1MC-CAL-';
        $last = UnitUnderTest::where('reference_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('reference_no', 'desc')
            ->first();

        $next = 1;
        if ($last && str_starts_with($last->reference_no, $prefix)) {
            $numeric = (int) substr($last->reference_no, strlen($prefix));
            $next = $numeric + 1;
        }

        return sprintf('%s%05d', $prefix, $next);
    }

    protected function computeEstimatedDate($receivedDate, ?string $leadTime): ?Carbon
    {
        if (! $receivedDate) {
            return null;
        }

        $date = Carbon::parse($receivedDate);
        $daysToAdd = 0;

        if ($leadTime === '3 to 5 working days') {
            $daysToAdd = 5;
        } elseif ($leadTime === '10 to 15 working days') {
            $daysToAdd = 15;
        }

        $added = 0;
        while ($added < $daysToAdd) {
            $date->addDay();
            if ($date->isWeekend()) {
                continue;
            }
            $added++;
        }

        return $date;
    }
}
