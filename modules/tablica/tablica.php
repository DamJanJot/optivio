<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];

// Dodawanie posta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tresc'])) {
    $tresc = trim($_POST['tresc']);
    if ($tresc != "") {
        $stmt = $pdo->prepare("INSERT INTO tablica_posty (user_id, tresc) VALUES (?, ?)");
        $stmt->execute([$user_id, $tresc]);
        header("Location: tablica.php");
        exit();
    }
}

// Usuwanie posta
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM tablica_posty WHERE id = ? AND user_id = ?")->execute([$delete_id, $user_id]);
    header("Location: tablica.php");
    exit();
}

// Dodawanie lajka
if (isset($_GET['like'])) {
    $like_post = (int)$_GET['like'];
    $pdo->prepare("INSERT IGNORE INTO tablica_lajki (user_id, post_id) VALUES (?, ?)")->execute([$user_id, $like_post]);
    header("Location: tablica.php");
    exit();
}

// Dodawanie komentarza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment']) && !empty($_POST['post_id'])) {
    $comment = trim($_POST['comment']);
    $post_id = (int)$_POST['post_id'];
    $pdo->prepare("INSERT INTO tablica_komentarze (user_id, post_id, komentarz) VALUES (?, ?, ?)")->execute([$user_id, $post_id, $comment]);
    header("Location: tablica.php");
    exit();
}

// Pobranie postów + użytkownicy + lajki + komentarze
$stmt = $pdo->prepare("
SELECT p.*, u.imie, u.nazwisko, u.zdjecie_profilowe,
(SELECT COUNT(*) FROM tablica_lajki WHERE post_id = p.id) AS lajki
FROM tablica_posty p
JOIN uzytkownicy u ON p.user_id = u.id
ORDER BY p.data_dodania DESC
");
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tablica PRO</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h5><a href='../apki/apki.php' style='color: #aaa; text-decoration:none;'>← Powrót</a></h5>
<h1>Tablica</h1>

<!-- Dodawanie posta -->
<button onclick="document.getElementById('addForm').style.display='block'">➕ Dodaj post</button>
<div id="addForm" style="display:none; margin-top: 10px;">
<form method="post">
<textarea name="tresc" rows="3" placeholder="Napisz coś..." required></textarea>
<button type="submit">Dodaj</button>
</form>
</div>

<hr>

<!-- Wyświetlanie postów -->
<?php foreach ($posts as $post): ?>
<?php
$img_path = (!empty($post['zdjecie_profilowe']) && file_exists('../' . $post['zdjecie_profilowe']))
    ? '../' . $post['zdjecie_profilowe']
    : '../uploads/default.png';
?>
<div class="post">
<div class="post-header">
<img src="<?= $img_path ?>" alt="avatar">
<b><?= htmlspecialchars($post['imie'] . " " . $post['nazwisko']) ?></b> - <i><?= $post['data_dodania'] ?></i>
<?php if ($post['user_id'] == $user_id): ?>
<a class="delete" href="?delete=<?= $post['id'] ?>">Usuń</a>
<?php endif; ?>
</div>
<p><?= nl2br(htmlspecialchars($post['tresc'])) ?></p>

<b>Lajki: <?= $post['lajki'] ?></b> <a href="?like=<?= $post['id'] ?>">❤️ Polub</a>

<?php
$comments = $pdo->prepare("SELECT k.*, u.imie, u.nazwisko FROM tablica_komentarze k JOIN uzytkownicy u ON k.user_id = u.id WHERE k.post_id = ? ORDER BY k.data_dodania ASC");
$comments->execute([$post['id']]);
foreach ($comments as $comment):
?>
<div class="comment"><b><?= htmlspecialchars($comment['imie'].' '.$comment['nazwisko']) ?>:</b> <?= htmlspecialchars($comment['komentarz']) ?></div>
<?php endforeach; ?>

<form method="post">
<input type="hidden" name="post_id" value="<?= $post['id'] ?>">
<input type="text" name="comment" placeholder="Dodaj komentarz..." required>
<button type="submit">Dodaj komentarz</button>
</form>

</div>
<?php endforeach; ?>

<script>
function toggleForm() {
    var form = document.getElementById('addForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

</body>
</html>
