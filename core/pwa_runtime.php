<?php
declare(strict_types=1);

const PWA_APP_ID = 'optivio';
const PWA_APP_NAME = 'Optivio';
const PWA_APP_VERSION = '2026.11.11.1';
const PWA_MINIMUM_VERSION = '2026.11.11.1';
const PWA_CRITICAL_UPDATE = false;
const PWA_REQUIRE_BROWSER_VERSION = true;

function pwa_runtime_status(): array
{
    return [
        'ok' => true,
        'appId' => PWA_APP_ID,
        'appName' => PWA_APP_NAME,
        'version' => PWA_APP_VERSION,
        'latestVersion' => PWA_APP_VERSION,
        'minimumVersion' => PWA_MINIMUM_VERSION,
        'critical' => PWA_CRITICAL_UPDATE,
        'serverTime' => gmdate('c'),
        'message' => PWA_CRITICAL_UPDATE
            ? 'Ta aktualizacja jest wymagana przed dalszym korzystaniem z aplikacji.'
            : 'Aplikacja jest gotowa.',
    ];
}

function pwa_runtime_request_version(): string
{
    $header = trim((string) ($_SERVER['HTTP_X_APP_VERSION'] ?? ''));
    if ($header !== '') {
        return $header;
    }

    $form = trim((string) ($_POST['__app_version'] ?? ''));
    if ($form !== '') {
        return $form;
    }

    return trim((string) ($_GET['__app_version'] ?? ''));
}

function pwa_runtime_is_safe_method(): bool
{
    return in_array(strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')), ['GET', 'HEAD', 'OPTIONS'], true);
}

function pwa_runtime_is_auth_route(): bool
{
    $path = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
    $file = strtolower(basename($path));
    return in_array($file, [
        'login.php',
        'logout.php',
        'forgot.php',
        'forgot_password.php',
        'reset.php',
        'reset_password.php',
        'activate.php',
        'register.php',
        'join.php',
        'invite.php',
        'orbit-handoff.php',
        'autologin.php',
        'autologin-redirect.php',
        'health.php',
        'pwa-status.php',
    ], true);
}

function pwa_runtime_is_browser_request(): bool
{
    if (pwa_runtime_request_version() !== '') {
        return true;
    }

    if (isset($_SERVER['HTTP_SEC_FETCH_SITE'])) {
        return true;
    }

    $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($origin !== '' && $host !== '') {
        $originHost = (string) (parse_url($origin, PHP_URL_HOST) ?: '');
        return $originHost !== '' && strcasecmp($originHost, preg_replace('/:\\d+$/', '', $host) ?? $host) === 0;
    }

    return false;
}

function pwa_runtime_send_upgrade_required(): never
{
    http_response_code(426);
    header('Cache-Control: no-store');
    header('X-App-Version: ' . PWA_APP_VERSION);
    header('X-App-Min-Version: ' . PWA_MINIMUM_VERSION);

    $message = 'Ta wersja ' . PWA_APP_NAME . ' jest nieaktualna. Odśwież aplikację i zainstaluj aktualizację przed zapisaniem zmian.';
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestPath = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
    $json = str_contains($accept, 'application/json')
        || str_contains($requestPath, '/api/')
        || str_contains($requestPath, 'api.php')
        || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => $message,
            'code' => 'APP_UPDATE_REQUIRED',
            'minimumVersion' => PWA_MINIMUM_VERSION,
            'latestVersion' => PWA_APP_VERSION,
            'reload' => true,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    $name = htmlspecialchars(PWA_APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $body = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<!doctype html><html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Wymagana aktualizacja · ' . $name . '</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#07101d;color:#f8fafc;font:16px/1.55 system-ui}.card{max-width:560px;padding:32px;border:1px solid #31405a;border-radius:24px;background:#101a2c;text-align:center}a{display:block;margin-top:20px;padding:13px;border-radius:12px;background:#60a5fa;color:#07101d;font-weight:800;text-decoration:none}</style></head>';
    echo '<body><main class="card"><h1>Zaktualizuj ' . $name . '</h1><p>' . $body . '</p><a href="' . htmlspecialchars((string) ($_SERVER['HTTP_REFERER'] ?? './'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Wróć i odśwież aplikację</a></main></body></html>';
    exit;
}

function pwa_runtime_enforce(): void
{
    if (PHP_SAPI === 'cli' || pwa_runtime_is_safe_method() || pwa_runtime_is_auth_route()) {
        return;
    }

    $clientVersion = pwa_runtime_request_version();
    $browserRequest = pwa_runtime_is_browser_request();
    if (!$browserRequest && $clientVersion === '') {
        return;
    }

    if (
        (PWA_REQUIRE_BROWSER_VERSION && $clientVersion === '')
        || ($clientVersion !== '' && version_compare($clientVersion, PWA_MINIMUM_VERSION, '<'))
    ) {
        pwa_runtime_send_upgrade_required();
    }
}

function pwa_runtime_send_status(): never
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-App-Version: ' . PWA_APP_VERSION);
    echo json_encode(pwa_runtime_status(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
