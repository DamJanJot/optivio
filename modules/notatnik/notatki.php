<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    exit('Brak dostępu');
}
require_once 'connect.php';

$user_id = $_SESSION['id'];
$action = $_POST['action'] ?? '';

if ($action === 'fetch') {
    $stmt = $pdo->prepare("SELECT * FROM notatki_mobile WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action === 'add') {
    $content = trim($_POST['content']);
    if ($content !== '') {
        $stmt = $pdo->prepare("INSERT INTO notatki_mobile (user_id, content) VALUES (?, ?)");
        $stmt->execute([$user_id, $content]);
        echo 'success';
    }
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id']);
    $content = trim($_POST['content']);
    $stmt = $pdo->prepare("UPDATE notatki_mobile SET content = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$content, $id, $user_id]);
    echo 'updated';
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $pdo->prepare("DELETE FROM notatki_mobile WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    echo 'deleted';
    exit;
}

echo 'no action';
?>
