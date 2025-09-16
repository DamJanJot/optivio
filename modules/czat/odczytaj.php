<?php
session_start();
require_once 'connect.php';

$me = $_SESSION['id'];
$other = (int)$_GET['id'];

$stmt = $pdo->prepare("UPDATE wiadomosci SET przeczytana = 1 WHERE nadawca_id = ? AND odbiorca_id = ?");
$stmt->execute([$other, $me]);