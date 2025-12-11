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

    // ---------------- REPORT PAYLOAD NORMALIZATION ----------------
    $reportRaw = $unit->report_payload ?? [];

    // Handle both casted array and raw JSON string
    if (is_string($reportRaw)) {
        $report = json_decode($reportRaw, true) ?: [];
    } elseif (is_array($reportRaw)) {
        $report = $reportRaw;
    } else {
        $report = [];
    }

    // Helper: normalize any value → string
    $normalizeText = function ($val): string {
        if (is_array($val)) {
            // Flatten array into lines, ignore empty items
            $val = implode("\n", array_filter($val, function ($v) {
                return trim((string) $v) !== '';
            }));
        }

        if ($val === null) {
            return '';
        }

        return (string) $val;
    };

    // Pull out sections from payload (if present)
    $uncText = $normalizeText($report['uncertainty'] ?? '');
    $remarks = $normalizeText($report['remarks'] ?? '');

    // You can expand later:
    // $specText      = $normalizeText($report['sections']['specification'] ?? '');
    // $resultsText   = $normalizeText($report['calibration_results'] ?? '');
    // $traceText     = $normalizeText($report['traceability'] ?? '');
    // etc.

    return view('technical.edit', [
        'unit'      => $unit,
        'workOrder' => $unit->workOrder,

        // pass normalized stuff to Blade
        'report'    => $report,
        'uncText'   => $uncText,
        'remarks'   => $remarks,
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

        // ---------------- FILE UPLOADS ----------------
        $updates = $data;
        foreach (['certificate' => 'certificate_path', 'datasheet' => 'datasheet_path', 'tsrf' => 'tsrf_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $file       = $request->file($input);
                $original   = $file->getClientOriginalName();
                $storedName = uniqid($input . '_') . '.' . $file->getClientOriginalExtension();
                $path       = $file->storeAs("public/technical/{$input}s", $storedName);

                $exists = Storage::exists($path);
                Log::info('UUT upload', [
                    'uut_id' => $unit->id,
                    'input'  => $input,
                    'path'   => $path,
                    'exists' => $exists,
                ]);

                if (!$exists) {
                    $updates[$column]          = null;
                    $updates['report_payload'] = null;
                } else {
                    // delete old
                    $this->deleteStoredFile($existingPaths[$input]);

                    // save new
                    $updates[$column] = [
                        'path' => $path,
                        'name' => $original,
                    ];

                    // auto-parse certificate on upload
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

        // ---------------- MODE FILTER (details / files) ----------------
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

        // ---------------- MANUAL "Generate Certificate" BUTTON ----------------
        $parseError = null;

        if ($request->has('generate_certificate')) {
            $certificateArr = $updates['certificate_path'] ?? $unit->certificate_path;
            $path           = $this->extractStoragePath($certificateArr);

            if ($path && Storage::exists($path)) {
                $ext    = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $parsed = in_array($ext, ['xlsx', 'xlsm', 'xls'])
                    ? $this->parseCertificate(Storage::path($path))
                    : [];

                if (!empty($parsed)) {
                    $unit->update(['report_payload' => $parsed]);
                } else {
                    $parseError = "Parsed empty from {$path} (ext: {$ext}).";
                }
            } else {
                $parseError = 'Certificate file missing or path not set.';
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

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

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
            // JSON encoded?
            $decoded = json_decode($storageVal, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded['path'] ?? null;
            }

            // Old serialized format?
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
 * Parse certificate Excel and build compact JSON payload (no raw HTML).
 */
/**
 * Parse certificate Excel and build compact JSON payload (no raw HTML).
 */
protected function parseCertificate(string $absolutePath): array
{
    // Base payload structure
    $payload = [
        'meta'     => [
            'certificate_no'   => null,
            'customer'         => null,
            'address'          => null,
            'end_user'         => null,
            'end_user_address' => null,
            'date_received'    => null,
            'date_calibrated'  => null,
            'recommended_due'  => null,
            'date_issued'      => null,
        ],
        'sections' => [
            'customer'      => [],
            'specification' => [],
            'dates'         => [],
        ],
        'environment'         => [],
        'traceability_note'   => null,
        'traceability'        => [],
        'calibration_results' => [],
        'uncertainty'         => [],
        'remarks'             => [],
        'authorization'       => [],
        'method'              => [],
    ];

    if (!is_file($absolutePath)) {
        return $payload;
    }

    try {
        $spreadsheet = IOFactory::load($absolutePath);
    } catch (\Throwable $e) {
        return $payload;
    }

    /** @var Worksheet|null $sheet */
    $sheet = $spreadsheet->getSheetByName('Consolidated_Report')
        ?: $spreadsheet->getSheetByName('consolidated_report');

    // support “Consolidated_Report (Autoclave)” style
    if (!$sheet) {
        foreach ($spreadsheet->getWorksheetIterator() as $candidate) {
            $title = $candidate->getTitle();
            if (stripos($title, 'consolidated_report') === 0) {
                $sheet = $candidate;
                break;
            }
        }
    }

    if (!$sheet) {
        $sheet = $spreadsheet->getActiveSheet();
    }

    if (!$sheet) {
        return $payload;
    }

    $rows = $sheet->toArray(null, true, true, true);

    // ---------- helpers ----------

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

    $normalizeCell = function ($val) {
        if (!is_string($val)) {
            return $val;
        }
        return str_replace(['</br>', '<br/>', '<br />'], '<br>', $val);
    };

    $styled = function (int $r, string $c) use ($sheet, $normalizeCell): string {
        $coord   = $c . $r;
        $cellObj = $sheet->getCell($coord);
        $val     = trim((string) $cellObj->getFormattedValue());

        $font   = $sheet->getStyle($coord)->getFont();
        $italic = $font ? $font->getItalic() : false;

        if ($italic && $val !== '') {
            $val = '<i>' . $val . '</i>';
        }

        return $normalizeCell($val);
    };

    // ---------- detect numbered headings (1., 2., 3., ...) ----------

    $sections = [];
    foreach ($rows as $idx => $row) {
        [$first, ] = $rowFirstLast($row);
        if (!$first) {
            continue;
        }

        $text = $first['value'];
        if (preg_match('/^(\d+)\.\s*(.+)$/', (string) $text, $m)) {
            $num   = (int) $m[1];
            $title = trim($m[2]);
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

    // ---------- 1. CUSTOMER DETAILS ----------

    // certificate no. (assume A1)
    $payload['meta']['certificate_no'] = $styled(1, 'A');

    $customerBlockStart = isset($sections[1]) ? $sections[1]['row'] + 1 : null;

    if ($customerBlockStart !== null) {
        $maxRow = max(array_keys($rows));

        for ($r = $customerBlockStart; $r <= $maxRow; $r++) {
            if (!isset($rows[$r])) {
                continue;
            }

            [$first, $last] = $rowFirstLast($rows[$r]);

            // stop at next numbered heading
            if ($first && preg_match('/^\d+\.\s+/', $first['value'])) {
                break;
            }

            if (!$first && !$last) {
                continue;
            }

            $labelCell = $rows[$r]['C'] ?? null;
            $labelText = trim((string) $labelCell);
            if ($labelText === '' && $first) {
                $labelText = $first['value'];
            }
            $label = rtrim($labelText, ':');

            $value = '';
            if ($last) {
                $value = $styled($r, $last['col']);
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

    // ---------- 2. SPECIFICATION ----------

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

    // ---------- 3. DATES ----------

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

    // ---------- 6. ENVIRONMENTAL CONDITIONS ----------

    [$eStart, $eEnd] = $getRange(6);
    if ($eStart && $eEnd) {
        for ($r = $eStart; $r <= $eEnd; $r++) {
            if (!isset($rows[$r])) {
                continue;
            }

            [$first, $last] = $rowFirstLast($rows[$r]);
            if (!$first && !$last) {
                continue;
            }

            $label = strtolower($first['value'] ?? '');
            $val   = $last ? $styled($r, $last['col']) : '';

            if (str_contains($label, 'temperature')) {
                $payload['environment']['temperature'] = $val;
            } elseif (str_contains($label, 'relative humidity')) {
                $payload['environment']['relative_humidity'] = $val;
            }
        }
    }

    // ---------- 7. TRACEABILITY NOTE (fixed text) ----------

    [$tStart, $tEnd] = $getRange(7);
    if ($tStart && $tEnd) {
        $lines = [];
        for ($r = $tStart; $r <= $tEnd; $r++) {
            if (!isset($rows[$r])) {
                continue;
            }

            $row = $rows[$r];
            $text = $row['B'] ?? null;
            if (!is_string($text) || trim($text) === '') {
                [$first, ] = $rowFirstLast($row);
                $text = $first['value'] ?? null;
            }

            if (is_string($text) && trim($text) !== '') {
                $lines[] = $normalizeCell($text);
            }
        }

        $payload['traceability_note'] = implode("\n", $lines);
    }

    // ---------- heading list for paragraph sections ----------

    $headingList = [];
    foreach ($sections as $num => $info) {
        $headingList[] = [
            'row'   => $info['row'],
            'num'   => $num,
            'title' => $info['title'],
        ];
    }

    usort($headingList, function ($a, $b) {
        return $a['row'] <=> $b['row'];
    });

    /**
     * Collect paragraph-style text from one numbered section heading
     * (e.g. "UNCERTAINTY OF MEASUREMENTS", "REMARKS"),
     * using heading rows and ranges (robust kahit may blank rows).
     */
    $collectParagraphSection = function (string $needle) use ($headingList, $rows, $rowFirstLast, $normalizeCell): array {
        $needleLower = strtolower($needle);
        $startRow    = null;
        $endRow      = null;

        if (empty($rows)) {
            return [];
        }

        $maxRow = max(array_keys($rows));
        $count  = count($headingList);

        // Find the heading row by matching title
        for ($i = 0; $i < $count; $i++) {
            $titleLower = strtolower($headingList[$i]['title']);
            if (!str_contains($titleLower, $needleLower)) {
                continue;
            }

            $startRow = $headingList[$i]['row'] + 1;

            // End row = row before next heading (or end of sheet)
            if ($i === $count - 1) {
                $endRow = $maxRow;
            } else {
                $endRow = $headingList[$i + 1]['row'] - 1;
            }

            break;
        }

        if (!$startRow || !$endRow || $startRow > $endRow) {
            return [];
        }

        $lines = [];

        for ($r = $startRow; $r <= $endRow; $r++) {
            if (!isset($rows[$r])) {
                continue;
            }

            $row  = $rows[$r];
            $text = $row['B'] ?? null; // main text nasa col B

            if (!is_string($text) || trim($text) === '') {
                [$first, ] = $rowFirstLast($row);
                $text = $first['value'] ?? null;
            }

            if (!is_string($text)) {
                continue;
            }

            $clean = $normalizeCell($text);

            if (trim(strip_tags($clean)) === '') {
                continue;
            }

            $lines[] = $clean;
        }

        return array_values($lines);
    };

    // ---------- 9. UNCERTAINTY (any heading containing "uncertainty") ----------
    $payload['uncertainty'] = $collectParagraphSection('uncertainty');

    // ---------- *. REMARKS (any heading containing "remark") ----------
    $payload['remarks']     = $collectParagraphSection('remark');

    return $payload;
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

            $row     = $rows[$r];
            $vals    = [];
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
     * Clean up invalid break tags from PhpSpreadsheet output so they render properly,
     * and convert <cs>/<rs> markers into real colspan/rowspan attributes.
     */
    protected function normalizeSheetHtml(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        // Normalize line breaks
        $html = str_ireplace(
            ['</br>', '<br/>', '<br />', '&lt;/br&gt;', '&lt;br/&gt;', '&lt;br /&gt;'],
            '<br>',
            $html
        );

        // Decode custom colspan/rowspan markers if they were HTML-escaped
        $html = str_ireplace(
            ['&lt;rs&gt;', '&lt;/rs&gt;', '&lt;cs&gt;', '&lt;/cs&gt;'],
            ['<rs>', '</rs>', '<cs>', '</cs>'],
            $html
        );

        // If there are no markers, nothing more to do
        if (!str_contains(strtolower($html), '<rs>') && !str_contains(strtolower($html), '<cs>')) {
            return $html;
        }

        $prevUseInternal = libxml_use_internal_errors(true);
        $doc             = new \DOMDocument('1.0', 'UTF-8');

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

        return $html;
    }
}
