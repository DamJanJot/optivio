<?php
require_once __DIR__ . '/db.php';

$month = max(1, min(12, (int) ($_GET['month'] ?? date('n'))));
$year = (int) ($_GET['year'] ?? date('Y'));
$stmt = $pdo->prepare("SELECT data, kolor FROM wydarzenia WHERE user_id = ? AND MONTH(data) = ? AND YEAR(data) = ?");
$stmt->execute([(int) $_SESSION['id'], $month, $year]);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
?>
