<?php
session_start();
require 'db.php';

$me = $_SESSION['id'];

// Zapisanie odczytu wszystkich postów
$posts = $pdo->query("SELECT id FROM tablica_posty")->fetchAll();

foreach ($posts as $post) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO tablica_odczyt (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$me, $post['id']]);
}

header("Location: tablica.php");
exit();
?>
