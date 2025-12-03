<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Receiving
            </h2>
            <p class="text-xs text-gray-500 mt-1">
                Service / Receiving
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Top stats row --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @php
                    $stats = [
                        ['label' => 'Receiving', 'value' => $receivingCount ?? 0,  'accent' => 'bg-orange-400', 'icon' => '📁'],
                        ['label' => 'Technical', 'value' => $technicalCount ?? 0, 'accent' => 'bg-teal-400',   'icon' => '✅'],
                        ['label' => 'Releasing', 'value' => $releasingCount ?? 0, 'accent' => 'bg-emerald-400','icon' => '📨'],
                        ['label' => 'Billing',   'value' => $billingCount ?? 0,   'accent' => 'bg-sky-500',    'icon' => '🧾'],
                        ['label' => 'AR',        'value' => $arCount ?? 0,        'accent' => 'bg-rose-400',   'icon' => '💰'],
                    ];
                @endphp

                @foreach ($stats as $stat)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                        <div class="{{ $stat['accent'] }} h-1 w-full"></div>
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $stat['label'] }}
                                </div>
                                <div class="mt-1 text-2xl font-semibold text-slate-900">
                                    {{ number_format($stat['value']) }}
                                </div>
                            </div>
                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-xl">
                                {{ $stat['icon'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Receiving list card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 space-y-4">

                    {{-- Tabs + create button --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button
                                class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold
                                       bg-emerald-500 text-white shadow-sm">
                                All
                            </button>

                            <button
                                class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold
                                       bg-slate-100 text-slate-600 hover:bg-slate-200">
                                For Approval (0)
                            </button>
                        </div>

                        <a href="{{ route('receiving.create') }}"
                           class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold
                                  bg-emerald-500 text-white hover:bg-emerald-600 focus:outline-none focus:ring
                                  focus:ring-emerald-400 focus:ring-offset-1">
                            + Create new order
                        </a>
                    </div>

                    {{-- Show entries / Search / Export row --}}
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pt-2 border-t border-slate-100 mt-2">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span>Show</span>
                            <select class="border border-slate-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-emerald-300">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100" selected>100</option>
                            </select>
                            <span>entries</span>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center md:justify-end gap-3 w-full lg:w-auto">
                            <div class="flex items-center gap-2 md:ml-auto">
                                <span class="text-sm text-slate-600">Search:</span>
                                <input
                                    type="text"
                                    class="border border-slate-300 rounded-md px-3 py-1.5 text-sm w-full md:w-64
                                           focus:outline-none focus:ring focus:ring-emerald-300"
                                    placeholder="Search receiving...">
                            </div>

                            <div class="flex items-center gap-1 justify-end">
                                @foreach (['Copy', 'CSV', 'Excel', 'PDF', 'Print'] as $btn)
                                    <button
                                        class="px-3 py-1.5 border border-slate-300 rounded-md text-xs font-medium text-slate-600
                                               bg-slate-50 hover:bg-slate-100">
                                        {{ $btn }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Showing X to Y of Z entries --}}
                    @php
                        if (method_exists($jobs, 'total')) {
                            $total       = $jobs->total();
                            $perPage     = $jobs->perPage();
                            $currentPage = $jobs->currentPage();
                            $from        = $total ? (($currentPage - 1) * $perPage) + 1 : 0;
                            $to          = min($total, $currentPage * $perPage);
                        } else {
                            $total   = $jobs->count();
                            $from    = $total ? 1 : 0;
                            $to      = $total;
                        }

                        $currentSort = $sort ?? 'order_date';
                        $currentDir  = $dir  ?? 'desc';
                    @endphp

                    <div class="text-xs text-slate-500">
                        Showing {{ $from }} to {{ $to }} of {{ $total }} entries
                    </div>

                    {{-- TABLE --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wide">

                                    {{-- Order No. --}}
                                    @php
                                        $isSorted = $currentSort === 'order_number';
                                        $nextDir  = $isSorted && $currentDir === 'asc' ? 'desc' : 'asc';
                                        $arrow    = $isSorted ? ($currentDir === 'asc' ? '↑' : '↓') : '';
                                        $url      = request()->fullUrlWithQuery(['sort' => 'order_number', 'dir' => $nextDir]);
                                    @endphp
                                    <th class="px-3 py-2">
                                        <a href="{{ $url }}" class="flex items-center gap-1 hover:text-emerald-600">
                                            <span>Order No.</span>
                                            @if($arrow)
                                                <span>{{ $arrow }}</span>
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Date --}}
                                    @php
                                        $isSorted = $currentSort === 'order_date';
                                        $nextDir  = $isSorted && $currentDir === 'asc' ? 'desc' : 'asc';
                                        $arrow    = $isSorted ? ($currentDir === 'asc' ? '↑' : '↓') : '';
                                        $url      = request()->fullUrlWithQuery(['sort' => 'order_date', 'dir' => $nextDir]);
                                    @endphp
                                    <th class="px-3 py-2">
                                        <a href="{{ $url }}" class="flex items-center gap-1 hover:text-emerald-600">
                                            <span>Date</span>
                                            @if($arrow)
                                                <span>{{ $arrow }}</span>
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Customer --}}
                                    @php
                                        $isSorted = $currentSort === 'customer_name';
                                        $nextDir  = $isSorted && $currentDir === 'asc' ? 'desc' : 'asc';
                                        $arrow    = $isSorted ? ($currentDir === 'asc' ? '↑' : '↓') : '';
                                        $url      = request()->fullUrlWithQuery(['sort' => 'customer_name', 'dir' => $nextDir]);
                                    @endphp
                                    <th class="px-3 py-2">
                                        <a href="{{ $url }}" class="flex items-center gap-1 hover:text-emerald-600">
                                            <span>Customer</span>
                                            @if($arrow)
                                                <span>{{ $arrow }}</span>
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Contact Person --}}
                                    @php
                                        $isSorted = $currentSort === 'contact_person';
                                        $nextDir  = $isSorted && $currentDir === 'asc' ? 'desc' : 'asc';
                                        $arrow    = $isSorted ? ($currentDir === 'asc' ? '↑' : '↓') : '';
                                        $url      = request()->fullUrlWithQuery(['sort' => 'contact_person', 'dir' => $nextDir]);
                                    @endphp
                                    <th class="px-3 py-2">
                                        <a href="{{ $url }}" class="flex items-center gap-1 hover:text-emerald-600">
                                            <span>Contact Person</span>
                                            @if($arrow)
                                                <span>{{ $arrow }}</span>
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Status --}}
                                    @php
                                        $isSorted = $currentSort === 'status';
                                        $nextDir  = $isSorted && $currentDir === 'asc' ? 'desc' : 'asc';
                                        $arrow    = $isSorted ? ($currentDir === 'asc' ? '↑' : '↓') : '';
                                        $url      = request()->fullUrlWithQuery(['sort' => 'status', 'dir' => $nextDir]);
                                    @endphp
                                    <th class="px-3 py-2">
                                        <a href="{{ $url }}" class="flex items-center gap-1 hover:text-emerald-600">
                                            <span>Status</span>
                                            @if($arrow)
                                                <span>{{ $arrow }}</span>
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Receiver --}}
                                    @php
                                        $isSorted = $currentSort === 'receiver_user_id';
                                        $nextDir  = $isSorted && $currentDir === 'asc' ? 'desc' : 'asc';
                                        $arrow    = $isSorted ? ($currentDir === 'asc' ? '↑' : '↓') : '';
                                        $url      = request()->fullUrlWithQuery(['sort' => 'receiver_user_id', 'dir' => $nextDir]);
                                    @endphp
                                    <th class="px-3 py-2">
                                        <a href="{{ $url }}" class="flex items-center gap-1 hover:text-emerald-600">
                                            <span>Receiver</span>
                                            @if($arrow)
                                                <span>{{ $arrow }}</span>
                                            @endif
                                        </a>
                                    </th>

                                    <th class="px-3 py-2 text-right">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($jobs as $job)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/60">
                                        <td class="px-3 py-2 whitespace-nowrap text-emerald-700 font-semibold">
                                            {{ $job->order_number }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-slate-700">
                                            {{ $job->order_date }}
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $job->customer_name }}
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $job->contact_person }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                bg-slate-100 text-slate-700">
                                                {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $job->receiver_user_id ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs text-slate-400">
                                            Pending UI
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500">
                                            No work orders yet. Click
                                            <span class="font-semibold text-emerald-600">"Create new order"</span>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if(method_exists($jobs, 'links'))
                        <div class="mt-4">
                            {{ $jobs->appends(['sort' => $sort, 'dir' => $dir])->links() }}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
