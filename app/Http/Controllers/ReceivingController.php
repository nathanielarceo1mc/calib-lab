<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobItem;
use App\Models\JobUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        // --- Sorting setup ---------------------------------------------------
        $sortField = $request->get('sort', 'order_date');
        $sortDir   = $request->get('dir', 'desc');

        // Only allow sorting by these columns
        $allowedSorts = [
            'order_number',
            'order_date',
            'customer_name',
            'contact_person',
            'status',
            'receiver_user_id',
        ];

        if (! in_array($sortField, $allowedSorts, true)) {
            $sortField = 'order_date';
        }

        if (! in_array(strtolower($sortDir), ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        // --- Summary tiles ---------------------------------------------------
        $receivingCount = Job::where('status', 'receiving')->count();
        $technicalCount = Job::where('status', 'technical')->count();
        $releasingCount = Job::where('status', 'releasing')->count();
        $billingCount   = Job::where('status', 'billing')->count();
        $arCount        = Job::where('status', 'ar')->count();

        // --- Jobs list (sortable, paginated) ---------------------------------
        $jobs = Job::orderBy($sortField, $sortDir)
            ->orderBy('id', 'desc')   // stable tie-breaker
            ->paginate(100);          // matches your "Show 100 entries" UI

        return view('receiving.index', [
            'jobs'           => $jobs,
            'receivingCount' => $receivingCount,
            'technicalCount' => $technicalCount,
            'releasingCount' => $releasingCount,
            'billingCount'   => $billingCount,
            'arCount'        => $arCount,
            'sort'           => $sortField,
            'dir'            => $sortDir,
        ]);
    }

    public function create()
    {
        // Auto-generate unique work order number based on last sequence this year
        $year   = Carbon::now()->format('Y');
        $prefix = 'WO-' . $year . '-';

        // Look for the last order_number like "WO-2025-000X"
        $last = Job::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($last) {
            // Extract last 4-digit sequence
            $lastSeq = (int) substr($last->order_number, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        $orderNumber = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        $today       = Carbon::now()->format('Y-m-d');

        return view('receiving.create', compact('orderNumber', 'today'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // IMPORTANT: use work_orders, not jobs
            'order_number'      => 'required|string|max:50|unique:work_orders,order_number',
            'order_date'        => 'required|date',
            'customer_name'     => 'required|string|max:255',
            'address'           => 'nullable|string',
            'telephone'         => 'nullable|string|max:100',
            'email'             => 'nullable|email|max:255',
            'contact_person'    => 'nullable|string|max:255',
            'designation'       => 'nullable|string|max:255',
            'po_number'         => 'nullable|string|max:255',
            'discount_percent'  => 'nullable|numeric|min:0|max:100',
            'quotation_number'  => 'nullable|string|max:255',
            'end_user'          => 'nullable|string|max:255',
            'notes'             => 'nullable|string',

            // UUT arrays
            'accreditation'     => 'nullable|array',
            'service_type'      => 'nullable|array',
            'capability'        => 'nullable|array',
            'uut_description'   => 'nullable|array',
            'uut_type'          => 'nullable|array',
            'receiving_method'  => 'nullable|array',
            'qty'               => 'nullable|array',
            'unit'              => 'nullable|array',
            'lead_time_days'    => 'nullable|array',
        ]);

        DB::transaction(function () use (&$job, $data) {
            // Create the Job (work order)
            $job = Job::create([
                'order_number'     => $data['order_number'],
                'order_date'       => $data['order_date'],
                'customer_name'    => $data['customer_name'],
                'address'          => $data['address'] ?? null,
                'telephone'        => $data['telephone'] ?? null,
                'email'            => $data['email'] ?? null,
                'contact_person'   => $data['contact_person'] ?? null,
                'designation'      => $data['designation'] ?? null,
                'po_number'        => $data['po_number'] ?? null,
                'discount_percent' => $data['discount_percent'] ?? 0,
                'quotation_number' => $data['quotation_number'] ?? null,
                'end_user'         => $data['end_user'] ?? null,
                'notes'            => $data['notes'] ?? null,
                // status at Receiving before posting
                'status'           => 'receiving',
            ]);

            // Save Unit Under Test rows (JobItem) + create JobUnit records
            $accreditations    = $data['accreditation']    ?? [];
            $serviceTypes      = $data['service_type']     ?? [];
            $capabilities      = $data['capability']       ?? [];
            $descriptions      = $data['uut_description']  ?? [];
            $types             = $data['uut_type']         ?? [];
            $receivingMethods  = $data['receiving_method'] ?? [];
            $qtys              = $data['qty']              ?? [];
            $units             = $data['unit']             ?? [];
            $leadTimes         = $data['lead_time_days']   ?? [];

            foreach ($accreditations as $index => $acc) {
                $rowEmpty =
                    empty($acc) &&
                    empty($serviceTypes[$index] ?? null) &&
                    empty($descriptions[$index] ?? null);

                if ($rowEmpty) {
                    continue;
                }

                $item = JobItem::create([
                    'job_id'          => $job->id,
                    'accreditation'   => $acc,
                    'service_type'    => $serviceTypes[$index]     ?? null,
                    'capability'      => $capabilities[$index]     ?? null,
                    'description'     => $descriptions[$index]     ?? null,
                    'type'            => $types[$index]            ?? null,
                    'receiving_method'=> $receivingMethods[$index] ?? null,
                    'qty'             => (int) ($qtys[$index]      ?? 1),
                    'unit'            => $units[$index]            ?? null,
                    'lead_time_days'  => $leadTimes[$index]        ?? null,
                ]);

                // Create JobUnit records, one per quantity, with unique reference no.
                $qty = max(1, (int) ($qtys[$index] ?? 1));
                for ($i = 0; $i < $qty; $i++) {
                    $ref = $this->generateReferenceNo(
                        $item->service_type,
                        $job->order_date ?? now()
                    );

                    JobUnit::create([
                        'job_item_id' => $item->id,
                        'reference_no'=> $ref,
                        'status'      => 'for_approval',
                        'name'        => $item->description,
                    ]);
                }
            }
        });

        // After create, go straight to EDIT screen
        return redirect()
            ->route('receiving.edit', $job)
            ->with('success', 'New order has been successfully created.');
    }

    /**
     * Edit view – shows order details + generated UUTs and a POST button.
     */
    public function edit(Job $job)
    {
        $job->load(['items.units']);

        return view('receiving.edit', compact('job'));
    }

    /**
     * Update per-unit details (manufacturer, model, SN, range, remarks, assigned_to).
     */
    public function updateUnits(Job $job, Request $request)
    {
        $data = $request->validate([
            'unit_id'        => 'required|array',
            'manufacturer'   => 'nullable|array',
            'model'          => 'nullable|array',
            'serial_number'  => 'nullable|array',
            'range'          => 'nullable|array',
            'remarks'        => 'nullable|array',
            'assigned_to'    => 'nullable|array',
        ]);

        // list of all unit IDs
        $unitIds        = $data['unit_id'];
        $manufacturer   = $data['manufacturer']   ?? [];
        $model          = $data['model']          ?? [];
        $serial_number  = $data['serial_number']  ?? [];
        $range          = $data['range']          ?? [];
        $remarks        = $data['remarks']        ?? [];
        $assigned_to    = $data['assigned_to']    ?? [];

        // Only allow updating units that belong to this job
        $validUnitIds = JobUnit::whereIn('id', $unitIds)
            ->whereHas('item', function ($q) use ($job) {
                $q->where('job_id', $job->id);
            })
            ->pluck('id')
            ->toArray();

        foreach ($unitIds as $index => $id) {
            if (! in_array($id, $validUnitIds)) {
                continue;
            }

            $unit = JobUnit::find($id);
            if (! $unit) {
                continue;
            }

            $unit->update([
                'manufacturer'  => $manufacturer[$index]  ?? null,
                'model'         => $model[$index]         ?? null,
                'serial_number' => $serial_number[$index] ?? null,
                'range'         => $range[$index]         ?? null,
                'remarks'       => $remarks[$index]       ?? null,
                'assigned_to'   => $assigned_to[$index]   ?? null,
            ]);
        }

        return redirect()
            ->route('receiving.edit', $job)
            ->with('success', 'Unit under test details updated.');
    }

    /**
     * POST /receiving/{job}/post
     * Marks the order (and all units) as posted.
     */
    public function post(Job $job)
    {
        if ($job->status === 'posted') {
            return redirect()
                ->route('receiving.edit', $job)
                ->with('success', 'Order has already been posted.');
        }

        DB::transaction(function () use ($job) {
            // Update job status
            $job->status = 'posted';
            $job->save();

            // Mark all units as posted
            $itemIds = $job->items()->pluck('id');
            if ($itemIds->isNotEmpty()) {
                JobUnit::whereIn('job_item_id', $itemIds)
                    ->update(['status' => 'posted']);
            }
        });

        return redirect()
            ->route('receiving.edit', $job)
            ->with('success', 'Order has been successfully posted.');
    }

    /**
     * Generate reference number like:
     *   2025CAL00001, 2025CAL00002, ...
     *   2025INS00001, 2025INS00002, ...
     */
    protected function generateReferenceNo(?string $serviceType, $orderDate): string
    {
        $year = Carbon::parse($orderDate)->format('Y');

        $prefixType = strtoupper($serviceType ?? '');
        if ($prefixType === 'INSPECTION') {
            $middle = 'INS';
        } else {
            // default to CAL
            $middle = 'CAL';
        }

        $prefix = $year . $middle;

        $last = JobUnit::where('reference_no', 'like', $prefix . '%')
            ->orderBy('reference_no', 'desc')
            ->first();

        if ($last) {
            $lastSeq = (int) substr($last->reference_no, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }
}
