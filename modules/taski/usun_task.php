<?php
session_start();
require_once 'db.php';

if (!isset($_POST['zadanie_id']) || !isset($_SESSION['id'])) {
  header('Location: taski.php');
  exit;
}

$zadanie_id = $_POST['zadanie_id'];
$uzytkownik_id = $_SESSION['id'];

// sprawdź, czy użytkownik jest autorem
$stmt = $pdo->prepare("SELECT 1 FROM zadania_mobile_uzytkownicy WHERE zadanie_id = ? AND autor_id = ? LIMIT 1");
$stmt->execute([$zadanie_id, $uzytkownik_id]);

if ($stmt->fetch()) {
  $pdo->prepare("DELETE FROM zadania_mobile_uzytkownicy WHERE zadanie_id = ?")->execute([$zadanie_id]);
}

header("Location: taski.php");
exit;
