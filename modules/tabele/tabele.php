<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('tabele');

$user_id = (int) $_SESSION['id'];
$db = new SQLite3(__DIR__ . '/tabele.db');
$db->enableExceptions(true);

$db->exec("CREATE TABLE IF NOT EXISTS sheets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    name TEXT,
    columns TEXT,
    content TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

if (isset($_POST['new_sheet'], $_POST['columns'])) {
    $name = trim((string) $_POST['new_sheet']);
    $columns = json_encode(array_map('trim', explode(",", (string) $_POST['columns'])), JSON_UNESCAPED_UNICODE);
    if ($name !== '') {
        $stmt = $db->prepare("INSERT INTO sheets (user_id, name, columns, content) VALUES (:user_id, :name, :columns, '')");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':columns', $columns, SQLITE3_TEXT);
        $stmt->execute();
    }
    header("Location: tabele.php");
    exit();
}

$stmt = $db->prepare("SELECT * FROM sheets WHERE user_id = :user_id ORDER BY updated_at DESC");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Twoje arkusze</title>
<style>
body { background:#1a1c2c; color:#e5e7eb; font-family:Arial, sans-serif; margin:0; min-height:100vh; }
a { color:#66ccff; text-decoration:none; }
a:hover { text-decoration:underline; }
.back { margin:18px; }
.shell { min-height:calc(100vh - 74px); display:flex; justify-content:center; align-items:flex-start; padding:28px 16px 96px; }
.sheet-list { width:min(760px, 100%); background:rgba(255,255,255,.06); padding:24px; border-radius:16px; border:1px solid rgba(255,255,255,.12); box-shadow:0 20px 60px rgba(0,0,0,.24); }
.header { display:flex; align-items:center; justify-content:space-between; gap:16px; border-bottom:1px solid rgba(255,255,255,.12); padding-bottom:18px; margin-bottom:18px; }
h1 { color:#66ccff; margin:0; font-size:30px; }
form { display:grid; grid-template-columns:1fr 1fr auto; gap:10px; align-items:center; margin-bottom:22px; }
input { min-width:0; padding:11px 12px; background:#202334; color:#fff; border:1px solid rgba(255,255,255,.12); border-radius:10px; }
button { padding:11px 14px; background:#2563eb; color:white; border:none; cursor:pointer; border-radius:10px; }
button:hover { background:#3b82f6; }
ul { list-style:none; padding:0; margin:0; display:grid; gap:10px; }
li { padding:14px; border:1px solid rgba(255,255,255,.1); border-radius:12px; background:rgba(255,255,255,.04); }
small { color:#aab2c5; }
.empty { color:#aab2c5; text-align:center; padding:28px; border:1px dashed rgba(255,255,255,.18); border-radius:12px; }
@media (max-width:760px) { form { grid-template-columns:1fr; } .header { align-items:flex-start; flex-direction:column; } }
</style>
</head>
<body>
<h5 class="back"><a href="../apki/apki.php">← Powrót</a></h5>
<main class="shell">
<section class="sheet-list">
<div class="header">
  <h1>Twoje arkusze</h1>
</div>
<form method="post">
  <input type="text" name="new_sheet" placeholder="Nazwa arkusza" required>
  <input type="text" name="columns" placeholder="Nazwy kolumn (np. Imię, Nazwisko, Email)" required>
  <button type="submit">Utwórz arkusz</button>
</form>

<ul>
<?php $hasRows = false; ?>
<?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
  <?php $hasRows = true; ?>
  <li>
    <a href="sheet.php?id=<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8'); ?></a><br>
    <small>Ostatnia modyfikacja: <?php echo htmlspecialchars((string) $row['updated_at'], ENT_QUOTES, 'UTF-8'); ?></small>
  </li>
<?php endwhile; ?>
</ul>
<?php if (!$hasRows): ?>
  <div class="empty">Nie masz jeszcze arkuszy.</div>
<?php endif; ?>
</section>
</main>
</body>
</html>
