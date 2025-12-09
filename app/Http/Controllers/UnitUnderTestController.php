<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnitUnderTest;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Html as HtmlWriter;

class UnitUnderTestController extends Controller
{
    public function index()
    {
        $units = UnitUnderTest::with([
            'workOrder',
            'approver',
            'personnel',
        ])
            ->orderByDesc('id')
            ->get();

        return view('technical.index', compact('units'));
    }

    public function edit(string $id)
    {
        $unit = UnitUnderTest::with(['workOrder', 'approver', 'personnel'])->findOrFail($id);
        return view('technical.edit', [
            'unit' => $unit,
            'workOrder' => $unit->workOrder,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $unit = UnitUnderTest::findOrFail($id);

        $mode = $this->resolveMode($request);
        $existingPaths = [
            'certificate' => $this->extractStoragePath($unit->certificate_path),
            'datasheet'   => $this->extractStoragePath($unit->datasheet_path),
            'tsrf'        => $this->extractStoragePath($unit->tsrf_path),
        ];

        $fileRequiredBase   = in_array($mode, ['files', 'generate'], true);
        $requireCertificate = $fileRequiredBase && empty($existingPaths['certificate']);
        $requireDatasheet   = $fileRequiredBase && empty($existingPaths['datasheet']);
        $requireTsrf        = $fileRequiredBase && empty($existingPaths['tsrf']);

        $data = $request->validate([
            'name'                          => ['nullable', 'string', 'max:255'],
            'method_procedure'              => ['nullable', 'string', 'max:255'],
            'manufacturer'                  => ['nullable', 'string', 'max:255'],
            'model'                         => ['nullable', 'string', 'max:255'],
            'serial_number'                 => ['nullable', 'string', 'max:255'],
            'identification'                => ['nullable', 'string', 'max:255'],
            'sub_contracted'                => ['nullable', 'boolean'],
            'service_interval'              => ['nullable', 'integer', 'min:0'],
            'service_interval_type'         => ['nullable', 'string', 'max:20'],
            'type'                          => ['nullable', 'string', 'max:50'],
            'receiving_method'              => ['nullable', 'string', 'max:50'],
            'tracking_no'                   => ['nullable', 'string', 'max:255'],
            'received_date'                 => ['nullable', 'date'],
            'estimated_completion_date'     => ['nullable', 'date'],
            'onsite_date'                   => ['nullable', 'date'],
            'accessories'                   => ['nullable', 'string', 'max:255'],
            'receiving_condition'           => ['nullable', 'string', 'max:500'],
            'require_conformance'           => ['nullable', 'boolean'],
            'require_opinion'               => ['nullable', 'boolean'],
            'deviate_methods'               => ['nullable', 'boolean'],
            'request_new_method'            => ['nullable', 'boolean'],
            'request_details'               => ['nullable', 'string'],
            'delivery_method'               => ['nullable', 'string', 'max:255'],
            'delivery_contact'              => ['nullable', 'string', 'max:255'],
            'special_instructions'          => ['nullable', 'string'],
            'test_points'                   => ['nullable', 'string'],
            'approver_remarks'              => ['nullable', 'string'],
            'uut_status'                    => ['nullable', 'string', 'max:50'],
            'reporting_status'              => ['nullable', 'string', 'max:50'],
            'date_performed'                => ['nullable', 'date'],
            'certificate'                   => [$requireCertificate ? 'required' : 'nullable', 'file', 'mimes:xlsm,xlsx,xls'],
            'datasheet'                     => [$requireDatasheet ? 'required' : 'nullable', 'file', 'mimes:pdf'],
            'tsrf'                          => [$requireTsrf ? 'required' : 'nullable', 'file', 'mimes:pdf'],
            'report_custom_html'            => ['nullable', 'string'],
        ]);

        // Handle uploads
        $updates = $data;
        foreach (['certificate' => 'certificate_path', 'datasheet' => 'datasheet_path', 'tsrf' => 'tsrf_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $file       = $request->file($input);
                $original   = $file->getClientOriginalName();
                $storedName = uniqid($input . '_') . '.' . $file->getClientOriginalExtension();
                $path       = $file->storeAs("public/technical/{$input}s", $storedName);

                $exists = Storage::exists($path);
                Log::info("UUT upload", [
                    'uut_id' => $unit->id,
                    'input'  => $input,
                    'path'   => $path,
                    'exists' => $exists,
                ]);

                if (!$exists) {
                    $updates[$column]           = null;
                    $updates['report_payload']  = null;
                } else {
                    $this->deleteStoredFile($existingPaths[$input]);
                    $updates[$column] = [
                        'path' => $path,
                        'name' => $original,
                    ];

                    if ($input === 'certificate') {
                        $parsed = $this->parseCertificate(Storage::path($path));
                        if (!empty($parsed)) {
                            $updates['report_payload'] = $parsed;
                        }
                    }
                }
            } else {
                unset($updates[$input]);
            }
        }

        if ($mode === 'details') {
            $allowed = [
                'name',
                'method_procedure',
                'manufacturer',
                'model',
                'serial_number',
                'identification',
                'sub_contracted',
                'type',
                'receiving_method',
                'tracking_no',
                'accessories',
                'received_date',
                'estimated_completion_date',
                'onsite_date',
                'service_interval',
                'service_interval_type',
                'receiving_condition',
                'require_conformance',
                'require_opinion',
                'deviate_methods',
                'request_new_method',
                'request_details',
                'delivery_method',
                'delivery_contact',
                'special_instructions',
                'test_points',
                'report_custom_html',
            ];
            $updates = array_intersect_key($updates, array_flip($allowed));
        } elseif (in_array($mode, ['files', 'generate'], true)) {
            $allowed = [
                'uut_status',
                'reporting_status',
                'date_performed',
                'certificate_path',
                'datasheet_path',
                'tsrf_path',
                'approver_remarks',
                'report_payload',
                'report_custom_html',
            ];
            $updates = array_intersect_key($updates, array_flip($allowed));
        }

        if (!empty($updates)) {
            $unit->update($updates);
        }

        $parseError = null;

        if ($request->has('generate_certificate')) {
            $certificateArr = $updates['certificate_path'] ?? $unit->certificate_path;
            $path           = $this->extractStoragePath($certificateArr);

            if ($path && Storage::exists($path)) {
                $ext    = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $parsed = in_array($ext, ['xlsx', 'xlsm', 'xls']) ? $this->parseCertificate(Storage::path($path)) : [];
                if (!empty($parsed)) {
                    $unit->update(['report_payload' => $parsed]);
                } else {
                    $parseError = "Parsed empty from {$path} (ext: {$ext}).";
                }
            } else {
                $parseError = "Certificate file missing or path not set.";
            }
        }

        $showPreview = $mode === 'generate';

        return redirect()->route('technical.edit', $unit->id)
            ->with('success', 'Unit Under Test updated.')
            ->with('show_preview', $showPreview)
            ->with('parse_error', $parseError);
    }

    public function download(UnitUnderTest $unit, string $file)
    {
        $map = [
            'certificate' => 'certificate_path',
            'datasheet'   => 'datasheet_path',
            'tsrf'        => 'tsrf_path',
        ];

        if (!isset($map[$file])) {
            abort(404);
        }

        $column     = $map[$file];
        $storageVal = $unit->{$column};
        $path       = is_array($storageVal) ? ($storageVal['path'] ?? null) : $storageVal;

        if (!$path || !Storage::exists($path)) {
            abort(404);
        }

        $originalName = is_array($storageVal) ? ($storageVal['name'] ?? null) : null;
        $name         = $originalName ?: ($file . '.' . pathinfo($path, PATHINFO_EXTENSION));

        return Storage::download($path, $name);
    }

    protected function resolveMode(Request $request): string
    {
        if ($request->has('generate_certificate')) {
            return 'generate';
        }
        if ($request->has('save_files')) {
            return 'files';
        }
        if ($request->has('save_details')) {
            return 'details';
        }

        return 'full';
    }

    protected function extractStoragePath(mixed $storageVal): ?string
    {
        if (is_array($storageVal)) {
            return $storageVal['path'] ?? null;
        }

        if (is_string($storageVal)) {
            $decoded = json_decode($storageVal, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded['path'] ?? null;
            }

            if (str_starts_with($storageVal, 'a:')) {
                $unserialized = @unserialize($storageVal);
                if (is_array($unserialized)) {
                    return $unserialized['path'] ?? null;
                }
            }

            return trim($storageVal) !== '' ? $storageVal : null;
        }

        return null;
    }

    protected function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    /**
     * Parse certificate Excel and build payload used by the preview/modal.
     */
    protected function parseCertificate(string $absolutePath): array
    {
        if (!is_file($absolutePath)) {
            return [];
        }

        try {
            $spreadsheet = IOFactory::load($absolutePath);
        } catch (\Throwable $e) {
            return [];
        }

        /** @var Worksheet|null $sheet */
        $sheet = $spreadsheet->getSheetByName('Consolidated_Report')
            ?: $spreadsheet->getSheetByName('consolidated_report')
            ?: $spreadsheet->getActiveSheet();

        if (!$sheet) {
            return [];
        }

        $rows = $sheet->toArray(null, true, true, true);

        // ---------- helpers ----------

        // First + last non-empty per row (with col code)
        $rowFirstLast = function (array $row): array {
            $first = null;
            $last  = null;

            foreach ($row as $col => $value) {
                $trim = trim((string) $value);
                if ($trim === '') {
                    continue;
                }

                if ($first === null) {
                    $first = [
                        'col'   => $col,
                        'value' => $trim,
                    ];
                }

                $last = [
                    'col'   => $col,
                    'value' => $trim,
                ];
            }

            return [$first, $last];
        };

        // Normalize line breaks, keep <br> and inline tags
        $normalizeCell = function ($val) {
            if (!is_string($val)) {
                return $val;
            }
            $val = str_replace(['</br>', '<br/>', '<br />'], '<br>', $val);
            return $val;
        };

        // Styled cell (preserve italics as <i>)
        $styled = function (int $r, string $c) use ($sheet): string {
            $coord   = $c . $r;
            $cellObj = $sheet->getCell($coord);
            $val     = trim((string) $cellObj->getFormattedValue());

            $font   = $sheet->getStyle($coord)->getFont();
            $italic = $font ? $font->getItalic() : false;

            if ($italic && $val !== '') {
                return '<i>' . $val . '</i>';
            }

            return $val;
        };

        // Detect section headings "1. CUSTOMER DETAILS", "2. SPECIFICATION...", etc.
        $sections = [];
        foreach ($rows as $idx => $row) {
            [$first, ] = $rowFirstLast($row);
            if (!$first) {
                continue;
            }

            $text = $first['value'];
            if (preg_match('/^(\d+)\.\s*(.+)$/', $text, $m)) {
                $num   = (int) $m[1];
                $title = $m[2];
                $sections[$num] = ['row' => $idx, 'title' => $title];
            }
        }
        ksort($sections);

        $getRange = function (int $sectionNo) use ($rows, $sections): array {
            if (!isset($sections[$sectionNo])) {
                return [null, null];
            }

            $start = $sections[$sectionNo]['row'] + 1;

            $keys   = array_keys($sections);
            $pos    = array_search($sectionNo, $keys, true);
            $maxRow = max(array_keys($rows));

            if ($pos === false || $pos === count($keys) - 1) {
                $end = $maxRow;
            } else {
                $nextNo = $keys[$pos + 1];
                $end    = $sections[$nextNo]['row'] - 1;
            }

            return [$start, $end];
        };

        // Generic label/value block parser using first/last cell
        $parseLabelValueBlock = function (int $start, int $end) use ($rows, $rowFirstLast, $normalizeCell): array {
            $out = [];

            for ($r = $start; $r <= $end; $r++) {
                if (!isset($rows[$r])) {
                    continue;
                }

                [$first, $last] = $rowFirstLast($rows[$r]);

                if (!$first && !$last) {
                    continue;
                }

                $labelRaw = $first ? $first['value'] : '';
                $label    = rtrim($labelRaw, ':');

                $value = $last ? $normalizeCell($last['value']) : '';

                $out[] = [
                    'row'   => $r,
                    'label' => $label,
                    'value' => $value,
                ];
            }

            return $out;
        };

        // ---------- payload skeleton ----------

        $payload = [
            'meta'       => [],
            'sections'   => [
                'customer'      => [],
                'specification' => [],
                'dates'         => [],
            ],
            'authorization'       => [],
            'method'              => [],
            'environment'         => [],
            'traceability'        => [],
            'calibration_results' => [],
        ];

        // Certificate no. (still assume A1)
        $payload['meta']['certificate_no'] = $styled(1, 'A');

        // ---------- 1. CUSTOMER DETAILS ----------
        $customerBlockStart = null;
        if (isset($sections[1])) {
            $customerBlockStart = $sections[1]['row'] + 1;
        }

        if ($customerBlockStart !== null) {
            $maxRow = max(array_keys($rows));

            for ($r = $customerBlockStart; $r <= $maxRow; $r++) {
                if (!isset($rows[$r])) {
                    continue;
                }

                [$first, $last] = $rowFirstLast($rows[$r]);

                // Stop sa next numbered heading (2., 3., etc.)
                if ($first && preg_match('/^\d+\.\s+/', $first['value'])) {
                    break;
                }

                if (!$first && !$last) {
                    continue;
                }

                // Label: prefer column C kung may laman, else first non-empty
                $labelCell = $rows[$r]['C'] ?? null;
                $labelText = trim((string) $labelCell);
                if ($labelText === '' && $first) {
                    $labelText = $first['value'];
                }
                $label = rtrim($labelText, ':');

                // Value: last non-empty cell, with italic styling preserved
                $value = '';
                if ($last) {
                    $value = $styled($r, $last['col']);
                    $value = $normalizeCell($value);
                }

                if ($label === '' && $value === '') {
                    continue;
                }

                $payload['sections']['customer'][] = [
                    'row'   => $r,
                    'label' => $label,
                    'value' => $value,
                ];

                $lower = strtolower($label);

                if (str_starts_with($lower, 'customer')) {
                    $payload['meta']['customer'] = $value;
                } elseif (str_starts_with($lower, 'address') && empty($payload['meta']['address'] ?? '')) {
                    $payload['meta']['address'] = $value;
                } elseif (str_starts_with($lower, 'end-user address')) {
                    $payload['meta']['end_user_address'] = $value;
                } elseif (str_starts_with($lower, 'end-user')) {
                    $payload['meta']['end_user'] = $value;
                }
            }
        }

        // ---------- 2. SPECIFICATION OF UNIT UNDER TEST ----------
        [$sStart, $sEnd] = $getRange(2);
        if ($sStart && $sEnd) {
            $specRows = $parseLabelValueBlock($sStart, $sEnd);
            $payload['sections']['specification'] = $specRows;

            foreach ($specRows as $row) {
                $label = strtolower($row['label']);
                $val   = $row['value'];

                if (str_starts_with($label, 'unit under test')) {
                    $payload['meta']['uut_name'] = $val;
                } elseif (str_starts_with($label, 'range')) {
                    $payload['meta']['range'] = $val;
                } elseif (str_starts_with($label, 'graduation')) {
                    $payload['meta']['graduation'] = $val;
                } elseif (str_starts_with($label, 'manufacturer')) {
                    $payload['meta']['manufacturer'] = $val;
                } elseif (str_starts_with($label, 'model')) {
                    $payload['meta']['model'] = $val;
                } elseif (str_starts_with($label, 'serial number')) {
                    $payload['meta']['serial_number'] = $val;
                } elseif (str_starts_with($label, 'identification')) {
                    $payload['meta']['identification'] = $val;
                }
            }
        }

        // ---------- 3. DATE OF ACTIVITIES ----------
        [$dStart, $dEnd] = $getRange(3);
        if ($dStart && $dEnd) {
            $dateRows = $parseLabelValueBlock($dStart, $dEnd);
            $payload['sections']['dates'] = $dateRows;

            foreach ($dateRows as $row) {
                $label = strtolower($row['label']);
                $val   = $row['value'];

                if (str_starts_with($label, 'date received')) {
                    $payload['meta']['date_received'] = $val;
                } elseif (str_starts_with($label, 'date calibrated')) {
                    $payload['meta']['date_calibrated'] = $val;
                } elseif (str_starts_with($label, 'recommended due')) {
                    $payload['meta']['recommended_due'] = $val;
                } elseif (str_starts_with($label, 'date issued')) {
                    $payload['meta']['date_issued'] = $val;
                }
            }
        }

        // Full HTML (for comparison / printing)
        $payload['raw_html'] = $this->normalizeSheetHtml(
            $this->sheetToHtml($sheet)
        );

        return $payload;
    }

    /**
     * Extract a table by matching header labels (case-insensitive).
     */
    protected function extractTable(array $rows, array $headerKeys, array $columns = null): array
    {
        $normalized = array_map(fn($h) => strtolower(trim($h)), $headerKeys);

        $headerRowIndex = null;

        // 1) Find header row
        foreach ($rows as $idx => $row) {
            foreach ($row as $val) {
                $cell = strtolower(trim((string) $val));
                foreach ($normalized as $needle) {
                    if ($needle !== '' && ($cell === $needle || str_contains($cell, $needle))) {
                        $headerRowIndex = $idx;
                        break 3;
                    }
                }
            }
        }

        if ($headerRowIndex === null) {
            return [];
        }

        // 2) Determine which columns belong to this table
        $headerCols = [];

        if ($columns !== null) {
            $headerCols = $columns;
        } else {
            foreach ($rows[$headerRowIndex] as $col => $val) {
                if (trim((string) $val) !== '') {
                    $headerCols[] = $col;
                }
            }
        }

        if (empty($headerCols)) {
            return [];
        }

        // 3) Read downwards until an empty row after data
        $startIndex = $headerRowIndex + 1;
        $rowKeys    = array_keys($rows);
        $max        = max($rowKeys);

        $table    = [];
        $seenData = false;

        for ($r = $startIndex; $r <= $max; $r++) {
            if (!isset($rows[$r])) {
                continue;
            }

            $row     = $rows[$r];
            $isEmpty = true;
            $item    = [];

            foreach ($headerCols as $col) {
                $val = isset($row[$col]) ? trim((string) $row[$col]) : '';
                if ($val !== '') {
                    $isEmpty = false;
                }
                $item[] = $val;
            }

            if ($isEmpty && !$seenData) {
                continue;
            }

            if ($isEmpty && $seenData) {
                break;
            }

            if (!$isEmpty) {
                $seenData = true;
                $table[]  = $item;
            }
        }

        return $table;
    }

    protected function extractTableByString(array $rows, string $needle, int $skipLines = 1): array
    {
        $needle = strtolower($needle);
        $start  = null;

        foreach ($rows as $i => $row) {
            foreach ($row as $val) {
                if (str_contains(strtolower(trim((string) $val)), $needle)) {
                    $start = $i + $skipLines;
                    break 2;
                }
            }
        }

        if ($start === null) {
            return [];
        }

        $table   = [];
        $rowKeys = array_keys($rows);
        $max     = max($rowKeys);

        for ($r = $start; $r <= $max; $r++) {
            if (!isset($rows[$r])) {
                continue;
            }

            $row    = $rows[$r];
            $vals   = [];
            $isEmpty = true;

            foreach ($row as $val) {
                $val = trim((string) $val);
                if ($val !== '') {
                    $isEmpty = false;
                }
                $vals[] = $val;
            }

            if ($isEmpty) {
                break;
            }

            $table[] = $vals;
        }

        return $table;
    }

    /**
     * Extract a rectangular range of rows/columns preserving empty cells.
     */
    protected function extractRange(array $rows, int $rowStart, int $rowEnd, array $columns): array
    {
        $table = [];

        for ($r = $rowStart; $r <= $rowEnd; $r++) {
            if (!isset($rows[$r])) {
                continue;
            }

            $row     = [];
            $isEmpty = true;

            foreach ($columns as $col) {
                $val = isset($rows[$r][$col]) ? $rows[$r][$col] : '';
                if (trim((string) $val) !== '') {
                    $isEmpty = false;
                }
                $row[] = $val;
            }

            if ($isEmpty) {
                continue;
            }

            $table[] = $row;
        }

        return $table;
    }

    /**
     * Render a single worksheet to HTML and return the body contents.
     */
    protected function sheetToHtml(Worksheet $sheet): string
    {
        try {
            $book   = $sheet->getParent();
            $writer = new HtmlWriter($book);
            $writer->setSheetIndex($book->getIndex($sheet));

            ob_start();
            $writer->save('php://output');
            $html = ob_get_clean() ?: '';

            $style = '';
            if (preg_match('/<style[^>]*>(.*?)<\/style>/is', $html, $mStyle)) {
                $style = '<style>' . ($mStyle[1] ?? '') . '</style>';
            }

            $body = $html;
            if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $mBody)) {
                $body = $mBody[1];
            }

            return trim($style . $body);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Clean up invalid break tags from PhpSpreadsheet output so they render properly.
     */
    protected function normalizeSheetHtml(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $html = str_ireplace(
            ['</br>', '<br/>', '<br />', '&lt;/br&gt;', '&lt;br/&gt;', '&lt;br /&gt;'],
            '<br>',
            $html
        );

        $html = str_ireplace(
            ['&lt;rs&gt;', '&lt;/rs&gt;', '&lt;cs&gt;', '&lt;/cs&gt;'],
            ['<rs>', '</rs>', '<cs>', '</cs>'],
            $html
        );

        if (str_contains(strtolower($html), '<rs>') || str_contains(strtolower($html), '<cs>')) {
            $prevUseInternal = libxml_use_internal_errors(true);
            $doc             = new \DOMDocument('1.0', 'UTF-8');

            $wrapped = '<html><body>' . $html . '</body></html>';

            if ($doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                $xpath = new \DOMXPath($doc);

                foreach ($xpath->query('//td|//th') as $cell) {
                    /** @var \DOMElement $cell */
                    $innerHtml = '';
                    foreach (iterator_to_array($cell->childNodes) as $child) {
                        $innerHtml += $doc->saveHTML($child);
                    }

                    $rowSpan = null;
                    $colSpan = null;

                    if (preg_match('/<rs>(\d+)<\/rs>/i', $innerHtml, $m)) {
                        $rowSpan = (int) $m[1];
                    }
                    if (preg_match('/<cs>(\d+)<\/cs>/i', $innerHtml, $m)) {
                        $colSpan = (int) $m[1];
                    }

                    if ($rowSpan || $colSpan) {
                        $clean = preg_replace(
                            ['/\\s*<rs>\\d+<\\/rs>\\s*/i', '/\\s*<cs>\\d+<\\/cs>\\s*/i'],
                            '',
                            $innerHtml
                        );

                        while ($cell->firstChild) {
                            $cell->removeChild($cell->firstChild);
                        }

                        if ($clean !== '') {
                            $fragment = $doc->createDocumentFragment();
                            if (!$fragment->appendXML($clean)) {
                                $cell->appendChild($doc->createTextNode(strip_tags($clean)));
                            } else {
                                $cell->appendChild($fragment);
                            }
                        }

                        if ($rowSpan) {
                            $cell->setAttribute('rowspan', (string) $rowSpan);
                        }
                        if ($colSpan) {
                            $cell->setAttribute('colspan', (string) $colSpan);
                        }
                    }
                }

                $body = $doc->getElementsByTagName('body')->item(0);
                if ($body) {
                    $rebuilt = '';
                    foreach ($body->childNodes as $child) {
                        $rebuilt .= $doc->saveHTML($child);
                    }
                    $html = $rebuilt;
                }
            }

            libxml_clear_errors();
            libxml_use_internal_errors($prevUseInternal);
        }

        return $html;
    }
}
