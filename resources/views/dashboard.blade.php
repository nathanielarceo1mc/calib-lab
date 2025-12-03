<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
            <p class="text-xs text-gray-500 mt-1">
                Home
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome panel --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    Welcome, {{ auth()->user()->name }}.
                </div>
            </div>

            {{-- Top stats row: Receiving / Technical / Releasing / Billing / AR --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                @php
                    $stats = [
                        ['label' => 'Receiving', 'value' => $receivingCount ?? 63,  'accent' => 'bg-orange-400', 'icon' => '📁'],
                        ['label' => 'Technical', 'value' => $technicalCount ?? 1983,'accent' => 'bg-teal-400',   'icon' => '✅'],
                        ['label' => 'Releasing', 'value' => $releasingCount ?? 646, 'accent' => 'bg-emerald-400','icon' => '📨'],
                        ['label' => 'Billing',   'value' => $billingCount ?? 6435, 'accent' => 'bg-sky-500',    'icon' => '🧾'],
                        ['label' => 'AR',        'value' => $arCount ?? 4851,      'accent' => 'bg-rose-400',   'icon' => '💰'],
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

            {{-- Second row: New survey / Open Ticket / Certificate Copy / Feedback --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                    $secondary = [
                        ['label' => 'New survey',                    'value' => $surveyCount ?? 0,        'icon' => '📝'],
                        ['label' => 'Open Ticket',                   'value' => $openTickets ?? 0,       'icon' => '🎟️'],
                        ['label' => 'Certificate Copy Request',      'value' => $certCopyCount ?? 0,     'icon' => '📄'],
                        ['label' => 'Certificate/Report Feedback',   'value' => $feedbackCount ?? 2,     'icon' => '💬'],
                    ];
                @endphp

                @foreach ($secondary as $item)
                    <div class="bg-slate-900 rounded-xl shadow-sm text-slate-50 p-4 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-sm">
                                {{ $item['label'] }}
                            </div>
                            <div class="h-9 w-9 rounded-full bg-slate-800 flex items-center justify-center text-xl">
                                {{ $item['icon'] }}
                            </div>
                        </div>
                        <div class="text-2xl font-semibold">
                            {{ number_format($item['value']) }}
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
