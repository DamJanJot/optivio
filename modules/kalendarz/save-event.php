<?php
require_once __DIR__ . '/db.php';

$date = (string) ($_POST['date'] ?? '');
$title = trim((string) ($_POST['title'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$type = trim((string) ($_POST['type'] ?? ''));
$hour = trim((string) ($_POST['hour'] ?? ''));
$color = trim((string) ($_POST['color'] ?? '#3478f6'));

if ($date === '' || $title === '') {
    http_response_code(422);
    exit('Brak wymaganych danych.');
}

$stmt = $pdo->prepare("INSERT INTO wydarzenia (user_id, data, tytul, opis, typ, godzina, kolor) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([(int) $_SESSION['id'], $date, $title, $description, $type, $hour, $color]);
?>
