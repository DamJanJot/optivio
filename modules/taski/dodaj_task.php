<?php
session_start();
require_once 'db.php';

$tytul = $_POST['tytul'];
$opis = $_POST['opis'];
$autor_id = $_SESSION['id'];

$wybrani_uzytkownicy = $_POST['uzytkownicy'] ?? [];

$stmtInsert = $pdo->prepare("
  INSERT INTO zadania_mobile_uzytkownicy 
  (zadanie_id, user_id, tytul, opis, wykonane, created_at, autor_id) 
  VALUES (?, ?, ?, ?, 0, NOW(), ?)
");

$random_task_id = rand(100000, 999999);

// Dodaj dla autora (zawsze)
$stmtInsert->execute([$random_task_id, $autor_id, $tytul, $opis, $autor_id]);

// Dodaj dla wybranych użytkowników (jeśli są)
foreach ($wybrani_uzytkownicy as $user_id) {
  if ($user_id != $autor_id) {
    $stmtInsert->execute([$random_task_id, $user_id, $tytul, $opis, $autor_id]);
  }
}

header("Location: taski.php");
exit;
