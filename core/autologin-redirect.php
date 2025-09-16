
<?php
session_start();
require_once 'connect.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    echo "Brak tokena.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM autologiny WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$row = $stmt->fetch();

if ($row) {
    $pdo->prepare("UPDATE autologiny SET uses = uses + 1 WHERE id = ?")->execute([$row['id']]);

    $user_stmt = $pdo->prepare("SELECT id, email FROM uzytkownicy WHERE id = ?");
    $user_stmt->execute([$row['user_id']]);
    $user = $user_stmt->fetch();

    if ($user) {
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = $user['id'];
        $_SESSION['email'] = $user['email'];

        $redirect = $row['redirect_to'] ?? '/';
        header("Location: " . $redirect);
        exit;
    }
}

echo "Token nieważny lub wygasł.";
exit;
?>
