<?php
require_once __DIR__ . '/db.php';

$user_id = (int) $_SESSION['id'];
$sql = "SELECT id, tytul, data, opis, kolor, typ FROM wydarzenia WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);

$events = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $events[] = [
        'id' => (int) $row['id'],
        'title' => $row['tytul'],
        'start' => $row['data'],
        'description' => $row['opis'],
        'backgroundColor' => $row['kolor'],
        'typ' => $row['typ'],
    ];
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($events, JSON_UNESCAPED_UNICODE);
?>
