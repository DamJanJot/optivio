<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

require_once 'connect.php';
$me = $_SESSION['id'];
$other = (int)$_GET['id'];

$stmt = $pdo->prepare(
  "SELECT w.*, u.zdjecie_profilowe
   FROM wiadomosci w
   JOIN uzytkownicy u ON u.id = w.nadawca_id
   WHERE (w.nadawca_id = :me AND w.odbiorca_id = :other)
      OR (w.nadawca_id = :other AND w.odbiorca_id = :me)
   ORDER BY w.data_wyslania ASC"
);
$stmt->execute(['me' => $me, 'other' => $other]);
$wiadomosci = $stmt->fetchAll(PDO::FETCH_ASSOC);




?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Rozmowa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style_rozmowa.css">
</head>
<body class="container mt-3">  
<h5 class="mb-4"><a href="index.php" style="color: #aaa; text-decoration:none;">← Powrót</a></h5>




  <div id="chat-box"></div>



  <form method="post" action="wyslij.php">
    <input type="hidden" name="odbiorca_id" value="<?= $other ?>">
    <input type="text" name="tresc" placeholder="Napisz wiadomość..." required>
    <button><i class="fa-solid fa-paper-plane"></i></button>
  </form>




  <script src="js/chat.js"></script>
</body>
</html>