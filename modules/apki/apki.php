<?php
require_once __DIR__ . '/../../core/access_control.php';

optivio_require_login();
optivio_require_app_access('apki');

$allowedApps = (array) (optivio_current_user_access()['apps'] ?? []);

$cards = [
    ['key' => 'notatnik', 'label' => 'Notatnik', 'icon' => 'https://img.icons8.com/arcade/64/task.png', 'path' => '../notatnik/note.php'],
    ['key' => 'dysk', 'label' => 'Dysk', 'icon' => 'https://img.icons8.com/arcade/64/cloud-storage.png', 'path' => '../dysk/dysk.php'],
    ['key' => 'galeria', 'label' => 'Galeria', 'icon' => 'https://img.icons8.com/arcade/50/gallery.png', 'path' => '../galeria/galeria.php'],
    ['key' => 'todo', 'label' => 'ToDo', 'icon' => 'https://img.icons8.com/arcade/64/test-passed.png', 'path' => '../todo/todo_index.php'],
    ['key' => 'tabele', 'label' => 'Tabele', 'icon' => 'https://img.icons8.com/arcade/64/activity-grid.png', 'path' => '../tabele/tabele.php'],
    ['key' => 'terminal', 'label' => 'Terminal', 'icon' => 'https://img.icons8.com/arcade/64/console.png', 'path' => '../terminal_mobile/terminal.php'],
    ['key' => 'kalendarz', 'label' => 'Kalendarz', 'icon' => 'https://img.icons8.com/arcade/64/calendar.png', 'path' => '../kalendarz/kalendarz.php'],
    ['key' => 'tablica', 'label' => 'Tablica', 'icon' => 'https://img.icons8.com/arcade/50/flipboard.png', 'path' => '../tablica/read_all.php'],
    ['key' => 'portfel', 'label' => 'Portfel', 'icon' => 'https://img.icons8.com/arcade/64/card-wallet.png', 'path' => '../portfel_mobile/portfel.php'],
    ['key' => 'paint', 'label' => 'Rysunki', 'icon' => 'https://img.icons8.com/arcade/50/sign-up.png', 'path' => '../paint/paint_mabile.php'],
    ['key' => 'nauka', 'label' => 'Nauka', 'icon' => 'https://img.icons8.com/arcade/50/translation.png', 'path' => '../nauka/nauka_ang.php'],
    ['key' => 'admin_panel', 'label' => 'Admin', 'icon' => 'https://img.icons8.com/arcade/64/admin-settings-male.png', 'path' => '../../public/admin/index.php'],
];

$visibleCards = [];
foreach ($cards as $card) {
    if (in_array($card['key'], $allowedApps, true)) {
        $visibleCards[] = $card;
    }
}

$unreadTablica = 0;
if (in_array('tablica', $allowedApps, true) && optivio_table_exists($pdo, 'tablica_posty') && optivio_table_exists($pdo, 'tablica_odczyt')) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tablica_posty WHERE id NOT IN (SELECT post_id FROM tablica_odczyt WHERE user_id = ?)');
        $stmt->execute([(int) $_SESSION['id']]);
        $unreadTablica = (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $unreadTablica = 0;
    }
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Apki</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
<script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="style.css">
</head>
<body class="bgzdj">

<header class="bgzdj-shadow"></header>

<h2 class="text-center py-3">Apki <img width="34" height="34" src="https://img.icons8.com/arcade/64/activity-grid-2.png" alt="activity-grid-2"/></h2>

<section class="app-boxy">
<div class="container text-center text-light py-4">
<div class="row row-cols-3 row-cols-lg-5 g-2 g-lg-3 justify-content-center">

<?php if (empty($visibleCards)): ?>
  <div class="col-12 py-3">
    <div class="alert alert-warning">Brak przypisanych aplikacji. Skontaktuj się z administratorem.</div>
  </div>
<?php endif; ?>

<?php foreach ($visibleCards as $card): ?>
  <div class="col py-3">
    <button
      id="<?php echo $card['key'] === 'tablica' ? 'tablica-btn' : ''; ?>"
      class="button btn app-img glass-box p-2"
      data-app-card
      onclick="location.href='<?php echo htmlspecialchars($card['path'], ENT_QUOTES, 'UTF-8'); ?>'"
      type="button">
      <img width="50" height="50" class="iko" src="<?php echo htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?>"/>
      <?php if ($card['key'] === 'tablica' && $unreadTablica > 0): ?>
        <span class="badge rounded-pill bg-danger"><?php echo $unreadTablica; ?></span>
      <?php endif; ?>
      <p class="text-center text-light"><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></p>
    </button>
  </div>
<?php endforeach; ?>

</div>
</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

<?php if (in_array('tablica', $allowedApps, true)): ?>
<script>
function updateUnreadTablica() {
  fetch('../tablica/unread_count.php')
    .then(function (res) { return res.json(); })
    .then(function (data) {
      var btn = document.getElementById('tablica-btn');
      if (!btn) {
        return;
      }

      var badge = btn.querySelector('span.badge');
      if (data.count > 0) {
        if (!badge) {
          badge = document.createElement('span');
          badge.className = 'badge rounded-pill bg-danger';
          btn.appendChild(badge);
        }
        badge.textContent = String(data.count);
      } else if (badge) {
        badge.remove();
      }
    });
}

setInterval(updateUnreadTablica, 5000);
updateUnreadTablica();
</script>
<?php endif; ?>

<script>
document.addEventListener('keydown', function (event) {
  var cards = Array.from(document.querySelectorAll('[data-app-card]'));
  if (cards.length === 0 || !['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Enter', ' '].includes(event.key)) {
    return;
  }

  var index = cards.indexOf(document.activeElement);
  if (index === -1) {
    if (event.key.startsWith('Arrow')) {
      event.preventDefault();
      cards[0].focus();
    }
    return;
  }

  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault();
    cards[index].click();
    return;
  }

  event.preventDefault();
  var columns = window.matchMedia('(min-width: 992px)').matches ? 5 : 3;
  var step = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : event.key === 'ArrowDown' ? columns : -columns;
  cards[(index + step + cards.length) % cards.length].focus();
});
</script>

</body>
</html>
