<?php require_once __DIR__ . '/../core/env_loader.php'; 
$id = $_GET['id']; $stmt = $pdo->prepare("DELETE FROM wydarzenia WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['id']]);
?>