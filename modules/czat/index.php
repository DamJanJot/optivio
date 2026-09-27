<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('czat');
require_once 'connect.php';

$userId = (int) $_SESSION['id'];
$activitySelect = $chatHasLastActivity ? 'u.ostatnia_aktywnosc' : 'NULL AS ostatnia_aktywnosc';

$stmt = $pdo->prepare("
  SELECT
    u.id,
    CONCAT(u.imie, ' ', u.nazwisko) AS display_name,
    u.zdjecie_profilowe,
    $activitySelect,
    c.last_message_at,
    (
      SELECT w2.tresc
      FROM wiadomosci w2
      WHERE (w2.nadawca_id = ? AND w2.odbiorca_id = u.id)
         OR (w2.nadawca_id = u.id AND w2.odbiorca_id = ?)
      ORDER BY w2.data_wyslania DESC
      LIMIT 1
    ) AS last_message,
    (
      SELECT COUNT(*)
      FROM wiadomosci unread
      WHERE unread.nadawca_id = u.id
        AND unread.odbiorca_id = ?
        AND unread.przeczytana = 0
    ) AS nieprzeczytane
  FROM (
    SELECT
      CASE WHEN nadawca_id = ? THEN odbiorca_id ELSE nadawca_id END AS user_id,
      MAX(data_wyslania) AS last_message_at
    FROM wiadomosci
    WHERE nadawca_id = ? OR odbiorca_id = ?
    GROUP BY CASE WHEN nadawca_id = ? THEN odbiorca_id ELSE nadawca_id END
  ) c
  JOIN uzytkownicy u ON u.id = c.user_id
  ORDER BY c.last_message_at DESC
");
$stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
  SELECT u.id, CONCAT(u.imie, ' ', u.nazwisko) AS display_name, u.zdjecie_profilowe, $activitySelect
  FROM uzytkownicy u
  WHERE u.id != ?
    AND NOT EXISTS (
      SELECT 1 FROM wiadomosci w
      WHERE (w.nadawca_id = ? AND w.odbiorca_id = u.id)
         OR (w.nadawca_id = u.id AND w.odbiorca_id = ?)
    )
  ORDER BY display_name ASC
");
$stmt->execute([$userId, $userId, $userId]);
$newContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

function chat_activity_meta($value): array
{
    $timestamp = $value ? strtotime((string) $value) : false;
    if ($timestamp && time() - $timestamp < 180) {
        return ['Aktywny teraz', true];
    }
    if ($timestamp) {
        return ['Ostatnio: ' . date('d.m.Y H:i', $timestamp), false];
    }
    return ['Brak informacji o aktywności', false];
}

function chat_preview(string $message): string
{
    if (preg_match('~uploads/chat/[^\s]+~', $message)) {
        return 'Zdjęcie';
    }
    $flat = trim(preg_replace('/\s+/', ' ', $message));
    return mb_strlen($flat, 'UTF-8') > 55 ? mb_substr($flat, 0, 55, 'UTF-8') . '…' : $flat;
}
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Rozmowy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./css/style.css?v=20260614-2">
</head>
<body>
  <header class="conversations-header">
    <div>
      <h1>Rozmowy</h1>
      <span>Ostatnie wiadomości</span>
    </div>
    <button type="button" id="new-message-toggle">Wyślij wiadomość</button>
  </header>

  <section id="new-message-panel" hidden>
    <div class="panel-title">
      <h2>Rozpocznij rozmowę</h2>
      <button type="button" id="new-message-close" aria-label="Zamknij">×</button>
    </div>
    <?php if (empty($newContacts)): ?>
      <div class="empty">Rozmawiałeś już ze wszystkimi użytkownikami.</div>
    <?php endif; ?>
    <?php foreach ($newContacts as $row): ?>
      <?php [$activity, $online] = chat_activity_meta($row['ostatnia_aktywnosc'] ?? null); ?>
      <a class="contact-row" href="rozmowa.php?id=<?= (int) $row['id'] ?>">
        <span class="avatar-wrap">
          <img src="<?= htmlspecialchars(optivio_stored_file_url((string) $row['zdjecie_profilowe']), ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
          <?php if ($online): ?><span class="online-dot"></span><?php endif; ?>
        </span>
        <span class="contact-copy">
          <strong><?= htmlspecialchars((string) $row['display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
          <small><?= htmlspecialchars($activity, ENT_QUOTES, 'UTF-8') ?></small>
        </span>
      </a>
    <?php endforeach; ?>
  </section>

  <main class="conversation-list">
    <?php if (empty($conversations)): ?>
      <div class="empty">Nie masz jeszcze żadnych rozmów.</div>
    <?php endif; ?>
    <?php foreach ($conversations as $row): ?>
      <?php [$activity, $online] = chat_activity_meta($row['ostatnia_aktywnosc'] ?? null); ?>
      <a class="conversation-row" href="rozmowa.php?id=<?= (int) $row['id'] ?>">
        <span class="avatar-wrap">
          <img src="<?= htmlspecialchars(optivio_stored_file_url((string) $row['zdjecie_profilowe']), ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
          <?php if ($online): ?><span class="online-dot"></span><?php endif; ?>
        </span>
        <span class="conversation-copy">
          <span class="conversation-line">
            <strong><?= htmlspecialchars((string) $row['display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
            <time><?= htmlspecialchars(date('d.m H:i', strtotime((string) $row['last_message_at'])), ENT_QUOTES, 'UTF-8') ?></time>
          </span>
          <span class="conversation-line secondary">
            <small><?= htmlspecialchars(chat_preview((string) $row['last_message']), ENT_QUOTES, 'UTF-8') ?></small>
            <small><?= htmlspecialchars($activity, ENT_QUOTES, 'UTF-8') ?></small>
          </span>
        </span>
        <?php if ((int) $row['nieprzeczytane'] > 0): ?>
          <span class="unread"><?= (int) $row['nieprzeczytane'] ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </main>

  <script>
  const panel = document.getElementById('new-message-panel');
  document.getElementById('new-message-toggle').addEventListener('click', () => {
    panel.hidden = false;
  });
  document.getElementById('new-message-close').addEventListener('click', () => {
    panel.hidden = true;
  });
  </script>
</body>
</html>
