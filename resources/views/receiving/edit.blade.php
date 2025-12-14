{{-- resources/views/receiving/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Receiving - Edit Order')

@section('header')
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Receiving</h2>
    </div>
@endsection

@php
    $isPosted = ($job->status ?? '') === 'posted';
    $currentUserId = auth()->id();
@endphp

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
@php
    $roleString = auth()->user()->roles ?? '';
    $roleList = collect(explode(',', $roleString))->map(fn($r) => trim($r))->filter();
    $isTsr = $roleList->contains('TSRF Approver');
    $isReceivingRole = $roleList->contains('Receiving');
@endphp
    <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if(session('success'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-md bg-rose-50 border border-rose-200 px-4 py-2 text-sm text-rose-800">
                {{ session('error') }}
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

                    <div class="{{ $isPosted ? 'opacity-75 pointer-events-none' : '' }}">
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
                    </div>

@if($isPosted)
                        <div class="bg-slate-50 border border-slate-200 rounded-md">
                            <div class="px-3 py-2 flex items-center justify-between border-b border-slate-200">
                                <span class="text-sm font-semibold text-slate-800">Unit Under Test</span>
                                @if($isReceivingRole)
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="selectAllUutBtn"
                                                class="px-3 py-1.5 rounded border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Select all UUT
                                        </button>
                                        <label class="text-xs font-semibold text-slate-600">Assign selected to:</label>
                                        <select form="batchApproverForm" name="approver_id" class="border border-slate-300 rounded-md text-xs px-2 py-1.5">
                                            <option value="">-- Select TSRF Approver --</option>
                                            @foreach($tsrfApprovers as $ap)
                                                <option value="{{ $ap->id }}">{{ $ap->name ?? (($ap->first_name ?? '').' '.($ap->last_name ?? '')) }} {{ $ap->position ? '('.$ap->position.')' : '' }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" form="batchApproverForm"
                                                class="px-3 py-1.5 rounded bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                                            Submit selected
                                        </button>
                                    </div>
                                @elseif($isTsr)
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="selectAllPersonnelBtn"
                                                class="px-3 py-1.5 rounded border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Select all UUT
                                        </button>
                                        <label class="text-xs font-semibold text-slate-600">Assign UUT(s) to:</label>
                                        <select form="batchPersonnelForm" name="personnel_id" class="border border-slate-300 rounded-md text-xs px-2 py-1.5">
                                            <option value="">-- Select Personnel --</option>
                                            @foreach($technicalUsers as $ap)
                                                <option value="{{ $ap->id }}">{{ $ap->name ?? (($ap->first_name ?? '').' '.($ap->last_name ?? '')) }} {{ $ap->position ? '('.$ap->position.')' : '' }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" form="batchPersonnelForm"
                                                class="px-3 py-1.5 rounded bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                                            Assign selected
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-4 p-3">
                                @forelse($job->units as $unit)
                                    @php
                                        $collapse = ($unit->status ?? 'for_approval') === 'approved' || !empty($unit->assigned_approver_id);
                                        $assignedToMe = $unit->assigned_approver_id && $unit->assigned_approver_id === $currentUserId;
                                    @endphp
                                    <div class="border border-slate-200 rounded-md bg-white shadow-sm">
                                        <div class="flex items-center justify-between px-3 py-2 border-b border-slate-100 bg-slate-50 ref-toggle cursor-pointer" data-target="ref-body-{{ $unit->id }}">
                                            <div class="space-y-0.5">
                                                <div class="text-[11px] uppercase tracking-wide text-slate-500">Reference No.</div>
                                                <div class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                                                    @if($isReceivingRole && ! $unit->assigned_approver_id)
                                                        <input type="checkbox" form="batchApproverForm" name="unit_ids[]" value="{{ $unit->id }}" class="h-4 w-4 border-slate-300 rounded">
                                                    @elseif($isTsr && $unit->assigned_approver_id === $currentUserId && ! $unit->assigned_personnel_id)
                                                        <input type="checkbox" form="batchPersonnelForm" name="unit_ids[]" value="{{ $unit->id }}" class="h-4 w-4 border-slate-300 rounded">
                                                    @endif
                                                    {{ $unit->reference_no ?? 'Pending Ref' }}
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ ($unit->status ?? 'for_approval') === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                        {{ \Illuminate\Support\Str::of($unit->status ?? 'for_approval')->replace('_',' ')->title() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="ref-body-{{ $unit->id }}" class="{{ $collapse ? 'hidden' : '' }} px-3 py-3 space-y-4 uut-card" data-unit-id="{{ $unit->id }}" data-order-date="{{ optional($job->order_date)->format('Y-m-d') }}">
                                                @php
                                                    $receivedBase = $unit->received_date ?? $job->order_date;
                                                    $receivedValue = old('units.'.$unit->id.'.received_date', optional($receivedBase)->format('Y-m-d'));
                                                    $estimatedValue = old('units.'.$unit->id.'.estimated_completion_date');
                                                    $leadRef = $unit->lead_time;
                                                    if (!$estimatedValue) {
                                                    $estCarbon = $unit->estimated_completion_date;
                                                    if (!$estCarbon && $receivedBase) {
                                                        $estCarbon = \Illuminate\Support\Carbon::parse($receivedBase);
                                                        $days = $leadRef === '10 to 15 working days' ? 15 : ($leadRef === '3 to 5 working days' ? 5 : 0);
                                                        $added = 0;
                                                        while ($added < $days) {
                                                            $estCarbon->addDay();
                                                            if ($estCarbon->isWeekend()) {
                                                                continue;
                                                            }
                                                            $added++;
                                                        }
                                                    }
                                                    $estimatedValue = $estCarbon ? $estCarbon->format('Y-m-d') : null;
                                                }
                                                $contactOptions = optional($job->customer)->contactPersons ?? collect();
                                                $selectedContact = old('units.'.$unit->id.'.delivery_contact', $unit->delivery_contact);
                                                @endphp
                                                <input type="hidden" name="units[{{ $unit->id }}][status]" value="{{ $unit->status ?? 'for_approval' }}">
                                                <input type="hidden" name="units[{{ $unit->id }}][action]" class="uut-action-{{ $unit->id }}" value="">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Name</label>
                                                    <textarea name="units[{{ $unit->id }}][name]" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5 resize-y">{{ old('units.'.$unit->id.'.name', $unit->name ?? $unit->description) }}</textarea>
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Method / Procedure</label>
                                                    <input type="text" name="units[{{ $unit->id }}][method_procedure]" value="{{ old('units.'.$unit->id.'.method_procedure', $unit->method_procedure) }}"
                                                           class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                                                </div>
                                            </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Manufacturer</label>
                                <input type="text" name="units[{{ $unit->id }}][manufacturer]" value="{{ old('units.'.$unit->id.'.manufacturer', $unit->manufacturer) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Model</label>
                                <input type="text" name="units[{{ $unit->id }}][model]" value="{{ old('units.'.$unit->id.'.model', $unit->model) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Serial Number</label>
                                <input type="text" name="units[{{ $unit->id }}][serial_number]" value="{{ old('units.'.$unit->id.'.serial_number', $unit->serial_number) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Identification</label>
                                <input type="text" name="units[{{ $unit->id }}][identification]" value="{{ old('units.'.$unit->id.'.identification', $unit->identification) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Indicate recommended due date in certificate/report/sticker?</label>
                                <div class="flex items-center gap-4 text-xs text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="noop_due_date[{{ $unit->id }}]" value="1">
                                        <span>Yes</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="noop_due_date[{{ $unit->id }}]" value="0" checked>
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Service Interval</label>
                                <input type="number" name="units[{{ $unit->id }}][service_interval]" value="{{ old('units.'.$unit->id.'.service_interval', $unit->service_interval) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Service Interval Type</label>
                                <div class="flex items-center gap-4 text-xs text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="units[{{ $unit->id }}][service_interval_type]" value="Month" {{ ($unit->service_interval_type ?? '') === 'Month' ? 'checked' : '' }}>
                                        <span>Month</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="units[{{ $unit->id }}][service_interval_type]" value="Year" {{ ($unit->service_interval_type ?? '') === 'Year' ? 'checked' : '' }}>
                                        <span>Year</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sub-Contracted UUT</label>
                                <div class="flex items-center gap-3 text-xs text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="units[{{ $unit->id }}][sub_contracted]" value="1" {{ old('units.'.$unit->id.'.sub_contracted', $unit->sub_contracted) ? 'checked' : '' }}>
                                        <span>Yes</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="units[{{ $unit->id }}][sub_contracted]" value="0" {{ !old('units.'.$unit->id.'.sub_contracted', $unit->sub_contracted) ? 'checked' : '' }}>
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sub-Contractor</label>
                                <input type="text" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5 bg-slate-100" value="" disabled>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Type</label>
                                <select name="units[{{ $unit->id }}][type]" class="w-full border border-slate-300 rounded-md text-xs type-select">
                                    <option value="">-</option>
                                    <option value="On-site" {{ ($unit->type ?? '') === 'On-site' ? 'selected' : '' }}>On-site</option>
                                    <option value="In-house" {{ ($unit->type ?? '') === 'In-house' ? 'selected' : '' }}>In-house</option>
                                    <option value="Pull-out" {{ ($unit->type ?? '') === 'Pull-out' ? 'selected' : '' }}>Pull-out</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Receiving Method</label>
                                <select name="units[{{ $unit->id }}][receiving_method]" class="w-full border border-slate-300 rounded-md text-xs receiving-method">
                                    <option value="">-</option>
                                    <option value="Walk-in" {{ ($unit->receiving_method ?? '') === 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
                                    <option value="Pick-up" {{ ($unit->receiving_method ?? '') === 'Pick-up' ? 'selected' : '' }}>Pick-up</option>
                                    <option value="Courier" {{ ($unit->receiving_method ?? '') === 'Courier' ? 'selected' : '' }}>Courier</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tracking No.</label>
                                <input type="text" name="units[{{ $unit->id }}][tracking_no]" value="{{ old('units.'.$unit->id.'.tracking_no', $unit->tracking_no) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5 tracking-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lead Time</label>
                                <select name="units[{{ $unit->id }}][lead_time]" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5 lead-time">
                                    <option value="">-</option>
                                    <option value="3 to 5 working days" {{ ($unit->lead_time ?? '') === '3 to 5 working days' ? 'selected' : '' }}>3 to 5 working days</option>
                                    <option value="10 to 15 working days" {{ ($unit->lead_time ?? '') === '10 to 15 working days' ? 'selected' : '' }}>10 to 15 working days</option>
                                    <option value="Others" {{ ($unit->lead_time ?? '') === 'Others' ? 'selected' : '' }}>Others</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Accessories</label>
                                <input type="text" name="units[{{ $unit->id }}][accessories]" value="{{ old('units.'.$unit->id.'.accessories', $unit->accessories) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Received Date</label>
                                <input type="date" name="units[{{ $unit->id }}][received_date]" value="{{ $receivedValue }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5 received-date bg-slate-100" readonly>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Estimated Completion Date</label>
                                <input type="date" name="units[{{ $unit->id }}][estimated_completion_date]" value="{{ $estimatedValue }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5 estimated-date">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Onsite Date</label>
                                <input type="date" name="units[{{ $unit->id }}][onsite_date]" value="{{ old('units.'.$unit->id.'.onsite_date', optional($unit->onsite_date)->format('Y-m-d')) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5 onsite-date bg-slate-100">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Receiving Conditions</label>
                                <input type="text" name="units[{{ $unit->id }}][receiving_condition]" value="{{ old('units.'.$unit->id.'.receiving_condition', $unit->receiving_condition) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5" placeholder="In good condition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-700">
                                <div class="text-[11px] font-semibold text-slate-600 w-full">Does the customer require any statements of conformance?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="units[{{ $unit->id }}][require_conformance]" value="1" {{ old('units.'.$unit->id.'.require_conformance', $unit->require_conformance) ? 'checked' : '' }}>
                                    <span>Yes</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="units[{{ $unit->id }}][require_conformance]" value="0" {{ !old('units.'.$unit->id.'.require_conformance', $unit->require_conformance) ? 'checked' : '' }}>
                                    <span>No</span>
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-700">
                                <div class="text-[11px] font-semibold text-slate-600 w-full">Does the customer require opinions or interpretations based on the results?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="units[{{ $unit->id }}][require_opinion]" value="1" {{ old('units.'.$unit->id.'.require_opinion', $unit->require_opinion) ? 'checked' : '' }}>
                                    <span>Yes</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="units[{{ $unit->id }}][require_opinion]" value="0" {{ !old('units.'.$unit->id.'.require_opinion', $unit->require_opinion) ? 'checked' : '' }}>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-700">
                                <div class="text-[11px] font-semibold text-slate-600 w-full">Does the laboratory plan to deviate from the methods selected?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="units[{{ $unit->id }}][deviate_methods]" value="1" {{ old('units.'.$unit->id.'.deviate_methods', $unit->deviate_methods) ? 'checked' : '' }}>
                                    <span>Yes</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="units[{{ $unit->id }}][deviate_methods]" value="0" {{ !old('units.'.$unit->id.'.deviate_methods', $unit->deviate_methods) ? 'checked' : '' }}>
                                    <span>No</span>
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-700">
                                <div class="text-[11px] font-semibold text-slate-600 w-full">Request New Method</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" class="request-new-method" name="units[{{ $unit->id }}][request_new_method]" value="1" {{ old('units.'.$unit->id.'.request_new_method', $unit->request_new_method) ? 'checked' : '' }}>
                                    <span>Yes</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" class="request-new-method" name="units[{{ $unit->id }}][request_new_method]" value="0" {{ !old('units.'.$unit->id.'.request_new_method', $unit->request_new_method) ? 'checked' : '' }}>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <textarea name="units[{{ $unit->id }}][request_details]" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5 resize-y request-details" placeholder="Insert details here..." {{ $unit->request_new_method ? '' : 'disabled' }}>{{ old('units.'.$unit->id.'.request_details', $unit->request_details) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Delivery method of certificate/report</label>
                                <select name="units[{{ $unit->id }}][delivery_method]" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                                    <option value="">-</option>
                                    <option value="Hand-carry / Hard copy" {{ ($unit->delivery_method ?? '') === 'Hand-carry / Hard copy' ? 'selected' : '' }}>Hand-carry / Hard copy</option>
                                    <option value="Courier" {{ ($unit->delivery_method ?? '') === 'Courier' ? 'selected' : '' }}>Courier</option>
                                    <option value="Customer Portal" {{ ($unit->delivery_method ?? '') === 'Customer Portal' ? 'selected' : '' }}>Customer Portal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                                    Email, PDF (
                                    @if($job->customer_id)
                                        <a href="{{ route('customers.edit', $job->customer_id) }}" target="_blank" class="text-emerald-700 underline">Add Contact</a>
                                    @else
                                        Add Contact
                                    @endif
                                    )
                                </label>
                                <select name="units[{{ $unit->id }}][delivery_contact]" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                                    <option value="">-- Select email --</option>
                                    @foreach($contactOptions as $cp)
                                        @php
                                            $email = $cp->email ?? $cp->email_address ?? $cp->contact_email ?? null;
                                            $contactName = trim(($cp->first_name ?? '').' '.($cp->last_name ?? ''));
                                        @endphp
                                        <option value="{{ $email }}" {{ $selectedContact === $email ? 'selected' : '' }}>
                                            {{ $contactName ?: 'Contact' }}{{ $email ? ' - '.$email : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Special Instructions</label>
                                <input type="text" name="units[{{ $unit->id }}][special_instructions]" value="{{ old('units.'.$unit->id.'.special_instructions', $unit->special_instructions) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-semibold text-slate-600">Test Points / Nominal Values / Parameter</label>
                                    <button type="button" class="h-6 w-6 rounded bg-emerald-500 text-white text-xs font-bold testpoint-add" data-target="tp-{{ $unit->id }}">+</button>
                                </div>
                                <div id="tp-{{ $unit->id }}" class="space-y-2 testpoint-list">
                                    @php
                                        $tpLines = explode("\n", old('units.'.$unit->id.'.test_points', $unit->test_points));
                                        $tpLines = array_filter($tpLines, function ($v) { return $v !== null && $v !== ''; });
                                        if (empty($tpLines)) { $tpLines = ['']; }
                                    @endphp
                                    @foreach($tpLines as $tp)
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="units[{{ $unit->id }}][test_points_list][]" value="{{ $tp }}" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                                            <button type="button" class="h-6 w-6 rounded bg-rose-500 text-white text-xs font-bold tp-remove">x</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Assigned Approver *</label>
                                <select name="units[{{ $unit->id }}][assigned_approver_id]" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5" {{ $unit->assigned_approver_id ? 'disabled' : '' }}>
                                    <option value="">-- Select approver --</option>
                                    @foreach($tsrfApprovers as $ap)
                                        <option value="{{ $ap->id }}" {{ ($unit->assigned_approver_id == $ap->id) ? 'selected' : '' }}>
                                            {{ $ap->name ?? (($ap->first_name ?? '').' '.($ap->last_name ?? '')) }} {{ $ap->position ? '('.$ap->position.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($unit->assigned_approver_id)
                                    <input type="hidden" name="units[{{ $unit->id }}][assigned_approver_id]" value="{{ $unit->assigned_approver_id }}">
                                @endif
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Approver's Remarks</label>
                                <input type="text" name="units[{{ $unit->id }}][approver_remarks]" value="{{ old('units.'.$unit->id.'.approver_remarks', $unit->approver_remarks) }}"
                                       class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                            </div>
                            @if($isTsr && ! $unit->assigned_personnel_id)
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Assigned Personnel</label>
                                    <select name="units[{{ $unit->id }}][assigned_personnel_id]" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                                        <option value="">-- Select personnel --</option>
                                        @foreach($technicalUsers as $ap)
                                            <option value="{{ $ap->id }}" {{ ($unit->assigned_personnel_id == $ap->id) ? 'selected' : '' }}>
                                                {{ $ap->name ?? (($ap->first_name ?? '').' '.($ap->last_name ?? '')) }} {{ $ap->position ? '('.$ap->position.')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            data-unit="{{ $unit->id }}"
                                            class="assign-btn px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-semibold">
                                        Assign
                                    </button>
                                    <button type="button"
                                            data-unit="{{ $unit->id }}"
                                            class="return-btn px-4 py-2 bg-amber-500 text-white rounded-md text-sm font-semibold">
                                        Return
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-slate-100 pt-2">
                            <div class="text-[11px] text-slate-500">
                                Capability: {{ $unit->capability ?? '-' }} | Description: {{ $unit->description ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-slate-500">No unit references yet.</div>
            @endforelse
        </div>
    </div>
@else
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
                        <td class="px-2 py-1 text-center">
                            <button type="button" class="inline-flex items-center justify-center h-7 w-7 rounded bg-rose-500 text-white text-xs delete-row">x</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
                    </div>
                </form>
                @if($isPosted && $isReceivingRole)
                    <form id="batchApproverForm" method="POST" action="{{ route('receiving.assignApproverBatch', $job->id) }}" class="hidden">
                        @csrf
                    </form>
                @endif
                @if($isPosted && $isTsr)
                    <form id="batchPersonnelForm" method="POST" action="{{ route('receiving.assignPersonnelBatch', $job->id) }}" class="hidden">
                        @csrf
                    </form>
                @endif
            </div>
            <div class="pt-2 flex flex-wrap justify-end gap-3 mt-2">
                @unless($isPosted)
                    <form method="POST" action="{{ route('receiving.post', $job->id) }}" class="inline" id="post-form">
                        @csrf
                        <button type="button"
                                class="px-5 py-2 rounded-full bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700"
                                onclick="confirmPost()">
                            Post
                        </button>
                    </form>
                @endunless

                @if($isPosted && ! $isTsr)
                    <form method="POST" action="{{ route('receiving.submitApproval', $job->id) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-5 py-2 rounded-full bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700">
                            Submit for approval
                        </button>
                    </form>
                @endif
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
                const selectAllUutBtn = document.getElementById('selectAllUutBtn');
                const selectAllPersonnelBtn = document.getElementById('selectAllPersonnelBtn');

                if (selectAllUutBtn) {
                    selectAllUutBtn.addEventListener('click', () => {
                        document.querySelectorAll('input[type=\"checkbox\"][form=\"batchApproverForm\"]').forEach((cb) => {
                            cb.checked = true;
                        });
                    });
                }

                if (selectAllPersonnelBtn) {
                    selectAllPersonnelBtn.addEventListener('click', () => {
                        document.querySelectorAll('input[type=\"checkbox\"][form=\"batchPersonnelForm\"]').forEach((cb) => {
                            cb.checked = true;
                        });
                    });
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

                if (uutBody) {
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
                }

                function addWorkingDays(dateStr, days) {
                    if (!dateStr || !days) return null;
                    const date = new Date(dateStr);
                    if (isNaN(date.getTime())) return null;
                    let added = 0;
                    while (added < days) {
                        date.setDate(date.getDate() + 1);
                        const day = date.getDay();
                        if (day === 0 || day === 6) continue;
                        added++;
                    }
                    return date.toISOString().slice(0, 10);
                }

                function computeEstimate(received, lead) {
                    const leadDays = lead === '10 to 15 working days' ? 15 : (lead === '3 to 5 working days' ? 5 : 0);
                    if (!leadDays) return null;
                    return addWorkingDays(received, leadDays);
                }

                function addTpRow(container) {
                    if (!container) return;
                    const name = container.dataset.name || container.querySelector('input')?.getAttribute('name') || '';
                    const row = document.createElement('div');
                    row.className = 'flex items-center gap-2';
                    row.innerHTML = `
                        <input type="text" name="${name}" class="w-full border border-slate-300 rounded-md text-sm px-2 py-1.5">
                        <button type="button" class="h-6 w-6 rounded bg-rose-500 text-white text-xs font-bold tp-remove">x</button>
                    `;
                    const btn = row.querySelector('.tp-remove');
                    btn.addEventListener('click', () => row.remove());
                    container.appendChild(row);
                }

                function setupPostedCard(card) {
                    const orderDate = card.dataset.orderDate;
                    const receivedInput = card.querySelector('.received-date');
                    if (receivedInput && !receivedInput.value && orderDate) {
                        receivedInput.value = orderDate;
                    }

                    const leadSelect = card.querySelector('.lead-time');
                    const estInput = card.querySelector('.estimated-date');
                    const updateEstimate = () => {
                        if (!receivedInput || !estInput || !leadSelect) return;
                        const newVal = computeEstimate(receivedInput.value, leadSelect.value);
                        if (newVal) {
                            estInput.value = newVal;
                        }
                    };
                    leadSelect?.addEventListener('change', updateEstimate);
                    if (estInput && !estInput.value) {
                        updateEstimate();
                    }

                    const typeSelect = card.querySelector('.type-select');
                    const onsiteInput = card.querySelector('.onsite-date');
                    const toggleOnsite = () => {
                        if (!onsiteInput) return;
                        if (typeSelect && typeSelect.value === 'On-site') {
                            onsiteInput.disabled = false;
                            onsiteInput.classList.remove('bg-slate-100');
                        } else {
                            onsiteInput.disabled = true;
                            onsiteInput.value = '';
                            onsiteInput.classList.add('bg-slate-100');
                        }
                    };
                    typeSelect?.addEventListener('change', toggleOnsite);
                    toggleOnsite();

                    const methodSelect = card.querySelector('.receiving-method');
                    const trackingInput = card.querySelector('.tracking-input');
                    const toggleTracking = () => {
                        if (!trackingInput) return;
                        if (methodSelect && methodSelect.value === 'Courier') {
                            trackingInput.disabled = false;
                            trackingInput.classList.remove('bg-slate-100');
                        } else {
                            trackingInput.disabled = true;
                            trackingInput.value = '';
                            trackingInput.classList.add('bg-slate-100');
                        }
                    };
                    methodSelect?.addEventListener('change', toggleTracking);
                    toggleTracking();

                    const requestRadios = card.querySelectorAll('.request-new-method');
                    const details = card.querySelector('.request-details');
                    const toggleDetails = () => {
                        if (!details) return;
                        const value = Array.from(requestRadios).find((r) => r.checked)?.value;
                        const enable = value === '1';
                        details.disabled = !enable;
                        if (!enable) {
                            details.value = '';
                        }
                    };
                    requestRadios.forEach((r) => r.addEventListener('change', toggleDetails));
                    toggleDetails();

                    card.querySelectorAll('.testpoint-list').forEach((list) => {
                        const first = list.querySelector('input');
                        if (first) {
                            list.dataset.name = first.getAttribute('name');
                        }
                        list.querySelectorAll('.tp-remove').forEach((btn) => {
                            btn.addEventListener('click', () => btn.closest('.flex')?.remove());
                        });
                    });
                }

                document.querySelectorAll('.uut-card').forEach((card) => setupPostedCard(card));

                document.querySelectorAll('.testpoint-add').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const target = document.getElementById(btn.dataset.target);
                        addTpRow(target);
                    });
                });

                document.querySelectorAll('.ref-toggle').forEach((header) => {
                    const targetId = header.getAttribute('data-target');
                    const target = document.getElementById(targetId);
                    if (!target) return;
                    header.addEventListener('click', () => {
                        target.classList.toggle('hidden');
                    });
                });

                // Assign / Return actions per unit
                document.querySelectorAll('.assign-btn').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.unit;
                        const actionInput = document.querySelector(`.uut-action-${id}`);
                        const personnel = document.querySelector(`select[name="units[${id}][assigned_personnel_id]"]`);
                        if (personnel && !personnel.value) {
                            alert('Select Assigned Personnel before assigning.');
                            return;
                        }
                        if (actionInput) {
                            actionInput.value = 'assign';
                        }
                        btn.closest('form')?.submit();
                    });
                });

                document.querySelectorAll('.return-btn').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.unit;
                        const actionInput = document.querySelector(`.uut-action-${id}`);
                        const remarks = prompt('Enter remarks for return:');
                        if (remarks !== null) {
                            const remarksInput = document.querySelector(`input[name="units[${id}][approver_remarks]"]`);
                            if (remarksInput) {
                                remarksInput.value = remarks;
                            }
                            if (actionInput) {
                                actionInput.value = 'return';
                            }
                            btn.closest('form')?.submit();
                        }
                    });
                });

                const batchForm = document.getElementById('batchApproverForm');
                if (batchForm) {
                    batchForm.addEventListener('submit', (e) => {
                        const selected = document.querySelectorAll('input[form="batchApproverForm"][name="unit_ids[]"]:checked');
                        if (!selected.length) {
                            e.preventDefault();
                            alert('Select at least one reference to submit.');
                        }
                    });
                }

                const batchPersonnelForm = document.getElementById('batchPersonnelForm');
                if (batchPersonnelForm) {
                    batchPersonnelForm.addEventListener('submit', (e) => {
                        const selected = document.querySelectorAll('input[form="batchPersonnelForm"][name="unit_ids[]"]:checked');
                        if (!selected.length) {
                            e.preventDefault();
                            alert('Select at least one reference to assign.');
                        }
                    });
                }
            })();
        </script>
    @endpush
@endsection
