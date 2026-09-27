<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('todo');

require 'db.php';
$user_id = (int) $_SESSION['id'];

if (isset($_POST['new_list'])) {
    $name = trim((string) $_POST['new_list']);
    $color = (string) $_POST['color'];
    $shared = isset($_POST['shared_with']) ? implode(",", array_map('intval', (array) $_POST['shared_with'])) : "";
    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO lists (user_id, name, color, shared_with) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $color, $shared]);
    }
    header("Location: todo_index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM lists WHERE user_id = ? OR FIND_IN_SET(?, shared_with)");
$stmt->execute([$user_id, $user_id]);
$lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

$users = $pdo->prepare("SELECT id, imie, nazwisko FROM uzytkownicy WHERE id != ? ORDER BY imie, nazwisko");
$users->execute([$user_id]);
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ToDo</title>
<style>
body { background:#1e1e2f; color:white; font-family:Arial, sans-serif; margin:0; min-height:100vh; }
a { color:#fff; text-decoration:none; }
.back { margin:18px; }
.back a { color:#aaa; }
.shell { min-height:calc(100vh - 74px); display:flex; justify-content:center; align-items:flex-start; padding:28px 16px 96px; }
.panel { width:min(760px,100%); background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); border-radius:16px; padding:24px; box-shadow:0 20px 60px rgba(0,0,0,.24); }
.head { display:flex; justify-content:space-between; align-items:center; gap:14px; border-bottom:1px solid rgba(255,255,255,.12); padding-bottom:18px; margin-bottom:18px; }
h1 { font-size:30px; margin:0; color:#66ccff; }
.list { background:rgba(255,255,255,0.055); border:1px solid rgba(255,255,255,.12); padding:15px; margin-bottom:10px; border-radius:12px; display:flex; align-items:center; gap:10px; }
.list:hover { border-color:#0dcaf0; background:rgba(255,255,255,.08); }
.color { width:20px; height:20px; border-radius:50%; flex:0 0 auto; }
.list a { font-size:18px; font-weight:700; }
.list small { color:#aab2c5; }
#form-list { display:none; margin-top:15px; }
button, .show-btn { padding:10px 15px; background:#2563eb; color:white; border:none; cursor:pointer; border-radius:10px; }
button:hover, .show-btn:hover { background:#3b82f6; }
input, select { width:100%; padding:10px; background:#202334; color:white; border:1px solid rgba(255,255,255,.12); border-radius:10px; box-sizing:border-box; margin-bottom:10px; }
.empty { color:#aab2c5; text-align:center; padding:24px; border:1px dashed rgba(255,255,255,.18); border-radius:12px; margin-bottom:14px; }
</style>
<script>
function toggleForm() {
    var form = document.getElementById('form-list');
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}
</script>
</head>
<body>
<h5 class="back"><a href="../apki/apki.php">← Powrót</a></h5>
<main class="shell">
<section class="panel">
<div class="head">
  <h1>Moje listy</h1>
  <button class="show-btn" onclick="toggleForm()">+ Dodaj listę</button>
</div>

<?php if (empty($lists)): ?>
  <div class="empty">Nie masz jeszcze list.</div>
<?php endif; ?>

<?php foreach ($lists as $row): ?>
    <?php $shared = (int) $row['user_id'] !== $user_id ? " (Udostępniona)" : ""; ?>
    <div class="list">
      <div class="color" style="background:<?php echo htmlspecialchars((string) $row['color'], ENT_QUOTES, 'UTF-8'); ?>"></div>
      <a href="list.php?id=<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8'); ?></a>
      <small><?php echo $shared; ?></small>
    </div>
<?php endforeach; ?>

<form method="post" id="form-list">
  <input type="text" name="new_list" placeholder="Nazwa listy" required>
  <select name="color">
    <option value="#f39c12">Pomarańczowy</option>
    <option value="#e74c3c">Czerwony</option>
    <option value="#27ae60">Zielony</option>
    <option value="#2980b9">Niebieski</option>
  </select>
  <select name="shared_with[]" multiple>
    <?php foreach ($users as $u): ?>
      <option value="<?php echo (int) $u['id']; ?>"><?php echo htmlspecialchars($u['imie'] . ' ' . $u['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Utwórz</button>
</form>
</section>
</main>
</body>
</html>
