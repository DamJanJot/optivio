<?php
require_once __DIR__ . '/../../core/access_control.php';

optivio_require_login();
optivio_require_app_access('kalendarz');

$pdo->exec("
    CREATE TABLE IF NOT EXISTS wydarzenia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        data DATE NOT NULL,
        tytul VARCHAR(255) NOT NULL,
        opis TEXT NULL,
        typ VARCHAR(100) NULL,
        godzina VARCHAR(5) NULL,
        kolor VARCHAR(20) NOT NULL DEFAULT '#3478f6',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_wydarzenia_user_data (user_id, data)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
");
?>
