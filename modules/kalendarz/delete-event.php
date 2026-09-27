<?php
require_once __DIR__ . '/db.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM wydarzenia WHERE id = ? AND user_id = ?");
$stmt->execute([$id, (int) $_SESSION['id']]);
?>
