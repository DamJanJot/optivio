<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];
$base_dir = "gallery/$user_id";
if (!file_exists($base_dir)) {
    mkdir($base_dir, 0777, true);
}

$folder = isset($_POST['current_folder']) ? trim($_POST['current_folder']) : "";
$target_dir = $base_dir;
if ($folder) {
    $target_dir .= "/" . $folder;
}

if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (isset($_POST['new_folder'])) {
    $new_folder = preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['new_folder']);
    mkdir($target_dir . "/" . $new_folder, 0777, true);
    header("Location: index.php?folder=" . urlencode($folder));
    exit;
}

if (isset($_FILES["fileToUpload"])) {
    $target_file = $target_dir . "/" . basename($_FILES["fileToUpload"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($imageFileType, $allowed)) {
        die("Tylko obrazki są dozwolone.");
    }

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        file_put_contents($target_file . ".txt", date("Y-m-d H:i:s"));
    }
}

header("Location: index.php?folder=" . urlencode($folder));
exit;
