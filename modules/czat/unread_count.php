<?php
session_start();
require_once 'connect.php';

$me = $_SESSION['id'];
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM wiadomosci WHERE odbiorca_id = :me AND przeczytana = 0");
$stmt->execute(['me' => $me]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['count' => (int)$row['count']]);
?>