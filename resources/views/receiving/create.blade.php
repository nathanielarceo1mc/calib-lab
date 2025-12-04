{{-- resources/views/receiving/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Receiving - New Order')

@section('header')
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Receiving</h2>
    </div>
@endsection

@section('content')
    <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
        <div class="text-xs text-slate-500 mb-4">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 hover:underline">Home</a>
            <span class="mx-1">/</span>
            <a href="{{ route('receiving.index') }}" class="hover:text-emerald-600 hover:underline">Receiving</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600 font-semibold">New Order</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800">New Order</h3>
                <a href="{{ route('receiving.index') }}"
                   class="px-4 py-1.5 rounded-full border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Back to list
                </a>
            </div>

            <div class="p-5">
                <form method="POST" action="{{ route('receiving.store') }}" class="space-y-6" enctype="multipart/form-data" id="receiving-form">
                    @csrf

                    {{-- HEADER DETAILS --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Order Number *</label>
                            <input name="order_number" value="{{ $newOrderNumber }}" readonly
                                   class="w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Date *</label>
                            <input type="date" name="order_date" value="{{ old('order_date', now()->toDateString()) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Customer *</label>
                            <select name="customer_id" id="customerSelect"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->business_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Address *</label>
                            <textarea name="address" id="customerAddress"
                                      class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm resize-y min-h-[60px]">{{ old('address') }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Telephone Number</label>
                                <input type="text" name="telephone" id="customerTelephone"
                                       value="{{ old('telephone') }}"
                                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">E-mail Address</label>
                                <input type="email" name="email" id="customerEmail"
                                       value="{{ old('email') }}"
                                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Person *</label>
                            <select name="contact_person" id="contactPersonSelect"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Select contact --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Designation</label>
                            <input type="text" name="designation" id="contactDesignation"
                                   value="{{ old('designation') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Use Details From *</label>
                            <select name="use_details_from" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="customer">Customer Only</option>
                                <option value="end_user">End-User Only</option>
                                <option value="customer_end_user">Customer & End-User</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">End-User</label>
                            <select name="end_user" id="endUserSelect"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Select end-user --</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">PO No. *</label>
                            <input type="text" name="po_number"
                                   value="{{ old('po_number') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">PO File *</label>
                            <input type="file" name="po_file" id="poFileInput"
                                   class="w-full text-sm">
                            <label class="inline-flex items-center gap-2 mt-2 text-xs text-slate-600">
                                <input type="checkbox" name="to_follow_po" id="toFollowPo" value="1">
                                <span>To follow PO</span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Discount %</label>
                            <input type="number" step="0.01" name="discount_percent"
                                   value="{{ old('discount_percent', 0) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Quotation No.</label>
                            <input type="text" name="quotation_number"
                                   value="{{ old('quotation_number') }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Remarks</label>
                            <textarea name="notes"
                                      class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm resize-y min-h-[60px]">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- UNIT UNDER TEST --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-md">
                        <div class="px-3 py-2 flex items-center justify-between border-b border-slate-200">
                            <span class="text-sm font-semibold text-slate-800">Unit Under Test</span>
                            <button type="button" id="addUutRow"
                                    class="inline-flex items-center px-3 py-1 rounded bg-emerald-600 text-white text-xs font-semibold">
                                + Add
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead class="bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-2 py-1 text-left">Accreditation</th>
                                    <th class="px-2 py-1 text-left">Service Type</th>
                                    <th class="px-2 py-1 text-left">Capability</th>
                                    <th class="px-2 py-1 text-left">Description / Specification / Calibration</th>
                                    <th class="px-2 py-1 text-left">Type</th>
                                    <th class="px-2 py-1 text-left">Receiving Method</th>
                                    <th class="px-2 py-1 text-left">Qty</th>
                                    <th class="px-2 py-1 text-left">Unit</th>
                                    <th class="px-2 py-1 text-left">Lead Time</th>
                                    <th class="px-2 py-1 text-left">Rush Fee</th>
                                    <th class="px-2 py-1 text-left">Discount</th>
                                    <th class="px-2 py-1"></th>
                                </tr>
                                </thead>
                                <tbody id="uut-body">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end gap-3">
                        <a href="{{ route('receiving.index') }}"
                           class="px-4 py-2 rounded-full border border-slate-300 text-sm text-slate-700 hover:bg-slate-50">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-5 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $customerData = $customers
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->business_name,
                    'address' => $c->address,
                    'telephone' => $c->company_phone,
                    'email' => $c->company_email,
                    'contacts' => $c->contactPersons
                        ->map(function ($p) {
                            return [
                                'id' => $p->id,
                                'name' => trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')),
                                'designation' => $p->position,
                            ];
                        })
                        ->values(),
                    'end_users' => $c->endUsers
                        ->map(function ($u) {
                            return [
                                'id' => $u->id,
                                'name' => $u->name,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    @endphp

    @push('scripts')
        <script>
            (function () {
                const customers = @json($customerData);

                const customerSelect = document.getElementById('customerSelect');
                const contactSelect = document.getElementById('contactPersonSelect');
                const endUserSelect = document.getElementById('endUserSelect');
                const addr = document.getElementById('customerAddress');
                const tel = document.getElementById('customerTelephone');
                const email = document.getElementById('customerEmail');
                const designation = document.getElementById('contactDesignation');
                const poFile = document.getElementById('poFileInput');
                const toFollow = document.getElementById('toFollowPo');

                function fillCustomer(customerId) {
                    const data = customers.find((c) => c.id == customerId);
                    if (!data) {
                        addr.value = '';
                        tel.value = '';
                        email.value = '';
                        rebuildSelect(contactSelect, []);
                        rebuildSelect(endUserSelect, []);
                        designation.value = '';
                        return;
                    }

                    addr.value = data.address || '';
                    tel.value = data.telephone || '';
                    email.value = data.email || '';
                    rebuildSelect(contactSelect, data.contacts || [], '-- Select contact --');
                    rebuildSelect(endUserSelect, data.end_users || [], '-- Select end-user --');
                }

                function rebuildSelect(select, items, placeholder = '') {
                    select.innerHTML = '';
                    if (placeholder) {
                        const opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = placeholder;
                        select.appendChild(opt);
                    }
                    items.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.name || item.id;
                        opt.textContent = item.name || '';
                        opt.dataset.designation = item.designation || '';
                        select.appendChild(opt);
                    });
                }

                customerSelect?.addEventListener('change', (e) => {
                    fillCustomer(e.target.value);
                });

                contactSelect?.addEventListener('change', (e) => {
                    const sel = e.target.selectedOptions[0];
                    designation.value = sel ? sel.dataset.designation || '' : '';
                });

                toFollow?.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        poFile.removeAttribute('required');
                    } else {
                        poFile.setAttribute('required', 'required');
                    }
                });
                // initialize required state
                if (poFile && !toFollow?.checked) {
                    poFile.setAttribute('required', 'required');
                }

                // UUT repeater
                const uutBody = document.getElementById('uut-body');
                const addBtn = document.getElementById('addUutRow');
                let uutIndex = 0;

                function addUutRow() {
                    const row = document.createElement('tr');
                    row.className = 'odd:bg-white even:bg-slate-50';
                    row.innerHTML = `
                        <td class="px-2 py-1">
                            <select name="uut[${uutIndex}][accreditation]" class="w-full border border-slate-300 rounded-md text-xs">
                                <option value="">-</option>
                                <option>PAB ISO/IEC 17025:2017</option>
                                <option>PAB ISO/IEC 17020:2012</option>
                                <option>Traceable</option>
                                <option>NSF Certifier</option>
                                <option>Sub-Contract</option>
                                <option>Add-on</option>
                                <option>Out of Scope</option>
                                <option>For Further Evaluation</option>
                            </select>
                        </td>
                        <td class="px-2 py-1">
                            <select name="uut[${uutIndex}][service_type]" class="w-full border border-slate-300 rounded-md text-xs">
                                <option value="">-</option>
                                <option>Calibration</option>
                                <option>Inspection</option>
                            </select>
                        </td>
                        <td class="px-2 py-1">
                            <textarea name="uut[${uutIndex}][capability]" class="w-full border border-slate-300 rounded-md text-xs resize-y"></textarea>
                        </td>
                        <td class="px-2 py-1">
                            <textarea name="uut[${uutIndex}][description]" class="w-full border border-slate-300 rounded-md text-xs resize-y"></textarea>
                        </td>
                        <td class="px-2 py-1">
                            <select name="uut[${uutIndex}][type]" class="w-full border border-slate-300 rounded-md text-xs uut-type">
                                <option value="">-</option>
                                <option>On-site</option>
                                <option>In-house</option>
                                <option>Pull-out</option>
                            </select>
                        </td>
                        <td class="px-2 py-1">
                            <select name="uut[${uutIndex}][receiving_method]" class="w-full border border-slate-300 rounded-md text-xs receiving-method" disabled>
                                <option value="">-</option>
                                <option>Walk-in</option>
                                <option>Pick-up</option>
                                <option>Courier</option>
                            </select>
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="uut[${uutIndex}][qty]" class="w-full border border-slate-300 rounded-md text-xs">
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="uut[${uutIndex}][unit]" class="w-full border border-slate-300 rounded-md text-xs">
                        </td>
                        <td class="px-2 py-1">
                            <select name="uut[${uutIndex}][lead_time]" class="w-full border border-slate-300 rounded-md text-xs">
                                <option value="">-</option>
                                <option>3 to 5 working days</option>
                                <option>10 to 15 working days</option>
                                <option>Others</option>
                            </select>
                        </td>
                        <td class="px-2 py-1">
                            <input type="number" step="0.01" name="uut[${uutIndex}][rush_fee]" class="w-full border border-slate-300 rounded-md text-xs">
                        </td>
                        <td class="px-2 py-1">
                            <input type="number" step="0.01" name="uut[${uutIndex}][discount]" class="w-full border border-slate-300 rounded-md text-xs">
                        </td>
                        <td class="px-2 py-1 text-center">
                            <button type="button" class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs delete-row">x</button>
                        </td>
                    `;
                    uutBody.appendChild(row);

                    const typeSelect = row.querySelector('.uut-type');
                    const receivingSelect = row.querySelector('.receiving-method');

                    typeSelect.addEventListener('change', () => {
                        if (typeSelect.value === 'In-house') {
                            receivingSelect.disabled = false;
                        } else {
                            receivingSelect.disabled = true;
                            receivingSelect.value = '';
                        }
                    });

                    row.querySelector('.delete-row').addEventListener('click', () => row.remove());
                    uutIndex++;
                }

                addBtn?.addEventListener('click', addUutRow);
                // start with one row
                addUutRow();
            })();
        </script>
    @endpush
@endsection



















