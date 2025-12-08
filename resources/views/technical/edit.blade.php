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
                <div id="previewModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4 {{ session('show_preview') ? '' : 'hidden' }}">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] overflow-y-auto relative">
                        <button type="button" class="absolute top-3 right-3 text-slate-500 hover:text-slate-700" onclick="document.getElementById('previewModal').classList.add('hidden')">x</button>
                        <div class="p-6 space-y-4">
                            <h2 class="text-2xl font-semibold text-slate-800 text-center">Certificate/Report</h2>
                            <div class="text-xs text-slate-600 text-center">Click the content below to edit, then apply to override the parsed preview.</div>

                            @php
                                $report = $unit->report_payload ?? [];
                                // Force using parsed preview; ignore previously saved custom HTML to avoid stale layouts
                                $customHtml = '';
                                $renderCell = function ($val) {
                                    if (!is_string($val)) {
                                        return $val;
                                    }
                                    // Normalize custom markers to HTML-friendly output
                                    $val = str_replace(['</br>', '<br/>', '<br />'], '<br>', $val);
                                    return $val;
                                };
                                $fmt = fn($v) => nl2br($renderCell($v));
                                $stripLabel = function ($val, $label) {
                                    $val = trim((string)$val);
                                    if ($val === '') return '';
                                    // Remove optional quotes and the label prefix (e.g., End-user: or "End-user:")
                                    $pattern = '/^[\"\\\']?\\s*' . preg_quote($label, '/') . '\\s*:??\\s*/i';
                                    $val = preg_replace($pattern, '', $val);
                                    // Trim any leftover leading colon/space
                                    $val = ltrim($val, " :\t\r\n");
                                    return trim($val);
                                };
                                $display = function ($val, $label = null) use ($fmt, $stripLabel) {
                                    if ($label) {
                                        $val = $stripLabel($val, $label);
                                    }
                                    return $fmt($val);
                                };
                            @endphp

                            <div id="customPreview" class="{{ empty($customHtml) ? 'hidden' : '' }} border border-slate-200 rounded-md p-3 text-sm text-slate-800 prose max-w-none cursor-pointer select-text" role="button" tabindex="0" aria-label="Edit custom content">
                                {!! $customHtml !!}
                            </div>

                            {{-- Parsed preview (structured, simple layout) --}}
                            <div id="parsedPreview" class="space-y-6 text-sm text-slate-800 {{ !empty($customHtml) ? 'hidden' : '' }} cursor-pointer select-text" role="button" tabindex="0" aria-label="Edit parsed content">
                                <div class="space-y-8 bg-slate-50 p-6 rounded-lg border border-slate-200">
                                {{-- 1. Customer Details --}}
                                @if(!empty($report['meta']))
                                    <div class="space-y-2">
                                        <div class="font-semibold uppercase text-slate-700">1. CUSTOMER DETAILS</div>
                                        <div class="ml-4 leading-relaxed space-y-3">
                                            <div>
                                                <div>Customer:</div>
                                                <div class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['customer'] ?? '') !!}</div>
                                            </div>
                                            <div>
                                                <div>Address:</div>
                                                <div class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['address'] ?? '') !!}</div>
                                            </div>
                                            <div>
                                                <div>End-user:</div>
                                                <div class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $display($report['meta']['end_user'] ?? '', 'End-user') !!}</div>
                                            </div>
                                            <div>
                                                <div>End-user Address:</div>
                                                <div class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $display($report['meta']['end_user_address'] ?? '', 'End-user Address') !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- 2. Specification --}}
                                @if(!empty($report['meta']['uut_name']))
                                    <div class="space-y-2">
                                        <div class="font-semibold uppercase text-slate-700">2. SPECIFICATION OF UNIT UNDER TEST</div>
                                        <div class="ml-4">
                                            <table class="text-sm align-top">
                                                <tbody>
                                                    <tr>
                                                        <td width="150">Unit Under Test:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['uut_name'] ?? '') !!}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Material:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['material'] ?? '') !!}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Manufacturer:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['manufacturer'] ?? '') !!}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Model:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['model'] ?? '') !!}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Serial Number:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['serial_number'] ?? '') !!}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Identification:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['identification'] ?? '') !!}</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                {{-- 3. Dates --}}
                                @if(!empty($report['meta']['date_received']) || !empty($report['meta']['date_calibrated']))
                                    <div class="space-y-2">
                                        <div class="font-semibold uppercase text-slate-700">3. DATE OF ACTIVITIES</div>
                                        <div class="ml-4">
                                            <table class="text-sm align-top">
                                                <tbody>
                                                    <tr>
                                                        <td width="150">Date Received:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['date_received'] ?? '') !!}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Date Calibrated:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['date_calibrated'] ?? '') !!}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Recommended Due:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['recommended_due'] ?? '') !!}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="150">Date Issued:</td>
                                                        <td><span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['meta']['date_issued'] ?? '') !!}</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                {{-- 4. Authorization --}}
                                @if(!empty($report['authorization']))
                                    <div class="space-y-2">
                                        <div class="font-semibold uppercase text-slate-700">4. Authorization</div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-1">
                                                <div class="font-semibold text-sm">Calibrated By:</div>
                                                <div class="editable-chunk outline-none" tabindex="0">{!! $fmt($report['authorization']['calibrated_by'] ?? '') !!}</div>
                                                <div class="text-xs text-slate-600 editable-chunk outline-none" tabindex="0">{!! $fmt($report['authorization']['calibrated_title'] ?? '') !!}</div>
                                            </div>
                                            <div class="space-y-1">
                                                <div class="font-semibold text-sm">Reviewed and Authorized By:</div>
                                                <div class="editable-chunk outline-none" tabindex="0">{!! $fmt($report['authorization']['reviewed_by'] ?? '') !!}</div>
                                                <div class="text-xs text-slate-600 editable-chunk outline-none" tabindex="0">{!! $fmt($report['authorization']['reviewed_title'] ?? '') !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- 5. Method --}}
                                    @if(!empty($report['method']))
                                        <div class="space-y-2">
                                            <div class="font-semibold uppercase text-slate-700">5. Method</div>
                                            <div>Method Used: <span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['method']['method_used'] ?? '') !!}</span></div>
                                            <div>Type of Calibration: <span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['method']['type_of_calibration'] ?? '') !!}</span></div>
                                            <div>Location: <span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['method']['location'] ?? '') !!}</span></div>
                                        </div>
                                    @endif

                                    {{-- 6. Environment --}}
                                    @if(!empty($report['environment']))
                                        <div class="space-y-2">
                                            <div class="font-semibold uppercase text-slate-700">6. Environmental Conditions</div>
                                            <div>Temperature: <span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['environment']['temperature'] ?? '') !!}</span></div>
                                            <div>Relative Humidity: <span class="editable-chunk outline-none whitespace-pre-line" tabindex="0">{!! $fmt($report['environment']['relative_humidity'] ?? '') !!}</span></div>
                                        </div>
                                    @endif

                                <div class="space-y-3">
                                    <div class="font-semibold uppercase text-slate-700 editable-chunk" tabindex="0">7. Traceability</div>
                                        @php
                                            $traceabilityNote = $report['traceability_note'] ?? 'The instrument was calibrated using reference standards traceable to International System of Units (SI) through the National Metrology Institute(s) and/or the issuing accredited laboratory(ies).';
                                        @endphp
                                        <div class="text-xs text-slate-700 editable-chunk whitespace-pre-wrap" tabindex="0">{!! $fmt($traceabilityNote) !!}</div>
                                    @if(!empty($report['traceability']))
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-xs border border-slate-200">
                                                <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Standard Used') !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Manufacturer/Model') !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Identification') !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Calibration Certificate No.') !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Issuing Laboratory') !!}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($report['traceability'] as $row)
                                                        <tr>
                                                            <td class="px-2 py-1 border border-slate-200 editable-chunk whitespace-pre-wrap">{!! $fmt($row[0] ?? '') !!}</td>
                                                            <td class="px-2 py-1 border border-slate-200 editable-chunk whitespace-pre-wrap">{!! $fmt($row[1] ?? '') !!}</td>
                                                            <td class="px-2 py-1 border border-slate-200 editable-chunk whitespace-pre-wrap">{!! $fmt($row[2] ?? '') !!}</td>
                                                            <td class="px-2 py-1 border border-slate-200 editable-chunk whitespace-pre-wrap">{!! $fmt($row[3] ?? '') !!}</td>
                                                            <td class="px-2 py-1 border border-slate-200 editable-chunk whitespace-pre-wrap">{!! $fmt($row[4] ?? '') !!}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>

                                @if(!empty($report['calibration_results']))
                                    <div class="space-y-2">
                                        <div class="font-semibold uppercase text-slate-700 editable-chunk" tabindex="0">8. Calibration Results</div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-xs border border-slate-200">
                                                <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Nominal Value, mm') !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Central Length Error, µm') !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Variation in Length Error, µm') !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk" tabindex="0">{!! $fmt('Uncertainty of Measurements, µm') !!}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($report['calibration_results'] as $row)
                                                        <tr>
                                                            @foreach($row as $cell)
                                                                <td class="px-2 py-1 border border-slate-200 editable-chunk whitespace-pre-wrap">{!! $fmt($cell) !!}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($report['tolerances']))
                                    <div class="space-y-2">
                                        @php
                                            $tolRows = $report['tolerances'] ?? [];
                                            $headerRow1 = array_shift($tolRows) ?? [];
                                            $headerRow2 = array_shift($tolRows) ?? [];
                                            $bodyRows = $tolRows;
                                            $stripSpan = function ($val) {
                                                $val = html_entity_decode((string)$val, ENT_QUOTES, 'UTF-8');
                                                $val = preg_replace('/<rs>\\d+<\\/rs>/i', '', $val);
                                                $val = preg_replace('/<cs>\\d+<\\/cs>/i', '', $val);
                                                return trim($val);
                                            };
                                            $titleText = $fmt($report['tolerances_title'] ?? 'Maximum Permitted Deviations of the Central Length and Tolerance on Variation in Length');
                                            $nominalLabel = $stripSpan($headerRow1[0] ?? 'Nominal Value, mm');
                                            $g0 = $stripSpan($headerRow1[2] ?? 'Grade 0');
                                            $g1 = $stripSpan($headerRow1[4] ?? 'Grade 1');
                                            $g2 = $stripSpan($headerRow1[6] ?? 'Grade 2');
                                            $g0c = $stripSpan($headerRow2[2] ?? 'Central length');
                                            $g0v = $stripSpan($headerRow2[3] ?? 'Variation in length');
                                            $g1c = $stripSpan($headerRow2[4] ?? 'Central length');
                                            $g1v = $stripSpan($headerRow2[5] ?? 'Variation in length');
                                            $g2c = $stripSpan($headerRow2[6] ?? 'Central length');
                                            $g2v = $stripSpan($headerRow2[7] ?? 'Variation in length');
                                        @endphp
                                        <div class="font-semibold text-slate-700 editable-chunk" tabindex="0">{!! $titleText !!}</div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-xs border border-slate-200">
                                                <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center" rowspan="2">{!! $fmt($nominalLabel) !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center" colspan="2">{!! $fmt($g0) !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center" colspan="2">{!! $fmt($g1) !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center" colspan="2">{!! $fmt($g2) !!}</th>
                                                    </tr>
                                                    <tr class="italic">
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center">{!! $fmt($g0c) !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center">{!! $fmt($g0v) !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center">{!! $fmt($g1c) !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center">{!! $fmt($g1v) !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center">{!! $fmt($g2c) !!}</th>
                                                        <th class="px-2 py-1 border border-slate-200 editable-chunk text-center">{!! $fmt($g2v) !!}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($bodyRows as $row)
                                                        @php
                                                            $cleanRow = array_values(array_filter(array_map(fn($c) => trim((string)$c), $row), fn($v) => $v !== ''));
                                                            // map to 7 columns: nominal, g0c, g0v, g1c, g1v, g2c, g2v
                                                            $mapped = [
                                                                $cleanRow[0] ?? '',
                                                                $cleanRow[1] ?? '',
                                                                $cleanRow[2] ?? '',
                                                                $cleanRow[3] ?? '',
                                                                $cleanRow[4] ?? '',
                                                                $cleanRow[5] ?? '',
                                                                $cleanRow[6] ?? '',
                                                            ];
                                                            $hasValue = array_reduce($mapped, fn($carry, $c) => $carry || $c !== '', false);
                                                        @endphp
                                                        @if($hasValue)
                                                            <tr>
                                                                @foreach($mapped as $cell)
                                                                    <td class="px-2 py-1 border border-slate-200 editable-chunk whitespace-pre-wrap">{!! $fmt($cell) !!}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($report['uncertainty']))
                                    @php
                                        $uncText = trim($report['uncertainty']);
                                        $showHeading = !preg_match('/uncertainty of measurements/i', $uncText);
                                    @endphp
                                    <div class="space-y-1">
                                        @if($showHeading)
                                            <div class="font-semibold uppercase text-slate-700">9. Uncertainty of Measurements</div>
                                        @endif
                                        <div class="text-sm text-slate-700 whitespace-pre-wrap editable-chunk" tabindex="0">{!! $fmt($uncText) !!}</div>
                                    </div>
                                @endif

                                @if(!empty($report['remarks']))
                                    <div class="space-y-1">
                                        <div class="font-semibold uppercase text-slate-700">10. Remarks</div>
                                        <div class="text-sm text-slate-700 whitespace-pre-line editable-chunk" tabindex="0">{!! $fmt($report['remarks']) !!}</div>
                                    </div>
                                @endif
                                </div>
                            </div>
                            </div>

                            <div id="editorSection" class="hidden space-y-3 border border-slate-200 rounded-md p-3 bg-slate-50">
                                <textarea id="reportCustomHtmlEditor" class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm" rows="12"></textarea>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700">Decimal Place</label>
                                        <input type="number" id="decimalPlaceInput" class="w-full border border-slate-300 rounded-md px-2 py-1" value="2" min="0" max="6">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700">Apply to</label>
                                        <select id="applyToSelect" class="w-full border border-slate-300 rounded-md px-2 py-1">
                                            <option value="cell">Cell</option>
                                            <option value="row">Row</option>
                                            <option value="col">Column</option>
                                            <option value="table">Table</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700">Range (e.g. A1)</label>
                                        <input type="text" id="rangeInput" class="w-full border border-slate-300 rounded-md px-2 py-1" placeholder="Optional">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700">Colspan</label>
                                        <input type="number" id="colspanInput" class="w-full border border-slate-300 rounded-md px-2 py-1" value="1" min="1">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700">Rowspan</label>
                                        <input type="number" id="rowspanInput" class="w-full border border-slate-300 rounded-md px-2 py-1" value="1" min="1">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700">Width (e.g. 120px or 20%)</label>
                                        <input type="text" id="widthInput" class="w-full border border-slate-300 rounded-md px-2 py-1" placeholder="Optional">
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" class="px-3 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="formatSelection()">Format Selection</button>
                                    <button type="button" class="px-3 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="applyCellAttributes()">Apply Col/Row Span</button>
                                    <button type="button" class="px-3 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="removeTableBorder()">Remove Table Border</button>
                                    <button type="button" class="px-3 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="insertPageBreak()">Add Page Break</button>
                                    <div class="ml-auto flex gap-2">
                                        <button type="button" class="px-4 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="cancelEdit()">Cancel</button>
                                        <button type="button" class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700" onclick="applyEdit()">Apply & Use</button>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-600">Applying will override the parsed preview. Save or Generate to persist. Colspan/Rowspan applies to the current selection by wrapping it in a &lt;td&gt; tag.</div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" class="px-4 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="toggleEdit(true)">Edit Content</button>
                                <button type="button" class="px-4 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="document.getElementById('previewModal').classList.add('hidden')">Close</button>
                            </div>

                            {{-- Single-chunk editor modal --}}
                            <div id="chunkEditorModal" class="fixed inset-0 bg-black/50 z-60 hidden items-center justify-center px-4">
                                <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[80vh] overflow-y-auto relative">
                                    <div class="flex justify-between items-center border-b border-slate-200 px-4 py-3">
                                        <h3 class="text-xl font-semibold text-slate-800">Content Editor</h3>
                                        <button type="button" class="text-slate-500 hover:text-slate-700" onclick="closeChunkEditor()">x</button>
                                    </div>
                                    <div class="p-4 space-y-4">
                                        <textarea id="chunkEditorInput" class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm" rows="8"></textarea>
                                        <div class="flex items-center justify-between">
                                            <button type="button" class="px-3 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="insertPageBreak()">Add Page Break</button>
                                            <div class="flex gap-2">
                                                <button type="button" class="px-4 py-2 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="closeChunkEditor()">Cancel</button>
                                                <button type="button" class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700" onclick="applyChunkEdit()">Ok</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-lg font-semibold text-gray-800">Edit Unit Under Test</div>

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

                <form action="{{ route('technical.update', $unit->id) }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="report_custom_html" id="reportCustomHtmlInput" value="">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Name</label>
                            <textarea name="name" class="w-full border border-slate-300 rounded-md px-3 py-2" rows="2">{{ old('name', $unit->name ?? $unit->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Method / Procedure *</label>
                            <input type="text" name="method_procedure" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('method_procedure', $unit->method_procedure) }}">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Manufacturer</label>
                                <input type="text" name="manufacturer" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('manufacturer', $unit->manufacturer) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Model</label>
                                <input type="text" name="model" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('model', $unit->model) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Serial Number</label>
                                <input type="text" name="serial_number" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('serial_number', $unit->serial_number) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Identification</label>
                                <input type="text" name="identification" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('identification', $unit->identification) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Service Interval</label>
                                <input type="number" name="service_interval" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('service_interval', $unit->service_interval) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Service Interval Type</label>
                                <div class="flex items-center gap-4 text-sm text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="service_interval_type" value="Month" {{ old('service_interval_type', $unit->service_interval_type)=='Month' ? 'checked' : '' }}> Month
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="service_interval_type" value="Year" {{ old('service_interval_type', $unit->service_interval_type)=='Year' ? 'checked' : '' }}> Year
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Sub-Contracted UUT</label>
                                <div class="flex items-center gap-4 text-sm text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="sub_contracted" value="1" {{ old('sub_contracted', $unit->sub_contracted) ? 'checked' : '' }}> Yes
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="sub_contracted" value="0" {{ !old('sub_contracted', $unit->sub_contracted) ? 'checked' : '' }}> No
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Accessories</label>
                                <input type="text" name="accessories" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('accessories', $unit->accessories) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Type</label>
                                <select name="type" id="typeSelect" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="On-site" {{ old('type', $unit->type)=='On-site' ? 'selected' : '' }}>On-site</option>
                                    <option value="In-house" {{ old('type', $unit->type)=='In-house' ? 'selected' : '' }}>In-house</option>
                                    <option value="Pull-out" {{ old('type', $unit->type)=='Pull-out' ? 'selected' : '' }}>Pull-out</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Receiving Method</label>
                                <select name="receiving_method" id="receivingMethod" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="Walk-in" {{ old('receiving_method', $unit->receiving_method)=='Walk-in' ? 'selected' : '' }}>Walk-in</option>
                                    <option value="Pick-up" {{ old('receiving_method', $unit->receiving_method)=='Pick-up' ? 'selected' : '' }}>Pick-up</option>
                                    <option value="Courier" {{ old('receiving_method', $unit->receiving_method)=='Courier' ? 'selected' : '' }}>Courier</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tracking No.</label>
                                <input type="text" name="tracking_no" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('tracking_no', $unit->tracking_no) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Received Date</label>
                                <input type="date" name="received_date" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('received_date', optional($unit->received_date)->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Estimated Completion Date</label>
                                <input type="date" name="estimated_completion_date" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('estimated_completion_date', optional($unit->estimated_completion_date)->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Onsite Date</label>
                                <input type="date" name="onsite_date" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('onsite_date', optional($unit->onsite_date)->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Receiving Conditions</label>
                            <input type="text" name="receiving_condition" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('receiving_condition', $unit->receiving_condition) }}">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Statements of conformance?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="require_conformance" value="1" {{ old('require_conformance', $unit->require_conformance) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="require_conformance" value="0" {{ !old('require_conformance', $unit->require_conformance) ? 'checked' : '' }}> No
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Opinions/interpretations based on results?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="require_opinion" value="1" {{ old('require_opinion', $unit->require_opinion) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="require_opinion" value="0" {{ !old('require_opinion', $unit->require_opinion) ? 'checked' : '' }}> No
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Deviate from methods selected?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="deviate_methods" value="1" {{ old('deviate_methods', $unit->deviate_methods) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="deviate_methods" value="0" {{ !old('deviate_methods', $unit->deviate_methods) ? 'checked' : '' }}> No
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Request New Method</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="request_new_method" value="1" {{ old('request_new_method', $unit->request_new_method) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="request_new_method" value="0" {{ !old('request_new_method', $unit->request_new_method) ? 'checked' : '' }}> No
                                </label>
                            </div>
                        </div>
                        <div>
                            <textarea name="request_details" class="w-full border border-slate-300 rounded-md px-3 py-2" rows="2" placeholder="Insert details here..." {{ old('request_new_method', $unit->request_new_method) ? '' : 'disabled' }}>{{ old('request_details', $unit->request_details) }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Delivery method of certificate/report</label>
                                <select name="delivery_method" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="Hand-carry / Hard copy" {{ old('delivery_method', $unit->delivery_method)=='Hand-carry / Hard copy' ? 'selected' : '' }}>Hand-carry / Hard copy</option>
                                    <option value="Courier" {{ old('delivery_method', $unit->delivery_method)=='Courier' ? 'selected' : '' }}>Courier</option>
                                    <option value="Customer Portal" {{ old('delivery_method', $unit->delivery_method)=='Customer Portal' ? 'selected' : '' }}>Customer Portal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Email, PDF</label>
                                <input type="text" name="delivery_contact" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('delivery_contact', $unit->delivery_contact) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Special Instructions</label>
                                <input type="text" name="special_instructions" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('special_instructions', $unit->special_instructions) }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Test Points / Nominal Values / Parameter</label>
                            <textarea name="test_points" class="w-full border border-slate-300 rounded-md px-3 py-2" rows="2">{{ old('test_points', $unit->test_points) }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" name="save_details" value="1" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                                Save Details
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-slate-200 pt-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">UUT Status *</label>
                                <select name="uut_status" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="Defective" {{ old('uut_status', $unit->uut_status) === 'Defective' ? 'selected' : '' }}>Defective</option>
                                    <option value="On Hold" {{ old('uut_status', $unit->uut_status) === 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="Calibrated" {{ old('uut_status', $unit->uut_status) === 'Calibrated' ? 'selected' : '' }}>Calibrated</option>
                                    <option value="Cancelled" {{ old('uut_status', $unit->uut_status) === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Reporting Status</label>
                                <select name="reporting_status" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="For Approval" {{ old('reporting_status', $unit->reporting_status) === 'For Approval' ? 'selected' : '' }}>For Approval</option>
                                    <option value="Approved" {{ old('reporting_status', $unit->reporting_status) === 'Approved' ? 'selected' : '' }}>Approved</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Date Performed *</label>
                                <input type="date" name="date_performed" class="w-full border border-slate-300 rounded-md px-3 py-2"
                                       value="{{ old('date_performed', optional($unit->date_performed)->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Certificate</label>
                                <input type="file" name="certificate" accept=".xlsm,.xlsx,.xls" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                @if($unit->certificate_path)
                                    <div class="text-xs text-slate-600 mt-1">
                                        {{ is_array($unit->certificate_path) ? ($unit->certificate_path['name'] ?? 'Download') : 'Download' }}
                                    </div>
                                    <a href="{{ route('technical.download', [$unit->id, 'certificate']) }}" class="text-xs text-sky-600 hover:underline">Download</a>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Datasheet</label>
                                <input type="file" name="datasheet" accept=".pdf" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                @if($unit->datasheet_path)
                                    <div class="text-xs text-slate-600 mt-1">
                                        {{ is_array($unit->datasheet_path) ? ($unit->datasheet_path['name'] ?? 'Download') : 'Download' }}
                                    </div>
                                    <a href="{{ route('technical.download', [$unit->id, 'datasheet']) }}" class="text-xs text-sky-600 hover:underline">Download</a>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">TSRF</label>
                                <input type="file" name="tsrf" accept=".pdf" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                @if($unit->tsrf_path)
                                    <div class="text-xs text-slate-600 mt-1">
                                        {{ is_array($unit->tsrf_path) ? ($unit->tsrf_path['name'] ?? 'Download') : 'Download' }}
                                    </div>
                                    <a href="{{ route('technical.download', [$unit->id, 'tsrf']) }}" class="text-xs text-sky-600 hover:underline">Download</a>
                                @endif
                            </div>
                        </div>
                        <div class="text-xs text-slate-600 -mt-2">
                            Upload all three files on first generation. Existing files are retained so you can regenerate without re-uploading.
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Approver's Remarks</label>
                            <input type="text" name="approver_remarks" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('approver_remarks', $unit->approver_remarks) }}">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" name="generate_certificate" value="1" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
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

        const htmlInput = document.getElementById('reportCustomHtmlInput');
        const editor = document.getElementById('reportCustomHtmlEditor');
        const editorSection = document.getElementById('editorSection');
        const customPreview = document.getElementById('customPreview');
        const parsedPreview = document.getElementById('parsedPreview');
        const chunkEditorModal = document.getElementById('chunkEditorModal');
        const chunkEditorInput = document.getElementById('chunkEditorInput');

        window.toggleEdit = function (forceShow = false) {
            if (!editorSection || !editor) return;
            const willShow = forceShow ? true : editorSection.classList.contains('hidden');
            if (willShow) {
                const current = htmlInput?.value || (customPreview ? customPreview.innerHTML.trim() : '');
                editor.value = current;
                editorSection.classList.remove('hidden');
                editor.focus();
                editorSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                editorSection.classList.add('hidden');
            }
        };

        window.cancelEdit = function () {
            if (!editorSection) return;
            editorSection.classList.add('hidden');
        };

        window.applyEdit = function () {
            if (!editor || !htmlInput) return;
            const val = editor.value;
            if (currentEditable) {
                currentEditable.innerHTML = val;
                // Persist the edited DOM into the hidden custom HTML so it survives submit
                htmlInput.value = parsedPreview?.innerHTML || val;
                customPreview?.classList.add('hidden');
                parsedPreview?.classList.remove('hidden');
            } else {
                htmlInput.value = val;
                if (customPreview) {
                    customPreview.innerHTML = val;
                    customPreview.classList.remove('hidden');
                }
                parsedPreview?.classList.add('hidden');
            }

            cancelEdit();
        };

        function insertAtCursor(text) {
            if (!editor) return;
            editor.focus();
            const start = editor.selectionStart ?? 0;
            const end = editor.selectionEnd ?? 0;
            const current = editor.value;
            editor.value = current.slice(0, start) + text + current.slice(end);
            const pos = start + text.length;
            editor.selectionStart = editor.selectionEnd = pos;
        }

        window.insertPageBreak = function () {
            insertAtCursor('\n<div style="page-break-after: always;"></div>\n');
        };

        window.formatSelection = function () {
            if (!editor) return;
            const decimals = parseInt(document.getElementById('decimalPlaceInput')?.value ?? '2', 10);
            if (Number.isNaN(decimals)) return;
            const start = editor.selectionStart ?? 0;
            const end = editor.selectionEnd ?? 0;
            if (end <= start) return;
            const sel = editor.value.slice(start, end);
            const num = parseFloat(sel);
            if (Number.isNaN(num)) return;
            const formatted = num.toFixed(decimals);
            editor.value = editor.value.slice(0, start) + formatted + editor.value.slice(end);
            const pos = start + formatted.length;
            editor.selectionStart = editor.selectionEnd = pos;
        };

        window.applyCellAttributes = function () {
            if (!editor) return;
            const colspan = parseInt(document.getElementById('colspanInput')?.value ?? '1', 10) || 1;
            const rowspan = parseInt(document.getElementById('rowspanInput')?.value ?? '1', 10) || 1;
            const width = document.getElementById('widthInput')?.value?.trim() ?? '';
            const start = editor.selectionStart ?? 0;
            const end = editor.selectionEnd ?? 0;
            const sel = editor.value.slice(start, end) || ' ';
            const widthAttr = width !== '' ? ` style="width:${width};"` : '';
            const wrapped = `<td colspan="${colspan}" rowspan="${rowspan}"${widthAttr}>${sel}</td>`;
            editor.value = editor.value.slice(0, start) + wrapped + editor.value.slice(end);
            const pos = start + wrapped.length;
            editor.selectionStart = editor.selectionEnd = pos;
        };

        window.removeTableBorder = function () {
            if (!editor) return;
            let html = editor.value;
            html = html.replace(/<table([^>]*)>/gi, (m, attrs) => {
                const clean = attrs.replace(/border\s*=\s*\"?\d+\"?/gi, '').replace(/style=\"[^\"]*\"/gi, '');
                return `<table${clean} style="border-collapse:collapse;width:100%;border:0;">`;
            });
            html = html.replace(/<t[dh]([^>]*)>/gi, (m, attrs) => {
                const clean = attrs.replace(/border\s*=\s*\"?\d+\"?/gi, '');
                return `<td${clean} style="border:0;">`;
            });
            editor.value = html;
        };

        let currentEditable = null;
        window.startEditFromPreview = function (sourceEl) {
            if (!editorSection || !editor || !sourceEl) return;
            currentEditable = sourceEl;
            const val = sourceEl.innerHTML.trim();
            editor.value = val;
            editorSection.classList.remove('hidden');
            editor.focus();
            editorSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        };

        document.querySelectorAll('.editable-chunk').forEach(el => {
            el.setAttribute('tabindex', '0');
            el.classList.add('outline-none');
            el.addEventListener('click', () => openChunkEditor(el));
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openChunkEditor(el);
                }
            });
        });

        // Fallback: Edit whole parsed preview
        parsedPreview?.addEventListener('click', (e) => {
            if (e.target.classList.contains('editable-chunk')) return;
            currentEditable = null;
            openChunkEditor(parsedPreview);
        });

        window.openChunkEditor = function (el) {
            if (!el || !chunkEditorModal || !chunkEditorInput) return;
            currentEditable = el;
            const hasChildren = el.children && el.children.length > 0;
            chunkEditorInput.value = hasChildren ? el.innerHTML.trim() : el.textContent.trim();
            chunkEditorModal.classList.remove('hidden');
            chunkEditorModal.classList.add('flex');
            chunkEditorInput.focus();
        };

        window.closeChunkEditor = function () {
            if (!chunkEditorModal) return;
            chunkEditorModal.classList.add('hidden');
            chunkEditorModal.classList.remove('flex');
            currentEditable = null;
        };

        window.applyChunkEdit = function () {
            if (!currentEditable || !chunkEditorInput) {
                closeChunkEditor();
                return;
            }
            currentEditable.innerHTML = chunkEditorInput.value;
            htmlInput.value = parsedPreview?.innerHTML || htmlInput.value;
            closeChunkEditor();
        };
    })();
</script>
