{{-- resources/views/customers/index.blade.php --}}
<x-app-layout>
    {{-- NO header slot → layout top bar only shows the global search + buttons --}}

    {{-- =============== PAGE CONTENT =============== --}}
    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Breadcrumb + title (same style as create, but clickable + no double "Customers") --}}
            <div class="mb-4">
                <div class="text-[11px] text-gray-400">
                    <a href="{{ route('dashboard') }}"
                       class="hover:underline hover:text-emerald-600">
                        Home
                    </a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('customers.index') }}"
                       class="font-semibold text-gray-600 hover:underline hover:text-emerald-600">
                        Customers
                    </a>
                </div>
                <h2 class="mt-1 text-2xl font-semibold text-gray-800">
                    
                </h2>
            </div>

            <div class="bg-white border border-gray-200 shadow-sm sm:rounded-md overflow-hidden">

                {{-- Card header: title + create button on the right --}}
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Customer List
                    </h3>

                    <a href="{{ route('customers.create') }}"
                       class="inline-flex items-center px-4 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow">
                        + Create new customer
                    </a>
                </div>

                <div class="px-4 py-3">
                    {{-- Flash message --}}
                    @if (session('success'))
                        <div class="mb-3 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- =============== DATA TABLE =============== --}}
                    <table id="customers-table" class="display stripe hover w-full text-sm">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Business Name</th>
                                <th>Address</th>
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
                                    <td>{{ $customer->company_email }}</td>
                                    <td>{{ $customer->company_phone }}</td>
                                    <td>{{ $customer->primary_contact }}</td>
                                    <td>{{ optional($customer->created_at)->format('d M Y') }}</td>
                                    <td>{{ optional($customer->user)->name ?? '-' }}</td>

                                    <td class="text-center">
                                        <div class="inline-flex items-center space-x-1">
                                            {{-- Edit --}}
                                            <a href="{{ route('customers.edit', $customer->id) }}"
                                               class="inline-flex items-center justify-center h-8 w-8 rounded bg-sky-500 hover:bg-sky-600 text-white text-xs"
                                               title="Edit">
                                                ✎
                                            </a>

                                            {{-- Delete --}}
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
    </div>

    {{-- =============== STYLES =============== --}}
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.0/css/buttons.dataTables.min.css">

        <style>
            /* --- Top layout: only search bar, aligned right --- */
            .dt-top {
                display: flex;
                justify-content: flex-end;
                padding: 0.25rem 0.5rem;
            }

            .dt-top .dt-search {
                display: flex;
                align-items: center;
            }

            .dt-top .dt-search label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.75rem;
                color: #374151;
                margin: 0;
            }

            .dt-top .dt-search input {
                border-radius: 0.375rem;
                border: 1px solid #d1d5db;
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            /* --- Bottom layout: info left, buttons+pagination right --- */
            .dt-bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0.75rem;
            }

            .dt-left {
                font-size: 0.75rem;
                color: #4b5563;
            }

            .dt-right {
                display: flex;
                align-items: center;
                gap: 0.35rem;
            }

            /* --- Export buttons: small & subtle --- */
            .dt-right .dt-buttons {
                display: flex;
                gap: 0.25rem;
            }

            .dt-button {
                border-radius: 0.35rem !important;
                padding: 0.25rem 0.5rem !important;
                font-size: 0.70rem !important;
                background: #f3f4f6 !important;
                border: 1px solid #d1d5db !important;
                color: #374151 !important;
            }

            .dt-button:hover {
                background: #e5e7eb !important;
            }

            .dt-button:focus {
                outline: none !important;
                box-shadow: none !important;
            }

            /* --- Pagination buttons --- */
            .dt-right .dt-paging .dt-paging-button {
                border-radius: 0.35rem !important;
                padding: 0.25rem 0.45rem !important;
                font-size: 0.75rem !important;
                border: 1px solid transparent;
                color: #374151;
                margin: 0 1px;
            }

            .dt-right .dt-paging .dt-paging-button.current {
                background: #e5e7eb;
                border-color: #d1d5db;
            }

            .dt-right .dt-paging .dt-paging-button:hover:not(.disabled) {
                background: #e5e7eb;
                border-color: #d1d5db;
            }

            .dt-right .dt-paging .dt-paging-button.disabled {
                opacity: 0.4;
                cursor: default;
            }
        </style>
    @endpush

    {{-- =============== SCRIPTS =============== --}}
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
                dom:
                    "<'dt-top' f>" +
                    "t" +
                    "<'dt-bottom' <'dt-left'i> <'dt-right'B p>>",
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });
        </script>
    @endpush
</x-app-layout>
