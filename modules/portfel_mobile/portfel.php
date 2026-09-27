<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('portfel');

require 'db.php';
$user_id = (int) $_SESSION['id'];
$month = isset($_GET['month']) ? (string) $_GET['month'] : date('Y-m');

if (isset($_POST['amount'], $_POST['description'])) {
    $stmt = $pdo->prepare("INSERT INTO portfel (user_id, amount, description, type, category, tags) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        (float) $_POST['amount'],
        trim((string) $_POST['description']),
        (string) $_POST['type'],
        (string) $_POST['category'],
        trim((string) ($_POST['tags'] ?? '')),
    ]);
    header("Location: portfel.php?month=$month");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM portfel WHERE id = ? AND user_id = ?")->execute([$id, $user_id]);
    header("Location: portfel.php?month=$month");
    exit();
}

if (isset($_POST['edit_id'])) {
    $stmt = $pdo->prepare("UPDATE portfel SET description = ?, amount = ?, type = ?, category = ?, tags = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([
        trim((string) $_POST['description']),
        (float) $_POST['amount'],
        (string) $_POST['type'],
        (string) $_POST['category'],
        trim((string) ($_POST['tags'] ?? '')),
        (int) $_POST['edit_id'],
        $user_id,
    ]);
    header("Location: portfel.php?month=$month");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM portfel WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ? ORDER BY id DESC");
$stmt->execute([$user_id, $month]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM portfel WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$user_id, $month]);
$sum = (float) $stmt->fetchColumn();

$categories = ['Wpływy' => 0.0, 'Wydatki' => 0.0];
foreach ($items as $item) {
    $categories[$item['type'] === 'income' ? 'Wpływy' : 'Wydatki'] += (float) $item['amount'];
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portfel</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { background:#1e1e2f; color:white; font-family:Arial, sans-serif; margin:0; min-height:100vh; padding:18px 16px 96px; }
a { color:inherit; text-decoration:none; }
.back a { color:#aaa; }
.wallet { width:min(760px,100%); margin:24px auto 0; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); border-radius:16px; padding:22px; box-shadow:0 20px 60px rgba(0,0,0,.24); }
h1 { color:#66ccff; font-size:30px; text-align:center; margin:0 0 8px; }
h3 { text-align:center; margin:0 0 20px; }
.item { background:rgba(0,0,0,.22); padding:12px; margin-bottom:10px; border-radius:12px; border:1px solid rgba(255,255,255,.08); }
.item-row { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; }
.income { color:#2ecc71; }
.expense { color:#f87171; }
input, select { width:100%; padding:10px; background:#202334; color:white; border:1px solid rgba(255,255,255,.12); border-radius:10px; margin-bottom:8px; box-sizing:border-box; }
button { width:100%; padding:10px; background:#2563eb; color:white; border:none; cursor:pointer; border-radius:10px; margin-top:6px; }
button:hover { background:#3b82f6; }
a.delete { color:#fb7185; font-size:14px; }
.editForm { display:none; background:rgba(0,0,0,.18); padding:10px; border-radius:10px; margin-top:10px; }
.month-form { margin-bottom:18px; }
.empty { text-align:center; color:#aab2c5; border:1px dashed rgba(255,255,255,.18); border-radius:12px; padding:22px; margin-bottom:14px; }
canvas { margin-top:18px; max-height:280px; }
</style>
</head>
<body>
<h5 class="back"><a href="../apki/apki.php">← Powrót</a></h5>
<main class="wallet">
<h1><?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?></h1>
<h3>Saldo: <?= number_format($sum, 2) ?> zł</h3>

<form method="get" class="month-form">
    <label>Miesiąc</label>
    <input type="month" name="month" value="<?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit">Pokaż</button>
</form>

<?php if (empty($items)): ?>
  <div class="empty">Brak wpisów w tym miesiącu.</div>
<?php endif; ?>

<?php foreach ($items as $item): ?>
<?php $class = $item['type'] === 'income' ? "income" : "expense"; ?>
<div class="item <?= $class ?>">
  <div class="item-row">
    <div>
      <strong><?= htmlspecialchars((string) $item['description'], ENT_QUOTES, 'UTF-8') ?></strong><br>
      <?= number_format((float) $item['amount'], 2) ?> zł · <?= htmlspecialchars((string) $item['category'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <a class="delete" href="?delete=<?= (int) $item['id'] ?>&month=<?= urlencode($month) ?>">Usuń</a>
  </div>
  <button onclick="toggleEdit(<?= (int) $item['id'] ?>)">Edytuj</button>
  <div id="editForm_<?= (int) $item['id'] ?>" class="editForm">
    <form method="post">
      <input type="hidden" name="edit_id" value="<?= (int) $item['id'] ?>">
      <input type="text" name="description" value="<?= htmlspecialchars((string) $item['description'], ENT_QUOTES, 'UTF-8') ?>">
      <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars((string) $item['amount'], ENT_QUOTES, 'UTF-8') ?>">
      <select name="type"><option value="income"<?= $item['type'] === 'income' ? ' selected' : '' ?>>Wpływ</option><option value="expense"<?= $item['type'] === 'expense' ? ' selected' : '' ?>>Wydatek</option></select>
      <select name="category"><option value="Jedzenie"<?= $item['category'] === 'Jedzenie' ? ' selected' : '' ?>>Jedzenie</option><option value="Zakupy"<?= $item['category'] === 'Zakupy' ? ' selected' : '' ?>>Zakupy</option><option value="Rachunki"<?= $item['category'] === 'Rachunki' ? ' selected' : '' ?>>Rachunki</option><option value="Inne"<?= $item['category'] === 'Inne' ? ' selected' : '' ?>>Inne</option></select>
      <input type="text" name="tags" value="<?= htmlspecialchars((string) $item['tags'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Tagi">
      <button type="submit">Zapisz</button>
    </form>
  </div>
</div>
<?php endforeach; ?>

<button onclick="toggleAdd()">+ Dodaj wpis</button>
<div id="addForm" style="display:none;">
  <form method="post">
    <input type="text" name="description" placeholder="Opis" required>
    <input type="number" step="0.01" name="amount" placeholder="Kwota" required>
    <select name="type"><option value="income">Wpływ</option><option value="expense">Wydatek</option></select>
    <select name="category"><option value="Jedzenie">Jedzenie</option><option value="Zakupy">Zakupy</option><option value="Rachunki">Rachunki</option><option value="Inne">Inne</option></select>
    <input type="text" name="tags" placeholder="Tagi (opcjonalnie)">
    <button type="submit">Dodaj</button>
  </form>
</div>

<canvas id="chart"></canvas>
</main>
<script>
function toggleEdit(id) {
    var el = document.getElementById('editForm_' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleAdd() {
    var el = document.getElementById('addForm');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
new Chart(document.getElementById('chart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Wpływy', 'Wydatki'],
        datasets: [{ data: [<?= $categories['Wpływy'] ?>, <?= $categories['Wydatki'] ?>], backgroundColor: ['#22c55e', '#ef4444'] }]
    },
    options: { plugins: { legend: { labels: { color: '#fff' } } } }
});
</script>
</body>
</html>
