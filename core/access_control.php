<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

const OPTIVIO_APPS = [
    'apki' => 'Katalog aplikacji',
    'taski' => 'Taski',
    'czat' => 'Czat',
    'profil' => 'Profil',
    'notatnik' => 'Notatnik',
    'dysk' => 'Dysk',
    'galeria' => 'Galeria',
    'todo' => 'ToDo',
    'tabele' => 'Tabele',
    'terminal' => 'Terminal',
    'kalendarz' => 'Kalendarz',
    'tablica' => 'Tablica',
    'portfel' => 'Portfel',
    'paint' => 'Rysunki',
    'nauka' => 'Nauka',
    'admin_panel' => 'Panel administratora',
];

function optivio_start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function optivio_base_url(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    foreach (['/public/', '/views/', '/modules/', '/core/'] as $marker) {
        $pos = strpos($script, $marker);
        if ($pos !== false) {
            return rtrim(substr($script, 0, $pos), '/');
        }
    }

    return '';
}

function optivio_url(string $path): string
{
    $base = optivio_base_url();
    $normalized = '/' . ltrim($path, '/');

    return ($base !== '' ? $base : '') . $normalized;
}

function optivio_stored_file_url(?string $storedPath, string $fallback = 'public/assets/img/default.png'): string
{
    $normalized = ltrim(str_replace('\\', '/', (string) $storedPath), './');
    $normalized = ltrim($normalized, '/');
    $root = dirname(__DIR__);

    if ($normalized !== '' && file_exists($root . '/' . $normalized)) {
        return optivio_url($normalized);
    }

    return optivio_url($fallback);
}

function optivio_redirect(string $path): void
{
    header('Location: ' . optivio_url($path));
    exit();
}

function optivio_table_exists(PDO $pdo, string $tableName): bool
{
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$tableName]);

        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function optivio_column_exists(PDO $pdo, string $tableName, string $columnName): bool
{
    try {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$tableName, $columnName]);

        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function optivio_touch_activity(): void
{
    global $pdo;

    $now = time();
    $lastTouch = (int) ($_SESSION['last_activity_touch'] ?? 0);
    if ($now - $lastTouch < 60 || !isset($_SESSION['id'])) {
        return;
    }

    $_SESSION['last_activity_touch'] = $now;
    if (!optivio_column_exists($pdo, 'uzytkownicy', 'ostatnia_aktywnosc')) {
        return;
    }

    try {
        $stmt = $pdo->prepare('UPDATE uzytkownicy SET ostatnia_aktywnosc = NOW() WHERE id = ?');
        $stmt->execute([(int) $_SESSION['id']]);
    } catch (Throwable $e) {
        // Informacja o aktywności nie może blokować aplikacji.
    }
}

function optivio_normalize_role(?string $role): string
{
    $normalized = strtolower(trim((string) $role));
    return $normalized !== '' ? $normalized : 'user';
}

function optivio_default_role_apps(string $role): array
{
    $all = array_keys(OPTIVIO_APPS);

    $map = [
        'owner' => $all,
        'admin' => $all,
        'manager' => ['apki', 'taski', 'czat', 'profil', 'notatnik', 'dysk', 'galeria', 'todo', 'tabele', 'terminal', 'kalendarz', 'tablica', 'portfel', 'paint', 'nauka'],
        'pracownik' => ['apki', 'taski', 'czat', 'profil', 'notatnik', 'todo', 'kalendarz', 'tablica'],
        'nauczyciel' => ['apki', 'taski', 'czat', 'profil', 'notatnik', 'todo', 'kalendarz', 'tablica', 'nauka'],
        'uczen' => ['apki', 'profil', 'notatnik', 'todo', 'nauka'],
        'user' => ['apki', 'taski', 'czat', 'profil', 'notatnik', 'todo', 'kalendarz', 'tablica'],
        'guest' => ['apki', 'profil'],
    ];

    return $map[$role] ?? $map['user'];
}

function optivio_fetch_role_apps(PDO $pdo, string $role): ?array
{
    if (!optivio_table_exists($pdo, 'optivio_role_app_assignments')) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT app_key FROM optivio_role_app_assignments WHERE role_key = ? ORDER BY app_key');
    $stmt->execute([$role]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($rows)) {
        return null;
    }

    $allowed = array_keys(OPTIVIO_APPS);
    $apps = [];
    foreach ($rows as $app) {
        $key = strtolower(trim((string) $app));
        if (in_array($key, $allowed, true) && !in_array($key, $apps, true)) {
            $apps[] = $key;
        }
    }

    return $apps;
}

function optivio_fetch_user_apps(PDO $pdo, int $userId): array
{
    if (!optivio_table_exists($pdo, 'optivio_user_app_assignments')) {
        return [];
    }

    $stmt = $pdo->prepare('SELECT app_key FROM optivio_user_app_assignments WHERE user_id = ? ORDER BY app_key');
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $allowed = array_keys(OPTIVIO_APPS);
    $apps = [];
    foreach ($rows as $app) {
        $key = strtolower(trim((string) $app));
        if (in_array($key, $allowed, true) && !in_array($key, $apps, true)) {
            $apps[] = $key;
        }
    }

    return $apps;
}

function optivio_resolve_user_apps(int $userId, string $role): array
{
    global $pdo;

    $apps = optivio_default_role_apps($role);
    $fromRoleTable = optivio_fetch_role_apps($pdo, $role);
    if ($fromRoleTable !== null) {
        $apps = $fromRoleTable;
    }

    $userApps = optivio_fetch_user_apps($pdo, $userId);
    if (!empty($userApps)) {
        foreach ($userApps as $key) {
            if (!in_array($key, $apps, true)) {
                $apps[] = $key;
            }
        }
    }

    sort($apps);
    return array_values($apps);
}

function optivio_require_login(): void
{
    optivio_start_session();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id'])) {
        optivio_redirect('public/login.php');
    }

    optivio_touch_activity();
}

function optivio_current_user_access(): array
{
    optivio_require_login();

    if (isset($_SESSION['access']) && is_array($_SESSION['access'])) {
        return $_SESSION['access'];
    }

    $role = optivio_normalize_role($_SESSION['rola'] ?? null);
    $_SESSION['rola'] = $role;

    $apps = optivio_resolve_user_apps((int) $_SESSION['id'], $role);
    $payload = [
        'role' => $role,
        'apps' => $apps,
    ];

    $_SESSION['access'] = $payload;
    return $payload;
}

function optivio_has_app_access(string $appKey): bool
{
    $access = optivio_current_user_access();
    return in_array($appKey, (array) ($access['apps'] ?? []), true);
}

function optivio_require_app_access(string $appKey): void
{
    if (optivio_has_app_access($appKey)) {
        return;
    }

    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="pl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Brak dostepu</title>';
    echo '<style>body{font-family:Segoe UI,Tahoma,sans-serif;background:#0b1220;color:#e5e7eb;display:grid;place-items:center;min-height:100vh;margin:0}';
    echo '.box{max-width:640px;padding:28px;border:1px solid rgba(255,255,255,.14);border-radius:16px;background:rgba(255,255,255,.06)}';
    echo 'a{display:inline-block;margin-top:14px;color:#7dd3fc;text-decoration:none}</style></head><body><div class="box">';
    echo '<h1>Brak dostępu do aplikacji</h1>';
    echo '<p>Twoja rola nie ma przypisanego dostępu do tego modułu. Skontaktuj się z administratorem.</p>';
    echo '<a href="' . htmlspecialchars(optivio_url('views/nav.php'), ENT_QUOTES, 'UTF-8') . '">Wróć do panelu</a>';
    echo '</div></body></html>';
    exit();
}

function optivio_require_admin(): void
{
    optivio_require_login();
    $role = optivio_normalize_role((string) ($_SESSION['rola'] ?? ''));
    if (!in_array($role, ['admin', 'owner'], true) || !optivio_has_app_access('admin_panel')) {
        optivio_redirect('views/nav.php');
    }
}

function optivio_refresh_access_cache(): void
{
    optivio_start_session();
    unset($_SESSION['access']);
}
