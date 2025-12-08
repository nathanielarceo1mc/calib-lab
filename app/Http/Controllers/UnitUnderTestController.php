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
        $fileRequiredBase = in_array($mode, ['files', 'generate'], true);
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
                $file = $request->file($input);
                $original = $file->getClientOriginalName();
                $storedName = uniqid($input . '_') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs("public/technical/{$input}s", $storedName);
                // Verify existence immediately
                $exists = Storage::exists($path);
                Log::info("UUT upload", ['uut_id' => $unit->id, 'input' => $input, 'path' => $path, 'exists' => $exists]);
                if (!$exists) {
                    $updates[$column] = null;
                    $updates['report_payload'] = null;
                } else {
                    $this->deleteStoredFile($existingPaths[$input]);
                    $updates[$column] = [
                        'path' => $path,
                        'name' => $original,
                    ];

                    // Parse excel for certificate to build preview payload
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
        // If generating certificate, always parse the current certificate file to refresh preview payload
        if ($request->has('generate_certificate')) {
            $certificateArr = $updates['certificate_path'] ?? $unit->certificate_path;
            $path = $this->extractStoragePath($certificateArr);
            if ($path && Storage::exists($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $parsed = in_array($ext, ['xlsx','xlsm','xls']) ? $this->parseCertificate(Storage::path($path)) : [];
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
        $column = $map[$file];
        $storageVal = $unit->{$column};
        $path = is_array($storageVal) ? ($storageVal['path'] ?? null) : $storageVal;
        if (!$path || !Storage::exists($path)) {
            abort(404);
        }
        $originalName = is_array($storageVal) ? ($storageVal['name'] ?? null) : null;
        $name = $originalName ?: ($file . '.' . pathinfo($path, PATHINFO_EXTENSION));
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

        // Try to locate the sheet by common names (case-insensitive)
        $sheet = null;
        $wanted = ['consolidate_report', 'consolidated_report'];
        foreach ($spreadsheet->getAllSheets() as $sh) {
            $title = strtolower(trim($sh->getTitle()));
            if (in_array($title, $wanted, true)) {
                $sheet = $sh;
                break;
            }
        }
        if (!$sheet) {
            /** @var Worksheet $sheet */
            $sheet = $spreadsheet->getSheetByName('Consolidate_Report')
                ?: $spreadsheet->getSheetByName('Consolidated_Report')
                ?: $spreadsheet->getActiveSheet();
        }
        if (!$sheet) {
            return [];
        }

        $rows = $sheet->toArray(null, true, true, true);
        $matrix = [];
        foreach ($rows as $rIdx => $row) {
            $matrix[$rIdx] = [];
            foreach ($row as $cIdx => $val) {
                $matrix[$rIdx][$cIdx] = is_string($val) ? trim($val) : $val;
            }
        }
        $payload = [
            'meta' => [],
            'traceability' => [],
            'calibration_results' => [],
            'tolerances' => [],
            'remarks' => '',
            'uncertainty' => '',
            'authorization' => [],
            'method' => [],
            'environment' => [],
        ];
        // Capture raw HTML representation of the selected sheet to mirror the consolidated_report layout exactly
        $payload['raw_html'] = $this->sheetToHtml($sheet);

        // Helper to normalize keys
        $norm = function ($v) {
            $v = strtolower(trim((string) $v));
            $v = str_replace(':', '', $v);
            $v = preg_replace('/\s+/', ' ', $v);
            return $v;
        };

        // Sectioned parsing based on known row layout of consolidated.xlsx
        $cell = function ($r, $c) use ($matrix) {
            return $matrix[$r][$c] ?? '';
        };
        $styled = function (int $r, string $c) use ($sheet) {
            $coord = $c . $r;
            $cellObj = $sheet->getCell($coord);
            $val = trim((string) $cellObj->getFormattedValue());
            $font = $sheet->getStyle($coord)->getFont();
            $italic = $font ? $font->getItalic() : false;
            if ($italic && $val !== '') {
                return '<i>' . $val . '</i>';
            }
            return $val;
        };
        $join = function (...$parts) {
            $parts = array_map(fn($v) => trim((string) $v), $parts);
            $parts = array_filter($parts, fn($v) => $v !== '');
            return implode(' ', $parts);
        };

        // Customer details
        $payload['meta']['customer'] = $styled(4, 'B');
        $payload['meta']['address'] = $styled(6, 'B');
        // Preserve the raw text (including any embedded tags or labels) from the sheet
        $payload['meta']['end_user'] = $join($styled(8, 'B'), $styled(9, 'B'));
        $payload['meta']['end_user_address'] = $join($styled(10, 'B'), $styled(11, 'B'));
        // Build a raw block for customer details (rows 4-11, col B) to render exactly as in the sheet
        $customerLines = [];
        foreach ([4,5,6,7,8,9,10,11] as $r) {
            $val = $cell($r, 'B');
            if ($val !== '' && $val !== null) {
                $customerLines[] = $val;
            }
        }
        $payload['customer_block'] = implode("\n", $customerLines);

        // UUT specs
        $payload['meta']['uut_name'] = $styled(14, 'D');
        $payload['meta']['material'] = $styled(15, 'D');
        $payload['meta']['manufacturer'] = $styled(16, 'D');
        $payload['meta']['model'] = $styled(17, 'D');
        $payload['meta']['serial_number'] = $styled(18, 'D');
        $payload['meta']['identification'] = $styled(19, 'D');
        // Build raw spec block (labels + values) from rows 13-20, cols B and D
        $specLines = [];
        foreach (range(13, 20) as $r) {
            $label = trim((string) $cell($r, 'B'));
            $val = trim((string) $cell($r, 'D'));
            if ($label === '' && $val === '') {
                continue;
            }
            $specLines[] = trim($label . ' ' . $val);
        }
        $payload['spec_block'] = implode("\n", $specLines);

        // Dates
        $payload['meta']['date_received'] = $styled(22, 'D');
        $payload['meta']['date_calibrated'] = $styled(23, 'D');
        $payload['meta']['recommended_due'] = $styled(24, 'D');
        $payload['meta']['date_issued'] = $styled(25, 'D');

        // Authorization
        $payload['authorization'] = [
            'calibrated_by' => $styled(31, 'B'),
            'calibrated_title' => $styled(32, 'B'),
            'reviewed_by' => $styled(31, 'E'),
            'reviewed_title' => $styled(32, 'E'),
        ];

        // Method
        $payload['method'] = [
            'method_used' => $styled(35, 'D'),
            'type_of_calibration' => $styled(36, 'D'),
            'location' => trim($join($styled(37, 'D'), $styled(38, 'D'), $styled(39, 'D'))),
        ];

        // Environment
        $payload['environment'] = [
            'temperature' => $styled(43, 'E'),
            'relative_humidity' => $styled(44, 'E'),
        ];

        // Traceability table
        $payload['traceability'] = $this->extractTable($rows, [
            'standard used',
            'manufacturer/ model',
        ], ['B','D','F','H','J']);

        // Calibration results table (Nominal Value header)
        $payload['calibration_results'] = $this->extractTable($rows, [
            'nominal value',
            'central length error',
        ], ['B','D','F','H']);

        // Tolerance / permissible deviation table: read the explicit block to preserve spans/headers
        $payload['tolerances_title'] = $styled(102, 'B');
        // Include full 8 columns (Nominal + 2 cols per grade) so Grade 2 variation is retained
        $payload['tolerances'] = $this->extractRange($rows, 103, 110, ['B','C','D','E','F','G','H','I']);

        // Uncertainty text (collect consecutive rows after the 'uncertainty' label until a blank or 'remarks')
        $uncertainty = [];
        $collecting = false;
        foreach ($rows as $idx => $row) {
            $labelA = strtolower(trim((string) ($row['A'] ?? '')));
            $textB = trim((string) ($row['B'] ?? ''));
            if (!$collecting && str_contains($labelA, 'uncertainty')) {
                $collecting = true;
                // Capture current row text (prefer B, else A)
                $uncertainty[] = $textB !== '' ? $textB : trim((string) ($row['A'] ?? ''));
                continue;
            }
            if ($collecting) {
                if ($labelA === '' && $textB === '') {
                    break;
                }
                if (str_contains($labelA, 'remarks')) {
                    break;
                }
                if ($textB !== '') {
                    $uncertainty[] = $textB;
                }
            }
        }
        $payload['uncertainty'] = trim(implode(' ', $uncertainty));

        // Remarks (after '10. REMARKS')
        $remarks = [];
        foreach ([129,130,131,132,133,134] as $r) {
            $letter = $cell($r, 'B');
            $text = $cell($r, 'C');
            if ($text !== '') {
                $remarks[] = trim($letter . ' ' . $text);
            }
        }
        $payload['remarks'] = trim(implode("\n", array_filter($remarks)));

        // Normalize any stray HTML-ish line breaks inside the generated sheet HTML (so </br> is rendered as <br>)
        $payload['raw_html'] = $this->normalizeSheetHtml(
            $this->sheetToHtml($sheet)
        );

        return $payload;
    }

    /**
     * Extract a table by matching header labels (case-insensitive) in columns starting at A.
     */
    protected function extractTable(array $rows, array $headerKeys, array $columns = null): array
    {
        $normalized = array_map(fn($h) => strtolower(trim($h)), $headerKeys);
        $startIndex = null;
        $rowKeys = array_keys($rows);
        foreach ($rowKeys as $idx) {
            $row = $rows[$idx];
            foreach ($row as $val) {
                $cell = strtolower(trim((string) $val));
                if ($cell === $normalized[0] || str_contains($cell, $normalized[0])) {
                    $startIndex = $idx + 1;
                    break 2;
                }
            }
        }
        if ($startIndex === null) {
            return [];
        }
        $table = [];
        $max = max($rowKeys);
        for ($r = $startIndex; $r <= $max; $r++) {
            if (!isset($rows[$r])) {
                continue;
            }
            $row = $rows[$r];
            $isEmpty = true;
            $item = [];
            $cols = $columns ?: array_keys($row);
            foreach ($cols as $col) {
                $val = isset($row[$col]) ? trim((string) $row[$col]) : '';
                if ($val !== '') {
                    $isEmpty = false;
                }
                $item[] = $val;
            }
            if ($isEmpty) {
                break;
            }
            $table[] = $item;
        }
        return $table;
    }

    protected function extractTableByString(array $rows, string $needle, int $skipLines = 1): array
    {
        $needle = strtolower($needle);
        $start = null;
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
        $table = [];
        $rowKeys = array_keys($rows);
        $max = max($rowKeys);
        for ($r = $start; $r <= $max; $r++) {
            if (!isset($rows[$r])) continue;
            $row = $rows[$r];
            $vals = [];
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
            $row = [];
            $isEmpty = true;
            foreach ($columns as $col) {
                $val = isset($rows[$r][$col]) ? $rows[$r][$col] : '';
                if (trim((string)$val) !== '') {
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
            // Generate HTML with the embedded CSS (mirrors how SMCS parses/prints the cert)
            $book = $sheet->getParent();
            $writer = new HtmlWriter($book);
            $writer->setSheetIndex($book->getIndex($sheet));
            ob_start();
            $writer->save('php://output');
            $html = ob_get_clean() ?: '';

            // Grab generated CSS plus the body markup so formatting/borders stay intact
            $style = '';
            if (preg_match('/<style[^>]*>(.*?)<\\/style>/is', $html, $mStyle)) {
                $style = '<style>' . ($mStyle[1] ?? '') . '</style>';
            }

            $body = $html;
            if (preg_match('/<body[^>]*>(.*)<\\/body>/is', $html, $mBody)) {
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
        // Normalize common variants and malformed closing tags
        $html = str_ireplace(
            ['</br>', '<br/>', '<br />', '&lt;/br&gt;', '&lt;br/&gt;', '&lt;br /&gt;'],
            '<br>',
            $html
        );
        // Decode placeholder row/col span markers (<rs>rowspan</rs>, <cs>colspan</cs>) that may be stored as text
        $html = str_ireplace(
            ['&lt;rs&gt;', '&lt;/rs&gt;', '&lt;cs&gt;', '&lt;/cs&gt;'],
            ['<rs>', '</rs>', '<cs>', '</cs>'],
            $html
        );

        // Apply rowspan/colspan attributes to the containing cell when markers are present
        if (str_contains(strtolower($html), '<rs>') || str_contains(strtolower($html), '<cs>')) {
            $prevUseInternal = libxml_use_internal_errors(true);
            $doc = new \DOMDocument('1.0', 'UTF-8');
            // Wrap in a body so DomDocument can parse fragment cleanly
            $wrapped = '<html><body>' . $html . '</body></html>';
            if ($doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                $xpath = new \DOMXPath($doc);
                foreach ($xpath->query('//td|//th') as $cell) {
                    /** @var \DOMElement $cell */
                    $innerHtml = '';
                    foreach (iterator_to_array($cell->childNodes) as $child) {
                        $innerHtml .= $doc->saveHTML($child);
                    }

                    $rowSpan = null;
                    $colSpan = null;
                    if (preg_match('/<rs>(\\d+)<\\/rs>/i', $innerHtml, $m)) {
                        $rowSpan = (int) $m[1];
                    }
                    if (preg_match('/<cs>(\\d+)<\\/cs>/i', $innerHtml, $m)) {
                        $colSpan = (int) $m[1];
                    }
                    if ($rowSpan || $colSpan) {
                        // Strip markers from content
                        $clean = preg_replace(['/\\s*<rs>\\d+<\\/rs>\\s*/i', '/\\s*<cs>\\d+<\\/cs>\\s*/i'], '', $innerHtml);
                        while ($cell->firstChild) {
                            $cell->removeChild($cell->firstChild);
                        }
                        if ($clean !== '') {
                            $fragment = $doc->createDocumentFragment();
                            // appendXML returns bool; fall back to text node on failure
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

                // Pull back out the body contents (without html/head wrappers)
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
