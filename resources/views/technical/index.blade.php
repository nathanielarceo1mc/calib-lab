<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Technical List
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="text-lg font-semibold text-gray-800">UUT Status</div>
                </div>

                <div class="overflow-x-auto">
                    <table id="uut-table" class="min-w-full text-sm text-left">
                        <thead>
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="px-3 py-2">Ref No.</th>
                            <th class="px-3 py-2">UUT</th>
                            <th class="px-3 py-2">Customer Name</th>
                            <th class="px-3 py-2">Assigned Personnel</th>
                            <th class="px-3 py-2">Assigned Approver</th>
                            <th class="px-3 py-2">Received Date</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Actions</th>
                        </tr>
                        <tr class="bg-white border-b">
                            <th class="px-3 py-1"><input data-col="0" class="filter-input w-full border rounded px-2 py-1 text-xs" placeholder="Search Ref"></th>
                            <th class="px-3 py-1"><input data-col="1" class="filter-input w-full border rounded px-2 py-1 text-xs" placeholder="Search UUT"></th>
                            <th class="px-3 py-1"><input data-col="2" class="filter-input w-full border rounded px-2 py-1 text-xs" placeholder="Search Customer"></th>
                            <th class="px-3 py-1"><input data-col="3" class="filter-input w-full border rounded px-2 py-1 text-xs" placeholder="Search Personnel"></th>
                            <th class="px-3 py-1"><input data-col="4" class="filter-input w-full border rounded px-2 py-1 text-xs" placeholder="Search Approver"></th>
                            <th class="px-3 py-1"><input data-col="5" class="filter-input w-full border rounded px-2 py-1 text-xs" placeholder="Search Date"></th>
                            <th class="px-3 py-1"><input data-col="6" class="filter-input w-full border rounded px-2 py-1 text-xs" placeholder="Search Status"></th>
                            <th class="px-3 py-1"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach($units as $unit)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 text-emerald-700 font-semibold">
                                    {{ $unit->reference_no ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-slate-800">
                                    {{ $unit->name ?? $unit->description ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ optional($unit->workOrder)->customer_name ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ optional($unit->personnel)->name ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ optional($unit->approver)->name ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ optional($unit->received_date)->format('Y-m-d') ?? '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ ($unit->status ?? '') === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ \Illuminate\Support\Str::of($unit->status ?? '')->replace('_',' ')->title() ?: 'In Progress' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <a href="{{ route('technical.edit', $unit->id) }}"
                                       class="inline-flex items-center justify-center h-8 w-8 rounded bg-emerald-500 text-white text-sm font-bold"
                                       title="Edit">
                                        ✎
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.filter-input').forEach((input) => {
                input.addEventListener('input', () => {
                    const colIndex = parseInt(input.dataset.col, 10);
                    const term = input.value.toLowerCase();
                    document.querySelectorAll('#uut-table tbody tr').forEach((row) => {
                        const cell = row.children[colIndex];
                        if (!cell) return;
                        const text = (cell.textContent || '').toLowerCase();
                        const show = text.includes(term);
                        row.style.display = show ? '' : 'none';
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
