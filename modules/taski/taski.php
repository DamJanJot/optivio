<?php
session_start();
require 'db.php';

$user_id = $_SESSION['id'];

// pobierz wszystkie zadania przypisane do użytkownika
$stmt = $pdo->prepare("
    SELECT *
    FROM zadania_mobile_uzytkownicy
    WHERE user_id = ?
    ORDER BY zadanie_id DESC
");
$stmt->execute([$user_id]);
$zadania = $stmt->fetchAll(PDO::FETCH_ASSOC);

// pobierz użytkowników
$users_stmt = $pdo->query("SELECT id, imie, nazwisko FROM uzytkownicy");
$wszyscy_uzytkownicy = $users_stmt->fetchAll(PDO::FETCH_ASSOC);


$me = $_SESSION['id'];

// oznacz wszystkie jako przeczytane
$stmt = $pdo->prepare("UPDATE zadania_mobile_uzytkownicy SET przeczytane = 1 WHERE user_id = ?");
$stmt->execute([$me]);

$celeFeatureEnabled = true;
try {
  $pdo->query("SELECT id FROM cele LIMIT 1");
} catch (Throwable $e) {
  $celeFeatureEnabled = false;
}


?>

<!DOCTYPE html>
<html lang='pl'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <title>Taski</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
  <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="style.css">
</head>
<body>


  <h4 class="text-center mb-4 "> Taski <img width="28" height="28" src="https://img.icons8.com/arcade/28/shortlist.png" alt="shortlist"/></h4>

<!-- Przycisk do rozwijania formularza -->
<button class=" btn-task w-100" onclick="toggleForm()">➕ Dodaj nowe zadanie</button>

<!-- Ukryty formularz -->
<div id="taskForm" style="display: none;">
  <form action="dodaj_task.php" method="POST">
    <div class="mb-2">
      <input type="text" name="tytul" class="form-control" placeholder="Tytuł zadania" required>
    </div>
    <div class="mb-2">
      <textarea name="opis" class="form-control" placeholder="Opis zadania" rows="2" required></textarea>
    </div>
    <div class="mb-2">
      <label for="uzytkownicy">Wybierz użytkowników:</label>
      <select name="uzytkownicy[]" multiple class="form-select">
        <?php
        $userQuery = $pdo->query("SELECT id, imie, nazwisko FROM uzytkownicy WHERE id != $user_id");
        while ($u = $userQuery->fetch()) {
          echo '<option value="'.$u['id'].'">'.htmlspecialchars($u['imie'].' '.$u['nazwisko']).'</option>';
        }
        ?>
      </select>
    </div>
    <button type="submit" class="btn btn-success w-100 p-2">➕ Dodaj</button>
<br>
  </form>
</div>



<h6 class='mb-4  py-2' >Aktualne <img width="16" height="16" src="https://img.icons8.com/arcade/32/pin.png" alt="pin"/></h6>

<div class='task-container'>

<?php foreach ($zadania as $z): ?>
  <?php if (!$z['wykonane']): ?>
    <div class='task'>
  <div class='task-header'>
    <div class='task-title'><?= htmlspecialchars($z['tytul']) ?></div>
    <div class='task-buttons'>
      <?php if ($z['autor_id'] == $user_id): ?>
        <form method='POST' action='usun_task.php' style='display:inline;'>
          <input type='hidden' name='zadanie_id' value='<?= $z['zadanie_id'] ?>'>
          <button class='btn btn-sm btn-danger'><i class="fa-solid fa-xmark"></i></button>
        </form>
        <button class='btn btn-sm btn-secondary' onclick="toggleEdit('edit-<?= $z['zadanie_id'] ?>')">
          <i class="fa-solid fa-pen-to-square"></i>
        </button>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-light" onclick="toggleInfo('info-<?= $z['zadanie_id'] ?>')">
        <i class="fa-solid fa-circle-info"></i>
      </button>
    </div>
  </div>

  <p><?= nl2br(htmlspecialchars($z['opis'])) ?></p>

  <?php
    $cele = [];
    $wykonaneCele = 0;
    $procent = 0;
    if ($celeFeatureEnabled) {
      try {
        $cele_stmt = $pdo->prepare("SELECT id, opis, wykonane FROM cele WHERE zadanie_id = ? ORDER BY id ASC");
        $cele_stmt->execute([$z['zadanie_id']]);
        $cele = $cele_stmt->fetchAll(PDO::FETCH_ASSOC);
        $wykonaneCele = count(array_filter($cele, fn($c) => (int) $c['wykonane'] === 1));
        $procent = count($cele) > 0 ? (int) round(($wykonaneCele / count($cele)) * 100) : 0;
      } catch (Throwable $e) {
        $cele = [];
        $procent = 0;
      }
    }
  ?>

  <?php if ($celeFeatureEnabled): ?>
    <?php if (count($cele) > 0): ?>
      <div class="mt-3">
        <strong>Cele:</strong>
        <ul class="list-unstyled mb-2">
          <?php foreach ($cele as $cel): ?>
            <li>
              <form method="POST" action="aktualizuj_cel.php" style="display:inline;">
                <input type="hidden" name="id" value="<?= $cel['id'] ?>">
                <input type="checkbox" name="wykonane" value="1" onchange="this.form.submit()" <?= ((int) $cel['wykonane'] === 1) ? 'checked' : '' ?>>
              </form>
              <?= htmlspecialchars($cel['opis']) ?>
              <?php if ($z['autor_id'] == $_SESSION['id']): ?>
                <form method="POST" action="usun_cel.php" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $cel['id'] ?>">
                  <button class="btn btn-sm btn-link text-danger">❌</button>
                </form>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($z['autor_id'] == $_SESSION['id']): ?>
      <form action="dodaj_cel.php" method="POST" class="d-flex gap-2 mt-2 mb-2">
        <input type="hidden" name="zadanie_id" value="<?= $z['zadanie_id'] ?>">
        <input type="text" name="opis" placeholder="Nowy cel" class="form-control" required>
        <button class="btn btn-primary">➕</button>
      </form>
    <?php endif; ?>

    <div class="progress mb-2">
      <div class="progress-bar bg-success" role="progressbar" style="width: <?= $procent ?>%;" aria-valuenow="<?= $procent ?>" aria-valuemin="0" aria-valuemax="100">
        <?= $procent ?>%
      </div>
    </div>
  <?php endif; ?>

  <?php if (!$z['wykonane']): ?>
    <form method='POST' action='wykonaj_task.php'>
      <input type='hidden' name='id' value='<?= $z['zadanie_id'] ?>'>
      <button class='btn btn-sm  btn-outline-success'>Ukończ</button>
    </form>
  <?php else: ?>
    <span class='badge bg-success'>Ukończone <i class="fa-solid fa-square-check"></i></span>
  <?php endif; ?>

  <!-- INFO BOX -->
  <div id="info-<?= $z['zadanie_id'] ?>" class="task-info-box" style="display: none;">
    <strong>Autor:</strong> <?= htmlspecialchars(getUserName($z['autor_id'], $wszyscy_uzytkownicy)) ?><br>
    <strong>Uczestnicy:</strong> <?= htmlspecialchars(joinUsers($z['zadanie_id'], $pdo)) ?><br>
    <strong>Dodano:</strong> <?= date('Y-m-d', strtotime($z['created_at'])) ?>
  </div>

  <!-- FORMULARZ EDYCJI -->
  <?php if ($z['autor_id'] == $user_id): ?>
    <div class='form-wrapper mt-2' id='edit-<?= $z['zadanie_id'] ?>' style='display:none;'>
      <form method='POST' action='edytuj_task.php'>
        <input type='hidden' name='id' value='<?= $z['zadanie_id'] ?>'>
        <input class='form-control mb-2' type='text' name='tytul' value='<?= htmlspecialchars($z['tytul']) ?>'>
        <textarea class='form-control mb-2' name='opis'><?= htmlspecialchars($z['opis']) ?></textarea>
        <button class='btn btn-warning btn-sm'>Zapisz</button>
      </form>
    </div>
  <?php endif; ?>
</div>

  <?php endif; ?>
<?php endforeach; ?>
</div>

<h6 class='mt-5 mb-4 '>Wykonane <img width="16" height="16" src="https://img.icons8.com/arcade/28/checkmark.png" alt="checkmark"/></h6>

<div class='task-container'>

<?php foreach ($zadania as $z): ?>
  <?php if ($z['wykonane']): ?>
    <div class='task'>
  <div class='task-header'>
    <div class='task-title'><?= htmlspecialchars($z['tytul']) ?></div>
    <div class='task-buttons'>
      <?php if ($z['autor_id'] == $user_id): ?>
        <form method='POST' action='usun_task.php' style='display:inline;'>
          <input type='hidden' name='zadanie_id' value='<?= $z['zadanie_id'] ?>'>
          <button class='btn btn-sm btn-danger'><i class="fa-solid fa-xmark"></i></button>
        </form>
        <button class='btn btn-sm btn-secondary' onclick="toggleEdit('edit-<?= $z['zadanie_id'] ?>')">
          <i class="fa-solid fa-pen-to-square"></i>
        </button>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-light" onclick="toggleInfo('info-<?= $z['zadanie_id'] ?>')">
        <i class="fa-solid fa-circle-info"></i>
      </button>
    </div>
  </div>

  <p><?= nl2br(htmlspecialchars($z['opis'])) ?></p>

  <?php
    $cele = [];
    $wykonaneCele = 0;
    $procent = 0;
    if ($celeFeatureEnabled) {
      try {
        $cele_stmt = $pdo->prepare("SELECT id, opis, wykonane FROM cele WHERE zadanie_id = ? ORDER BY id ASC");
        $cele_stmt->execute([$z['zadanie_id']]);
        $cele = $cele_stmt->fetchAll(PDO::FETCH_ASSOC);
        $wykonaneCele = count(array_filter($cele, fn($c) => (int) $c['wykonane'] === 1));
        $procent = count($cele) > 0 ? (int) round(($wykonaneCele / count($cele)) * 100) : 0;
      } catch (Throwable $e) {
        $cele = [];
        $procent = 0;
      }
    }
  ?>

  <?php if ($celeFeatureEnabled): ?>
    <?php if (count($cele) > 0): ?>
      <div class="mt-3">
        <strong>Cele:</strong>
        <ul class="list-unstyled mb-2">
          <?php foreach ($cele as $cel): ?>
            <li>
              <form method="POST" action="aktualizuj_cel.php" style="display:inline;">
                <input type="hidden" name="id" value="<?= $cel['id'] ?>">
                <input type="checkbox" name="wykonane" value="1" onchange="this.form.submit()" <?= ((int) $cel['wykonane'] === 1) ? 'checked' : '' ?>>
              </form>
              <?= htmlspecialchars($cel['opis']) ?>
              <?php if ($z['autor_id'] == $_SESSION['id']): ?>
                <form method="POST" action="usun_cel.php" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $cel['id'] ?>">
                  <button class="btn btn-sm btn-link text-danger">❌</button>
                </form>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($z['autor_id'] == $_SESSION['id']): ?>
      <form action="dodaj_cel.php" method="POST" class="d-flex gap-2 mt-2 mb-2">
        <input type="hidden" name="zadanie_id" value="<?= $z['zadanie_id'] ?>">
        <input type="text" name="opis" placeholder="Nowy cel" class="form-control" required>
        <button class="btn btn-primary">➕</button>
      </form>
    <?php endif; ?>

    <div class="progress mb-2">
      <div class="progress-bar bg-success" role="progressbar" style="width: <?= $procent ?>%;" aria-valuenow="<?= $procent ?>" aria-valuemin="0" aria-valuemax="100">
        <?= $procent ?>%
      </div>
    </div>
  <?php endif; ?>

  <?php if (!$z['wykonane']): ?>
    <form method='POST' action='wykonaj_task.php'>
      <input type='hidden' name='id' value='<?= $z['zadanie_id'] ?>'>
      <button class='btn btn-sm  btn-outline-success'>Ukończ</button>
    </form>
  <?php else: ?>
    <span class='badge bg-success'>Ukończone <i class="fa-solid fa-square-check"></i></span>
  <?php endif; ?>

  <!-- INFO BOX -->
  <div id="info-<?= $z['zadanie_id'] ?>" class="task-info-box" style="display: none;">
    <strong>Autor:</strong> <?= htmlspecialchars(getUserName($z['autor_id'], $wszyscy_uzytkownicy)) ?><br>
    <strong>Uczestnicy:</strong> <?= htmlspecialchars(joinUsers($z['zadanie_id'], $pdo)) ?><br>
    <strong>Dodano:</strong> <?= date('Y-m-d', strtotime($z['created_at'])) ?>
  </div>

  <!-- FORMULARZ EDYCJI -->
  <?php if ($z['autor_id'] == $user_id): ?>
    <div class='form-wrapper mt-2' id='edit-<?= $z['zadanie_id'] ?>' style='display:none;'>
      <form method='POST' action='edytuj_task.php'>
        <input type='hidden' name='id' value='<?= $z['zadanie_id'] ?>'>
        <input class='form-control mb-2' type='text' name='tytul' value='<?= htmlspecialchars($z['tytul']) ?>'>
        <textarea class='form-control mb-2' name='opis'><?= htmlspecialchars($z['opis']) ?></textarea>
        <button class='btn btn-warning btn-sm'>Zapisz</button>
      </form>
    </div>
  <?php endif; ?>
</div>

  <?php endif; ?>
<?php endforeach; ?>

</div>

<script>
  function toggleEdit(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
  }
</script>

<?php
function getUserName($id, $users) {
  foreach ($users as $u) {
    if ($u['id'] == $id) return $u['imie'] . ' ' . $u['nazwisko'];
  }
  return 'Nieznany';
}

function joinUsers($zadanie_id, $pdo) {
  $stmt = $pdo->prepare("SELECT u.imie, u.nazwisko FROM zadania_mobile_uzytkownicy zmu
                         JOIN uzytkownicy u ON zmu.user_id = u.id
                         WHERE zmu.zadanie_id = ?");
  $stmt->execute([$zadanie_id]);
  $names = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return implode(', ', array_map(fn($u) => $u['imie'] . ' ' . $u['nazwisko'], $names));
}
?>


<script>

function toggleInfo(id) {
  const el = document.getElementById(id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

</script>


<script>
  function toggleForm() {
    const form = document.getElementById('taskForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  }
</script>


</body>
</html>
