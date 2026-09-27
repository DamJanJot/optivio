<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('galeria');

$user_id = $_SESSION['id'];
$folder = isset($_GET['folder']) ? trim((string) $_GET['folder'], '/') : '';
$folder = str_replace(['..', '\\'], '', $folder);
$base_dir = "gallery/$user_id";
$current_dir = $base_dir . ($folder ? '/' . $folder : '');

if (!file_exists($base_dir)) {
    mkdir($base_dir, 0777, true);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Galeria</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h5 class="back-link"><a href="../apki/apki.php">← Powrót</a></h5>

<main class="gallery-shell">
<section class="gallery-card">
<div class="gallery-head">
    <div>
        <h1>Galeria</h1>
        <div class="nav">
            <a href="galeria.php"><img width="34" height="34" src="https://img.icons8.com/arcade/50/rental-house-contract.png" alt="Home"/></a>
            <?php
            if ($folder) {
                $parts = explode('/', $folder);
                $path = "";
                foreach ($parts as $part) {
                    $path .= ($path ? '/' : '') . $part;
                    echo " &raquo; <a href='?folder=" . urlencode($path) . "'>" . htmlspecialchars($part, ENT_QUOTES, 'UTF-8') . "</a>";
                }
            }
            ?>
        </div>
    </div>
    <div class="gallery-actions">
        <button class="btn-app" onclick="toggleForm('uploadForm')" title="Dodaj zdjęcie"><img width="34" height="34" src="https://img.icons8.com/arcade/50/add-image.png" alt="Dodaj zdjęcie"/></button>
        <button class="btn-app" onclick="toggleForm('folderForm')" title="Dodaj folder"><img width="34" height="34" src="https://img.icons8.com/arcade/50/add-folder.png" alt="Dodaj folder"/></button>
    </div>
</div>

<div id="uploadForm" style="display:none;" class="form-box">
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="current_folder" value="<?php echo htmlspecialchars($folder, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="file" name="fileToUpload" required>
        <input type="submit" value="Prześlij">
    </form>
</div>

<div id="folderForm" style="display:none;" class="form-box">
    <form action="upload.php" method="post">
        <input type="hidden" name="current_folder" value="<?php echo htmlspecialchars($folder, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="new_folder" placeholder="Nazwa folderu" required>
        <input type="submit" value="Utwórz">
    </form>
</div>

<?php include 'gallery_manager.php'; ?>
</section>
</main>

<script src="script.js"></script>
</body>
</html>
