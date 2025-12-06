<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\QuotationUnit;
use App\Models\QuotationCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class QuotationController extends Controller
{
    /**
     * Display a listing of the quotations.
     */
    public function index()
    {
        $status = request('status');
        $query = Quotation::with(['customer', 'creator']);
        if ($status && in_array($status, ['approved', 'disapproved', 'for approval', 'draft'])) {
            $query->where('status', $status);
        }
        $quotations = $query->orderByDesc('created_at')->get();

        return view('quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create()
    {
        $this->authorizeMarketing();

        // For the customer dropdown in the form
        $customers = Customer::orderBy('business_name')->get();

        $year = now()->year;
        $prefix = '1MC-QUOTE-' . $year . '-';
        $last = Quotation::where('quote_no', 'like', $prefix . '%')
            ->orderBy('quote_no', 'desc')
            ->first();
        $nextSeq = 1;
        if ($last) {
            $lastSeq = (int) substr($last->quote_no, -4);
            $nextSeq = $lastSeq + 1;
        }
        $nextRefNo = sprintf('%s%04d', $prefix, $nextSeq);

        return view('quotations.create', compact('customers', 'nextRefNo'));
    }

    /**
     * Store a newly created quotation in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'quote_no'       => ['required', 'string', 'max:50', 'unique:quotations,quote_no'],
            'date'           => ['required', 'date'],
            'customer_id'    => ['required', 'exists:customers,id'],
            'customer_name'  => ['required', 'string', 'max:255'],
            'address'        => ['nullable', 'string'],
            'telephone'      => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:50'],
            'tin'            => ['nullable', 'string', 'max:50'],
            'payment_terms'  => ['nullable', 'string', 'max:100'],
            'vat_type'       => ['nullable', 'string', 'max:50'],
            'reference'      => ['nullable', 'string', 'max:255'],
            'discount_percent' => ['nullable', 'numeric'],
            'validity_days'  => ['nullable', 'integer'],
            'quotation_request_no' => ['nullable', 'string', 'max:255'],
            'status'         => ['nullable', 'string', 'max:50'],

            'uut'                       => ['array'],
            'uut.*.accreditation'       => ['nullable', 'string', 'max:255'],
            'uut.*.service_type'        => ['nullable', 'string', 'max:255'],
            'uut.*.scope'               => ['nullable', 'string'],
            'uut.*.description'         => ['nullable', 'string'],
            'uut.*.range'               => ['nullable', 'string'],
            'uut.*.cmc'                 => ['nullable', 'string'],
            'uut.*.method'              => ['nullable', 'string'],
            'uut.*.environment'         => ['nullable', 'string'],
            'uut.*.type'                => ['nullable', 'string', 'max:50'],
            'uut.*.qty'                 => ['nullable', 'integer'],
            'uut.*.uom'                 => ['nullable', 'string', 'max:50'],
            'uut.*.price'               => ['nullable', 'numeric'],
            'uut.*.discount'            => ['nullable', 'numeric'],
            'uut.*.rush_fee'            => ['nullable', 'numeric'],
            'uut.*.total_price'         => ['nullable', 'numeric'],
            'uut.*.lead_time'           => ['nullable', 'string', 'max:100'],
            'uut.*.notes'               => ['nullable', 'string'],

            'charges'                   => ['array'],
            'charges.*.description'     => ['nullable', 'string'],
            'charges.*.qty'             => ['nullable', 'numeric'],
            'charges.*.uom'             => ['nullable', 'string', 'max:50'],
            'charges.*.price'           => ['nullable', 'numeric'],
            'charges.*.total'           => ['nullable', 'numeric'],
        ]);

        $this->authorizeMarketing();

        $quotation = Quotation::create([
            'quote_no'      => $data['quote_no'],
            'date'          => $data['date'],
            'customer_id'   => $data['customer_id'],
            'customer_name' => $data['customer_name'],
            'address'       => $data['address'] ?? null,
            'telephone'     => $data['telephone'] ?? null,
            'contact_person'=> $data['contact_person'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'tin'           => $data['tin'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'vat_type'      => $data['vat_type'] ?? null,
            'reference'     => $data['reference'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? 0,
            'validity_days' => $data['validity_days'] ?? null,
            'quotation_request_no' => $data['quotation_request_no'] ?? null,
            'status'        => $request->has('submit_for_approval') ? 'for approval' : ($data['status'] ?? 'draft'),
            'po_amount'     => $this->calculatePoAmount($request->input('uut', []), $request->input('charges', [])),
            'prepared_by'   => auth()->user()->name ?? null,
            'created_by'    => auth()->id(),
        ]);

        $this->syncUnits($quotation, $request->input('uut', []));
        $this->syncCharges($quotation, $request->input('charges', []));

        return redirect()
            ->route('quotations.edit', $quotation->id)
            ->with('success', 'New quotation has been successfully created.');
    }

    /**
     * Show the form for editing the specified quotation.
     */
    public function edit(Quotation $quotation)
    {
        $this->authorizeMarketing();

        // Same customer list for the edit form
        $customers = Customer::orderBy('business_name')->get();

        $quotation->load(['units', 'charges']);

        return view('quotations.edit', compact('quotation', 'customers'));
    }

    /**
     * Update the specified quotation in storage.
     */
    public function update(Request $request, Quotation $quotation)
    {
        $this->authorizeMarketing();

        $data = $request->validate([
            'date'           => ['required', 'date'],
            'customer_id'    => ['required', 'exists:customers,id'],
            'customer_name'  => ['required', 'string', 'max:255'],
            'address'        => ['nullable', 'string'],
            'telephone'      => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:50'],
            'tin'            => ['nullable', 'string', 'max:50'],
            'payment_terms'  => ['nullable', 'string', 'max:100'],
            'vat_type'       => ['nullable', 'string', 'max:50'],
            'reference'      => ['nullable', 'string', 'max:255'],
            'discount_percent' => ['nullable', 'numeric'],
            'validity_days'  => ['nullable', 'integer'],
            'quotation_request_no' => ['nullable', 'string', 'max:255'],
            'status'         => ['nullable', 'string', 'max:50'],

            'uut'                       => ['array'],
            'uut.*.accreditation'       => ['nullable', 'string', 'max:255'],
            'uut.*.service_type'        => ['nullable', 'string', 'max:255'],
            'uut.*.scope'               => ['nullable', 'string'],
            'uut.*.description'         => ['nullable', 'string'],
            'uut.*.range'               => ['nullable', 'string'],
            'uut.*.cmc'                 => ['nullable', 'string'],
            'uut.*.method'              => ['nullable', 'string'],
            'uut.*.environment'         => ['nullable', 'string'],
            'uut.*.type'                => ['nullable', 'string', 'max:50'],
            'uut.*.qty'                 => ['nullable', 'integer'],
            'uut.*.uom'                 => ['nullable', 'string', 'max:50'],
            'uut.*.price'               => ['nullable', 'numeric'],
            'uut.*.discount'            => ['nullable', 'numeric'],
            'uut.*.rush_fee'            => ['nullable', 'numeric'],
            'uut.*.total_price'         => ['nullable', 'numeric'],
            'uut.*.lead_time'           => ['nullable', 'string', 'max:100'],
            'uut.*.notes'               => ['nullable', 'string'],

            'charges'                   => ['array'],
            'charges.*.description'     => ['nullable', 'string'],
            'charges.*.qty'             => ['nullable', 'numeric'],
            'charges.*.uom'             => ['nullable', 'string', 'max:50'],
            'charges.*.price'           => ['nullable', 'numeric'],
            'charges.*.total'           => ['nullable', 'numeric'],
        ]);

        $statusValue = $quotation->status;
        $message = 'Quotation updated.';

        if ($request->has('approve')) {
            $this->authorizeMarketingApprover();
            $statusValue = 'approved';
            $message = 'Quotation approved.';
        } elseif ($request->has('disapprove')) {
            $this->authorizeMarketingApprover();
            $statusValue = 'disapproved';
            $message = 'Quotation disapproved.';
        } elseif ($request->has('submit_for_approval')) {
            $statusValue = 'for approval';
            $message = 'Quotation submitted for approval.';
        } else {
            $statusValue = $data['status'] ?? $quotation->status;
        }

        $quotation->update([
            'date'          => $data['date'],
            'customer_id'   => $data['customer_id'],
            'customer_name' => $data['customer_name'],
            'address'       => $data['address'] ?? null,
            'telephone'     => $data['telephone'] ?? null,
            'contact_person'=> $data['contact_person'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'tin'           => $data['tin'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'vat_type'      => $data['vat_type'] ?? null,
            'reference'     => $data['reference'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? 0,
            'validity_days' => $data['validity_days'] ?? null,
            'quotation_request_no' => $data['quotation_request_no'] ?? null,
            'status'        => $statusValue,
            'po_amount'     => $this->calculatePoAmount($request->input('uut', []), $request->input('charges', [])),
            'prepared_by'   => $quotation->prepared_by ?? (auth()->user()->name ?? null),
            'approved_by'   => $statusValue === 'approved' ? auth()->id() : ($statusValue === 'disapproved' ? null : $quotation->approved_by),
            'approved_at'   => $statusValue === 'approved' ? now() : ($statusValue === 'disapproved' ? null : $quotation->approved_at),
        ]);

        $this->syncUnits($quotation, $request->input('uut', []));
        $this->syncCharges($quotation, $request->input('charges', []));

        return back()->with('success', $message);
    }

    /**
     * Approve the quotation and set PO info.
     */
    public function approve(Request $request, Quotation $quotation)
    {
        $this->authorizeMarketingApprover();

        $data = $request->validate([
            'po_number' => ['required', 'string', 'max:100'],
            'po_date'   => ['required', 'date'],
        ]);

        $quotation->fill($data);
        $quotation->status          = 'approved';
        $quotation->approved_at     = now();
        $quotation->approved_by     = auth()->id();
        $quotation->proceed_to_receiving = $quotation->proceed_to_receiving ?? false;
        $quotation->save();

        return back()->with('success', 'Quotation approved and PO saved.');
    }

    /**
     * Remove the specified quotation from storage.
     */
    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Quotation deleted.');
    }

    public function proceedToReceiving(Quotation $quotation)
    {
        $this->authorizeMarketingApprover();
        $quotation->update([
            'proceed_to_receiving' => true,
        ]);
        return back()->with('success', 'Quotation handed off for receiving.');
    }

    protected function syncUnits(Quotation $quotation, array $units): void
    {
        $quotation->units()->delete();
        foreach ($units as $row) {
            if ($this->isFilled($row)) {
                $quotation->units()->create([
                    'accreditation' => Arr::get($row, 'accreditation'),
                    'service_type'  => Arr::get($row, 'service_type'),
                    'scope'         => Arr::get($row, 'scope'),
                    'description'   => Arr::get($row, 'description'),
                    'range'         => Arr::get($row, 'range'),
                    'cmc'           => Arr::get($row, 'cmc'),
                    'method'        => Arr::get($row, 'method'),
                    'environment'   => Arr::get($row, 'environment'),
                    'type'          => Arr::get($row, 'type'),
                    'qty'           => Arr::get($row, 'qty'),
                    'uom'           => Arr::get($row, 'uom'),
                    'price'         => Arr::get($row, 'price'),
                    'discount'      => Arr::get($row, 'discount'),
                    'rush_fee'      => Arr::get($row, 'rush_fee'),
                    'total_price'   => Arr::get($row, 'total_price'),
                    'lead_time'     => Arr::get($row, 'lead_time'),
                    'notes'         => Arr::get($row, 'notes'),
                ]);
            }
        }
    }

    protected function syncCharges(Quotation $quotation, array $charges): void
    {
        $quotation->charges()->delete();
        foreach ($charges as $row) {
            if ($this->isFilled($row)) {
                $quotation->charges()->create([
                    'description' => Arr::get($row, 'description'),
                    'qty'         => Arr::get($row, 'qty'),
                    'uom'         => Arr::get($row, 'uom'),
                    'price'       => Arr::get($row, 'price'),
                    'total'       => Arr::get($row, 'total'),
                ]);
            }
        }
    }

    protected function isFilled(array $row): bool
    {
        return collect($row)->filter(fn($v) => $v !== null && $v !== '' && $v !== [])->isNotEmpty();
    }

    protected function calculatePoAmount(array $units, array $charges): float
    {
        $unitTotal = 0;
        foreach ($units as $u) {
            $qty = (float)($u['qty'] ?? 0);
            $price = (float)($u['price'] ?? 0);
            $discount = (float)($u['discount'] ?? 0);
            $rush = (float)($u['rush_fee'] ?? 0);
            $line = ($qty * $price) - $discount + $rush;
            if ($line > 0) {
                $unitTotal += $line;
            }
        }

        $chargeTotal = 0;
        foreach ($charges as $c) {
            $qty = (float)($c['qty'] ?? 0);
            $price = (float)($c['price'] ?? 0);
            $chargeTotal += $qty * $price;
        }

        return $unitTotal + $chargeTotal;
    }

    protected function authorizeMarketing(): void
    {
        $user = auth()->user();
        if (!$user || !str_contains(strtolower($user->roles ?? ''), 'marketing')) {
            abort(403, 'Marketing role required.');
        }
    }

    protected function authorizeMarketingApprover(): void
    {
        $user = auth()->user();
        if (!$user || !str_contains(strtolower($user->roles ?? ''), 'marketing approver')) {
            abort(403, 'Marketing Approver role required.');
        }
    }
}
