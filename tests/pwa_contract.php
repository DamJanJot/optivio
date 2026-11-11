<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$read = static function (string $path) use ($root): string {
    $contents = file_get_contents($root . '/' . $path);
    if (!is_string($contents)) {
        throw new RuntimeException('Missing file: ' . $path);
    }
    return $contents;
};
$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$manifest = json_decode($read('public/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
$runtime = $read('public/assets/js/pwa-runtime.js');
$worker = $read('service-worker.js');
$publicWorker = $read('public/service-worker.js');
$helper = $read('core/pwa_runtime.php');
$login = $read('public/login.php');
$nav = $read('views/nav.php');

$assert(($manifest['id'] ?? null) === '/optivio/', 'Optivio needs its own PWA identity.');
$assert(($manifest['scope'] ?? null) === '../', 'Optivio scope must cover its public and views entry points.');
$assert(($manifest['start_url'] ?? null) === '../views/nav.php?source=pwa', 'Optivio PWA start route changed.');
$assert(str_contains($runtime, 'beforeinstallprompt'), 'Install prompt handling is missing.');
$assert(str_contains($runtime, "connectionState === 'online'"), 'Offline writes are not guarded.');
$assert(str_contains($runtime, 'response.status === 426'), 'Mandatory updates are not handled.');
$assert(str_contains($worker, "const APP_ID = 'optivio'"), 'Service worker identity is missing.');
$assert(str_contains($worker, 'SKIP_WAITING'), 'Service worker cannot activate confirmed updates.');
$assert(str_contains($worker, 'X-App-Version'), 'Service worker does not identify mutation requests.');
$assert(str_contains($publicWorker, 'self.registration.unregister()'), 'Legacy public worker does not retire its old registration.');
$assert(str_contains($publicWorker, "LEGACY_CACHE_PREFIXES"), 'Legacy Optivio caches are not scoped for cleanup.');
$assert(str_contains($publicWorker, "LEGACY_CACHE_NAMES = ['myapp']"), 'Original Optivio cache is not removed during migration.');
$assert(str_contains($helper, "PWA_APP_ID = 'optivio'"), 'Server PWA identity is missing.');
$assert(str_contains($helper, 'pwa_runtime_send_upgrade_required'), 'Server update enforcement is missing.');
$workerVersionMatched = preg_match("/const APP_VERSION = '([^']+)'/", $worker, $workerVersion) === 1;
$serverVersionMatched = preg_match("/const PWA_APP_VERSION = '([^']+)'/", $helper, $serverVersion) === 1;
$assert(
    $workerVersionMatched && $serverVersionMatched && $workerVersion[1] === $serverVersion[1],
    'Browser and server PWA versions do not match.'
);
$assert(str_contains($login, 'data-app-id="optivio"'), 'Login page does not load the Optivio runtime.');
$assert(str_contains($login, 'data-app-version="<?= htmlspecialchars(PWA_APP_VERSION'), 'Login page does not use the server release version.');
$assert(str_contains($nav, 'data-app-id="optivio"'), 'Main navigation does not load the Optivio runtime.');

foreach (['public/icons/icon-192.png' => 192, 'public/icons/icon-512.png' => 512] as $path => $size) {
    $dimensions = getimagesize($root . '/' . $path);
    $assert(is_array($dimensions) && $dimensions[0] === $size && $dimensions[1] === $size, 'Invalid Optivio icon size: ' . $path);
}

fwrite(STDOUT, "Optivio PWA contracts passed.\n");
