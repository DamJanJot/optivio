
<?php
session_start();
require_once '../connect.php';

if (!isset($_SESSION['id'])) {
  http_response_code(403);
  exit('Nie zalogowano.');
}

$user_id = $_SESSION['id'];
$data = $_POST['img'] ?? '';

if (strpos($data, 'data:image/png;base64,') === 0) {
  $data = str_replace('data:image/png;base64,', '', $data);
  $data = str_replace(' ', '+', $data);
  $decoded = base64_decode($data);

  $stmt = $pdo->prepare("REPLACE INTO rysunki (user_id, obrazek) VALUES (?, ?)");
  $stmt->execute([$user_id, $decoded]);
  echo "Zapisano!";
} else {
  echo "Nieprawidłowe dane.";
}
?>
