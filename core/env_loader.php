<?php
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

// Compatibility layer for legacy modules that use old variable names.
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASS') ?: '';

$db = $dbname;
$user = $username;
$pass = $password;
$servername = $host;

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('MAIL_HOST') ?: '');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int) (getenv('MAIL_PORT') ?: 587));
}
if (!defined('SMTP_USER')) {
    define('SMTP_USER', getenv('MAIL_USERNAME') ?: '');
}
if (!defined('SMTP_PASS')) {
    define('SMTP_PASS', getenv('MAIL_PASSWORD') ?: '');
}
if (!defined('MAIL_FROM')) {
    define('MAIL_FROM', getenv('MAIL_FROM') ?: '');
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Optivio');
}
?>
