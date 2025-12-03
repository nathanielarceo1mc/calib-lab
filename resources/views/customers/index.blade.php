{{-- resources/views/customers/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Customers')

@section('header')
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Customers</h2>
    </div>
@endsection

@section('content')
    <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8 py-5">
        <div class="mb-3 text-xs text-slate-500">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 hover:underline">Home</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600 font-semibold">Customers</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-800">Customer List</h3>
                <a href="{{ route('customers.create') }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow">
                    + Create new customer
                </a>
            </div>

            <div class="px-4 py-3">
                @if (session('success'))
                    <div class="mb-3 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                <table id="customers-table" class="display stripe hover w-full text-sm">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Business Name</th>
                        <th>Address</th>
                        <th>Region</th>
                        <th>Province</th>
                        <th>Company Email</th>
                        <th>Company Phone</th>
                        <th>Primary Contact</th>
                        <th>Created Date</th>
                        <th>Created By</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>{{ $customer->code }}</td>
                            <td>
                                <a href="{{ route('customers.edit', $customer->id) }}"
                                   class="text-sky-700 hover:text-sky-900 hover:underline font-semibold">
                                    {{ $customer->business_name }}
                                </a>
                            </td>
                            <td>{{ $customer->address }}</td>
                            <td>{{ $customer->region }}</td>
                            <td>{{ $customer->province }}</td>
                            <td>{{ $customer->company_email }}</td>
                            <td>{{ $customer->company_phone }}</td>
                            <td>{{ $customer->primary_contact }}</td>
                            <td>{{ $customer->created_at?->format('Y-m-d') }}</td>
                            <td>{{ optional($customer->user)->name }}</td>
                            <td class="text-center">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('customers.edit', $customer->id) }}"
                                       class="inline-flex items-center justify-center h-8 w-8 rounded bg-sky-500 hover:bg-sky-600 text-white text-xs"
                                       title="Edit">
                                        ✎
                                    </a>
                                    <form action="{{ route('customers.destroy', $customer->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this customer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center h-8 w-8 rounded bg-rose-500 hover:bg-rose-600 text-white text-xs"
                                                title="Delete">
                                            🗑
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.0/css/buttons.dataTables.min.css">
    <style>
        .dt-top { display:flex; justify-content:flex-end; padding:0.25rem 0.5rem; }
        .dt-top .dt-search { display:flex; align-items:center; }
        .dt-top .dt-search label { font-size:0.75rem; margin-right:0.25rem; color:#6b7280; }
        .dt-top .dt-search input { border-radius:9999px; border:1px solid #d1d5db; padding:0.15rem 0.75rem; font-size:0.75rem; }
        .dt-bottom { display:flex; align-items:center; justify-content:space-between; margin-top:0.5rem; }
        .dt-left { font-size:0.70rem; color:#6b7280; }
        .dt-right { display:flex; align-items:center; gap:0.35rem; }
        .dt-right .dt-buttons { display:flex; gap:0.25rem; }
        .dt-button { border-radius:0.35rem !important; padding:0.25rem 0.5rem !important; font-size:0.70rem !important; background:#f3f4f6 !important; border:1px solid #d1d5db !important; }
        .dt-button:hover { background:#e5e7eb !important; }
        .dt-right .dt-paging .dt-paging-button { border-radius:9999px; padding:0.15rem 0.5rem; margin:0 0.05rem; border:1px solid transparent; font-size:0.70rem; }
        .dt-right .dt-paging .dt-paging-button.current { background:#059669; border-color:#059669; color:white !important; }
        .dt-right .dt-paging .dt-paging-button:hover:not(.disabled) { background:#e5e7eb; border-color:#d1d5db; }
        .dt-right .dt-paging .dt-paging-button.disabled { opacity:0.4; cursor:default; }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script>
        new DataTable('#customers-table', {
            pageLength: 100,
            order: [[0, 'asc']],
            dom: "<'dt-top' f>t<'dt-bottom' <'dt-left'i> <'dt-right'B p>>",
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });
    </script>
@endpush
