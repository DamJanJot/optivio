-- SQL migration for Taski goals (cele)
-- Run once on production/local database used by modules/taski

CREATE TABLE IF NOT EXISTS cele (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zadanie_id INT NOT NULL,
    opis TEXT NOT NULL,
    wykonane TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cele_zadanie_id (zadanie_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
