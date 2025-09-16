<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

require 'db.php';
$user_id = $_SESSION['id'];

if (isset($_POST['new_list'])) {
    $name = $_POST['new_list'];
    $color = $_POST['color'];
    $shared = isset($_POST['shared_with']) ? implode(",", $_POST['shared_with']) : "";
    $stmt = $pdo->prepare("INSERT INTO lists (user_id, name, color, shared_with) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $name, $color, $shared]);
    header("Location: todo_index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM lists WHERE user_id = ? OR FIND_IN_SET(?, shared_with)");
$stmt->execute([$user_id, $user_id]);
$lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

$users = $pdo->prepare("SELECT id, imie, nazwisko FROM uzytkownicy WHERE id != ?");
$users->execute([$user_id]);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>ULTRA TODO PDO</title>

<style> body { background: #1e1e2f; color: white; font-family: Arial; margin: 20px; }
    h1 { font-size: 24px; margin-bottom: 20px; }
    .list { background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
    -webkit-backdrop-filter: blur(20px);
    -backdrop-filter: blur(20px);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    border: 1px solid rgba(255, 255, 255, 0.18);
    z-index: 5; padding: 15px; margin-bottom: 10px;  border-radius: 16px; display: flex; align-items: center; }
    .color { width: 20px; height: 20px; border-radius: 50%; margin-right: 10px; }
    .list a { color: white; text-decoration: none; font-size: 18px; }
    form, .form-hidden { display: none; margin-top: 15px; }
    button, .show-btn { padding: 10px 15px; background-color: #444; color: white; border: none; cursor: pointer; border-radius: 5px; margin-top: 10px; }
    input, select { padding: 10px; background: #222; color: white; border: none; border-radius: 5px; margin-right: 10px; }

.list:hover {
    
      border: 1px solid #0dcaf0;
   transition: 200ms; 
      
  }
</style>
<script>
function toggleForm() {
    var form = document.getElementById('form-list');
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}
</script>
</head><body>";

echo "<h5 class='mb-4'><a href='../apki/apki.php' style='color: #aaa; text-decoration:none;'>← Powrót</a></h5>";
echo "<h1>Moje listy</h1>";

foreach ($lists as $row) {
    $shared = $row['user_id'] != $user_id ? " (Udostępniona)" : "";
    echo "<div class='list'><div class='color' style='background:".htmlspecialchars($row['color'])."'></div><a href='list.php?id={$row['id']}'>" . htmlspecialchars($row['name']) . "</a> <small>$shared</small></div>";
}

echo "<button class='show-btn' onclick='toggleForm()'>+ Dodaj listę</button>";
echo "<form method='post' id='form-list' class='form-hidden'>
<input type='text' name='new_list' placeholder='Nazwa listy' required>
<select name='color'>
<option value='#f39c12'>Pomarańczowy</option>
<option value='#e74c3c'>Czerwony</option>
<option value='#27ae60'>Zielony</option>
<option value='#2980b9'>Niebieski</option>
</select>
<select name='shared_with[]' multiple>";

foreach ($users as $u) {
    echo '<option value="'.$u['id'].'">'.htmlspecialchars($u['imie'].' '.$u['nazwisko']).'</option>';
}

echo "</select>
<button type='submit'>Utwórz</button>
</form></body></html>";
?>