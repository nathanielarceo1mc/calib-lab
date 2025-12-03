{{-- resources/views/receiving/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Receiving - Edit Order')

@section('header')
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Receiving</h2>
    </div>
@endsection

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

@section('content')
    <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if(session('success'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="text-xs text-slate-500">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 hover:underline">Home</a>
            <span class="mx-1">/</span>
            <a href="{{ route('receiving.index') }}" class="hover:text-emerald-600 hover:underline">Receiving</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600 font-semibold">Edit Order</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-800">Edit Order</div>
                    <div class="text-xs text-slate-500">
                        Status:
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-700">
                            {{ ucfirst($job->status ?? 'receiving') }}
                        </span>
                        @if($job->for_approval)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700">For approval</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('receiving.index') }}"
                   class="px-4 py-1.5 rounded-full border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Back to list
                </a>
            </div>

            <div class="p-5">
                <form method="POST" action="{{ route('receiving.update', $job->id) }}" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Order Number *</label>
                            <input name="order_number" value="{{ old('order_number', $job->order_number) }}" readonly
                                   class="w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Date *</label>
                            <input type="date" name="order_date" value="{{ old('order_date', optional($job->order_date)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Customer *</label>
                            <select name="customer_id" id="customerSelect"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ (old('customer_id', $job->customer_id) == $customer->id) ? 'selected' : '' }}>
                                        {{ $customer->business_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Address *</label>
                            <textarea name="address" id="customerAddress"
                                      class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm resize-y min-h-[60px]">{{ old('address', $job->address) }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Telephone Number</label>
                                <input type="text" name="telephone" id="customerTelephone"
                                       value="{{ old('telephone', $job->telephone) }}"
                                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">E-mail Address</label>
                                <input type="email" name="email" id="customerEmail"
                                       value="{{ old('email', $job->email) }}"
                                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Person *</label>
                            <input type="text" name="contact_person" id="contactPersonInput"
                                   value="{{ old('contact_person', $job->contact_person) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Designation</label>
                            <input type="text" name="designation" id="contactDesignation"
                                   value="{{ old('designation', $job->designation) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Use Details From *</label>
                            <select name="use_details_from" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="customer" {{ old('use_details_from', 'customer') === 'customer' ? 'selected' : '' }}>Customer Only</option>
                                <option value="end_user" {{ old('use_details_from') === 'end_user' ? 'selected' : '' }}>End-User Only</option>
                                <option value="customer_end_user" {{ old('use_details_from') === 'customer_end_user' ? 'selected' : '' }}>Customer & End-User</option>
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
                                   value="{{ old('po_number', $job->po_number) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">PO File *</label>
                            <input type="file" name="po_file" class="w-full text-sm">
                            @if($job->po_file_path)
                                <div class="mt-1 text-xs text-slate-500">Current: {{ $job->po_file_path }}</div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Discount %</label>
                            <input type="number" step="0.01" name="discount_percent"
                                   value="{{ old('discount_percent', $job->discount_percent) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Quotation No.</label>
                            <input type="text" name="quotation_number"
                                   value="{{ old('quotation_number', $job->quotation_number) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Remarks</label>
                            <textarea name="notes"
                                      class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm resize-y min-h-[60px]">{{ old('notes', $job->notes) }}</textarea>
                        </div>
                    </div>

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
                                @php $oldUut = old('uut', []); @endphp
                                @foreach(($oldUut ?: $job->units) as $i => $unit)
                                    @php
                                        $acc = is_array($unit) ? ($unit['accreditation'] ?? null) : $unit->accreditation;
                                        $svc = is_array($unit) ? ($unit['service_type'] ?? null) : $unit->service_type;
                                        $cap = is_array($unit) ? ($unit['capability'] ?? null) : $unit->capability;
                                        $desc = is_array($unit) ? ($unit['description'] ?? null) : $unit->description;
                                        $type = is_array($unit) ? ($unit['type'] ?? null) : $unit->type;
                                        $rm = is_array($unit) ? ($unit['receiving_method'] ?? null) : $unit->receiving_method;
                                        $qty = is_array($unit) ? ($unit['qty'] ?? null) : $unit->qty;
                                        $unt = is_array($unit) ? ($unit['unit'] ?? null) : $unit->unit;
                                        $lt = is_array($unit) ? ($unit['lead_time'] ?? null) : $unit->lead_time;
                                        $rf = is_array($unit) ? ($unit['rush_fee'] ?? null) : $unit->rush_fee;
                                        $disc = is_array($unit) ? ($unit['discount'] ?? null) : $unit->discount;
                                    @endphp
                                    <tr class="odd:bg-white even:bg-slate-50">
                                        <td class="px-2 py-1">
                                            <select name="uut[{{ $i }}][accreditation]" class="w-full border border-slate-300 rounded-md text-xs">
                                                <option value="">-</option>
                                                <option value="PAB ISO/IEC 17025:2017" {{ $acc === 'PAB ISO/IEC 17025:2017' ? 'selected' : '' }}>PAB ISO/IEC 17025:2017</option>
                                                <option value="PAB ISO/IEC 17020:2012" {{ $acc === 'PAB ISO/IEC 17020:2012' ? 'selected' : '' }}>PAB ISO/IEC 17020:2012</option>
                                                <option value="Traceable" {{ $acc === 'Traceable' ? 'selected' : '' }}>Traceable</option>
                                                <option value="NSF Certifier" {{ $acc === 'NSF Certifier' ? 'selected' : '' }}>NSF Certifier</option>
                                                <option value="Sub-Contract" {{ $acc === 'Sub-Contract' ? 'selected' : '' }}>Sub-Contract</option>
                                                <option value="Add-on" {{ $acc === 'Add-on' ? 'selected' : '' }}>Add-on</option>
                                                <option value="Out of Scope" {{ $acc === 'Out of Scope' ? 'selected' : '' }}>Out of Scope</option>
                                                <option value="For Further Evaluation" {{ $acc === 'For Further Evaluation' ? 'selected' : '' }}>For Further Evaluation</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-1">
                                            <select name="uut[{{ $i }}][service_type]" class="w-full border border-slate-300 rounded-md text-xs">
                                                <option value="">-</option>
                                                <option value="Calibration" {{ $svc === 'Calibration' ? 'selected' : '' }}>Calibration</option>
                                                <option value="Inspection" {{ $svc === 'Inspection' ? 'selected' : '' }}>Inspection</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-1">
                                            <textarea name="uut[{{ $i }}][capability]" class="w-full border border-slate-300 rounded-md text-xs resize-y">{{ $cap }}</textarea>
                                        </td>
                                        <td class="px-2 py-1">
                                            <textarea name="uut[{{ $i }}][description]" class="w-full border border-slate-300 rounded-md text-xs resize-y">{{ $desc }}</textarea>
                                        </td>
                                        <td class="px-2 py-1">
                                            <select name="uut[{{ $i }}][type]" class="w-full border border-slate-300 rounded-md text-xs uut-type">
                                                <option value="">-</option>
                                                <option value="On-site" {{ $type === 'On-site' ? 'selected' : '' }}>On-site</option>
                                                <option value="In-house" {{ $type === 'In-house' ? 'selected' : '' }}>In-house</option>
                                                <option value="Pull-out" {{ $type === 'Pull-out' ? 'selected' : '' }}>Pull-out</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-1">
                                            <select name="uut[{{ $i }}][receiving_method]" class="w-full border border-slate-300 rounded-md text-xs receiving-method" {{ $type === 'In-house' ? '' : 'disabled' }}>
                                                <option value="">-</option>
                                                <option value="Walk-in" {{ $rm === 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
                                                <option value="Pick-up" {{ $rm === 'Pick-up' ? 'selected' : '' }}>Pick-up</option>
                                                <option value="Courier" {{ $rm === 'Courier' ? 'selected' : '' }}>Courier</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][qty]" class="w-full border border-slate-300 rounded-md text-xs"
                                                   value="{{ $qty }}">
                                        </td>
                                        <td class="px-2 py-1">
                                            <input type="text" name="uut[{{ $i }}][unit]" class="w-full border border-slate-300 rounded-md text-xs"
                                                   value="{{ $unt }}">
                                        </td>
                                        <td class="px-2 py-1">
                                            <select name="uut[{{ $i }}][lead_time]" class="w-full border border-slate-300 rounded-md text-xs">
                                                <option value="">-</option>
                                                <option value="3 to 5 working days" {{ $lt === '3 to 5 working days' ? 'selected' : '' }}>3 to 5 working days</option>
                                                <option value="10 to 15 working days" {{ $lt === '10 to 15 working days' ? 'selected' : '' }}>10 to 15 working days</option>
                                                <option value="Others" {{ $lt === 'Others' ? 'selected' : '' }}>Others</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-1">
                                            <input type="number" step="0.01" name="uut[{{ $i }}][rush_fee]" class="w-full border border-slate-300 rounded-md text-xs"
                                                   value="{{ $rf }}">
                                        </td>
                                        <td class="px-2 py-1">
                                            <input type="number" step="0.01" name="uut[{{ $i }}][discount]" class="w-full border border-slate-300 rounded-md text-xs"
                                                   value="{{ $disc }}">
                                        </td>
                                        <td class="px-2 py-1 text-center">
                                            <button type="button" class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs delete-row">x</button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="pt-2 flex flex-wrap justify-end gap-3">
                        <a href="{{ route('receiving.index') }}"
                           class="px-4 py-2 rounded-full border border-slate-300 text-sm text-slate-700 hover:bg-slate-50">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-5 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                            Save
                        </button>

                        <form method="POST" action="{{ route('receiving.post', $job->id) }}" class="inline" id="post-form">
                            @csrf
                            <button type="button"
                                    class="px-5 py-2 rounded-full bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700"
                                    onclick="confirmPost()">
                                Post
                            </button>
                        </form>

                        @if(($job->status ?? '') === 'posted')
                            <form method="POST" action="{{ route('receiving.submitApproval', $job->id) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-5 py-2 rounded-full bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700">
                                    Submit for approval
                                </button>
                            </form>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmPost() {
                if (confirm('Are you sure you want to post this record?')) {
                    document.getElementById('post-form').submit();
                }
            }

            (function () {
                const customers = @json($customerData);
                const customerSelect = document.getElementById('customerSelect');
                const endUserSelect = document.getElementById('endUserSelect');
                const addr = document.getElementById('customerAddress');
                const tel = document.getElementById('customerTelephone');
                const email = document.getElementById('customerEmail');
                const designation = document.getElementById('contactDesignation');
                const contactInput = document.getElementById('contactPersonInput');

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

                function fillCustomer(customerId) {
                    const data = customers.find((c) => c.id == customerId);
                    if (!data) {
                        rebuildSelect(endUserSelect, [], '-- Select end-user --');
                        return;
                    }
                    addr.value = data.address || '';
                    tel.value = data.telephone || '';
                    email.value = data.email || '';
                    rebuildSelect(endUserSelect, data.end_users || [], '-- Select end-user --');
                    if (data.contacts && data.contacts.length > 0) {
                        contactInput.value = data.contacts[0].name || '';
                        designation.value = data.contacts[0].designation || '';
                    }
                }

                customerSelect?.addEventListener('change', (e) => {
                    fillCustomer(e.target.value);
                });

                if (customerSelect?.value) {
                    fillCustomer(customerSelect.value);
                    if ("{{ old('end_user', $job->end_user) }}") {
                        endUserSelect.value = "{{ old('end_user', $job->end_user) }}";
                    }
                }

                const uutBody = document.getElementById('uut-body');
                const addBtn = document.getElementById('addUutRow');
                let uutIndex = uutBody.children.length;

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

                document.querySelectorAll('.uut-type').forEach((typeSelect) => {
                    const receivingSelect = typeSelect.closest('tr').querySelector('.receiving-method');
                    const toggle = () => {
                        if (typeSelect.value === 'In-house') {
                            receivingSelect.disabled = false;
                        } else {
                            receivingSelect.disabled = true;
                            receivingSelect.value = '';
                        }
                    };
                    typeSelect.addEventListener('change', toggle);
                    toggle();
                });

                if (uutBody.children.length === 0) {
                    addUutRow();
                }
            })();
        </script>
    @endpush
@endsection
