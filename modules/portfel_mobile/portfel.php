<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

require 'db.php';
$user_id = $_SESSION['id'];
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

if (isset($_POST['amount']) && isset($_POST['description'])) {
    $amount = floatval($_POST['amount']);
    $description = $_POST['description'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $tags = $_POST['tags'];
    $stmt = $pdo->prepare("INSERT INTO portfel (user_id, amount, description, type, category, tags) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $amount, $description, $type, $category, $tags]);
    header("Location: portfel.php?month=$month");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM portfel WHERE id = ? AND user_id = ?")->execute([$id, $user_id]);
    header("Location: portfel.php?month=$month");
    exit();
}

if (isset($_POST['edit_id'])) {
    $stmt = $pdo->prepare("UPDATE portfel SET description = ?, amount = ?, type = ?, category = ?, tags = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['description'], $_POST['amount'], $_POST['type'], $_POST['category'], $_POST['tags'], $_POST['edit_id'], $user_id]);
    header("Location: portfel.php?month=$month");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM portfel WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ? ORDER BY id DESC");
$stmt->execute([$user_id, $month]);
$items = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM portfel WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$user_id, $month]);
$sum = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Portfel Mobile V2</title>
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<style>
body { background: #1e1e2f; color: white; font-family: Arial; margin: 10px; }
h1 { color: #66ccff; font-size: 22px; text-align:center; }
.item { background: #111; padding: 10px; margin-bottom: 8px; border-radius: 10px; }
.income { color: #2ecc71; }
.expense { color: #e74c3c; }
form input, select {
    width: 100%;
    padding: 10px;
    background: #222;
    color: white;
    border: none;
    border-radius: 8px;
    margin-bottom: 8px;
    box-sizing: border-box;
}
input[type="month"] {
    width: 100%;
    padding: 10px;
    background: #222;
    color: white;
    border: none;
    border-radius: 8px;
    margin-bottom: 8px;
    box-sizing: border-box;
}
form button { width: 100%; padding: 10px; background-color: #444; color: white; border: none; cursor: pointer; border-radius: 8px; }
form button:hover { background-color: #555; }
a.delete { color: red; float:right; text-decoration: none; font-size: 14px; }
.editForm { display: none; background: #222; padding: 10px; border-radius: 8px; margin-top: 5px;}
</style>
</head>
<body>
<h5><a href='../apki/apki.php' style='color: #aaa; text-decoration:none;'>← Powrót</a></h5>
<h1><?= $month ?></h1>
<h3>Saldo: <?= number_format($sum, 2) ?> zł</h3>

<form method='get'>Miesiąc:
    <input type='month' name='month' value='<?= $month ?>'>
    <button type='submit'>Pokaż</button>
</form>

<?php
$categories = ['Wpływy'=>0, 'Wydatki'=>0];
foreach ($items as $item):
$class = $item['type'] == 'income' ? "income" : "expense";
?>
<div class='item <?= $class ?>'>
<?= htmlspecialchars($item['description']) ?> - <?= number_format($item['amount'], 2) ?> zł (<?= $item['category'] ?>)
<a class='delete' href='?delete=<?= $item['id'] ?>&month=<?= $month ?>'>Usuń</a>
<button onclick="toggleEdit(<?= $item['id'] ?>)">Edytuj</button>

<div id='editForm_<?= $item['id'] ?>' class='editForm'>
<form method='post'>
<input type='hidden' name='edit_id' value='<?= $item['id'] ?>'>
<input type='text' name='description' value='<?= htmlspecialchars($item['description']) ?>'>
<input type='number' step='0.01' name='amount' value='<?= $item['amount'] ?>'>
<select name='type'><option value='income'<?= $item['type']=='income'?' selected':'' ?>>Wpływ</option><option value='expense'<?= $item['type']=='expense'?' selected':'' ?>>Wydatek</option></select>
<select name='category'><option value='Jedzenie'<?= $item['category']=='Jedzenie'?' selected':'' ?>>Jedzenie</option><option value='Zakupy'<?= $item['category']=='Zakupy'?' selected':'' ?>>Zakupy</option><option value='Rachunki'<?= $item['category']=='Rachunki'?' selected':'' ?>>Rachunki</option><option value='Inne'<?= $item['category']=='Inne'?' selected':'' ?>>Inne</option></select>
<input type='text' name='tags' value='<?= htmlspecialchars($item['tags']) ?>' placeholder='Tagi'>
<button type='submit'>Zapisz</button>
</form>
</div>
</div>
<?php
$categories[$item['type']=='income'?'Wpływy':'Wydatki'] += $item['amount'];
endforeach;
?>

<button onclick="toggleAdd()">➕ Dodaj wpis</button>
<div id='addForm' style='display:none;'>
<form method='post'>
<input type='text' name='description' placeholder='Opis' required>
<input type='number' step='0.01' name='amount' placeholder='Kwota' required>
<select name='type'><option value='income'>Wpływ</option><option value='expense'>Wydatek</option></select>
<select name='category'><option value='Jedzenie'>Jedzenie</option><option value='Zakupy'>Zakupy</option><option value='Rachunki'>Rachunki</option><option value='Inne'>Inne</option></select>
<input type='text' name='tags' placeholder='Tagi (opcjonalnie)'>
<button type='submit'>Dodaj</button>
</form>
</div>

<canvas id='chart'></canvas>
<script>
function toggleEdit(id) {
    var el = document.getElementById('editForm_' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function toggleAdd() {
    var el = document.getElementById('addForm');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

const ctx = document.getElementById('chart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Wpływy', 'Wydatki'],
        datasets: [{
            data: [<?= $categories['Wpływy'] ?>, <?= $categories['Wydatki'] ?>],
            backgroundColor: ['green', 'red']
        }]
    }
});
</script>
</body>
</html>
