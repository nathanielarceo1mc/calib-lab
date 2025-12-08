<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$p = 'C:/Users/Natsu/calib-lab/Sample Cert/consolidated.xlsx';
$spreadsheet = IOFactory::load($p);
$sheet = $spreadsheet->getSheetByName('Consolidated_Report') ?? $spreadsheet->getSheetByName('Consolidate_Report') ?? $spreadsheet->getActiveSheet();
$rows = $sheet->rangeToArray('A1:J150', null, true, true, true);
foreach ($rows as $idx => $row) {
    echo $idx, ':';
    foreach (['A','B','C','D','E','F','G','H','I','J'] as $col) {
        $v = $row[$col];
        if (is_array($v)) $v = json_encode($v);
        $v = str_replace(["\n","\r"], ' ', (string)$v);
        echo "[$col=$v]";
    }
    echo "\n";
}
?>
