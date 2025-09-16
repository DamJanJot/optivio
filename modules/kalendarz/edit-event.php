<?php require_once __DIR__ . '/../core/env_loader.php'; 
$id = $_POST['id']; $title = $_POST['title']; $desc = $_POST['description'];
$type = $_POST['type']; $hour = $_POST['hour']; $color = $_POST['color'];
$stmt = $pdo->prepare("UPDATE wydarzenia SET tytul = ?, opis = ?, typ = ?, godzina = ?, kolor = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$title, $desc, $type, $hour, $color, $id, $_SESSION['id']]);
?>