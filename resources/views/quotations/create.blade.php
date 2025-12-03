{{-- resources/views/quotations/create.blade.php --}}
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
                <span class="font-semibold text-gray-600">New Quotation</span>
            </div>
            <h2 class="mt-1 text-2xl font-semibold text-gray-800">
                New Quotation
            </h2>
        </div>

        <a href="{{ route('quotations.index') }}"
           class="inline-flex items-center px-4 py-1.5 rounded-md border border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50">
            ← Back to list
        </a>
    </div>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('quotations.store') }}">
                @csrf

                {{-- MAIN QUOTATION INFO --}}
                <div class="bg-white border border-gray-200 shadow-sm sm:rounded-md overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 border-b text-sm font-semibold text-gray-700">
                        New Quotation
                    </div>

                    <div class="px-4 py-4 space-y-4">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Ref Number *</label>
                                <input type="text" name="ref_no" value="{{ old('ref_no', $nextRefNo ?? '') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Date *</label>
                                <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
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

                                {{-- Customer select with data-* for autofill --}}
                                <select name="customer_id"
                                        id="customer_select"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                    <option value="">-- Select customer --</option>
                                    @foreach($customers as $customer)
                                        <option
                                            value="{{ $customer->id }}"
                                            data-business-name="{{ $customer->business_name }}"
                                            data-address="{{ $customer->address }}"
                                            data-email="{{ $customer->company_email }}"
                                            data-telephone="{{ $customer->company_phone }}"
                                            data-tin="{{ $customer->tin }}"
                                            data-payment-terms="{{ $customer->payment_terms }}"
                                            data-vat-type="{{ $customer->vat_type }}"
                                            data-contact-person="{{ $customer->primary_contact }}"
                                            @selected(old('customer_id') == $customer->id)
                                        >
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
                                      id="customer_address"
                                      class="w-full border-gray-300 rounded-md text-sm bg-gray-100"
                                      readonly>{{ old('address') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Telephone Number</label>
                                <input type="text" name="telephone"
                                       id="customer_telephone"
                                       value="{{ old('telephone') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">E-mail Address</label>
                                <input type="email" name="email"
                                       id="customer_email"
                                       value="{{ old('email') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">TIN</label>
                                <input type="text" name="tin"
                                       id="customer_tin"
                                       value="{{ old('tin') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Terms</label>
                                <input type="text" name="payment_terms"
                                       id="customer_payment_terms"
                                       value="{{ old('payment_terms') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">VAT Type</label>
                                <select name="vat_type"
                                        id="customer_vat_type"
                                        class="w-full border-gray-300 rounded-md text-sm bg-gray-100"
                                        disabled>
                                    <option value="">--</option>
                                    <option value="VATable">VATable</option>
                                    <option value="VAT Zero Rated">VAT Zero Rated</option>
                                    <option value="Non-VAT">Non-VAT</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Contact Person *</label>
                                {{-- For now we just autofill a single contact name into this select if you want later,
                                     but structure is already here for future per-customer contact list --}}
                                <select name="contact_person_id"
                                        id="customer_contact_person"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                    {{-- You can later populate this with real contacts --}}
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Reference</label>
                                <input type="text" name="reference"
                                       value="{{ old('reference') }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Discount % *</label>
                                <input type="number" step="0.01" name="discount_percent"
                                       value="{{ old('discount_percent', 0) }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Validity Days *</label>
                                <input type="number" name="validity_days"
                                       value="{{ old('validity_days', 30) }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                                <input type="text" name="status"
                                       value="{{ old('status', 'Draft') }}"
                                       class="w-full border-gray-300 rounded-md text-sm bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Quotation Request No.</label>
                                <input type="text" name="request_no"
                                       value="{{ old('request_no') }}"
                                       class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- UNIT UNDER TEST --}}
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
                                <tbody id="uut-rows">
    {{-- Starter row --}}
    <tr>
        {{-- Accreditation --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <select name="uut[0][accreditation]"
                    class="w-full border-gray-300 rounded-md text-[11px] accreditation-select">
            </select>
        </td>

        {{-- Service Type --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <select name="uut[0][service_type]"
                    class="w-full border-gray-300 rounded-md text-[11px] service-type-select">
            </select>
        </td>

        {{-- Scope --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="text" name="uut[0][scope]" class="w-full border-gray-300 rounded-md">
        </td>

        {{-- Description / Specification --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="text" name="uut[0][description]" class="w-full border-gray-300 rounded-md">
        </td>

        {{-- Range --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="text" name="uut[0][range]" class="w-full border-gray-300 rounded-md">
        </td>

        {{-- CMC --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="text" name="uut[0][cmc]" class="w-full border-gray-300 rounded-md">
        </td>

        {{-- Method --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="text" name="uut[0][method]" class="w-full border-gray-300 rounded-md">
        </td>

        {{-- Required Environmental Condition --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="text" name="uut[0][environment]" class="w-full border-gray-300 rounded-md">
        </td>

        {{-- Type (Test Type) --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <select name="uut[0][type]"
                    class="w-full border-gray-300 rounded-md text-[11px] test-type-select">
            </select>
        </td>

        {{-- Qty --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="number" name="uut[0][qty]"
                   class="w-full border-gray-300 rounded-md text-right" value="1" min="1">
        </td>

        {{-- UOM --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="text" name="uut[0][uom]" class="w-full border-gray-300 rounded-md">
        </td>

        {{-- Price --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="number" step="0.01" name="uut[0][price]"
                   class="w-full border-gray-300 rounded-md text-right">
        </td>

        {{-- Discount --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="number" step="0.01" name="uut[0][line_discount]"
                   class="w-full border-gray-300 rounded-md text-right" value="0">
        </td>

        {{-- Rush Fee --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="number" step="0.01" name="uut[0][rush_fee]"
                   class="w-full border-gray-300 rounded-md text-right" value="0">
        </td>

        {{-- Total Price --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="number" step="0.01" name="uut[0][total_price]"
                   class="w-full border-gray-300 rounded-md text-right" readonly>
        </td>

        {{-- Lead Time --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <select name="uut[0][lead_time]"
                    class="w-full border-gray-300 rounded-md text-[11px] lead-time-select">
            </select>
        </td>

        {{-- Notes --}}
        <td class="border-t border-gray-200 px-2 py-1">
            <input type="text" name="uut[0][notes]" class="w-full border-gray-300 rounded-md">
        </td>

        {{-- Actions --}}
        <td class="border-t border-gray-200 px-2 py-1 text-center">
            <button type="button"
                    class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs">
                ×
            </button>
        </td>
    </tr>
</tbody>

                            </thead>
                            <tbody id="uut-rows">
                                {{-- Starter row --}}
                                <tr>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][accreditation]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][service_type]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][scope]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][description]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][range]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][cmc]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][method]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][environment]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][type]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="number" name="uut[0][qty]" class="w-full border-gray-300 rounded-md text-right" value="1" min="1">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][uom]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="number" step="0.01" name="uut[0][price]" class="w-full border-gray-300 rounded-md text-right">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="number" step="0.01" name="uut[0][line_discount]" class="w-full border-gray-300 rounded-md text-right" value="0">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="number" step="0.01" name="uut[0][rush_fee]" class="w-full border-gray-300 rounded-md text-right" value="0">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="number" step="0.01" name="uut[0][total_price]" class="w-full border-gray-300 rounded-md text-right" readonly>
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][lead_time]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1">
                                        <input type="text" name="uut[0][notes]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t border-gray-200 px-2 py-1 text-center">
                                        <button type="button"
                                                class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs">
                                            ×
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- OTHER CHARGES --}}
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
                                <tr>
                                    <td class="border-t px-2 py-1">
                                        <input type="text" name="charges[0][description]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t px-2 py-1">
                                        <input type="number" name="charges[0][qty]" class="w-full border-gray-300 rounded-md text-right" value="1" min="1">
                                    </td>
                                    <td class="border-t px-2 py-1">
                                        <input type="text" name="charges[0][uom]" class="w-full border-gray-300 rounded-md">
                                    </td>
                                    <td class="border-t px-2 py-1">
                                        <input type="number" step="0.01" name="charges[0][price]" class="w-full border-gray-300 rounded-md text-right">
                                    </td>
                                    <td class="border-t px-2 py-1">
                                        <input type="number" step="0.01" name="charges[0][total_price]" class="w-full border-gray-300 rounded-md text-right" readonly>
                                    </td>
                                    <td class="border-t px-2 py-1 text-center">
                                        <button type="button"
                                                class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs">
                                            ×
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-3 text-right text-sm font-semibold text-gray-700">
                            Overall Total:
                            <span id="overall-total">0.00</span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER SAVE BUTTON --}}
                <div class="flex items-center justify-end mt-4">
                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow">
                        💾 Save
                    </button>
                </div>
            </form>
        </div>
    </div>
@push('scripts')
    {{-- existing scripts like customer auto-fill can stay above or below --}}

    <script type="text/javascript">
        (function () {
            // Base arrays exactly like your view-source snippet
            var accreditationOptions = ['<option value=""></option>'],
                serviceTypeOptions   = ['<option value=""></option>'],
                testTypeOptions      = ['<option value=""></option>'],
                leadTimeOptions      = ['<option value=""></option>'];

            // Accreditation
            accreditationOptions.push('<option value="1" data-tc="1">PAB ISO/IEC 17025:2017</option>');
            accreditationOptions.push('<option value="8" data-tc="1">PAB ISO/IEC 17020:2012</option>');
            accreditationOptions.push('<option value="9" data-tc="1">PAB ISO/IEC 17020:2012 and NSF Certifier</option>');
            accreditationOptions.push('<option value="2" data-tc="0">Traceable</option>');
            accreditationOptions.push('<option value="3" data-tc="1">NSF Certifier</option>');
            accreditationOptions.push('<option value="4" data-tc="0">Sub-Contract</option>');
            accreditationOptions.push('<option value="5" data-tc="0">Add-on</option>');
            accreditationOptions.push('<option value="6" data-tc="0">Out of Scope</option>');
            accreditationOptions.push('<option value="7" data-tc="0">For Further Evaluation</option>');

            // Service Type
            serviceTypeOptions.push('<option value="1">Calibration</option>');
            serviceTypeOptions.push('<option value="2">Inspection</option>');

            // Test Type (Type column)
            testTypeOptions.push('<option value="1">On-site</option>');
            testTypeOptions.push('<option value="2">In-house</option>');
            testTypeOptions.push('<option value="4">Pullout</option>');

            // Lead Time
            leadTimeOptions.push('<option value="1">3 to 5 working days</option>');
            leadTimeOptions.push('<option value="2">10 to 15 working days</option>');
            leadTimeOptions.push('<option value="3">Others</option>');

            function populateUUTSelects(context) {
                var root = context || document;

                root.querySelectorAll('.accreditation-select').forEach(function (sel) {
                    sel.innerHTML = accreditationOptions.join('');
                });

                root.querySelectorAll('.service-type-select').forEach(function (sel) {
                    sel.innerHTML = serviceTypeOptions.join('');
                });

                root.querySelectorAll('.test-type-select').forEach(function (sel) {
                    sel.innerHTML = testTypeOptions.join('');
                });

                root.querySelectorAll('.lead-time-select').forEach(function (sel) {
                    sel.innerHTML = leadTimeOptions.join('');
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Populate selects in existing rows on page load
                populateUUTSelects(document);
            });

            // 🔮 Future-proof:
            // If later your "+ Add" button creates new rows via JS, just call:
            //    populateUUTSelects(newRowElement);
            // after appending the row to the table.
        })();
    </script>
@endpush

    @push('scripts')
        <script>
            (function () {
                const select = document.getElementById('customer_select');
                if (!select) return;

                const addressEl        = document.getElementById('customer_address');
                const telEl            = document.getElementById('customer_telephone');
                const emailEl          = document.getElementById('customer_email');
                const tinEl            = document.getElementById('customer_tin');
                const paymentTermsEl   = document.getElementById('customer_payment_terms');
                const vatSelect        = document.getElementById('customer_vat_type');
                const contactPersonSel = document.getElementById('customer_contact_person');

                select.addEventListener('change', function () {
                    const opt = this.options[this.selectedIndex];

                    // No customer selected → clear fields
                    if (!opt || !opt.value) {
                        if (addressEl)      addressEl.value = '';
                        if (telEl)          telEl.value = '';
                        if (emailEl)        emailEl.value = '';
                        if (tinEl)          tinEl.value = '';
                        if (paymentTermsEl) paymentTermsEl.value = '';
                        if (vatSelect)      vatSelect.value = '';
                        if (contactPersonSel) {
                            contactPersonSel.innerHTML = '';
                        }
                        return;
                    }

                    const d = opt.dataset;

                    if (addressEl)      addressEl.value        = d.address        || '';
                    if (telEl)          telEl.value            = d.telephone      || '';
                    if (emailEl)        emailEl.value          = d.email          || '';
                    if (tinEl)          tinEl.value            = d.tin            || '';
                    if (paymentTermsEl) paymentTermsEl.value   = d.paymentTerms   || '';

                    // VAT
                    if (vatSelect && d.vatType) {
                        vatSelect.value = d.vatType;
                    }

                    // Simple contact person handling: put 1 option using primary_contact
                    if (contactPersonSel) {
                        contactPersonSel.innerHTML = '';
                        if (d.contactPerson) {
                            const optEl = document.createElement('option');
                            optEl.value = ''; // you can wire an ID when you have contact persons table
                            optEl.textContent = d.contactPerson;
                            contactPersonSel.appendChild(optEl);
                        }
                    }
                });
            })();
        </script>
    @endpush

</x-app-layout>
