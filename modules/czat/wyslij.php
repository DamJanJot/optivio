<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}
require_once 'connect.php';

$nadawca = $_SESSION['id'];
$odbiorca = $_POST['odbiorca_id'];
$tresc = trim($_POST['tresc']);

if (!empty($tresc)) {
    $stmt = $pdo->prepare("INSERT INTO wiadomosci (nadawca_id, odbiorca_id, tresc, data_wyslania, przeczytana) VALUES (?, ?, ?, NOW(), 0)");
    $stmt->execute([$nadawca, $odbiorca, $tresc]);
}
header("Location: rozmowa.php?id=" . $odbiorca);
exit;