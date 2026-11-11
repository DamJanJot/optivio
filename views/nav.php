<?php
require_once __DIR__ . '/../core/access_control.php';

optivio_require_login();

$access = optivio_current_user_access();
$allowedApps = (array) ($access['apps'] ?? []);

$sections = [
    'apki' => ['label' => 'Apki', 'icon' => 'fa-brands fa-medapps', 'src' => '../modules/apki/apki.php'],
    'taski' => ['label' => 'Taski', 'icon' => 'fa-solid fa-list-check', 'src' => '../modules/taski/taski.php'],
    'czat' => ['label' => 'Czat', 'icon' => 'fa-solid fa-comment', 'src' => '../modules/czat/index.php'],
    'profil' => ['label' => 'Profil', 'icon' => 'fa-solid fa-user', 'src' => '../views/profil.php'],
];

$visibleSections = [];
foreach ($sections as $key => $meta) {
    if (in_array($key, $allowedApps, true)) {
        $visibleSections[$key] = $meta;
    }
}

if (empty($visibleSections)) {
    http_response_code(403);
    echo '<!doctype html><html lang="pl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Brak dostępu</title>';
    echo '<style>body{font-family:Segoe UI,Tahoma,sans-serif;background:#0b1220;color:#e5e7eb;display:grid;place-items:center;min-height:100vh;margin:0}';
    echo '.box{max-width:640px;padding:28px;border:1px solid rgba(255,255,255,.14);border-radius:16px;background:rgba(255,255,255,.06)}';
    echo 'a{display:inline-block;margin-top:14px;color:#7dd3fc;text-decoration:none}</style></head><body><div class="box">';
    echo '<h1>Konto bez dostępu</h1><p>Twoje konto nie ma przypisanych sekcji panelu. Skontaktuj się z administratorem.</p>';
    echo '<a href="' . htmlspecialchars(optivio_url('public/logout.php'), ENT_QUOTES, 'UTF-8') . '">Wyloguj</a>';
    echo '</div></body></html>';
    exit();
}

$activeKey = (string) array_key_first($visibleSections);
$taskUnread = 0;
$chatUnread = 0;

if (in_array('taski', $allowedApps, true)) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM zadania_mobile_uzytkownicy WHERE user_id = ? AND przeczytane = 0');
    $stmt->execute([(int) $_SESSION['id']]);
    $taskUnread = (int) $stmt->fetchColumn();
}

if (in_array('czat', $allowedApps, true)) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM wiadomosci WHERE odbiorca_id = ? AND przeczytana = 0');
    $stmt->execute([(int) $_SESSION['id']]);
    $chatUnread = (int) $stmt->fetchColumn();
}

$stmt = $pdo->prepare('SELECT zdjecie_profilowe FROM uzytkownicy WHERE id = ? LIMIT 1');
$stmt->execute([(int) $_SESSION['id']]);
$avatar = optivio_stored_file_url((string) ($stmt->fetchColumn() ?: ''));
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel aplikacji</title>
  <meta name="theme-color" content="#0b1220">
  <link rel="manifest" href="../public/manifest.json">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="../public/assets/css/portfolio_app.css" />
  <link rel="stylesheet" href="../public/assets/css/pwa-runtime.css?v=<?= rawurlencode(PWA_APP_VERSION) ?>">
  <script src="../public/assets/js/pwa-runtime.js?v=<?= rawurlencode(PWA_APP_VERSION) ?>" data-pwa-runtime data-app-id="optivio" data-app-name="Optivio" data-app-version="<?= htmlspecialchars(PWA_APP_VERSION, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-status-url="../public/pwa-status.php" data-worker-url="../service-worker.js" data-worker-scope="../"></script>
  <link rel="website icon" type="png" href="../public/assets/img/logo.png">
  <meta name="description" content="Panel aplikacji użytkownika">
</head>
<body>

<?php foreach ($visibleSections as $key => $meta): ?>
  <div class="section <?php echo $key === $activeKey ? 'active' : ''; ?>" id="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
    <iframe src="<?php echo htmlspecialchars($meta['src'], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?>"></iframe>
  </div>
<?php endforeach; ?>

<div class="nav-bottom" data-focus-zone="nav">
  <?php foreach ($visibleSections as $key => $meta): ?>
    <button
      id="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>-btn"
      class="<?php echo $key === $activeKey ? 'active' : ''; ?>"
      data-nav-button
      onclick="switchTab('<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>', this)">
      <?php if ($key === 'profil'): ?>
        <img src="<?php echo htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>" class="avatar-icon" alt="Avatar">
      <?php else: ?>
        <i class="<?php echo htmlspecialchars($meta['icon'], ENT_QUOTES, 'UTF-8'); ?>">
          <?php if ($key === 'taski' && $taskUnread > 0): ?>
            <span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger"><?php echo $taskUnread; ?></span>
          <?php endif; ?>
          <?php if ($key === 'czat' && $chatUnread > 0): ?>
            <span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger"><?php echo $chatUnread; ?></span>
          <?php endif; ?>
        </i>
        <span><?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?></span>
      <?php endif; ?>
    </button>
  <?php endforeach; ?>
</div>

<script>
function switchTab(tab, btn) {
  document.querySelectorAll('.section').forEach(function (s) { s.classList.remove('active'); });
  var section = document.getElementById(tab);
  if (section) {
    section.classList.add('active');
  }
  document.querySelectorAll('.nav-bottom button').forEach(function (b) { b.classList.remove('active'); });
  btn.classList.add('active');
}

function focusBottomNav(index) {
  var buttons = Array.from(document.querySelectorAll('[data-nav-button]'));
  if (buttons.length === 0) {
    return;
  }
  var activeIndex = Math.max(0, buttons.findIndex(function (button) { return button.classList.contains('active'); }));
  buttons[((index ?? activeIndex) + buttons.length) % buttons.length].focus();
}

document.addEventListener('keydown', function (event) {
  var buttons = Array.from(document.querySelectorAll('[data-nav-button]'));
  var focusedButtonIndex = buttons.indexOf(document.activeElement);

  if (event.key === 'F2') {
    event.preventDefault();
    focusBottomNav();
    return;
  }

  if ((event.key === 'ArrowLeft' || event.key === 'ArrowRight') && focusedButtonIndex !== -1) {
    event.preventDefault();
    var step = event.key === 'ArrowRight' ? 1 : -1;
    buttons[(focusedButtonIndex + step + buttons.length) % buttons.length].focus();
    return;
  }

  if ((event.key === 'Enter' || event.key === ' ') && focusedButtonIndex !== -1) {
    event.preventDefault();
    buttons[focusedButtonIndex].click();
  }
});

function updateBadge(buttonId, endpoint) {
  var btn = document.getElementById(buttonId);
  if (!btn) {
    return;
  }

  fetch(endpoint)
    .then(function (res) { return res.json(); })
    .then(function (data) {
      var icon = btn.querySelector('i');
      if (!icon) {
        return;
      }

      var badge = btn.querySelector('span.badge');
      if (data.count > 0) {
        if (!badge) {
          badge = document.createElement('span');
          badge.className = 'position-absolute top-0 translate-middle badge rounded-pill bg-danger';
          icon.appendChild(badge);
        }
        badge.textContent = String(data.count);
      } else if (badge) {
        badge.remove();
      }
    })
    .catch(function () {
      // Nie blokujemy interfejsu, gdy licznik chwilowo nie odpowiada.
    });
}

<?php if (isset($visibleSections['taski'])): ?>
setInterval(function () { updateBadge('taski-btn', '../modules/taski/unread_count_taski.php'); }, 5000);
updateBadge('taski-btn', '../modules/taski/unread_count_taski.php');
<?php endif; ?>

<?php if (isset($visibleSections['czat'])): ?>
setInterval(function () { updateBadge('czat-btn', '../modules/czat/unread_count.php'); }, 5000);
updateBadge('czat-btn', '../modules/czat/unread_count.php');
<?php endif; ?>
</script>

</body>
</html>
