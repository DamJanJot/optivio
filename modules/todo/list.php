<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

require 'db.php';
require 'env_loader.php';

$user_id = $_SESSION['id'];
$list_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM lists WHERE id = ?");
$stmt->execute([$list_id]);
$list = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$list) die("Lista nie istnieje.");

$allowed = $list['user_id'] == $user_id || in_array($user_id, explode(",", $list['shared_with']));
if (!$allowed) die("Brak dostępu.");

if (isset($_POST['new_task'])) {
    $task = $_POST['new_task'];
    $remind = $_POST['remind_date'];
    $stmt = $pdo->prepare("SELECT MAX(ordering)+1 FROM tasks WHERE list_id = ?");
    $stmt->execute([$list_id]);
    $order = $stmt->fetchColumn() ?: 1;
    $insert = $pdo->prepare("INSERT INTO tasks (list_id, user_id, name, remind_date, ordering) VALUES (?, ?, ?, ?, ?)");
    $insert->execute([$list_id, $user_id, $task, $remind, $order]);
    header("Location: list.php?id=$list_id");
    exit();
}

if (isset($_GET['toggle'])) {
    $task_id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE tasks SET completed = NOT completed WHERE id = ?")->execute([$task_id]);
    header("Location: list.php?id=$list_id");
    exit();
}

if (isset($_POST['order'])) {
    $order = json_decode($_POST['order'], true);
    foreach ($order as $i => $id) {
        $pdo->prepare("UPDATE tasks SET ordering = ? WHERE id = ?")->execute([$i, $id]);
    }
    exit("OK");
}

// Powiadomienia e-mail (na dzisiaj)
$now = date("Y-m-d");
$notif = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ? AND remind_date LIKE ? AND completed = 0");
$notif->execute([$list_id, "$now%"]);

// Pobierz e-mail właściciela listy
$stmtUser = $pdo->prepare("SELECT email FROM uzytkownicy WHERE id = ?");
$stmtUser->execute([$list['user_id']]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

while ($r = $notif->fetch(PDO::FETCH_ASSOC)) {
    if ($user && $user['email']) {
        sendReminder($user['email'], "Przypomnienie TODO: ".$r['name'], "Masz dzisiaj zadanie do wykonania: ".$r['name']);
    }
}

$tasks = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ? ORDER BY ordering ASC");
$tasks->execute([$list_id]);

?>
<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Lista</title>
<style>
body { background: #000; color: white; font-family: Arial; margin: 20px; }
h1 { font-size: 24px; margin-bottom: 20px; color: <?=htmlspecialchars($list['color'])?>; }
.task { padding: 10px 0; border-bottom: 1px solid #222; cursor: grab; }
.task.completed { color: #555; text-decoration: line-through; }
form input, form input[type=datetime-local] { padding: 10px; background: #222; color: white; border: none; border-radius: 5px; margin-right: 10px; }
form button, .show-btn { padding: 10px 15px; background-color: #444; color: white; border: none; cursor: pointer; border-radius: 5px; }
form button:hover, .show-btn:hover { background-color: #666; }
.back { margin-bottom: 20px; display: inline-block; color: #aaa; text-decoration: none; }
.back:hover { color: white; }
#addForm { display:none; }
</style>
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
</head><body>

<a class='back' href='todo_index.php'>← Powrót do list</a>
<h1><?=htmlspecialchars($list['name'])?></h1>

<div id='tasks'>
<?php while ($row = $tasks->fetch(PDO::FETCH_ASSOC)): ?>
<div class='<?= $row['completed'] ? "task completed" : "task" ?>' data-id='<?= $row['id'] ?>'><a href='?id=<?= $list_id ?>&toggle=<?= $row['id'] ?>'><?= htmlspecialchars($row['name']) ?></a> <?= $row['remind_date'] ? "<small> ({$row['remind_date']})</small>" : "" ?></div>
<?php endwhile; ?>
</div>

<button class='show-btn' onclick="document.getElementById('addForm').style.display='block'">+ Dodaj przypomnienie</button>
<form method='post' id='addForm'>
<input type='text' name='new_task' placeholder='Treść zadania' required>
<input type='datetime-local' name='remind_date'>
<button type='submit'>Dodaj</button>
</form>

<script>
$('#tasks').sortable({
    update: function() {
        var order = [];
        $('#tasks .task, #tasks .completed').each(function() {
            order.push($(this).data('id'));
        });
        $.post('list.php?id=<?= $list_id ?>', {order: JSON.stringify(order)});
    }
});
</script>

</body></html>
