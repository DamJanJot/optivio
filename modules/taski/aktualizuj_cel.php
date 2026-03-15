<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['id'], $_POST['id'])) {
    header('Location: taski.php');
    exit;
}

$cel_id = (int) $_POST['id'];
$status = isset($_POST['wykonane']) ? 1 : 0;
$user_id = (int) $_SESSION['id'];

if ($cel_id <= 0) {
    header('Location: taski.php');
    exit;
}

try {
    $accessStmt = $pdo->prepare(
        "SELECT c.id
         FROM cele c
         JOIN zadania_mobile_uzytkownicy z ON z.zadanie_id = c.zadanie_id
         WHERE c.id = ? AND z.user_id = ?
         LIMIT 1"
    );
    $accessStmt->execute([$cel_id, $user_id]);

    if ($accessStmt->fetch()) {
        $update = $pdo->prepare("UPDATE cele SET wykonane = ? WHERE id = ?");
        $update->execute([$status, $cel_id]);
    }
} catch (Throwable $e) {
    // Silent fallback to avoid breaking the UI when the table is missing.
}

header('Location: taski.php');
exit;
