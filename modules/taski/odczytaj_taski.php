<?php
session_start();
require_once 'connect.php';

$me = $_SESSION['id'];

$stmt = $pdo->prepare("UPDATE zadania_mobile_uzytkownicy SET przeczytane = 1 WHERE user_id = ?");
$stmt->execute([$me]);
?>
