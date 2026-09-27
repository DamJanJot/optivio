<?php
require_once __DIR__ . '/../core/access_control.php';
optivio_require_login();
optivio_require_app_access('profil');

require_once __DIR__ . '/../core/env_loader.php';

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

if (!$host || !$dbname || !$username) {
  die('Brak konfiguracji DB w zmiennych środowiskowych (DB_HOST, DB_NAME, DB_USER, DB_PASS).');
}

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

$uzytkownik_id = (int) $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['zdjecie_profilowe']) && $_FILES['zdjecie_profilowe']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__) . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $originalName = basename((string) $_FILES['zdjecie_profilowe']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeExt = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $ext : 'png';
        $fileName = 'avatar_' . $uzytkownik_id . '_' . time() . '.' . $safeExt;
        $targetFsPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['zdjecie_profilowe']['tmp_name'], $targetFsPath)) {
            $zdjecie_profilowe = 'uploads/' . $fileName;
            $stmt = $conn->prepare("UPDATE uzytkownicy SET zdjecie_profilowe = ? WHERE id = ?");
            $stmt->bind_param("si", $zdjecie_profilowe, $uzytkownik_id);
            $stmt->execute();
            $_SESSION['avatar'] = $zdjecie_profilowe;
            $stmt->close();
        }
    }

    $nick = (string) ($_POST['nick'] ?? '');
    $opis = (string) ($_POST['opis'] ?? '');

    $stmt = $conn->prepare("UPDATE uzytkownicy SET nick = ?, opis = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nick, $opis, $uzytkownik_id);
    $stmt->execute();
    $stmt->close();
}

$stmt = $conn->prepare("SELECT imie, nazwisko, email, zdjecie_profilowe, nick, opis, COALESCE(rola, 'user') AS rola FROM uzytkownicy WHERE id = ?");
$stmt->bind_param("i", $uzytkownik_id);
$stmt->execute();
$result = $stmt->get_result();
$uzytkownik = $result->fetch_assoc();
$stmt->close();

$contacts = [];
$contactsResult = $conn->query("SELECT id, imie, nazwisko, email, zdjecie_profilowe, nick, opis, COALESCE(rola, 'user') AS rola FROM uzytkownicy ORDER BY imie, nazwisko");
if ($contactsResult) {
    while ($row = $contactsResult->fetch_assoc()) {
        $contacts[] = $row;
    }
}
$conn->close();

$isAdmin = in_array(optivio_normalize_role((string) ($_SESSION['rola'] ?? $uzytkownik['rola'] ?? 'user')), ['admin', 'owner'], true);
$profile_photo = optivio_stored_file_url((string) ($uzytkownik['zdjecie_profilowe'] ?? ''));
?>

<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="../public/assets/css/profil.css" />
</head>
<body class="bgzdj">

<header class="bgzdj-shadow"></header>

<h4 class="text-center mb-3">Twój profil <img width="32" height="32" src="https://img.icons8.com/arcade/32/parse-from-clipboard.png" alt="parse-from-clipboard"/></h4>
<div class="profile-container">
  <div class="text-center">
    <img src="<?= htmlspecialchars($profile_photo, ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie profilowe" class="profile-image">
  </div>

  <div class="mt-3 px-3">
    <p><strong>Imię:</strong> <?= htmlspecialchars((string) $uzytkownik['imie'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Nazwisko:</strong> <?= htmlspecialchars((string) $uzytkownik['nazwisko'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars((string) $uzytkownik['email'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Nick:</strong> <?= htmlspecialchars((string) $uzytkownik['nick'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Opis:</strong> <?= htmlspecialchars((string) $uzytkownik['opis'], ENT_QUOTES, 'UTF-8') ?></p>
  </div>

  <button class="button-profil btn btn-outline-light w-100" type="button" data-bs-toggle="collapse" data-bs-target="#editForm" aria-expanded="false" aria-controls="editForm">
    Edytuj profil <i class="fa-regular fa-pen-to-square ms-1"></i>
  </button>

  <div class="collapse" id="editForm">
    <form method="post" enctype="multipart/form-data" class="mt-3">
      <label for="zdjecie_profilowe">Zdjęcie profilowe</label>
      <input type="file" name="zdjecie_profilowe" class="form-control" accept="image/*">

      <label for="nick">Nick</label>
      <input type="text" name="nick" class="form-control" value="<?= htmlspecialchars((string) $uzytkownik['nick'], ENT_QUOTES, 'UTF-8') ?>">

      <label for="opis">Opis</label>
      <textarea name="opis" class="form-control" rows="3"><?= htmlspecialchars((string) $uzytkownik['opis'], ENT_QUOTES, 'UTF-8') ?></textarea>

      <button type="submit" class="btn btn-primary w-100 mt-3">Zaktualizuj</button>
    </form>
  </div>
</div>

<section class="profile-container mt-4">
  <h5 class="mb-3">Kontakty</h5>
  <div class="contacts-list">
    <?php foreach ($contacts as $contact): ?>
      <?php $contactPhoto = optivio_stored_file_url((string) ($contact['zdjecie_profilowe'] ?? '')); ?>
      <details class="contact-card">
        <summary>
          <img src="<?= htmlspecialchars($contactPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
          <span><?= htmlspecialchars(trim(($contact['imie'] ?? '') . ' ' . ($contact['nazwisko'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
          <?php if ($isAdmin): ?>
            <small><?= htmlspecialchars((string) $contact['rola'], ENT_QUOTES, 'UTF-8') ?></small>
          <?php endif; ?>
        </summary>
        <div class="contact-body">
          <p><strong>Email:</strong> <?= htmlspecialchars((string) $contact['email'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><strong>Nick:</strong> <?= htmlspecialchars((string) $contact['nick'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><strong>Opis:</strong> <?= htmlspecialchars((string) $contact['opis'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php if ($isAdmin): ?>
            <a class="btn btn-sm btn-outline-info" href="<?= htmlspecialchars(optivio_url('public/admin/index.php'), ENT_QUOTES, 'UTF-8') ?>">Zarządzaj w panelu admina</a>
          <?php endif; ?>
        </div>
      </details>
    <?php endforeach; ?>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
