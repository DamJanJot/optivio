<?php
require_once __DIR__ . '/../../core/db.php';

function chat_column_exists(PDO $pdo, string $table, string $column): bool
{
    try {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function chat_prepare_schema(PDO $pdo): bool
{
    if (isset($_SESSION['chat_schema_checked_v5'])) {
        return (bool) $_SESSION['chat_schema_checked_v5'];
    }

    $hasLastActivity = chat_column_exists($pdo, 'uzytkownicy', 'ostatnia_aktywnosc');

    try {
        $stmt = $pdo->query(
            "SELECT CCSA.character_set_name
             FROM information_schema.TABLES T
             JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY CCSA
               ON CCSA.collation_name = T.table_collation
             WHERE T.table_schema = DATABASE() AND T.table_name = 'wiadomosci'
             LIMIT 1"
        );
        if ($stmt->fetchColumn() !== 'utf8mb4') {
            $pdo->exec('ALTER TABLE wiadomosci CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        }
    } catch (Throwable $e) {
        // Hosting może nie pozwalać na ALTER; połączenie nadal używa utf8mb4.
    }

    if (!$hasLastActivity) {
        try {
            $pdo->exec('ALTER TABLE uzytkownicy ADD COLUMN ostatnia_aktywnosc DATETIME NULL DEFAULT NULL');
            $hasLastActivity = true;
        } catch (Throwable $e) {
            $hasLastActivity = false;
        }
    }

    $_SESSION['chat_schema_checked_v5'] = $hasLastActivity;
    return $hasLastActivity;
}

$chatHasLastActivity = chat_prepare_schema($pdo);

if ($chatHasLastActivity && isset($_SESSION['id']) && time() - (int) ($_SESSION['chat_activity_touch'] ?? 0) >= 60) {
    try {
        $stmt = $pdo->prepare('UPDATE uzytkownicy SET ostatnia_aktywnosc = NOW() WHERE id = ?');
        $stmt->execute([(int) $_SESSION['id']]);
        $_SESSION['chat_activity_touch'] = time();
    } catch (Throwable $e) {
        // Ostatnia aktywność jest dodatkiem i nie może blokować czatu.
    }
}
?>
