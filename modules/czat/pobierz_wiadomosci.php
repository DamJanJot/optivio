<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('czat');
require_once 'connect.php';

$me = (int) $_SESSION['id'];
$other = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT w.tresc, w.data_wyslania, u.zdjecie_profilowe, w.nadawca_id
  FROM wiadomosci w
  JOIN uzytkownicy u ON w.nadawca_id = u.id
  WHERE (w.nadawca_id = :me AND w.odbiorca_id = :other)
     OR (w.nadawca_id = :other AND w.odbiorca_id = :me)
  ORDER BY w.data_wyslania ASC
");
$stmt->execute(['me' => $me, 'other' => $other]);
$msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function render_message_text(string $text): string
{
    $parts = preg_split('~(\s+)~', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $html = '';

    foreach ($parts as $part) {
        if (preg_match('~^(uploads/chat/[^\s<]+\.(?:png|jpe?g|gif|webp)|https?://[^\s<]+\.(?:png|jpe?g|gif|webp))$~i', $part)) {
            $url = str_starts_with($part, 'uploads/') ? '../../' . $part : $part;
            $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $html .= '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer"><img class="chat-image" src="' . $safeUrl . '" alt="Zdjęcie"></a>';
            continue;
        }

        if (preg_match('~^https?://[^\s<]+$~i', $part)) {
            $safeUrl = htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
            $html .= '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">' . $safeUrl . '</a>';
            continue;
        }

        $html .= htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
    }

    return nl2br($html);
}

foreach ($msgs as $msg) {
    $isSelf = (int) $msg['nadawca_id'] === $me;
    $img = optivio_stored_file_url((string) $msg['zdjecie_profilowe']);

    echo '<div class="msg ' . ($isSelf ? 'self' : '') . '">';
    echo '<img src="' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '" class="msg-avatar" alt="Avatar">';
    echo '<div><div class="bubble">' . render_message_text((string) $msg['tresc']) . '</div>';
    echo '<div class="timestamp">' . htmlspecialchars((string) $msg['data_wyslania'], ENT_QUOTES, 'UTF-8') . '</div></div>';
    echo '</div>';
}
?>
