<?php
session_start();
if (!isset($_SESSION['loggedin'])) exit();

$user_id = $_SESSION['id'];
$db = new SQLite3('tabele.db');

$id = (int)$_GET['id'];
$type = $_GET['type'];

$sheet = $db->querySingle("SELECT * FROM sheets WHERE id = $id AND user_id = $user_id", true);
if (!$sheet) die("Brak arkusza.");

$columns = json_decode($sheet['columns'], true);
$data = json_decode($sheet['content'], true);

if ($type == "csv") {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sheet.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, $columns);
    foreach ($data as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);
} elseif ($type == "xlsx") {
    require 'vendor/autoload.php';
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheetXLSX = $spreadsheet->getActiveSheet();

    foreach ($columns as $c => $val) {
        $sheetXLSX->setCellValueByColumnAndRow($c+1, 1, $val);
    }

    foreach ($data as $r => $fields) {
        foreach ($fields as $c => $val) {
            $sheetXLSX->setCellValueByColumnAndRow($c+1, $r+2, $val);
        }
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="sheet.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
}
?>
