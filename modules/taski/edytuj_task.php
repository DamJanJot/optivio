<?php
session_start();
require_once 'db.php';

if (!isset($_POST['id']) || !isset($_SESSION['id'])) {
  header('Location: index.php');
  exit;
}

$zadanie_id = $_POST['id'];
$tytul = $_POST['tytul'];
$opis = $_POST['opis'];
$user_id = $_SESSION['id'];

// edycja tylko jeśli jesteś autorem
$stmt = $pdo->prepare("SELECT 1 FROM zadania_mobile_uzytkownicy WHERE zadanie_id = ? AND autor_id = ? LIMIT 1");
$stmt->execute([$zadanie_id, $user_id]);

if ($stmt->fetch()) {
  $pdo->prepare("UPDATE zadania_mobile_uzytkownicy SET tytul = ?, opis = ? WHERE zadanie_id = ?")->execute([$tytul, $opis, $zadanie_id]);
}

header("Location: taski.php");
exit;
