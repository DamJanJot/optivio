<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('notatnik');
require_once 'connect.php';

$userId = (int) $_SESSION['id'];
$action = (string) ($_POST['action'] ?? '');
$hasPinned = notes_column_exists($pdo, 'pinned');
$hasUpdatedAt = notes_column_exists($pdo, 'updated_at');

if ($action === 'fetch') {
    $pinnedSelect = $hasPinned ? 'pinned' : '0 AS pinned';
    $updatedSelect = $hasUpdatedAt ? 'updated_at' : 'created_at AS updated_at';
    $order = $hasPinned
        ? 'pinned DESC, ' . ($hasUpdatedAt ? 'updated_at' : 'created_at') . ' DESC'
        : ($hasUpdatedAt ? 'updated_at' : 'created_at') . ' DESC';

    $stmt = $pdo->prepare("SELECT id, content, $pinnedSelect, created_at, $updatedSelect FROM notatki_mobile WHERE user_id = ? ORDER BY $order");
    $stmt->execute([$userId]);

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'add') {
    $content = trim((string) ($_POST['content'] ?? ''));
    if ($content !== '') {
        $stmt = $pdo->prepare("INSERT INTO notatki_mobile (user_id, content) VALUES (?, ?)");
        $stmt->execute([$userId, $content]);
    }
    exit('success');
}

if ($action === 'update') {
    $id = (int) ($_POST['id'] ?? 0);
    $content = trim((string) ($_POST['content'] ?? ''));
    $sql = $hasUpdatedAt
        ? "UPDATE notatki_mobile SET content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?"
        : "UPDATE notatki_mobile SET content = ? WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$content, $id, $userId]);
    exit('updated');
}

if ($action === 'pin' && $hasPinned) {
    $id = (int) ($_POST['id'] ?? 0);
    $sql = $hasUpdatedAt
        ? "UPDATE notatki_mobile SET pinned = IF(pinned = 1, 0, 1), updated_at = updated_at WHERE id = ? AND user_id = ?"
        : "UPDATE notatki_mobile SET pinned = IF(pinned = 1, 0, 1) WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $userId]);
    exit('pinned');
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM notatki_mobile WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    exit('deleted');
}

http_response_code(400);
echo 'unknown action';
?>
