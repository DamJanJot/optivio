<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}
require_once 'connect.php';
$user_id = $_SESSION['id'];

$stmt = $pdo->prepare("
  SELECT u.id, CONCAT(u.imie, ' ', u.nazwisko) AS display_name, u.zdjecie_profilowe, COUNT(w.id) AS nieprzeczytane
  FROM uzytkownicy u
  LEFT JOIN wiadomosci w 
    ON w.nadawca_id = u.id 
    AND w.odbiorca_id = :me 
    AND w.przeczytana = 0
  WHERE u.id != :me
  GROUP BY u.id, display_name, u.zdjecie_profilowe
  ORDER BY display_name ASC
");
$stmt->execute(['me' => $user_id]);
$rozmowy = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Rozmowy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./css/style.css">
</head>
<body >


    <h4 class="text-center mb-4">Twoje rozmowy <img width="28" height="28" src="https://img.icons8.com/arcade/28/messaging-.png" alt="messaging-"/></h4>
  <?php foreach ($rozmowy as $row): ?>
    <?php
      $img = (!empty($row['zdjecie_profilowe']) && file_exists('../' . $row['zdjecie_profilowe']))
        ? '../' . $row['zdjecie_profilowe']
        : '../uploads/default.png';
    ?>
    <a class="user" href="rozmowa.php?id=<?= $row['id'] ?>">
      <img src="<?= $img ?>" alt="avatar">
      <div><?= htmlspecialchars($row['display_name']) ?></div>
      <?php if ($row['nieprzeczytane'] > 0): ?>
        <span class="unread"><?= $row['nieprzeczytane'] ?></span>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>

<script>

// Automatyczne odświeżanie listy rozmów co 5 sekund
document.addEventListener("DOMContentLoaded", () => {
  setInterval(() => {
    window.location.reload();
  }, 5000);
});
</script>



</body>
</html>
