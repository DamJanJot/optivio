<?php require_once __DIR__ . '/../core/env_loader.php'; 
$date = $_GET['date'] ?? ''; 
$stmt = $pdo->prepare("SELECT id, tytul, opis, typ, godzina, kolor FROM wydarzenia WHERE data = ? AND user_id = ?");
$stmt->execute([$date, $_SESSION['id']]); $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json'); echo json_encode($events);
?>