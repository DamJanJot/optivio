<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

$unread_tablica = json_decode(@file_get_contents("../tablica/unread_count.php"), true)['count'] ?? 0;
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
<style>
#tablica-btn {
    position: relative;
}
#tablica-btn .badge {
    top: 0;
    right: 0;
    transform: translate(50%, -50%);
    position: absolute;
}
</style>
</head>
<body class="bgzdj">

<header class="bgzdj-shadow"></header>


<h2 class="text-center  py-3">Apki <img width="34" height="34" src="https://img.icons8.com/arcade/64/activity-grid-2.png" alt="activity-grid-2"/></h2>

<div class="container text-center text-light ">
<div class="row row-cols-2 row-cols-lg-5 g-2 g-lg-3 ">

<div class="col-4 py-2">
<a class="btn app-img glass-box p-2" onclick="location.href='../notatnik/note.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/64/task.png" alt="task"/><p class="text-center text-light">Notatnik</p></a>
</div>
<div class="col-4 py-2">
<a class="btn app-img glass-box p-2" onclick="location.href='../dysk/dysk.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/64/cloud-storage.png" alt="cloud-storage"/><p class="text-center text-light">Dysk</p></a>
</div>
<div class="col-4 py-2">
<a class="btn app-img glass-box p-2" onclick="location.href='../galeria/galeria.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/50/gallery.png" alt="gallery"/><p class="text-center text-light">Galeria</p></a>
</div>
<div class="col-4 py-2">
<a class="button btn app-img glass-box p-2" onclick="location.href='../todo/todo_index.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/64/test-passed.png" alt="test-passed"/><p class="text-center text-light">ToDo</p></a>
</div>
<div class="col-4 py-2">
<a class="button btn app-img glass-box p-2" onclick="location.href='../tabele/tabele.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/64/activity-grid.png" alt="activity-grid"/><p class="text-center text-light">Tabele</p></a>
</div>
<div class="col-4 py-2">
<a class="button btn app-img glass-box p-2" onclick="location.href='../terminal_mobile/terminal.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/64/console.png" alt="console"/><p class="text-center text-light">Terminal</p></a>
</div>
<div class="col-4 py-2">
<a class="btn app-img glass-box p-2" onclick="location.href='../kalendarz/kalendarz.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/64/calendar.png" alt="calendar"/><p class="text-center text-light">Kalendarz</p></a>
</div>


<div class="col-4 py-2">
<button id="tablica-btn" onclick="location.href='../tablica/read_all.php'" class="button btn app-img glass-box p-2" type="button">
<img width="50" height="50" src="https://img.icons8.com/arcade/50/flipboard.png" alt="flipboard"/>
<?php if ($unread_tablica > 0): ?>
  <span class="badge rounded-pill bg-danger"><?= $unread_tablica ?></span>
<?php endif; ?>
<p class="text-center text-light">Tablica</p>
</button>
</div>

<div class="col-4 py-2">
<a class="button btn app-img glass-box p-2" onclick="location.href='../portfel_mobile/portfel.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/64/card-wallet.png" alt="card-wallet"/><p class="text-center text-light">Portfel</p></a>
</div>


<div class="col-4 py-2">
<a class="button btn app-img glass-box p-2" onclick="location.href='../paint/paint_mobile.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/50/sign-up.png" alt="sign-up"/><p class="text-center text-light">Rysunki</p></a>
</div>

<div class="col-4 py-2">
<a class="button btn app-img glass-box p-2" onclick="location.href='../nauka/nauka_ang.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/50/translation.png" alt="translation"/><p class="text-center text-light">Nauka</p></a>
</div>

<div class="col-4 py-2">
<a class="button btn app-img glass-box p-2" onclick="location.href='../lista_uzytkownikow.php'"><img width="50" height="50" src="https://img.icons8.com/arcade/50/contacts--v2.png" alt="contacts--v2"/><p class="text-center text-light">Kontakty</p></a>
</div>


</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

<script>
function updateUnreadTablica() {
  fetch('../tablica/unread_count.php')
    .then(res => res.json())
    .then(data => {
      const btn = document.getElementById('tablica-btn');
      let badge = btn.querySelector('span.badge');
      if (data.count > 0) {
        if (!badge) {
          badge = document.createElement('span');
          badge.className = 'badge rounded-pill bg-danger';
          badge.textContent = data.count;
          btn.appendChild(badge);
        } else {
          badge.textContent = data.count;
        }
      } else if (badge) {
        badge.remove();
      }
    });
}

setInterval(updateUnreadTablica, 5000);
updateUnreadTablica();
</script>

</body>
</html>
