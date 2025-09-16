<?php
require_once 'db.php';
$id = $_POST['id'];
$stmt = $pdo->prepare("UPDATE zadania_mobile_uzytkownicy SET wykonane = 1 WHERE zadanie_id = ?");
$stmt->execute([$id]);
header("Location: taski.php");
exit;