<?php require_once __DIR__ . '/../core/env_loader.php'; 
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
} catch (\PDOException $e) {
    exit('DB connection failed: ' . $e->getMessage());
}