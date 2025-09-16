<?php
session_start();
require_once 'connect.php';

$me = $_SESSION['id'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM zadania_mobile_uzytkownicy WHERE user_id = :me AND przeczytane = 0");
$stmt->execute(['me' => $me]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['count' => (int)$row['count']]);
?>
