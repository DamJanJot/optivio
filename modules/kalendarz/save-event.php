<?php require_once __DIR__ . '/../core/env_loader.php'; 
$date = $_POST['date']; $title = $_POST['title']; $description = $_POST['description'];
$type = $_POST['type']; $hour = $_POST['hour']; $color = $_POST['color'];
$stmt = $pdo->prepare("INSERT INTO wydarzenia (user_id, data, tytul, opis, typ, godzina, kolor) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$_SESSION['id'], $date, $title, $description, $type, $hour, $color]);
?>