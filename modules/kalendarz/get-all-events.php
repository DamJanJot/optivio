<?php require_once __DIR__ . '/../core/env_loader.php'; 
$month = $_GET['month']; $year = $_GET['year'];
$stmt = $pdo->prepare("SELECT data, kolor FROM wydarzenia WHERE user_id = ? AND MONTH(data) = ? AND YEAR(data) = ?");
$stmt->execute([$_SESSION['id'], $month, $year]); $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json'); echo json_encode($events);
?>