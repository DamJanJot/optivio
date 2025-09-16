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
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    exit('Błąd połączenia z bazą: ' . $e->getMessage());
}

// Pobierz kategorię z zapytania GET
$category = $_GET['category'] ?? 'general';

// Przygotuj zapytanie z kategorią
$stmt = $pdo->prepare("
    SELECT id, word_en 
    FROM words 
    WHERE next_attempt <= CURDATE() AND category = ? 
    ORDER BY next_attempt ASC 
    LIMIT 1
");
$stmt->execute([$category]);
$word = $stmt->fetch();

// Zwróć JSON
header('Content-Type: application/json');
echo json_encode($word ?: ['id' => 0, 'word_en' => 'Brak słówek w tej kategorii!']);
?>

