<?php

require_once __DIR__ . '/../core/env_loader.php'; 

$charset = 'utf8mb4';


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Błąd połączenia z bazą: ' . $e->getMessage());
}

if (isset($_POST['word_id'], $_POST['translation'])) {
    $word_id = $_POST['word_id'];
    $user_answer = $_POST['translation'];

    $stmt = $pdo->prepare("SELECT word_pl, mistakes FROM words WHERE id = ?");
    $stmt->execute([$word_id]);
    $row = $stmt->fetch();

    if (!$row) {
        exit('Nie znaleziono słówka w bazie.');
    }

    if (strtolower(trim($user_answer)) == strtolower(trim($row['word_pl']))) {
        $next_attempt = date('Y-m-d', strtotime("+3 days"));
        $stmt = $pdo->prepare("UPDATE words SET next_attempt = ?, last_attempt = CURDATE() WHERE id = ?");
        $stmt->execute([$next_attempt, $word_id]);
        echo "✅ Dobra odpowiedź!";
    } else {
        $mistakes = $row['mistakes'] + 1;
        $next_attempt = date('Y-m-d', strtotime("+1 day"));
        $stmt = $pdo->prepare("UPDATE words SET mistakes = ?, next_attempt = ?, last_attempt = CURDATE() WHERE id = ?");
        $stmt->execute([$mistakes, $next_attempt, $word_id]);
        echo "❌ Zła odpowiedź. Słowo wróci jutro do kolejki!";
    }
} else {
    exit('Brak danych.');
}
?>





