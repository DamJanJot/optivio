<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('czat');
require_once 'connect.php';

$me = (int) $_SESSION['id'];
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM wiadomosci WHERE odbiorca_id = :me AND przeczytana = 0");
$stmt->execute(['me' => $me]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['count' => (int) ($row['count'] ?? 0)]);
?>
