{{-- resources/views/quotations/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Quotation - New Quotation')

@section('header')
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Quotation</h2>
    </div>
@endsection

@section('content')
    <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <div class="text-xs text-slate-500 mb-4">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 hover:underline">Home</a>
            <span class="mx-1">/</span>
            <a href="{{ route('quotations.index') }}" class="hover:text-emerald-600 hover:underline">Quotation</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600 font-semibold">New Quotation</span>
        </div>

        <form method="POST" action="{{ route('quotations.store') }}" id="quotation-form">
            @csrf

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100 text-sm font-semibold text-slate-800">
                    New Quotation
                </div>

                <div class="p-5 space-y-6">
                    {{-- Header fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Ref Number *</label>
                            <input type="text" name="quote_no" value="{{ old('quote_no', $nextRefNo ?? '') }}"
                                   class="w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Date *</label>
                            <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">
                                Customer *
                                <a href="{{ route('customers.create') }}" class="ml-1 text-xs text-sky-600 hover:underline">Add Customer</a>
                            </label>
                            <input type="hidden" name="customer_name" id="customer_name_input" value="{{ old('customer_name') }}">
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
                                            @selected(old('customer_id') == $customer->id)>
                                        {{ $customer->business_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Address *</label>
                        <textarea name="address" rows="2" id="customer_address"
                                  class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-slate-50"
                                  readonly>{{ old('address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Telephone Number</label>
                            <input type="text" name="telephone" id="customer_telephone"
                                   value="{{ old('telephone') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-slate-50" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">E-mail Address</label>
                            <input type="email" name="email" id="customer_email"
                                   value="{{ old('email') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-slate-50" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">TIN</label>
                            <input type="text" name="tin" id="customer_tin"
                                   value="{{ old('tin') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-slate-50" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Terms</label>
                            <input type="text" name="payment_terms" id="customer_payment_terms"
                                   value="{{ old('payment_terms') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-slate-50" readonly>
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
                                @if(old('contact_person'))
                                    <option value="{{ old('contact_person') }}">{{ old('contact_person') }}</option>
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
                                   value="{{ old('discount_percent', 0) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Validity Days *</label>
                            <input type="number" name="validity_days" value="{{ old('validity_days', 30) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                            <input type="text" name="status" value="{{ old('status', 'Draft') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-slate-50" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Quotation Request No.</label>
                            <input type="text" name="quotation_request_no" value="{{ old('quotation_request_no') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div></div>
                    </div>

                    {{-- Unit Under Test --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-md">
                        <div class="px-3 py-2 flex items-center gap-3 border-b border-slate-200">
                            <span class="text-sm font-semibold text-slate-800">Unit Under Test</span>
                            <button type="button" id="addUutRow"
                                    class="inline-flex items-center px-3 py-1 rounded bg-emerald-600 text-white text-xs font-semibold">
                                + Add
                            </button>
                            <button type="button" id="addBlankRow"
                                    class="inline-flex items-center px-3 py-1 rounded bg-slate-600 text-white text-xs font-semibold">
                                + Blank
                            </button>
                        </div>
                        <div class="overflow-x-auto pb-2">
                            <table class="w-full min-w-[1400px] text-xs">
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

                <div class="px-5 py-3 flex justify-end border-t border-slate-100 bg-slate-50 rounded-b-xl">
                    <button type="submit"
                            class="px-5 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                        Save
                    </button>
                </div>
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
            const seedUnits = @json(old('uut', []));

            function optionTags(list) {
                return list.map(v => `<option value=\"${v}\">${v}</option>`).join('');
            }

            function buildUutRow(blank = false) {
                const row = document.createElement('tr');
                row.className = 'uut-row odd:bg-white even:bg-slate-50';
                row.innerHTML = `
                    <td class="px-2 py-1">
                        <select name="uut[${uutIndex}][accreditation]" class="w-full border border-slate-300 rounded-md text-xs">
                            <option value="">-</option>
                            ${accreditationOptions.map(o => `<option value="${o.value}">${o.value}</option>`).join('')}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <select name="uut[${uutIndex}][service_type]" class="w-full border border-slate-300 rounded-md text-xs">
                            <option value="">-</option>
                            ${optionTags(serviceTypeOptions)}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][scope]" class="w-full border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <textarea name="uut[${uutIndex}][description]" class="w-full min-w-[180px] border border-slate-300 rounded-md text-xs resize-y h-10"></textarea>
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][range]" class="w-full min-w-[140px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][cmc]" class="w-full min-w-[140px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][method]" class="w-full min-w-[140px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][environment]" class="w-full min-w-[160px] border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <select name="uut[${uutIndex}][type]" class="w-full border border-slate-300 rounded-md text-xs">
                            <option value="">-</option>
                            ${optionTags(testTypeOptions)}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="1" min="0" name="uut[${uutIndex}][qty]" class="uut-qty w-full border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="uut[${uutIndex}][uom]" class="w-full border border-slate-300 rounded-md text-xs">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="0.01" min="0" name="uut[${uutIndex}][price]" class="uut-price w-full border border-slate-300 rounded-md text-xs price-field">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="0.01" min="0" name="uut[${uutIndex}][discount]" class="uut-discount w-full border border-slate-300 rounded-md text-xs price-field">
                    </td>
                    <td class="px-2 py-1">
                        <input type="number" step="0.01" min="0" name="uut[${uutIndex}][rush_fee]" class="uut-rush w-full border border-slate-300 rounded-md text-xs price-field">
                    </td>
                    <td class="px-2 py-1">
                        <select name="uut[${uutIndex}][lead_time]" class="w-full border border-slate-300 rounded-md text-xs">
                            <option value="">-</option>
                            ${optionTags(leadTimeOptions)}
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <textarea name="uut[${uutIndex}][notes]" class="w-full border border-slate-300 rounded-md text-xs resize-y"></textarea>
                    </td>
                    <td class="px-2 py-1 text-center">
                        <button type="button" class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs delete-row">x</button>
                    </td>
                `;

                row.querySelector('.delete-row').addEventListener('click', () => row.remove());
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
                attachUutRowListeners(uutBody.lastElementChild);
                recalcOverallTotals();
            });
            addBlankBtn?.addEventListener('click', () => {
                buildUutRow(true);
                attachUutRowListeners(uutBody.lastElementChild);
                recalcOverallTotals();
            });

            if (seedUnits.length) {
                seedUnits.forEach(u => {
                    buildUutRow(false);
                    const row = uutBody.lastElementChild;
                    if (!row) return;
            const fields = ['accreditation','service_type','scope','description','range','cmc','method','environment','type','qty','uom','price','discount','rush_fee','lead_time','notes'];
                    fields.forEach((f) => {
                        const el = row.querySelector(`[name=\"uut[${uutIndex-1}][${f}]\"]`);
                        if (el) el.value = u?.[f] ?? '';
                    });
                    attachUutRowListeners(row);
                });
            }
            recalcOverallTotals();

            // Other charges
            const chargesBody = document.getElementById('charges-body');
            const addChargeBtn = document.getElementById('addChargeRow');
            let chargeIndex = 0;
            const seedCharges = @json(old('charges', []));

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

            addChargeBtn?.addEventListener('click', buildChargeRow);
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
                const customerNameInput = document.getElementById('customer_name_input');
                const contactPersonSel = document.getElementById('customer_contact_person');

                function rebuildContacts(list) {
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
                        contactPersonSel.appendChild(optEl);
                    });
                }

                function ensureOption(select, value) {
                    if (!select || !value) return;
                    const exists = Array.from(select.options).some(o => o.value === value);
                    if (!exists) {
                        const optEl = document.createElement('option');
                        optEl.value = value;
                        optEl.textContent = value;
                        select.appendChild(optEl);
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
                        ensureOption(vatSelect, d.vatType);
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
                    rebuildContacts(contacts);
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
