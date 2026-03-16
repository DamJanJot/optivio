<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];

if (isset($_POST['file'])) {
    $file = $_POST['file'];
    if (strpos($file, "gallery/$user_id/") === 0 && file_exists($file)) {
        unlink($file);
        if (file_exists($file . ".txt")) {
            unlink($file . ".txt");
        }
    }
}

header("Location: index.php");
exit;
