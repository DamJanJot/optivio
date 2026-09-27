<?php
require_once __DIR__ . '/db.php';

$id = (int) ($_POST['id'] ?? 0);
$title = trim((string) ($_POST['title'] ?? ''));
$desc = trim((string) ($_POST['description'] ?? ''));
$type = trim((string) ($_POST['type'] ?? ''));
$hour = trim((string) ($_POST['hour'] ?? ''));
$color = trim((string) ($_POST['color'] ?? '#3478f6'));

if ($id <= 0 || $title === '') {
    http_response_code(422);
    exit('Brak wymaganych danych.');
}

$stmt = $pdo->prepare("UPDATE wydarzenia SET tytul = ?, opis = ?, typ = ?, godzina = ?, kolor = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$title, $desc, $type, $hour, $color, $id, (int) $_SESSION['id']]);
?>
