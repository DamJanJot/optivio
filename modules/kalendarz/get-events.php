<?php
require_once __DIR__ . '/db.php';

$date = (string) ($_GET['date'] ?? '');
$stmt = $pdo->prepare("SELECT id, tytul, opis, typ, godzina, kolor FROM wydarzenia WHERE data = ? AND user_id = ? ORDER BY godzina ASC, id ASC");
$stmt->execute([$date, (int) $_SESSION['id']]);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
?>
