<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];
$db = new SQLite3('tabele.db');

// Tworzenie tabeli bazy danych
$db->exec("CREATE TABLE IF NOT EXISTS sheets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    name TEXT,
    columns TEXT,
    content TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

if (isset($_POST['new_sheet']) && isset($_POST['columns'])) {
    $name = $_POST['new_sheet'];
    $columns = json_encode(array_map('trim', explode(",", $_POST['columns'])));
    $db->exec("INSERT INTO sheets (user_id, name, columns, content) VALUES ($user_id, '$name', '$columns', '')");
    header("Location: tabele.php");
    exit();
}

$result = $db->query("SELECT * FROM sheets WHERE user_id = $user_id ORDER BY updated_at DESC");

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Moje arkusze</title>
<style>
body { background: #1a1c2c; color: #ccc; font-family: Arial; margin: 20px; }
h1 { color: #66ccff; }
a { color: #66ccff; text-decoration: none; }
a:hover { text-decoration: underline; }
.sheet-list { background: #2a2c3a; padding: 20px; border-radius: 10px; max-width: 600px; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
.sheet-list ul { list-style: none; padding: 0; }
.sheet-list li { padding: 10px 0; border-bottom: 1px solid #444; }
.sheet-list li:last-child { border-bottom: none; }
button { padding: 8px 12px; background-color: #444; color: white; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px; }
button:hover { background-color: #666; }
</style>
</head><body>";

echo "<h5><a href='../apki/apki.php'>← Powrót</a></h5>";
echo "<h1>Twoje arkusze</h1>";
echo "<div class='sheet-list'>";
echo "<form method='post'>
<input type='text' name='new_sheet' placeholder='Nazwa arkusza' required><br><br>
<input type='text' name='columns' placeholder='Nazwy kolumn (np. Imię, Nazwisko, Email)' required><br><br>
<button type='submit'>Utwórz arkusz</button></form>";

echo "<ul>";
while ($row = $result->fetchArray()) {
    echo "<li><a href='sheet.php?id={$row['id']}'>" . htmlspecialchars($row['name']) . "</a><br><small>Ostatnia modyfikacja: {$row['updated_at']}</small></li>";
}
echo "</ul></div></body></html>";
?>
