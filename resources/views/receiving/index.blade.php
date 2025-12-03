@extends('layouts.app')

@section('title', 'Receiving')

@section('header')
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Receiving</h2>
    </div>
@endsection

@section('content')
    <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-3 text-xs text-slate-500">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 hover:underline">Home</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600 font-semibold">Receiving</span>
        </div>

        {{-- Filters + Create button --}}
        <div class="flex items-center gap-3 mt-2 mb-4">

            {{-- All --}}
            <a href="{{ route('receiving.index', ['filter' => 'all']) }}"
               class="px-4 py-1.5 rounded-full text-xs font-semibold
                      {{ ($activeFilter ?? 'all') === 'all'
                            ? 'bg-emerald-500 text-white'
                            : 'bg-gray-200 text-gray-600' }}">
                All
            </a>

            {{-- For Approval --}}
            <a href="{{ route('receiving.index', ['filter' => 'for-approval']) }}"
               class="px-4 py-1.5 rounded-full text-xs font-medium
                      {{ ($activeFilter ?? '') === 'for-approval'
                            ? 'bg-emerald-500 text-white'
                            : 'bg-gray-200 text-gray-600' }}">
                For Approval ({{ $forApprovalCount ?? 0 }})
            </a>

            {{-- Create new order --}}
            <a href="{{ route('receiving.create') }}"
               class="ml-auto px-4 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold shadow">
                + Create new order
            </a>

        </div>

        {{-- Receiving List table --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <h3 class="text-sm font-semibold text-slate-800">
                    Receiving List
                </h3>

                {{-- Page-level search just for this table --}}
                <div class="w-full md:w-64">
                    <input
                        id="receivingSearch"
                        type="text"
                        placeholder="Search orders..."
                        class="w-full rounded-full border border-slate-300 px-3 py-1.5 text-xs focus:outline-none focus:ring focus:ring-emerald-400/60">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="receivingTable" class="min-w-full text-xs md:text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-slate-600">Order No.</th>
                        <th class="px-4 py-2 font-semibold text-slate-600">Date</th>
                        <th class="px-4 py-2 font-semibold text-slate-600">Customer</th>
                        <th class="px-4 py-2 font-semibold text-slate-600">Contact Person</th>
                        <th class="px-4 py-2 font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-2 font-semibold text-slate-600">Receiver</th>
                        <th class="px-4 py-2 font-semibold text-slate-600">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($jobs as $job)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{-- Clickable order no. like SMCS --}}
                                <a href="{{ route('receiving.edit', $job->id) }}"
                                   class="text-emerald-600 hover:text-emerald-800 font-semibold">
                                    {{ $job->order_number ?? '-' }}
                                </a>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ optional($job->order_date)->format('Y-m-d') ?? '-' }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $job->customer_name ?? '-' }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $job->contact_person ?? '-' }}
                            </td>
                            <td class="px-4 py-2">
                                @php
                                    $status = $job->status ?? 'receiving';
                                    $statusLabel = ucfirst($status);
                                    $statusClass = match($status) {
                                        'receiving' => 'bg-yellow-100 text-yellow-800',
                                        'technical' => 'bg-cyan-100 text-cyan-800',
                                        'releasing' => 'bg-blue-100 text-blue-800',
                                        'billing'   => 'bg-indigo-100 text-indigo-800',
                                        'ar'        => 'bg-red-100 text-red-800',
                                        'posted'    => 'bg-emerald-100 text-emerald-800',
                                        default     => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                {{ $job->receiver_name ?? '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <a href="{{ route('receiving.edit', $job->id) }}"
                                   class="text-xs text-emerald-600 hover:text-emerald-800 font-semibold mr-3">
                                    Edit
                                </a>
                                <a href="#"
                                   class="text-xs text-slate-500 hover:text-slate-700">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500 text-sm">
                                No receiving records found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.initReceivingTable) {
            window.initReceivingTable();
        }
    });
</script>
@endpush






