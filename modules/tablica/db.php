<?php
require_once __DIR__ . '/../../core/db.php';

$pdo->exec("
    CREATE TABLE IF NOT EXISTS tablica_posty (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        tresc TEXT NOT NULL,
        data_dodania TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tablica_posty_user (user_id),
        INDEX idx_tablica_posty_data (data_dodania)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS tablica_lajki (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        data_dodania TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_tablica_lajki_user_post (user_id, post_id),
        INDEX idx_tablica_lajki_post (post_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS tablica_komentarze (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        komentarz TEXT NOT NULL,
        data_dodania TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tablica_komentarze_post (post_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS tablica_odczyt (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        data_odczytu TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_tablica_odczyt_user_post (user_id, post_id),
        INDEX idx_tablica_odczyt_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
?>
