<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['id'], $_POST['zadanie_id'], $_POST['opis'])) {
    header('Location: taski.php');
    exit;
}

$zadanie_id = (int) $_POST['zadanie_id'];
$opis = trim((string) $_POST['opis']);
$user_id = (int) $_SESSION['id'];

if ($zadanie_id <= 0 || $opis === '') {
    header('Location: taski.php');
    exit;
}

try {
    $isAuthorStmt = $pdo->prepare("SELECT 1 FROM zadania_mobile_uzytkownicy WHERE zadanie_id = ? AND autor_id = ? LIMIT 1");
    $isAuthorStmt->execute([$zadanie_id, $user_id]);

    if ($isAuthorStmt->fetch()) {
        $insert = $pdo->prepare("INSERT INTO cele (zadanie_id, opis, wykonane) VALUES (?, ?, 0)");
        $insert->execute([$zadanie_id, $opis]);
    }
} catch (Throwable $e) {
    // Silent fallback to avoid breaking the UI when the table is missing.
}

header('Location: taski.php');
exit;
