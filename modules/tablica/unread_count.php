<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('tablica');
require 'db.php';

$me = (int) $_SESSION['id'];
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM tablica_posty WHERE id NOT IN (SELECT post_id FROM tablica_odczyt WHERE user_id = ?)");
$stmt->execute([$me]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['count' => (int) ($row['count'] ?? 0)]);
?>
