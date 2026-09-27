<?php
require_once __DIR__ . '/../../core/db.php';

function notes_column_exists(PDO $pdo, string $column): bool
{
    try {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute(['notatki_mobile', $column]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function notes_prepare_schema(PDO $pdo): void
{
    if (isset($_SESSION['notes_schema_checked_v2'])) {
        return;
    }

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS notatki_mobile (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                content TEXT NOT NULL,
                pinned TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_notes_user_sort (user_id, pinned, updated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec('ALTER TABLE notatki_mobile CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    } catch (Throwable $e) {
        // Dalsze migracje są wykonywane osobno, aby starsza tabela nadal działała.
    }

    if (!notes_column_exists($pdo, 'pinned')) {
        try {
            $pdo->exec('ALTER TABLE notatki_mobile ADD COLUMN pinned TINYINT(1) NOT NULL DEFAULT 0 AFTER content');
        } catch (Throwable $e) {
        }
    }

    if (!notes_column_exists($pdo, 'created_at')) {
        try {
            $pdo->exec('ALTER TABLE notatki_mobile ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        } catch (Throwable $e) {
        }
    }

    if (!notes_column_exists($pdo, 'updated_at')) {
        try {
            $pdo->exec('ALTER TABLE notatki_mobile ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at');
        } catch (Throwable $e) {
        }
    }

    $_SESSION['notes_schema_checked_v2'] = true;
}

notes_prepare_schema($pdo);
?>
