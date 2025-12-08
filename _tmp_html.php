<?php
require "vendor/autoload.php";
$path = __DIR__ . '/storage/app/private/public/technical/certificates/certificate_693651720f5ca.xlsx';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
$sheet = $spreadsheet->getSheet(0);
try {
    $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $book->removeSheetByIndex(0);
    $clone = clone $sheet;
    $clone->setTitle('Sheet1');
    $book->addExternalSheet($clone);
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($book);
    $writer->setSheetIndex(0);
    ob_start();
    $writer->save('php://output');
    $html = ob_get_clean();
    echo strlen($html)."\n";
    echo substr($html,0,500);
} catch (\Throwable $e) {
    echo 'ERR:'.$e->getMessage();
}
