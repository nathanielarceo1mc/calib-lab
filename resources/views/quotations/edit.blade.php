{{-- resources/views/quotations/edit.blade.php --}}
<x-app-layout>

    {{-- Header: breadcrumb + title + back button --}}
    <div class="mb-4 px-6 flex items-center justify-between">
        <div>
            <div class="text-[11px] text-gray-400">
                <a href="{{ route('dashboard') }}" class="hover:underline hover:text-emerald-600">Home</a>
                <span class="mx-1">/</span>
                <a href="{{ route('quotations.index') }}" class="hover:underline hover:text-emerald-600">
                    Quotation
                </a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-gray-600">Edit</span>
            </div>
            <h2 class="mt-1 text-2xl font-semibold text-gray-800">
                Edit Quotation
                @if(!empty($quotation->ref_no ?? $quotation->reference_no))
                    <span class="text-sm font-normal text-gray-500">
                        ({{ $quotation->ref_no ?? $quotation->reference_no }})
                    </span>
                @endif
            </h2>
        </div>

        <a href="{{ route('quotations.index') }}"
           class="inline-flex items-center px-4 py-1.5 rounded-md border border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50">
            ← Back to list
        </a>
    </div>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('quotations.update', $quotation->id) }}">
                @csrf
                @method('PUT')

                {{-- MAIN QUOTATION INFO --}}
                <div class="bg-white border border-gray-200 shadow-sm sm:rounded-md overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 border-b text-sm font-semibold text-gray-700">
                        Quotation Information
                    </div>

                    <div class="px-4 py-4 space-y-4">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Ref Number *</label>
                                <input type="text" name="ref_no"
                                       value="{{ old('ref_no', $quotation->ref_no ?? $quotation->reference_no) }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100"
                                       readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Date *</label>
                                <input type="date" name="date"
                                       value="{{ old('date', optional($quotation->date ?? $quotation->quotation_date)->format('Y-m-d')) }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">
                                    Customer *
                                    <a href="{{ route('customers.create') }}"
                                       class="ml-1 text-xs text-sky-600 hover:underline">
                                        Add Customer
                                    </a>
                                </label>
                                <select name="customer_id"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                    <option value="">-- Select customer --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            @selected(old('customer_id', $quotation->customer_id ?? null) == $customer->id)>
                                            {{ $customer->business_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Address + contact info --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Address *</label>
                            <textarea name="address" rows="2"
                                      class="w-full border-gray-300 rounded-md text-sm bg-gray-100"
                                      readonly>{{ old('address', $quotation->address ?? '') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Telephone Number</label>
                                <input type="text" name="telephone"
                                       value="{{ old('telephone', $quotation->telephone ?? '') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">E-mail Address</label>
                                <input type="email" name="email"
                                       value="{{ old('email', $quotation->email ?? '') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">TIN</label>
                                <input type="text" name="tin"
                                       value="{{ old('tin', $quotation->tin ?? '') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Terms</label>
                                <input type="text" name="payment_terms"
                                       value="{{ old('payment_terms', $quotation->payment_terms ?? '') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">VAT Type</label>
                                <select name="vat_type" class="w-full border-gray-300 rounded-md text-sm bg-gray-100" disabled>
                                    @php $vat = old('vat_type', $quotation->vat_type ?? '') @endphp
                                    <option value="">--</option>
                                    <option value="VATable" @selected($vat === 'VATable')>VATable</option>
                                    <option value="VAT Zero Rated" @selected($vat === 'VAT Zero Rated')>VAT Zero Rated</option>
                                    <option value="Non-VAT" @selected($vat === 'Non-VAT')>Non-VAT</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Contact Person *</label>
                                <select name="contact_person_id"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                    {{-- you can populate this via JS or server based on customer --}}
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Reference</label>
                                <input type="text" name="reference"
                                       value="{{ old('reference', $quotation->reference ?? '') }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Discount % *</label>
                                <input type="number" step="0.01" name="discount_percent"
                                       value="{{ old('discount_percent', $quotation->discount_percent ?? 0) }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Validity Days *</label>
                                <input type="number" name="validity_days"
                                       value="{{ old('validity_days', $quotation->validity_days ?? 30) }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                                <input type="text" name="status"
                                       value="{{ old('status', ucfirst($quotation->status ?? 'Draft')) }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Quotation Request No.</label>
                                <input type="text" name="request_no"
                                       value="{{ old('request_no', $quotation->request_no ?? '') }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- UNIT UNDER TEST --}}
                @php
                    $uutItems = $quotation->units ?? $quotation->unitItems ?? [];
                @endphp

                <div class="bg-white border border-gray-200 shadow-sm sm:rounded-md overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 border-b text-sm font-semibold text-gray-700 flex items-center justify-between">
                        <span>Unit Under Test</span>
                        <div class="space-x-2">
                            <button type="button"
                                    class="px-3 py-1 rounded bg-emerald-500 text-white text-xs">
                                + Add
                            </button>
                            <button type="button"
                                    class="px-3 py-1 rounded bg-sky-600 text-white text-xs">
                                + Blank
                            </button>
                        </div>
                    </div>

                    <div class="px-4 py-3 overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 border-b text-left">Accreditation</th>
                                    <th class="px-2 py-2 border-b text-left">Service Type</th>
                                    <th class="px-2 py-2 border-b text-left">Scope</th>
                                    <th class="px-2 py-2 border-b text-left">Description / Specification</th>
                                    <th class="px-2 py-2 border-b text-left">Range</th>
                                    <th class="px-2 py-2 border-b text-left">CMC</th>
                                    <th class="px-2 py-2 border-b text-left">Method</th>
                                    <th class="px-2 py-2 border-b text-left">Required Environmental Condition</th>
                                    <th class="px-2 py-2 border-b text-left">Type</th>
                                    <th class="px-2 py-2 border-b text-right">Qty</th>
                                    <th class="px-2 py-2 border-b text-left">UOM</th>
                                    <th class="px-2 py-2 border-b text-right">Price</th>
                                    <th class="px-2 py-2 border-b text-right">Discount</th>
                                    <th class="px-2 py-2 border-b text-right">Rush Fee</th>
                                    <th class="px-2 py-2 border-b text-right">Total Price</th>
                                    <th class="px-2 py-2 border-b text-left">Lead Time</th>
                                    <th class="px-2 py-2 border-b text-left">Notes</th>
                                    <th class="px-2 py-2 border-b text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="uut-rows">
                                @forelse($uutItems as $i => $uut)
                                    <tr>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][accreditation]"
                                                   value="{{ old("uut.$i.accreditation", data_get($uut, 'accreditation')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][service_type]"
                                                   value="{{ old("uut.$i.service_type", data_get($uut, 'service_type')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][scope]"
                                                   value="{{ old("uut.$i.scope", data_get($uut, 'scope')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][description]"
                                                   value="{{ old("uut.$i.description", data_get($uut, 'description')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][range]"
                                                   value="{{ old("uut.$i.range", data_get($uut, 'range')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][cmc]"
                                                   value="{{ old("uut.$i.cmc", data_get($uut, 'cmc')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][method]"
                                                   value="{{ old("uut.$i.method", data_get($uut, 'method')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][environment]"
                                                   value="{{ old("uut.$i.environment", data_get($uut, 'environment')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][type]"
                                                   value="{{ old("uut.$i.type", data_get($uut, 'type')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="number" name="uut[{{ $i }}][qty]"
                                                   value="{{ old("uut.$i.qty", data_get($uut, 'qty', 1)) }}"
                                                   class="w-full border-gray-300 rounded-md text-right" min="1">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][uom]"
                                                   value="{{ old("uut.$i.uom", data_get($uut, 'uom')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="number" step="0.01" name="uut[{{ $i }}][price]"
                                                   value="{{ old("uut.$i.price", data_get($uut, 'price')) }}"
                                                   class="w-full border-gray-300 rounded-md text-right">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="number" step="0.01" name="uut[{{ $i }}][line_discount]"
                                                   value="{{ old("uut.$i.line_discount", data_get($uut, 'line_discount', 0)) }}"
                                                   class="w-full border-gray-300 rounded-md text-right">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="number" step="0.01" name="uut[{{ $i }}][rush_fee]"
                                                   value="{{ old("uut.$i.rush_fee", data_get($uut, 'rush_fee', 0)) }}"
                                                   class="w-full border-gray-300 rounded-md text-right">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="number" step="0.01" name="uut[{{ $i }}][total_price]"
                                                   value="{{ old("uut.$i.total_price", data_get($uut, 'total_price')) }}"
                                                   class="w-full border-gray-300 rounded-md text-right" readonly>
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][lead_time]"
                                                   value="{{ old("uut.$i.lead_time", data_get($uut, 'lead_time')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][notes]"
                                                   value="{{ old("uut.$i.notes", data_get($uut, 'notes')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1 text-center">
                                            <button type="button"
                                                    class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs">
                                                ×
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- If no rows yet, show one blank row --}}
                                    <tr>
                                        <td colspan="18" class="border-t px-2 py-2 text-center text-gray-400">
                                            No unit under test rows yet. Use + Add to insert rows.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- OTHER CHARGES --}}
                @php
                    $charges = $quotation->charges ?? $quotation->chargeItems ?? [];
                @endphp
                <div class="bg-white border border-gray-200 shadow-sm sm:rounded-md overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 border-b text-sm font-semibold text-gray-700 flex items-center justify-between">
                        <span>Other Charges</span>
                        <button type="button" class="px-3 py-1 rounded bg-emerald-500 text-white text-xs">
                            + Add
                        </button>
                    </div>

                    <div class="px-4 py-3 overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 border-b text-left">Description / Specification</th>
                                    <th class="px-2 py-2 border-b text-right">Qty</th>
                                    <th class="px-2 py-2 border-b text-left">UOM</th>
                                    <th class="px-2 py-2 border-b text-right">Price</th>
                                    <th class="px-2 py-2 border-b text-right">Total Price</th>
                                    <th class="px-2 py-2 border-b text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="charges-rows">
                                @forelse($charges as $i => $ch)
                                    <tr>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="charges[{{ $i }}][description]"
                                                   value="{{ old("charges.$i.description", data_get($ch, 'description')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="number" name="charges[{{ $i }}][qty]"
                                                   value="{{ old("charges.$i.qty", data_get($ch, 'qty', 1)) }}"
                                                   class="w-full border-gray-300 rounded-md text-right" min="1">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="text" name="charges[{{ $i }}][uom]"
                                                   value="{{ old("charges.$i.uom", data_get($ch, 'uom')) }}"
                                                   class="w-full border-gray-300 rounded-md">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="number" step="0.01" name="charges[{{ $i }}][price]"
                                                   value="{{ old("charges.$i.price", data_get($ch, 'price')) }}"
                                                   class="w-full border-gray-300 rounded-md text-right">
                                        </td>
                                        <td class="border-t px-2 py-1">
                                            <input type="number" step="0.01" name="charges[{{ $i }}][total_price]"
                                                   value="{{ old("charges.$i.total_price", data_get($ch, 'total_price')) }}"
                                                   class="w-full border-gray-300 rounded-md text-right" readonly>
                                        </td>
                                        <td class="border-t px-2 py-1 text-center">
                                            <button type="button"
                                                    class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs">
                                                ×
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="border-t px-2 py-2 text-center text-gray-400">
                                            No other charges. Use + Add to insert rows.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3 text-right text-sm font-semibold text-gray-700">
                            Overall Total:
                            <span id="overall-total">
                                {{ number_format($quotation->overall_total ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER SAVE BUTTON --}}
                <div class="flex items-center justify-end mt-4">
                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
