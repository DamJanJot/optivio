<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../public/login.php');
    exit();
}

require_once __DIR__ . '/../core/env_loader.php'; 

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

if (!$host || !$dbname || !$username) {
  die('Brak konfiguracji DB w zmiennych środowiskowych (DB_HOST, DB_NAME, DB_USER, DB_PASS).');
}

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

$uzytkownik_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['zdjecie_profilowe']) && $_FILES['zdjecie_profilowe']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = dirname(__DIR__) . '/uploads/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    $originalName = basename($_FILES['zdjecie_profilowe']['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $safeExt = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $ext : 'png';
    $fileName = 'avatar_' . $uzytkownik_id . '_' . time() . '.' . $safeExt;
    $targetFsPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['zdjecie_profilowe']['tmp_name'], $targetFsPath)) {
      $zdjecie_profilowe = 'uploads/' . $fileName;

      $sql = "UPDATE uzytkownicy SET zdjecie_profilowe = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("si", $zdjecie_profilowe, $uzytkownik_id);
      $stmt->execute();
      $_SESSION['avatar'] = $zdjecie_profilowe;
      $stmt->close();
    }
    }

    $nick = $_POST['nick'];
    $opis = $_POST['opis'];

    $sql = "UPDATE uzytkownicy SET nick = ?, opis = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nick, $opis, $uzytkownik_id);
    $stmt->execute();
    $stmt->close();
}

$sql = "SELECT imie, nazwisko, email, zdjecie_profilowe, nick, opis FROM uzytkownicy WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uzytkownik_id);
$stmt->execute();
$result = $stmt->get_result();
$uzytkownik = $result->fetch_assoc();
$stmt->close();
$conn->close();
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
  

  <?php
    $default_photo = '../public/assets/img/default.png';
    $profile_photo = $default_photo;

    if (!empty($uzytkownik['zdjecie_profilowe'])) {
      $normalizedAvatarPath = ltrim(str_replace('\\', '/', $uzytkownik['zdjecie_profilowe']), './');
      $normalizedAvatarPath = ltrim($normalizedAvatarPath, '/');
      $avatarFsPath = dirname(__DIR__) . '/' . $normalizedAvatarPath;
      if ($normalizedAvatarPath !== '' && file_exists($avatarFsPath)) {
        $profile_photo = '../' . $normalizedAvatarPath;
      }
    }
  ?>
  <div class="text-center">
    <img src="<?= $profile_photo ?>" alt="Zdjęcie Profilowe" class="profile-image">
  </div>

  <div class="mt-3 px-3">
    <p><strong>Imię:</strong> <?= htmlspecialchars($uzytkownik['imie']) ?></p>
    <p><strong>Nazwisko:</strong> <?= htmlspecialchars($uzytkownik['nazwisko']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($uzytkownik['email']) ?></p>
    <p><strong>Nick:</strong> <?= htmlspecialchars($uzytkownik['nick']) ?></p>
    <p><strong>Opis:</strong> <?= htmlspecialchars($uzytkownik['opis']) ?></p>
  </div>

  <button class="button-profil btn btn-outline-light w-100 " type="button" data-bs-toggle="collapse" data-bs-target="#editForm" aria-expanded="false" aria-controls="editForm">
    Edytuj profil <i class="fa-regular fa-pen-to-square ms-1"></i>
  </button>

  <div class="collapse" id="editForm">
    <form method="post" enctype="multipart/form-data" class="mt-3">
      <label for="zdjecie_profilowe">Zdjęcie profilowe</label>
      <input type="file" name="zdjecie_profilowe" class="form-control">

      <label for="nick">Nick</label>
      <input type="text" name="nick" class="form-control" value="<?= htmlspecialchars($uzytkownik['nick']) ?>">

      <label for="opis">Opis</label>
      <textarea name="opis" class="form-control" rows="3"><?= htmlspecialchars($uzytkownik['opis']) ?></textarea>

      <button type="submit" class="btn btn-primary w-100 mt-3">Zaktualizuj</button>
    </form>
  </div>
  
  
</div>





<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
