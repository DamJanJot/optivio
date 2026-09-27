<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('tablica');

require 'db.php';

$me = (int) $_SESSION['id'];

// Zapisanie odczytu wszystkich postów.
$posts = $pdo->query("SELECT id FROM tablica_posty")->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as $post) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO tablica_odczyt (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$me, (int) $post['id']]);
}

header("Location: tablica.php");
exit();
?>
