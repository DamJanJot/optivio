<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];
$folder = isset($_GET['folder']) ? $_GET['folder'] : '';
$base_dir = "gallery/$user_id";
$current_dir = $base_dir . ($folder ? '/' . $folder : '');

if (!file_exists($base_dir)) {
    mkdir($base_dir, 0777, true);
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Galeria</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h5 class="mb-4"><a href="../apki/apki.php" style="color: #aaa; text-decoration:none;">← Powrót</a></h5>

<div class="nav">
    <a href="galeria.php"><img width="34" height="34" src="https://img.icons8.com/arcade/50/rental-house-contract.png" alt="Home"/></a>
    <?php
    if ($folder) {
        $parts = explode('/', $folder);
        $path = "";
        foreach ($parts as $part) {
            $path .= ($path ? '/' : '') . $part;
            echo " &raquo; <a href='?folder=" . urlencode($path) . "'>" . htmlspecialchars($part) . "</a>";
        }
    }
    ?>
</div>

<button class="btn-app" onclick="toggleForm('uploadForm')"><img width="34" height="34" src="https://img.icons8.com/arcade/50/add-image.png" alt="Add Image"/></button>
<button class="btn-app" onclick="toggleForm('folderForm')"><img width="34" height="34" src="https://img.icons8.com/arcade/50/add-folder.png" alt="Add Folder"/></button>

<div id="uploadForm" style="display:none;" class="form-box">
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="current_folder" value="<?php echo htmlspecialchars($folder); ?>">
        <input type="file" name="fileToUpload" required>
        <input type="submit" value="Prześlij">
    </form>
</div>

<div id="folderForm" style="display:none;" class="form-box">
    <form action="upload.php" method="post">
        <input type="hidden" name="current_folder" value="<?php echo htmlspecialchars($folder); ?>">
        <input type="text" name="new_folder" placeholder="Nazwa folderu" required>
        <input type="submit" value="Utwórz">
    </form>
</div>

<?php include 'gallery_manager.php'; ?>

<script src="script.js"></script>
</body>
</html>
