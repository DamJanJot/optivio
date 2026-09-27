<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('tablica');

require 'db.php';

$user_id = (int) $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tresc'])) {
    $tresc = trim((string) $_POST['tresc']);
    if ($tresc !== '') {
        $stmt = $pdo->prepare("INSERT INTO tablica_posty (user_id, tresc) VALUES (?, ?)");
        $stmt->execute([$user_id, $tresc]);
    }
    header("Location: tablica.php");
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM tablica_posty WHERE id = ? AND user_id = ?")->execute([$delete_id, $user_id]);
    header("Location: tablica.php");
    exit();
}

if (isset($_GET['like'])) {
    $like_post = (int) $_GET['like'];
    $exists = $pdo->prepare("SELECT 1 FROM tablica_lajki WHERE user_id = ? AND post_id = ? LIMIT 1");
    $exists->execute([$user_id, $like_post]);
    if ($exists->fetchColumn()) {
        $pdo->prepare("DELETE FROM tablica_lajki WHERE user_id = ? AND post_id = ?")->execute([$user_id, $like_post]);
    } else {
        $pdo->prepare("INSERT IGNORE INTO tablica_lajki (user_id, post_id) VALUES (?, ?)")->execute([$user_id, $like_post]);
    }
    header("Location: tablica.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment']) && !empty($_POST['post_id'])) {
    $comment = trim((string) $_POST['comment']);
    $post_id = (int) $_POST['post_id'];
    if ($comment !== '') {
        $pdo->prepare("INSERT INTO tablica_komentarze (user_id, post_id, komentarz) VALUES (?, ?, ?)")->execute([$user_id, $post_id, $comment]);
    }
    header("Location: tablica.php");
    exit();
}

$stmt = $pdo->prepare("
SELECT p.*, u.imie, u.nazwisko, u.zdjecie_profilowe,
(SELECT COUNT(*) FROM tablica_lajki WHERE post_id = p.id) AS lajki,
EXISTS(SELECT 1 FROM tablica_lajki WHERE post_id = p.id AND user_id = ?) AS liked_by_me
FROM tablica_posty p
JOIN uzytkownicy u ON p.user_id = u.id
ORDER BY p.data_dodania DESC
");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tablica</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h5 class="back"><a href="../apki/apki.php">← Powrót</a></h5>

<main class="feed-shell">
<section class="feed">
<div class="feed-top">
  <h1>Tablica</h1>
  <button class="add-post" onclick="toggleForm()" type="button">+ Dodaj post</button>
</div>

<div id="addForm" class="composer" style="display:none;">
  <form method="post">
    <textarea name="tresc" rows="3" placeholder="Napisz coś..." required></textarea>
    <button type="submit">Dodaj</button>
  </form>
</div>

<?php foreach ($posts as $post): ?>
<?php $postAvatar = optivio_stored_file_url((string) $post['zdjecie_profilowe']); ?>
<article class="post">
  <div class="post-header">
    <img src="<?php echo htmlspecialchars($postAvatar, ENT_QUOTES, 'UTF-8'); ?>" alt="avatar">
    <div>
      <b><?php echo htmlspecialchars(trim($post['imie'] . " " . $post['nazwisko']), ENT_QUOTES, 'UTF-8'); ?></b>
      <time><?php echo htmlspecialchars((string) $post['data_dodania'], ENT_QUOTES, 'UTF-8'); ?></time>
    </div>
    <?php if ((int) $post['user_id'] === $user_id): ?>
      <a class="delete" href="?delete=<?php echo (int) $post['id']; ?>" title="Usuń">Usuń</a>
    <?php endif; ?>
  </div>
  <p><?php echo nl2br(htmlspecialchars((string) $post['tresc'], ENT_QUOTES, 'UTF-8')); ?></p>

  <div class="post-actions">
    <a class="heart <?php echo (int) $post['liked_by_me'] === 1 ? 'liked' : ''; ?>" href="?like=<?php echo (int) $post['id']; ?>" aria-label="Polub">
      <?php echo (int) $post['liked_by_me'] === 1 ? '♥' : '♡'; ?>
    </a>
    <span><?php echo (int) $post['lajki']; ?></span>
  </div>

  <?php
  $comments = $pdo->prepare("SELECT k.*, u.imie, u.nazwisko, u.zdjecie_profilowe FROM tablica_komentarze k JOIN uzytkownicy u ON k.user_id = u.id WHERE k.post_id = ? ORDER BY k.data_dodania ASC");
  $comments->execute([(int) $post['id']]);
  foreach ($comments as $comment):
      $commentAvatar = optivio_stored_file_url((string) $comment['zdjecie_profilowe']);
  ?>
    <div class="comment">
      <img src="<?php echo htmlspecialchars($commentAvatar, ENT_QUOTES, 'UTF-8'); ?>" alt="avatar">
      <div>
        <b><?php echo htmlspecialchars($comment['imie'] . ' ' . $comment['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></b>
        <span><?php echo htmlspecialchars((string) $comment['komentarz'], ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    </div>
  <?php endforeach; ?>

  <form method="post" class="comment-form">
    <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">
    <input type="text" name="comment" placeholder="Dodaj komentarz..." required>
    <button type="submit">Dodaj</button>
  </form>
</article>
<?php endforeach; ?>

<?php if (empty($posts)): ?>
  <div class="empty-feed">Jeszcze nie ma żadnych postów.</div>
<?php endif; ?>
</section>
</main>

<script>
function toggleForm() {
    var form = document.getElementById('addForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

</body>
</html>
