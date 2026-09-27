<?php
require_once __DIR__ . '/../../core/db.php';

$category = $_GET['category'] ?? 'general';

$stmt = $pdo->prepare("
    SELECT id, word_en
    FROM words
    WHERE next_attempt <= CURDATE() AND category = ?
    ORDER BY next_attempt ASC
    LIMIT 1
");
$stmt->execute([$category]);
$word = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($word ?: ['id' => 0, 'word_en' => 'Brak słówek w tej kategorii!'], JSON_UNESCAPED_UNICODE);
?>
