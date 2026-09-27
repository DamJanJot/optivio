<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('czat');
require_once 'connect.php';

$nadawca = (int) $_SESSION['id'];
$odbiorca = (int) ($_POST['odbiorca_id'] ?? 0);
$tresc = trim((string) ($_POST['tresc'] ?? ''));
$uploadError = '';

if ($odbiorca <= 0) {
    http_response_code(422);
    exit('Nieprawidłowy odbiorca.');
}

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $uploadError = 'Nie udało się przesłać zdjęcia.';
    } elseif ((int) $_FILES['image']['size'] > 8 * 1024 * 1024) {
        $uploadError = 'Zdjęcie jest za duże. Maksymalny rozmiar to 8 MB.';
    } else {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $_FILES['image']['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($extensions[$mime])) {
            $uploadError = 'Dozwolone są zdjęcia JPG, PNG, WEBP i GIF.';
        } else {
            $uploadDir = dirname(__DIR__, 2) . '/uploads/chat/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                $uploadError = 'Serwer nie może utworzyć katalogu na zdjęcia.';
            } elseif (!is_writable($uploadDir)) {
                $uploadError = 'Katalog zdjęć na serwerze nie ma praw zapisu.';
            } else {
                $fileName = 'chat_' . $nadawca . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
                if (move_uploaded_file((string) $_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                    $tresc = trim($tresc . "\n" . 'uploads/chat/' . $fileName);
                } else {
                    $uploadError = 'Serwer nie zapisał przesłanego zdjęcia.';
                }
            }
        }
    }
}

if ($tresc !== '') {
    $stmt = $pdo->prepare("INSERT INTO wiadomosci (nadawca_id, odbiorca_id, tresc, data_wyslania, przeczytana) VALUES (?, ?, ?, NOW(), 0)");
    $stmt->execute([$nadawca, $odbiorca, $tresc]);
}

$location = 'rozmowa.php?id=' . $odbiorca;
if ($uploadError !== '') {
    $location .= '&upload_error=' . rawurlencode($uploadError);
}
header('Location: ' . $location);
exit;
