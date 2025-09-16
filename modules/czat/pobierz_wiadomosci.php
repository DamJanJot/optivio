<?php
session_start();
require_once 'connect.php';

$me = $_SESSION['id'];
$other = (int)$_GET['id'];

$stmt = $pdo->prepare("
  SELECT w.tresc, w.data_wyslania, u.zdjecie_profilowe, w.nadawca_id
  FROM wiadomosci w
  JOIN uzytkownicy u ON w.nadawca_id = u.id
  WHERE (w.nadawca_id = :me AND w.odbiorca_id = :other)
     OR (w.nadawca_id = :other AND w.odbiorca_id = :me)
  ORDER BY w.data_wyslania ASC
");
$stmt->execute(['me' => $me, 'other' => $other]);
$msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($msgs as $msg) {
  $isSelf = $msg['nadawca_id'] == $me;
  // Determine image path
  $imgPath = $msg['zdjecie_profilowe'];
  if (!empty($imgPath) && file_exists(__DIR__ . '/../' . $imgPath)) {
      $img = '/' . $imgPath;
  } else {
      $img = '/uploads/default.png';
  }
  echo '<div class="msg ' . ($isSelf ? 'self' : '') . '">';
  echo '<img src="' . htmlspecialchars($img) . '" class="msg-avatar" alt="avatar">';
  echo '<div><div class="bubble">' . htmlspecialchars($msg['tresc']) . '</div>';
  echo '<div class="timestamp">' . $msg['data_wyslania'] . '</div></div>';
  echo '</div>';
}
?>