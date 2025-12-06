{{-- resources/views/quotations/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Quotation - Edit')

@section('header')
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Quotation</h2>
    </div>
@endsection

@section('content')
@php
    $roleStr = strtolower(auth()->user()->roles ?? '');
    $isApprover = str_contains($roleStr, 'marketing approver');
    $isMarketing = str_contains($roleStr, 'marketing');
    $status = strtolower($quotation->status ?? 'draft');
    $locked = (!$isApprover && in_array($status, ['for approval','approved']));
    $canEdit = $isApprover || !$locked || $status === 'disapproved';
@endphp
    <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Breadcrumb --}}
        <div class="text-xs text-slate-500 mb-4">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 hover:underline">Home</a>
            <span class="mx-1">/</span>
            <a href="{{ route('quotations.index') }}" class="hover:text-emerald-600 hover:underline">Quotation</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600 font-semibold">Edit Quotation</span>
        </div>

        <form method="POST" action="{{ route('quotations.update', $quotation->id) }}" id="quotation-form">
            @csrf
            @method('PUT')

            @php
                $uutRows = old('uut', $quotation->units->map(function ($u) {
                    return $u->only([
                        'accreditation','service_type','scope','description','range','cmc','method','environment',
                        'type','qty','uom','price','discount','rush_fee','lead_time','notes'
                    ]);
                })->all());
                $chargeRows = old('charges', $quotation->charges->map(function ($c) {
                    return $c->only(['description','qty','uom','price','total']);
                })->all());
            @endphp

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100 text-sm font-semibold text-slate-800">
                    Edit Quotation
                </div>

                <div class="p-5 space-y-6">
                    <fieldset class="{{ $locked && !$isApprover ? 'opacity-75' : '' }}" @if($locked && !$isApprover) disabled @endif>
                    {{-- Header fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Ref Number *</label>
                            <input type="text" name="quote_no" value="{{ old('quote_no', $quotation->quote_no) }}"
                                   class="w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Date *</label>
                            <input type="date" name="date" value="{{ old('date', optional($quotation->date)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">
                                Customer *
                                <a href="{{ route('customers.create') }}" class="ml-1 text-xs text-sky-600 hover:underline">Add Customer</a>
                            </label>
                            <input type="hidden" name="customer_name" id="customer_name_input" value="{{ old('customer_name', $quotation->customer_name ?? ($quotation->customer->business_name ?? '')) }}">
                            <select name="customer_id" id="customer_select"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Select customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                            data-business-name="{{ $customer->business_name }}"
                                            data-address="{{ $customer->address }}"
                                            data-email="{{ $customer->company_email }}"
                                            data-telephone="{{ $customer->company_phone }}"
                                            data-tin="{{ $customer->tin }}"
                                            data-payment-terms="{{ $customer->payment_terms }}"
                                            data-vat-type="{{ $customer->vat_type }}"
                                            data-contact-person="{{ $customer->primary_contact }}"
                                            data-contacts='@json(
                                                $customer->contactPersons
                                                    ->map(function ($p) {
                                                        return trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? ''))
                                                            ?: ($p->full_name ?? $p->position ?? '');
                                                    })
                                                    ->filter()
                                                    ->values()
                                            )'
                                            @selected(old('customer_id', $quotation->customer_id) == $customer->id)>
                                        {{ $customer->business_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Address *</label>
                        <textarea name="address" rows="2" id="customer_address"
                                  class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                                  >{{ old('address', $quotation->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Telephone Number</label>
                            <input type="text" name="telephone" id="customer_telephone"
                                   value="{{ old('telephone', $quotation->telephone) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">E-mail Address</label>
                            <input type="email" name="email" id="customer_email"
                                   value="{{ old('email', $quotation->contact_email) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">TIN</label>
                            <input type="text" name="tin" id="customer_tin"
                                   value="{{ old('tin', $quotation->tin) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Terms</label>
                            <input type="text" name="payment_terms" id="customer_payment_terms"
                                   value="{{ old('payment_terms', $quotation->payment_terms) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">VAT Type</label>
                            <select name="vat_type" id="customer_vat_type"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Select VAT type --</option>
                                <option value="VAT" @selected(old('vat_type') === 'VAT')>VAT</option>
                                <option value="NON-VAT" @selected(old('vat_type') === 'NON-VAT')>NON-VAT</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Person *</label>
                            <select name="contact_person" id="customer_contact_person"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                @if(old('contact_person', $quotation->contact_person))
                                    <option value="{{ old('contact_person', $quotation->contact_person) }}">{{ old('contact_person', $quotation->contact_person) }}</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Reference</label>
                            <input type="text" name="reference" value="{{ old('reference') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Discount % *</label>
                            <input type="number" step="0.01" name="discount_percent"
                                   value="{{ old('discount_percent', $quotation->discount_percent) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Validity Days *</label>
                            <input type="number" name="validity_days" value="{{ old('validity_days', $quotation->validity_days ?? 30) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                            <input type="text" name="status" value="{{ old('status', ucfirst($quotation->status)) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-slate-50" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Quotation Request No.</label>
                            <input type="text" name="quotation_request_no" value="{{ old('quotation_request_no', $quotation->quotation_request_no) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div></div>
                    </div>

                    {{-- Unit Under Test --}}
                    </div>
                        <div class="overflow-x-auto pb-2">
                            <table class="table-auto w-full min-w-[1400px] text-xs">
                                <thead class="bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-2 py-1 text-left">Accreditation</th>
                                    <th class="px-2 py-1 text-left">Service Type</th>
                                    <th class="px-2 py-1 text-left">Scope</th>
                                    <th class="px-2 py-1 text-left">Description / Specification</th>
                                    <th class="px-2 py-1 text-left">Range</th>
                                    <th class="px-2 py-1 text-left">CMC</th>
                                    <th class="px-2 py-1 text-left">Method</th>
                                    <th class="px-2 py-1 text-left">Required Env. Condition</th>
                                    <th class="px-2 py-1 text-left">Type</th>
                                    <th class="px-2 py-1 text-left">Qty</th>
                                    <th class="px-2 py-1 text-left">UOM</th>
                                    <th class="px-2 py-1 text-left">Price</th>
                                    <th class="px-2 py-1 text-left">Discount</th>
                                    <th class="px-2 py-1 text-left">Rush Fee</th>
                                    <th class="px-2 py-1 text-left">Lead Time</th>
                                    <th class="px-2 py-1 text-left">Notes</th>
                                    <th class="px-2 py-1"></th>
                                </tr>
                                </thead>
                                <tbody id="uut-body"></tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Other charges --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-md">
                        <div class="px-3 py-2 flex items-center gap-3 border-b border-slate-200">
                            <span class="text-sm font-semibold text-slate-800">Other Charges</span>
                            <button type="button" id="addChargeRow"
                                    class="inline-flex items-center px-3 py-1 rounded bg-emerald-600 text-white text-xs font-semibold">
                                + Add
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead class="bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-2 py-1 text-left">Description / Specification</th>
                                    <th class="px-2 py-1 text-left">Qty</th>
                                    <th class="px-2 py-1 text-left">UOM</th>
                                    <th class="px-2 py-1 text-left">Price</th>
                                    <th class="px-2 py-1 text-left">Total Price</th>
                                    <th class="px-2 py-1"></th>
                                </tr>
                                </thead>
                                <tbody id="charges-body"></tbody>
                                <tfoot class="bg-slate-50">
                                <tr>
                                    <td colspan="6" class="px-2 py-2 text-right text-sm font-semibold text-slate-700">
                                        Charges Total:
                                        <span id="charges-total" class="ml-2 text-emerald-700">0.00</span>
                                        <span class="mx-3 text-slate-400">|</span>
                                        Overall Total (UUT + Charges):
                                        <span id="overall-total" class="ml-2 text-emerald-700">0.00</span>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-3 flex justify-end gap-3 border-t border-slate-100 bg-slate-50 rounded-b-xl">
                    <a href="{{ route('quotations.index') }}"
                       class="px-5 py-2 rounded-full border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100">
                        Review Quotation
                    </a>

                    @if($isApprover && $status === 'for approval')
                        <button type="submit" name="disapprove" value="1"
                                class="px-5 py-2 rounded-full border border-rose-200 text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100">
                            Disapprove
                        </button>
                        <button type="submit" name="approve" value="1"
                                class="px-5 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                            Approve
                        </button>
                    @else
                        @if($isMarketing && in_array($status, ['draft','disapproved']))
                            <button type="submit" name="submit_for_approval" value="1"
                                    class="px-5 py-2 rounded-full border border-emerald-200 text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100">
                                Submit for approval
                            </button>
                        @endif
                        @if($canEdit)
                            <button type="submit"
                                    class="px-5 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                                Save
                            </button>
                        @endif
                    @endif
                </div>
                </fieldset>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const accreditationOptions = [
                { value: 'PAB ISO/IEC 17025:2017', tc: true },
                { value: 'PAB ISO/IEC 17020:2012', tc: true },
                { value: 'PAB ISO/IEC 17020:2012 and NSF Certifier', tc: true },
                { value: 'Traceable', tc: false },
                { value: 'NSF Certifier', tc: true },
                { value: 'Sub-Contract', tc: false },
                { value: 'Add-on', tc: false },
                { value: 'Out of Scope', tc: false },
                { value: 'For Further Evaluation', tc: false },
            ];

            const serviceTypeOptions = ['Calibration', 'Inspection'];
            const testTypeOptions = ['On-site', 'In-house', 'Pullout'];
            const leadTimeOptions = ['3 to 5 working days', '10 to 15 working days', 'Others'];

            const uutBody = document.getElementById('uut-body');
            const addUutBtn = document.getElementById('addUutRow');
            const addBlankBtn = document.getElementById('addBlankRow');
            let uutIndex = 0;

            function optionTags(list, selected = '') {
                return list.map(v => `<option value="${v}" ${selected === v ? 'selected' : ''}>${v}</option>`).join('');
            }

            function buildUutRow(blank = false, prefill = {}) {
                const row = document.createElement('tr');
                row.className = 'uut-row odd:bg-white even:bg-slate-50';
                row.innerHTML = `
                    <td class="px-2 py-1">
                        <select name="uut[${uutIndex}][accreditation]" class="w-full min-w-[140px] border border-slate-300 rounded-md text-xs">
                            <option value="">-</option>
                            ${accreditationOptions.map(o => `<option value="${o.value}" ${prefill.accreditation === o.value ? 'selected' : ''}>${o.value}</option>`).join('')}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <select name="uut[${uutIndex}][service_type]" class="w-full min-w-[120px] border border-slate-300 rounded-md text-xs">
                            <option value="">-</option>
                            ${optionTags(serviceTypeOptions, prefill.service_type)}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][scope]" value="${prefill.scope || ''}" class="w-full min-w-[120px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <textarea name="uut[${uutIndex}][description]" class="w-full min-w-[180px] border border-slate-300 rounded-md text-xs resize-y h-10">${prefill.description || ''}</textarea>
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][range]" value="${prefill.range || ''}" class="w-full min-w-[140px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][cmc]" value="${prefill.cmc || ''}" class="w-full min-w-[140px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][method]" value="${prefill.method || ''}" class="w-full min-w-[140px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][environment]" value="${prefill.environment || ''}" class="w-full min-w-[160px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <select name="uut[${uutIndex}][type]" class="w-full min-w-[120px] border border-slate-300 rounded-md text-xs">
                            <option value="">-</option>
                            ${optionTags(testTypeOptions, prefill.type)}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="1" min="0" name="uut[${uutIndex}][qty]" value="${prefill.qty ?? ''}" class="uut-qty w-full min-w-[90px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][uom]" value="${prefill.uom || ''}" class="w-full min-w-[90px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="0.01" min="0" name="uut[${uutIndex}][price]" value="${prefill.price ?? ''}" class="uut-price w-full min-w-[110px] border border-slate-300 rounded-md text-xs price-field">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="0.01" min="0" name="uut[${uutIndex}][discount]" value="${prefill.discount ?? ''}" class="uut-discount w-full min-w-[110px] border border-slate-300 rounded-md text-xs price-field">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="0.01" min="0" name="uut[${uutIndex}][rush_fee]" value="${prefill.rush_fee ?? ''}" class="uut-rush w-full min-w-[110px] border border-slate-300 rounded-md text-xs price-field">
                    </td>
                    <td class="px-2 py-1">
                        <select name="uut[${uutIndex}][lead_time]" class="w-full min-w-[150px] border border-slate-300 rounded-md text-xs">
                            <option value="">-</option>
                            ${optionTags(leadTimeOptions, prefill.lead_time)}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <textarea name="uut[${uutIndex}][notes]" class="w-full min-w-[140px] border border-slate-300 rounded-md text-xs resize-y">${prefill.notes || ''}</textarea>
                    </td>
                    <td class="px-2 py-1 text-center">
                        <button type="button" class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs delete-row">x</button>
                    </td>
                `;

                row.querySelector('.delete-row').addEventListener('click', () => {
                    row.remove();
                    recalcOverallTotals();
                });
                attachUutRowListeners(row);
                uutBody.appendChild(row);
                uutIndex++;
            }

            function recalcUutTotals() {
                let total = 0;
                uutBody.querySelectorAll('.uut-row').forEach((row) => {
                    const qty = parseFloat(row.querySelector('.uut-qty')?.value || 0);
                    const price = parseFloat(row.querySelector('.uut-price')?.value || 0);
                    const discount = parseFloat(row.querySelector('.uut-discount')?.value || 0);
                    const rush = parseFloat(row.querySelector('.uut-rush')?.value || 0);
                    const line = (qty * price) - discount + rush;
                    total += line > 0 ? line : 0;
                });
                return total;
            }

            function recalcOverallTotals() {
                const uutTotal = recalcUutTotals();
                const chargesTotal = parseFloat(document.getElementById('charges-total')?.textContent || 0);
                const overallEl = document.getElementById('overall-total');
                if (overallEl) overallEl.textContent = (uutTotal + chargesTotal).toFixed(2);
            }

            function attachUutRowListeners(row) {
                row.querySelectorAll('.uut-qty, .uut-price, .uut-discount, .uut-rush').forEach((el) => {
                    el.addEventListener('input', recalcOverallTotals);
                });
            }

            addUutBtn?.addEventListener('click', () => {
                buildUutRow(false);
                recalcOverallTotals();
            });
            addBlankBtn?.addEventListener('click', () => {
                buildUutRow(true);
                recalcOverallTotals();
            });

            const seedUnits = @json($uutRows);
            if (seedUnits.length) {
                seedUnits.forEach(u => buildUutRow(false, u || {}));
            }

            // Other charges
            const chargesBody = document.getElementById('charges-body');
            const addChargeBtn = document.getElementById('addChargeRow');
            let chargeIndex = 0;

            function recalcCharges() {
                let total = 0;
                chargesBody.querySelectorAll('.charge-row').forEach((row) => {
                    const qty = parseFloat(row.querySelector('.charge-qty')?.value || 0);
                    const price = parseFloat(row.querySelector('.charge-price')?.value || 0);
                    const line = qty * price;
                    const totalInput = row.querySelector('.charge-total');
                    if (totalInput) totalInput.value = line ? line.toFixed(2) : '';
                    total += line;
                });
                const totalEl = document.getElementById('charges-total');
                if (totalEl) totalEl.textContent = total.toFixed(2);
                recalcOverallTotals();
            }

            function buildChargeRow(prefill = {}) {
                const row = document.createElement('tr');
                row.className = 'charge-row odd:bg-white even:bg-slate-50';
                row.innerHTML = `
                    <td class="px-2 py-1">
                        <textarea name="charges[${chargeIndex}][description]" class="w-full border border-slate-300 rounded-md text-xs resize-y">${prefill.description || ''}</textarea>
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="1" min="0" name="charges[${chargeIndex}][qty]" value="${prefill.qty ?? ''}" class="w-full border border-slate-300 rounded-md text-xs charge-qty">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="charges[${chargeIndex}][uom]" value="${prefill.uom || ''}" class="w-full border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="0.01" min="0" name="charges[${chargeIndex}][price]" value="${prefill.price ?? ''}" class="w-full border border-slate-300 rounded-md text-xs charge-price">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="0.01" min="0" name="charges[${chargeIndex}][total]" value="${prefill.total ?? ''}" class="w-full border border-slate-300 rounded-md text-xs charge-total" readonly>
                    </td>
                    <td class="px-2 py-1 text-center">
                        <button type="button" class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs delete-charge">x</button>
                    </td>
                `;
                row.querySelector('.delete-charge').addEventListener('click', () => {
                    row.remove();
                    recalcCharges();
                });
                row.querySelectorAll('.charge-qty, .charge-price').forEach((el) => {
                    el.addEventListener('input', recalcCharges);
                });

                chargesBody.appendChild(row);
                chargeIndex++;
            }

            addChargeBtn?.addEventListener('click', () => {
                buildChargeRow({});
                recalcCharges();
            });

            const seedCharges = @json($chargeRows);
            if (seedCharges.length) {
                seedCharges.forEach(c => buildChargeRow(c || {}));
            }
            recalcCharges();
            recalcOverallTotals();

            // Customer autofill
            (function () {
                const select = document.getElementById('customer_select');
                if (!select) return;

                const addressEl = document.getElementById('customer_address');
                const telEl = document.getElementById('customer_telephone');
                const emailEl = document.getElementById('customer_email');
                const tinEl = document.getElementById('customer_tin');
                const paymentTermsEl = document.getElementById('customer_payment_terms');
                const vatSelect = document.getElementById('customer_vat_type');
                const contactPersonSel = document.getElementById('customer_contact_person');
                const customerNameInput = document.getElementById('customer_name_input');

                function rebuildContacts(list, selectedVal = '') {
                    if (!contactPersonSel) return;
                    contactPersonSel.innerHTML = '';
                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = '-- Select contact --';
                    contactPersonSel.appendChild(placeholder);
                    (list || []).forEach((name) => {
                        if (!name) return;
                        const optEl = document.createElement('option');
                        optEl.value = name;
                        optEl.textContent = name;
                        if (selectedVal && selectedVal === name) optEl.selected = true;
                        contactPersonSel.appendChild(optEl);
                    });
                    if (selectedVal && !list?.length) {
                        const optEl = document.createElement('option');
                        optEl.value = selectedVal;
                        optEl.textContent = selectedVal;
                        optEl.selected = true;
                        contactPersonSel.appendChild(optEl);
                    }
                }

                function fillFromOption(opt) {
                    const d = opt.dataset || {};
                    if (addressEl) addressEl.value = d.address || '';
                    if (telEl) telEl.value = d.telephone || '';
                    if (emailEl) emailEl.value = d.email || '';
                    if (tinEl) tinEl.value = d.tin || '';
                    if (paymentTermsEl) paymentTermsEl.value = d.paymentTerms || '';
                    if (vatSelect && d.vatType) {
                        const exists = Array.from(vatSelect.options).some(o => o.value === d.vatType);
                        if (!exists) {
                            const optEl = document.createElement('option');
                            optEl.value = d.vatType;
                            optEl.textContent = d.vatType;
                            vatSelect.appendChild(optEl);
                        }
                        vatSelect.value = d.vatType;
                    }
                    let contacts = [];
                    if (d.contacts) {
                        try { contacts = JSON.parse(d.contacts); } catch (e) { contacts = []; }
                    }
                    if (d.contactPerson && (!contacts || contacts.length === 0)) {
                        contacts = [d.contactPerson];
                    }
                    if (customerNameInput) customerNameInput.value = opt.textContent.trim();
                    const selectedVal = @json(old('contact_person', $quotation->contact_person));
                    rebuildContacts(contacts, selectedVal);
                }

                select.addEventListener('change', function () {
                    const opt = this.options[this.selectedIndex];
                    if (!opt || !opt.value) {
                        if (addressEl) addressEl.value = '';
                        if (telEl) telEl.value = '';
                        if (emailEl) emailEl.value = '';
                        if (tinEl) tinEl.value = '';
                        if (paymentTermsEl) paymentTermsEl.value = '';
                        if (vatSelect) vatSelect.value = '';
                        rebuildContacts([]);
                        return;
                    }
                    fillFromOption(opt);
                });

                // Pre-fill if old value exists
                if (select.value) {
                    const opt = select.options[select.selectedIndex];
                    if (opt) fillFromOption(opt);
                }
            })();
        })();
    </script>
@endpush
