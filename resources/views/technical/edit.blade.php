<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Unit Under Test
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @if(session('success'))
                    <div class="rounded-md bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-md bg-rose-50 border border-rose-200 px-4 py-2 text-sm text-rose-800">
                        <div class="font-semibold">Please fix the following:</div>
                        <ul class="list-disc list-inside space-y-1 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('parse_error'))
                    <div class="rounded-md bg-amber-50 border border-amber-200 px-4 py-2 text-sm text-amber-800">
                        {{ session('parse_error') }}
                    </div>
                @endif

                @if(session('show_preview') && empty($unit->report_payload))
                    <div class="rounded-md bg-amber-50 border border-amber-200 px-4 py-2 text-sm text-amber-800">
                        No parsed data found in the uploaded certificate. Please ensure the Consolidated_Report sheet exists and the file was uploaded.
                        @if(session('parse_error'))
                            <div class="text-xs mt-1 text-amber-700">Debug: {{ session('parse_error') }}</div>
                        @endif
                    </div>
                @endif

                {{-- Preview modal --}}
                <div id="previewModal"
                     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4 {{ session('show_preview') ? '' : 'hidden' }}">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] overflow-y-auto relative">
                        <button type="button"
                                class="absolute top-3 right-3 text-slate-500 hover:text-slate-700"
                                onclick="document.getElementById('previewModal').classList.add('hidden')">
                            x
                        </button>

                        <div class="p-6 space-y-4">
                            <h2 class="text-2xl font-semibold text-slate-800 text-center">Certificate/Report</h2>
                            <div class="text-xs text-slate-600 text-center">
                                Parsed preview from Consolidated_Report (JSON only).
                            </div>

                            @php
                                // Normalize report_payload to array
                                $raw = $unit->report_payload ?? [];

                                if (is_string($raw)) {
                                    $decoded = json_decode($raw, true);
                                    $report = is_array($decoded) ? $decoded : [];
                                } elseif (is_array($raw)) {
                                    $report = $raw;
                                } else {
                                    $report = [];
                                }

                                $renderCell = function ($val) {
                                    if (!is_string($val)) {
                                        return $val;
                                    }
                                    // Normalize breaks
                                    $val = str_replace(['</br>', '<br/>', '<br />'], '<br>', $val);
                                    return $val;
                                };

                                $fmt = fn($v) => nl2br($renderCell($v));

                                $meta      = $report['meta']     ?? [];
                                $sections  = $report['sections'] ?? [];
                                $trace     = $report['traceability'] ?? [];
                                $results   = $report['calibration_results'] ?? [];
                                $uncText   = $report['uncertainty'] ?? '';
                                $remarks   = $report['remarks'] ?? '';
                            @endphp

                            <div id="parsedPreview"
                                 class="space-y-6 text-sm text-slate-800 cursor-default select-text">

                                <div class="space-y-8 bg-slate-50 p-6 rounded-lg border border-slate-200">

                                    {{-- 1. CUSTOMER DETAILS --}}
                                    @if(!empty($meta))
                                        <div class="space-y-2">
                                            <div class="font-semibold uppercase text-slate-700">
                                                1. CUSTOMER DETAILS
                                            </div>

                                            <div class="ml-16 leading-relaxed space-y-3">
                                                <div>
                                                    <span>Customer:</span><br>
                                                    <span>{!! $fmt($meta['customer'] ?? '') !!}</span>
                                                </div>

                                                <div>
                                                    <span>Address:</span><br>
                                                    <span>{!! $fmt($meta['address'] ?? '') !!}</span>
                                                </div>

                                                <div>
                                                    <span>End-User:</span><br>
                                                    <span>{!! $fmt($meta['end_user'] ?? '') !!}</span>
                                                </div>

                                                <div>
                                                    <span>End-User Address:</span><br>
                                                    <span>{!! $fmt($meta['end_user_address'] ?? '') !!}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- 2. SPECIFICATION OF UNIT UNDER TEST --}}
                                    <div class="space-y-2">
                                        <div class="font-semibold uppercase text-slate-700">
                                            2. SPECIFICATION OF UNIT UNDER TEST
                                        </div>

                                        <div class="ml-16">
                                            <table class="vertical-top text-sm">
                                                <tbody>
                                                @foreach(($sections['specification'] ?? []) as $row)
                                                    <tr>
                                                        <td width="150">
                                                            {{ $row['label'] }}:
                                                        </td>
                                                        <td>
                                                            {!! $row['value'] !!}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- 3. DATE OF ACTIVITIES --}}
                                    @if(
                                        !empty($meta['date_received']) ||
                                        !empty($meta['date_calibrated']) ||
                                        !empty($meta['recommended_due']) ||
                                        !empty($meta['date_issued'])
                                    )
                                        <div class="space-y-2">
                                            <div class="font-semibold uppercase text-slate-700">
                                                3. DATE OF ACTIVITIES
                                            </div>
                                            <div class="ml-4">
                                                <table class="text-sm align-top">
                                                    <tbody>
                                                    <tr>
                                                        <td width="150">Date Received:</td>
                                                        <td>{!! $fmt($meta['date_received'] ?? '') !!}</td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Date Calibrated:</td>
                                                        <td>{!! $fmt($meta['date_calibrated'] ?? '') !!}</td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Recommended Due:</td>
                                                        <td>{!! $fmt($meta['recommended_due'] ?? '') !!}</td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Date Issued:</td>
                                                        <td>{!! $fmt($meta['date_issued'] ?? '') !!}</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- 4. AUTHORIZATION (reserved – depende sa parser mo) --}}
                                    @if(!empty($report['authorization']))
                                        <div class="space-y-2">
                                            <div class="font-semibold uppercase text-slate-700">4. AUTHORIZATION</div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="space-y-1">
                                                    <div class="font-semibold text-sm">Calibrated By:</div>
                                                    <div>{!! $fmt($report['authorization']['calibrated_by'] ?? '') !!}</div>
                                                    <div class="text-xs text-slate-600">
                                                        {!! $fmt($report['authorization']['calibrated_title'] ?? '') !!}
                                                    </div>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="font-semibold text-sm">Reviewed and Authorized By:</div>
                                                    <div>{!! $fmt($report['authorization']['reviewed_by'] ?? '') !!}</div>
                                                    <div class="text-xs text-slate-600">
                                                        {!! $fmt($report['authorization']['reviewed_title'] ?? '') !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- 5. METHOD --}}
                                    @if(!empty($report['method']))
                                        <div class="space-y-2">
                                            <div class="font-semibold uppercase text-slate-700">5. METHOD</div>
                                            <div>
                                                Method Used:
                                                <span>{!! $fmt($report['method']['method_used'] ?? '') !!}</span>
                                            </div>
                                            <div>
                                                Type of Calibration:
                                                <span>{!! $fmt($report['method']['type_of_calibration'] ?? '') !!}</span>
                                            </div>
                                            <div>
                                                Location:
                                                <span>{!! $fmt($report['method']['location'] ?? '') !!}</span>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- 6. ENVIRONMENTAL CONDITIONS --}}
                                    @if(!empty($report['environment']))
                                        <div class="space-y-2">
                                            <div class="font-semibold uppercase text-slate-700">
                                                6. ENVIRONMENTAL CONDITIONS
                                            </div>
                                            <div>
                                                Temperature:
                                                <span>{!! $fmt($report['environment']['temperature'] ?? '') !!}</span>
                                            </div>
                                            <div>
                                                Relative Humidity:
                                                <span>{!! $fmt($report['environment']['relative_humidity'] ?? '') !!}</span>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- 7. TRACEABILITY --}}
                                    <div class="space-y-3">
                                        <div class="font-semibold uppercase text-slate-700">7. TRACEABILITY</div>
                                        @php
                                            $traceabilityNote = $report['traceability_note']
                                                ?? 'The instrument was calibrated using reference standards traceable to International System of Units (SI) through the National Metrology Institute(s) and/or the issuing accredited laboratory(ies).';
                                        @endphp
                                        <div class="text-xs text-slate-700">
                                            {!! $fmt($traceabilityNote) !!}
                                        </div>

                                        @if(!empty($trace))
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full text-xs border border-slate-200">
                                                    <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="px-2 py-1 border border-slate-200">Standard Used</th>
                                                        <th class="px-2 py-1 border border-slate-200">Manufacturer/Model</th>
                                                        <th class="px-2 py-1 border border-slate-200">Identification</th>
                                                        <th class="px-2 py-1 border border-slate-200">Calibration Certificate No.</th>
                                                        <th class="px-2 py-1 border border-slate-200">Issuing Laboratory</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($trace as $row)
                                                        <tr>
                                                            <td class="px-2 py-1 border border-slate-200">{!! $fmt($row[0] ?? '') !!}</td>
                                                            <td class="px-2 py-1 border border-slate-200">{!! $fmt($row[1] ?? '') !!}</td>
                                                            <td class="px-2 py-1 border border-slate-200">{!! $fmt($row[2] ?? '') !!}</td>
                                                            <td class="px-2 py-1 border border-slate-200">{!! $fmt($row[3] ?? '') !!}</td>
                                                            <td class="px-2 py-1 border border-slate-200">{!! $fmt($row[4] ?? '') !!}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- 8. CALIBRATION RESULTS (generic table; depende sa payload mo) --}}
                                    @if(!empty($results))
                                        <div class="space-y-2">
                                            <div class="font-semibold uppercase text-slate-700">
                                                8. CALIBRATION RESULTS
                                            </div>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full text-xs border border-slate-200">
                                                    <tbody>
                                                    @foreach($results as $row)
                                                        <tr>
                                                            @foreach($row as $cell)
                                                                <td class="px-2 py-1 border border-slate-200 whitespace-pre-wrap">
                                                                    {!! $fmt($cell) !!}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                   {{-- 9. UNCERTAINTY OF MEASUREMENTS --}}
@php
    // Normalize to string (handles array/null)
    $uncTextStr = is_array($uncText) ? implode("\n", $uncText) : ($uncText ?? '');
@endphp

@if(trim($uncTextStr) !== '')
    @php
        $showHeading = !preg_match('/uncertainty of measurements/i', $uncTextStr);
    @endphp
    <div class="space-y-1">
        @if($showHeading)
            <div class="font-semibold uppercase text-slate-700">
                9. UNCERTAINTY OF MEASUREMENTS
            </div>
        @endif
        <div class="text-sm text-slate-700 whitespace-pre-wrap">
            {!! $fmt($uncTextStr) !!}
        </div>
    </div>
@endif

{{-- 10. REMARKS --}}
@php
    $remarksStr = is_array($remarks) ? implode("\n", $remarks) : ($remarks ?? '');
@endphp

@if(trim($remarksStr) !== '')
    <div class="space-y-1">
        <div class="font-semibold uppercase text-slate-700">
            10. REMARKS
        </div>
        <div class="text-sm text-slate-700 whitespace-pre-line">
            {!! $fmt($remarksStr) !!}
        </div>
    </div>
@endif


                                </div> {{-- end bg block --}}
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button"
                                        class="px-4 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50"
                                        onclick="document.getElementById('previewModal').classList.add('hidden')">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Header section --}}
                <div class="text-lg font-semibold text-gray-800">Edit Unit Under Test</div>

                {{-- Work order summary --}}
                <div class="border border-slate-200 rounded-md p-4 bg-slate-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-slate-700">
                        <div>
                            <div class="font-semibold">Reference Number</div>
                            <div>{{ $unit->reference_no ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Date</div>
                            <div>{{ optional($workOrder->order_date)->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Customer</div>
                            <div>{{ $workOrder->customer_name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Address</div>
                            <div>{{ $workOrder->address ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Telephone No.</div>
                            <div>{{ $workOrder->telephone ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">E-mail Address</div>
                            <div>{{ $workOrder->email ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Contact</div>
                            <div>{{ $workOrder->contact_person ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Designation</div>
                            <div>{{ $workOrder->designation ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Use Details From</div>
                            <div>{{ $workOrder->use_details_from ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Main form --}}
                <form action="{{ route('technical.update', $unit->id) }}"
                      method="POST"
                      class="space-y-6"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Name</label>
                            <textarea name="name"
                                      class="w-full border border-slate-300 rounded-md px-3 py-2"
                                      rows="2">{{ old('name', $unit->name ?? $unit->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Method / Procedure *</label>
                            <input type="text"
                                   name="method_procedure"
                                   class="w-full border border-slate-300 rounded-md px-3 py-2"
                                   value="{{ old('method_procedure', $unit->method_procedure) }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Manufacturer</label>
                                <input type="text"
                                       name="manufacturer"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('manufacturer', $unit->manufacturer) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Model</label>
                                <input type="text"
                                       name="model"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('model', $unit->model) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Serial Number</label>
                                <input type="text"
                                       name="serial_number"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('serial_number', $unit->serial_number) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Identification</label>
                                <input type="text"
                                       name="identification"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('identification', $unit->identification) }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Service Interval</label>
                                <input type="number"
                                       name="service_interval"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('service_interval', $unit->service_interval) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Service Interval Type</label>
                                <div class="flex items-center gap-4 text-sm text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio"
                                               name="service_interval_type"
                                               value="Month"
                                               {{ old('service_interval_type', $unit->service_interval_type)=='Month' ? 'checked' : '' }}> Month
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio"
                                               name="service_interval_type"
                                               value="Year"
                                               {{ old('service_interval_type', $unit->service_interval_type)=='Year' ? 'checked' : '' }}> Year
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Sub-Contracted UUT</label>
                                <div class="flex items-center gap-4 text-sm text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio"
                                               name="sub_contracted"
                                               value="1"
                                               {{ old('sub_contracted', $unit->sub_contracted) ? 'checked' : '' }}> Yes
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio"
                                               name="sub_contracted"
                                               value="0"
                                               {{ !old('sub_contracted', $unit->sub_contracted) ? 'checked' : '' }}> No
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Accessories</label>
                                <input type="text"
                                       name="accessories"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('accessories', $unit->accessories) }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Type</label>
                                <select name="type"
                                        id="typeSelect"
                                        class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="On-site" {{ old('type', $unit->type)=='On-site' ? 'selected' : '' }}>On-site</option>
                                    <option value="In-house" {{ old('type', $unit->type)=='In-house' ? 'selected' : '' }}>In-house</option>
                                    <option value="Pull-out" {{ old('type', $unit->type)=='Pull-out' ? 'selected' : '' }}>Pull-out</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Receiving Method</label>
                                <select name="receiving_method"
                                        id="receivingMethod"
                                        class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="Walk-in" {{ old('receiving_method', $unit->receiving_method)=='Walk-in' ? 'selected' : '' }}>Walk-in</option>
                                    <option value="Pick-up" {{ old('receiving_method', $unit->receiving_method)=='Pick-up' ? 'selected' : '' }}>Pick-up</option>
                                    <option value="Courier" {{ old('receiving_method', $unit->receiving_method)=='Courier' ? 'selected' : '' }}>Courier</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tracking No.</label>
                                <input type="text"
                                       name="tracking_no"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('tracking_no', $unit->tracking_no) }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Received Date</label>
                                <input type="date"
                                       name="received_date"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('received_date', optional($unit->received_date)->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Estimated Completion Date</label>
                                <input type="date"
                                       name="estimated_completion_date"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('estimated_completion_date', optional($unit->estimated_completion_date)->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Onsite Date</label>
                                <input type="date"
                                       name="onsite_date"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('onsite_date', optional($unit->onsite_date)->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Receiving Conditions</label>
                            <input type="text"
                                   name="receiving_condition"
                                   class="w-full border border-slate-300 rounded-md px-3 py-2"
                                   value="{{ old('receiving_condition', $unit->receiving_condition) }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Statements of conformance?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio"
                                           name="require_conformance"
                                           value="1"
                                           {{ old('require_conformance', $unit->require_conformance) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio"
                                           name="require_conformance"
                                           value="0"
                                           {{ !old('require_conformance', $unit->require_conformance) ? 'checked' : '' }}> No
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Opinions/interpretations based on results?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio"
                                           name="require_opinion"
                                           value="1"
                                           {{ old('require_opinion', $unit->require_opinion) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio"
                                           name="require_opinion"
                                           value="0"
                                           {{ !old('require_opinion', $unit->require_opinion) ? 'checked' : '' }}> No
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Deviate from methods selected?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio"
                                           name="deviate_methods"
                                           value="1"
                                           {{ old('deviate_methods', $unit->deviate_methods) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio"
                                           name="deviate_methods"
                                           value="0"
                                           {{ !old('deviate_methods', $unit->deviate_methods) ? 'checked' : '' }}> No
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Request New Method</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio"
                                           name="request_new_method"
                                           value="1"
                                           {{ old('request_new_method', $unit->request_new_method) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio"
                                           name="request_new_method"
                                           value="0"
                                           {{ !old('request_new_method', $unit->request_new_method) ? 'checked' : '' }}> No
                                </label>
                            </div>
                        </div>

                        <div>
                            <textarea name="request_details"
                                      class="w-full border border-slate-300 rounded-md px-3 py-2"
                                      rows="2"
                                      placeholder="Insert details here..."
                                      {{ old('request_new_method', $unit->request_new_method) ? '' : 'disabled' }}>{{ old('request_details', $unit->request_details) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Delivery method of certificate/report
                                </label>
                                <select name="delivery_method"
                                        class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="Hand-carry / Hard copy"
                                        {{ old('delivery_method', $unit->delivery_method)=='Hand-carry / Hard copy' ? 'selected' : '' }}>
                                        Hand-carry / Hard copy
                                    </option>
                                    <option value="Courier"
                                        {{ old('delivery_method', $unit->delivery_method)=='Courier' ? 'selected' : '' }}>
                                        Courier
                                    </option>
                                    <option value="Customer Portal"
                                        {{ old('delivery_method', $unit->delivery_method)=='Customer Portal' ? 'selected' : '' }}>
                                        Customer Portal
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Email, PDF</label>
                                <input type="text"
                                       name="delivery_contact"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('delivery_contact', $unit->delivery_contact) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Special Instructions</label>
                                <input type="text"
                                       name="special_instructions"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('special_instructions', $unit->special_instructions) }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Test Points / Nominal Values / Parameter
                            </label>
                            <textarea name="test_points"
                                      class="w-full border border-slate-300 rounded-md px-3 py-2"
                                      rows="2">{{ old('test_points', $unit->test_points) }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    name="save_details"
                                    value="1"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                                Save Details
                            </button>
                        </div>

                        {{-- Status + files --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-slate-200 pt-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">UUT Status *</label>
                                <select name="uut_status"
                                        class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="Defective" {{ old('uut_status', $unit->uut_status) === 'Defective' ? 'selected' : '' }}>Defective</option>
                                    <option value="On Hold" {{ old('uut_status', $unit->uut_status) === 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="Calibrated" {{ old('uut_status', $unit->uut_status) === 'Calibrated' ? 'selected' : '' }}>Calibrated</option>
                                    <option value="Cancelled" {{ old('uut_status', $unit->uut_status) === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Reporting Status</label>
                                <select name="reporting_status"
                                        class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="For Approval" {{ old('reporting_status', $unit->reporting_status) === 'For Approval' ? 'selected' : '' }}>For Approval</option>
                                    <option value="Approved" {{ old('reporting_status', $unit->reporting_status) === 'Approved' ? 'selected' : '' }}>Approved</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Date Performed *</label>
                                <input type="date"
                                       name="date_performed"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('date_performed', optional($unit->date_performed)->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Certificate --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Certificate</label>
                                <input type="file"
                                       name="certificate"
                                       accept=".xlsm,.xlsx,.xls"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2">
                                @if($unit->certificate_path)
                                    <div class="text-xs text-slate-600 mt-1">
                                        {{ is_array($unit->certificate_path) ? ($unit->certificate_path['name'] ?? 'Download') : 'Download' }}
                                    </div>
                                    <a href="{{ route('technical.download', [$unit->id, 'certificate']) }}"
                                       class="text-xs text-sky-600 hover:underline">
                                        Download
                                    </a>
                                @endif
                            </div>

                            {{-- Datasheet --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Datasheet</label>
                                <input type="file"
                                       name="datasheet"
                                       accept=".pdf"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2">
                                @if($unit->datasheet_path)
                                    <div class="text-xs text-slate-600 mt-1">
                                        {{ is_array($unit->datasheet_path) ? ($unit->datasheet_path['name'] ?? 'Download') : 'Download' }}
                                    </div>
                                    <a href="{{ route('technical.download', [$unit->id, 'datasheet']) }}"
                                       class="text-xs text-sky-600 hover:underline">
                                        Download
                                    </a>
                                @endif
                            </div>

                            {{-- TSRF --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">TSRF</label>
                                <input type="file"
                                       name="tsrf"
                                       accept=".pdf"
                                       class="w-full border border-slate-300 rounded-md px-3 py-2">
                                @if($unit->tsrf_path)
                                    <div class="text-xs text-slate-600 mt-1">
                                        {{ is_array($unit->tsrf_path) ? ($unit->tsrf_path['name'] ?? 'Download') : 'Download' }}
                                    </div>
                                    <a href="{{ route('technical.download', [$unit->id, 'tsrf']) }}"
                                       class="text-xs text-sky-600 hover:underline">
                                        Download
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="text-xs text-slate-600 -mt-2">
                            Upload all three files on first generation. Existing files are retained so you can regenerate without re-uploading.
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Approver's Remarks</label>
                            <input type="text"
                                   name="approver_remarks"
                                   class="w-full border border-slate-300 rounded-md px-3 py-2"
                                   value="{{ old('approver_remarks', $unit->approver_remarks) }}">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    name="generate_certificate"
                                    value="1"
                                    class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                                Generate Calibration Certificate
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    (function () {
        const typeSelect = document.getElementById('typeSelect');
        const receivingSelect = document.getElementById('receivingMethod');

        function toggleReceiving() {
            if (!typeSelect || !receivingSelect) return;
            if (typeSelect.value === 'On-site') {
                receivingSelect.disabled = true;
                receivingSelect.classList.add('bg-slate-100');
            } else {
                receivingSelect.disabled = false;
                receivingSelect.classList.remove('bg-slate-100');
            }
        }

        typeSelect?.addEventListener('change', toggleReceiving);
        toggleReceiving();
    })();
</script>
