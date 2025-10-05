<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    header('Content-Type: text/html; charset=utf-8');

    exit();
}

require_once 'connect.php';
$me = $_SESSION['id'];
$other = (int)$_GET['id'];

$stmt = $pdo->prepare(
  "SELECT w.*, u.zdjecie_profilowe
   FROM wiadomosci w
   JOIN uzytkownicy u ON u.id = w.nadawca_id
   WHERE (w.nadawca_id = :me AND w.odbiorca_id = :other)
      OR (w.nadawca_id = :other AND w.odbiorca_id = :me)
   ORDER BY w.data_wyslania ASC"
);
$stmt->execute(['me' => $me, 'other' => $other]);
$wiadomosci = $stmt->fetchAll(PDO::FETCH_ASSOC);




?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Rozmowa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style_rozmowa.css">
</head>
<body class="container mt-3">  
    <h5 class="mb-4"><a href="index.php" style="color: #aaa; text-decoration:none;">← Powrót</a></h5>

    <script>
    
    function linkify($text) {
    $text = preg_replace(
        '~(https?://[^\s]+)~i',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
        htmlspecialchars($text)
    );
    return $text;
}
    
    </script>

    <div id="chat-box"></div>

    <form method="post" action="wyslij.php">
        <input type="hidden" name="odbiorca_id" value="<?= $other ?>">
    <!--    <input type="text" id="tresc" name="tresc" placeholder="Napisz wiadomość..." required>
    
         ___________________________________ -->
    
        <div class="input-group mb-3">
            <input type="text" id="tresc" name="tresc" class="form-control" placeholder="Napisz wiadomość..." required>
            <button type="button" class="btn btn-outline-secondary" id="toggle-emojis">😊</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
        </div>

        <div id="emoji-picker" style="display: none; margin-bottom: 10px;">
            <?php
                $emojis = ['😀','😁','😂','🤣','😅','😎','😍','😢','😡','👍','🙏','❤️'];
                foreach ($emojis as $emoji) {
                    echo "<span class='emoji' style='cursor:pointer; font-size: 24px; margin-right: 5px;'>$emoji</span>";
                }
            ?>
        </div>

        <!-- ____________________<button></button>______________________ -->

    
        
        
    </form>




    <!-- ______________________________________________ -->


<script>
  // Funkcja kopiowania tekstu z dowolnego elementu z klasą 'copy-box'
  document.querySelectorAll('.copy-box').forEach(element => {
    element.addEventListener('click', () => {
      const textToCopy = element.getAttribute('data-copy');
      navigator.clipboard.writeText(textToCopy).then(() => {
        alert(`Tekst skopiowany: ${textToCopy}`);
      }).catch(err => {
        console.error('Błąd podczas kopiowania: ', err);
      });
    });
  });
</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

  <script src="js/chat.js"></script>
</body>
</html>