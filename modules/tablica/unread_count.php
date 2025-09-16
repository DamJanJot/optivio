<?php
session_start();
require 'db.php';

$me = $_SESSION['id'];
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM tablica_posty WHERE id NOT IN (SELECT post_id FROM tablica_odczyt WHERE user_id = ?)");
$stmt->execute([$me]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['count' => (int)$row['count']]);
?>
