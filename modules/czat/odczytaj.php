<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('czat');
require_once 'connect.php';

$me = (int) $_SESSION['id'];
$other = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("UPDATE wiadomosci SET przeczytana = 1 WHERE nadawca_id = ? AND odbiorca_id = ?");
$stmt->execute([$other, $me]);
?>
