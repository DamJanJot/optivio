<?php
require_once __DIR__ . '/../../core/db.php';

if (isset($_POST['word_id'], $_POST['translation'])) {
    $word_id = (int) $_POST['word_id'];
    $user_answer = (string) $_POST['translation'];

    $stmt = $pdo->prepare("SELECT word_pl, mistakes FROM words WHERE id = ?");
    $stmt->execute([$word_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        exit('Nie znaleziono słówka w bazie.');
    }

    if (mb_strtolower(trim($user_answer), 'UTF-8') === mb_strtolower(trim((string) $row['word_pl']), 'UTF-8')) {
        $next_attempt = date('Y-m-d', strtotime("+3 days"));
        $stmt = $pdo->prepare("UPDATE words SET next_attempt = ?, last_attempt = CURDATE() WHERE id = ?");
        $stmt->execute([$next_attempt, $word_id]);
        echo "✅ Dobra odpowiedź!";
    } else {
        $mistakes = (int) $row['mistakes'] + 1;
        $next_attempt = date('Y-m-d', strtotime("+1 day"));
        $stmt = $pdo->prepare("UPDATE words SET mistakes = ?, next_attempt = ?, last_attempt = CURDATE() WHERE id = ?");
        $stmt->execute([$mistakes, $next_attempt, $word_id]);
        echo "❌ Zła odpowiedź. Słowo wróci jutro do kolejki!";
    }
} else {
    exit('Brak danych.');
}
?>
