<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Customer;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    /**
     * Display a listing of the quotations.
     */
    public function index()
    {
        // If you later add a customer relation on Quotation,
        // you can change this to Quotation::with('customer')->latest()->get();
        $quotations = Quotation::orderByDesc('created_at')->get();

        return view('quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create()
    {
        // For the customer dropdown in the form
        $customers = Customer::orderBy('business_name')->get();

        return view('quotations.create', compact('customers'));
    }

    /**
     * Store a newly created quotation in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'quote_no'       => ['required', 'string', 'max:50', 'unique:quotations,quote_no'],
            'customer_name'  => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:50'],
            'scope'          => ['nullable', 'string'],
            'amount'         => ['nullable', 'numeric'],
        ]);

        $data['created_by'] = auth()->id();
        $data['status']     = 'draft';

        Quotation::create($data);

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Quotation created.');
    }

    /**
     * Show the form for editing the specified quotation.
     */
    public function edit(Quotation $quotation)
    {
        // Same customer list for the edit form
        $customers = Customer::orderBy('business_name')->get();

        return view('quotations.edit', compact('quotation', 'customers'));
    }

    /**
     * Update the specified quotation in storage.
     */
    public function update(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'customer_name'  => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:50'],
            'scope'          => ['nullable', 'string'],
            'amount'         => ['nullable', 'numeric'],
        ]);

        $quotation->update($data);

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Quotation updated.');
    }

    /**
     * Approve the quotation and set PO info.
     */
    public function approve(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'po_number' => ['required', 'string', 'max:100'],
            'po_date'   => ['required', 'date'],
        ]);

        $quotation->fill($data);
        $quotation->status      = 'approved';
        $quotation->approved_at = now();
        $quotation->save();

        // later we can notify receiving here

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Quotation approved and PO saved.');
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
}
