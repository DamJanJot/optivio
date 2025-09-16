<?php require_once __DIR__ . '/../core/env_loader.php'; 
session_start();


$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

$user_id = $_SESSION['id']; // Pobieranie ID zalogowanego użytkownika

$sql = "SELECT * FROM wydarzenia WHERE prywatne = 0 OR (prywatne = 1 AND user_id = ?)"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['id'],
        'title' => $row['tytul'],
        'start' => $row['data'],
        'description' => $row['opis'],
        'backgroundColor' => $row['kolor'],
        'typ' => $row['typ']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($events);
?>