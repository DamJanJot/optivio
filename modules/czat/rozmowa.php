<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('czat');
require_once 'connect.php';

$me = (int) $_SESSION['id'];
$other = (int) ($_GET['id'] ?? 0);

$activitySelect = $chatHasLastActivity ? 'ostatnia_aktywnosc' : 'NULL AS ostatnia_aktywnosc';
$stmt = $pdo->prepare("SELECT id, imie, nazwisko, zdjecie_profilowe, $activitySelect FROM uzytkownicy WHERE id = ? LIMIT 1");
$stmt->execute([$other]);
$otherUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$otherUser) {
    http_response_code(404);
    exit('Nie znaleziono uzytkownika.');
}

$otherAvatar = optivio_stored_file_url((string) ($otherUser['zdjecie_profilowe'] ?? ''));
$displayName = trim((string) ($otherUser['imie'] ?? '') . ' ' . (string) ($otherUser['nazwisko'] ?? ''));
$lastActivity = $otherUser['ostatnia_aktywnosc'] ?? null;
$lastActivityTimestamp = $lastActivity ? strtotime((string) $lastActivity) : false;
if ($lastActivityTimestamp && time() - $lastActivityTimestamp < 180) {
    $lastActivityLabel = 'Aktywny teraz';
} elseif ($lastActivityTimestamp) {
    $lastActivityLabel = 'Ostatnio aktywny: ' . date('d.m.Y H:i', $lastActivityTimestamp);
} else {
    $lastActivityLabel = 'Brak informacji o ostatniej aktywnosci';
}
$uploadError = trim((string) ($_GET['upload_error'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Rozmowa z <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="./css/style_rozmowa.css?v=20260614-3">
</head>
<body>
  <header class="chat-header">
    <a href="index.php" class="back-link" aria-label="Powrot">&larr;</a>
    <img
      src="<?= htmlspecialchars($otherAvatar, ENT_QUOTES, 'UTF-8') ?>"
      alt="Avatar"
      class="header-avatar"
      width="42"
      height="42">
    <div class="header-person">
      <strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></strong>
      <span><?= htmlspecialchars($lastActivityLabel, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <?php if ($uploadError !== ''): ?>
    <div class="chat-alert"><?= htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <main id="chat-box"></main>

  <form class="chat-composer" method="post" action="wyslij.php" enctype="multipart/form-data">
    <input type="hidden" name="odbiorca_id" value="<?= $other ?>">
    <div class="input-group">
      <input type="text" id="tresc" name="tresc" class="form-control" placeholder="Napisz wiadomo&#347;&#263;...">
      <label class="btn btn-outline-secondary mb-0 image-button" title="Dodaj zdjecie">
        <i class="fa-solid fa-image"></i>
        <input id="chat-image-input" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
      </label>
      <button type="button" class="btn btn-outline-secondary" id="toggle-emojis" aria-label="Emoji" onclick="toggleEmojiPicker()">&#128522;</button>
      <button type="submit" class="btn btn-primary" aria-label="Wyslij"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
    <div id="image-name" class="image-name"></div>
    <div id="emoji-picker">
      <button class="emoji" type="button">&#128512;</button>
      <button class="emoji" type="button">&#128513;</button>
      <button class="emoji" type="button">&#128514;</button>
      <button class="emoji" type="button">&#129315;</button>
      <button class="emoji" type="button">&#128517;</button>
      <button class="emoji" type="button">&#128526;</button>
      <button class="emoji" type="button">&#128525;</button>
      <button class="emoji" type="button">&#128546;</button>
      <button class="emoji" type="button">&#128519;</button>
      <button class="emoji" type="button">&#128075;</button>
      <button class="emoji" type="button">&#128591;</button>
      <button class="emoji" type="button">&#10084;&#65039;</button>
      <button class="emoji" type="button">&#128077;</button>
      <button class="emoji" type="button">&#127881;</button>
    </div>
  </form>

  <script>
  function toggleEmojiPicker() {
    var picker = document.getElementById('emoji-picker');
    if (picker) {
      picker.classList.toggle('is-open');
    }
  }
  </script>
  <script src="js/chat.js?v=20260614-3"></script>
</body>
</html>
