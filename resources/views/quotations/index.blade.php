{{-- resources/views/quotations/index.blade.php --}}
<x-app-layout>

    {{-- Page header (breadcrumb + title, search bar stays in global top bar) --}}
    <div class="mb-4 px-6">
        <div class="text-[11px] text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:underline hover:text-emerald-600">Home</a>
            <span class="mx-1">/</span>
            <span class="font-semibold text-gray-600">Quotations</span>
        </div>

        <h2 class="mt-1 text-2xl font-semibold text-gray-800">
            Quotation List
        </h2>
    </div>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 shadow-sm sm:rounded-md overflow-hidden">

                {{-- Card header: title, tabs, create button --}}
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

                        <div>
                            <h3 class="text-sm font-semibold text-gray-700">
                                Quotation List
                            </h3>

                            {{-- Simple status "tabs" --}}
                            <div class="mt-2 inline-flex rounded-md border border-gray-200 bg-white overflow-hidden text-xs">
                                {{-- Example filters. Hook these up to query params later. --}}
                                <a href="{{ route('quotations.index') }}"
                                   class="px-3 py-1.5 border-r border-gray-200 {{ request('status') ? 'text-gray-500' : 'bg-emerald-50 text-emerald-700 font-semibold' }}">
                                    All
                                </a>
                                <a href="{{ route('quotations.index', ['status' => 'approved']) }}"
                                   class="px-3 py-1.5 border-r border-gray-200 {{ request('status') === 'approved' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                                    Approved
                                </a>
                                <a href="{{ route('quotations.index', ['status' => 'disapproved']) }}"
                                   class="px-3 py-1.5 {{ request('status') === 'disapproved' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                                    Disapproved
                                </a>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Flash message spot if you want it inline --}}
                            @if (session('success'))
                                <div class="hidden md:block text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-3 py-1">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <a href="{{ route('quotations.create') }}"
                               class="inline-flex items-center px-4 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow">
                                + Create new quotation
                            </a>
                        </div>
                    </div>

                    {{-- On small screens show the flash below --}}
                    @if (session('success'))
                        <div class="mt-3 md:hidden text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-3 py-1">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>

                <div class="px-4 py-3">
                    <table id="quotations-table" class="display stripe hover w-full text-sm">
                        <thead>
                            <tr>
                                <th>Ref No.</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Prepared By</th>
                                <th>Status</th>
                                <th>Rev No.</th>
                                <th>PO Amount</th>
                                <th>PO Files</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quotations as $quotation)
                                <tr>
                                    {{-- Ref No --}}
                                    <td>
                                        <a href="{{ route('quotations.edit', $quotation->id) }}"
                                           class="text-sky-700 hover:text-sky-900 hover:underline font-semibold">
                                            {{ $quotation->ref_no ?? $quotation->reference_no }}
                                        </a>
                                    </td>

                                    {{-- Date --}}
                                    <td>{{ optional($quotation->date ?? $quotation->quotation_date)->format('d M Y') }}</td>

                                    {{-- Customer --}}
                                    <td>{{ optional($quotation->customer)->business_name ?? $quotation->customer_name }}</td>

                                    {{-- Prepared By --}}
                                    <td>{{ optional($quotation->preparedBy ?? $quotation->user)->name ?? '-' }}</td>

                                    {{-- Status --}}
                                    <td>{{ ucfirst($quotation->status ?? 'Draft') }}</td>

                                    {{-- Rev No --}}
                                    <td>{{ $quotation->revision_no ?? 0 }}</td>

                                    {{-- PO Amount --}}
                                    <td>{{ number_format($quotation->po_amount ?? 0, 2) }}</td>

                                    {{-- PO Files (simple "View" link placeholder) --}}
                                    <td>
                                        @if(!empty($quotation->po_file_path))
                                            <a href="{{ route('quotations.po.show', $quotation->id) }}"
                                               class="text-sky-600 hover:underline text-xs">
                                                View
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">None</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-center">
                                        <div class="inline-flex items-center space-x-1">
                                            <a href="{{ route('quotations.edit', $quotation->id) }}"
                                               class="inline-flex items-center justify-center h-8 w-8 rounded bg-sky-500 hover:bg-sky-600 text-white text-xs"
                                               title="Edit">
                                                ✎
                                            </a>

                                            <form action="{{ route('quotations.destroy', $quotation->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Delete this quotation?');">
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

    {{-- =============== STYLES (same DataTables layout as Customers) =============== --}}
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.0/css/buttons.dataTables.min.css">

        <style>
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
            new DataTable('#quotations-table', {
                pageLength: 100,
                order: [[1, 'desc']], // Date desc

                dom: "<'dt-top' f>" +
                     "t" +
                     "<'dt-bottom' <'dt-left'i> <'dt-right'B p>>",

                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });
        </script>
    @endpush
</x-app-layout>
