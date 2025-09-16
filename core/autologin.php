<?php
session_start();
require_once 'connect.php'; // Połączenie z PDO

$token = $_GET['token'] ?? '';
if (!$token) {
    echo "Brak tokena.";
    exit;
}

// Szukamy tokena w bazie (bez sprawdzania 'used')
$stmt = $pdo->prepare("SELECT * FROM autologiny WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$row = $stmt->fetch();

if ($row) {
    // Pobieramy dane użytkownika
    $user_stmt = $pdo->prepare("SELECT id, email FROM uzytkownicy WHERE id = ?");
    $user_stmt->execute([$row['user_id']]);
    $user = $user_stmt->fetch();

    if ($user) {
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = $user['id'];
        $_SESSION['email'] = $user['email'];

        // Nie oznaczamy tokena jako 'used' – działa wielokrotnie
        header("Location: terminal/");
        exit;
    }
}

echo "Token nieważny lub wygasł.";
exit;
?>
