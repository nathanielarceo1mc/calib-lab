<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\UnitUnderTest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $forApprovalCount = WorkOrder::where('status', 'receiving')
            ->where('for_approval', 1)
            ->count();

        $query = WorkOrder::orderBy('order_date', 'desc')
            ->orderBy('id', 'desc');

        if ($filter === 'for-approval') {
            $query->where('status', 'receiving')
                  ->where('for_approval', 1);
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

        return redirect()
            ->route('receiving.edit', $workOrder->id)
            ->with('success', 'New order has been successfully created.');
    }

    /**
     * Show edit form.
     */
    public function edit(WorkOrder $workOrder)
    {
        return view('receiving.edit', [
            'job'       => $workOrder->load('units'),
            'customers' => Customer::with(['contactPersons', 'endUsers'])->orderBy('business_name')->get(),
        ]);
    }

    /**
     * Update header fields.
     */
    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $this->validateHeader($request, $workOrder->id, $workOrder);

        // Customer name should remain the stored text value (not FK), so keep incoming value
        $poPath = $workOrder->po_file_path;
        if ($request->hasFile('po_file')) {
            $poPath = $request->file('po_file')->store('po_files', 'public');
        }

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

        // Sync UUT rows (simple replace)
        $workOrder->units()->delete();
        $uutRows = $request->input('uut', []);
        if (is_array($uutRows)) {
            foreach ($uutRows as $row) {
                if ($this->isUutRowFilled($row)) {
                    $workOrder->units()->create([
                        'accreditation'    => $row['accreditation'] ?? null,
                        'service_type'     => $row['service_type'] ?? null,
                        'capability'       => $row['capability'] ?? null,
                        'description'      => $row['description'] ?? null,
                        'type'             => $row['type'] ?? null,
                        'receiving_method' => $row['receiving_method'] ?? null,
                        'qty'              => $row['qty'] ?? null,
                        'unit'             => $row['unit'] ?? null,
                        'lead_time'        => $row['lead_time'] ?? null,
                        'rush_fee'         => $row['rush_fee'] ?? null,
                        'discount'         => $row['discount'] ?? null,
                    ]);
                }
            }
        }

        return back()->with('success', 'Order updated.');
    }

    /**
     * Post the order (lock in and mark posted).
     */
    public function post(WorkOrder $workOrder)
    {
        $workOrder->update([
            'status'       => 'posted',
            'for_approval' => 0,
        ]);

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
            'uut.*.rush_fee'            => ['nullable', 'numeric', 'min:0'],
            'uut.*.discount'            => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    protected function isUutRowFilled(array $row): bool
    {
        return collect($row)->filter(function ($v) {
            return $v !== null && $v !== '' && $v !== [];
        })->isNotEmpty();
    }
}

