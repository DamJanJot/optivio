<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../public/login.php');
    exit();
}


require_once __DIR__ . '/../core/env_loader.php'; 


$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

// Pobranie liczby nieprzeczytanych wiadomości
$sql = "SELECT COUNT(*) as nowe_wiadomosci FROM wiadomosci WHERE odbiorca_id = ? AND przeczytana = FALSE";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nowe_wiadomosci = $row['nowe_wiadomosci'];

$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Moje Projekty</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="../public/assets/css/portfolio_app.css" />
</head>
<body>

  <div class="section active" id="apki">
    <iframe src="../modules/apki/apki.php"></iframe>
  </div>
  <div class="section" id="taski">
    <iframe src="../modules/taski/taski.php"></iframe>
  </div>
  <div class="section" id="czat">
    <iframe src="../modules/czat/index.php"></iframe>
  </div>

  <div class="section" id="profil">
    <iframe src="../views/profil.php"></iframe>
  </div>






  <div class="nav-bottom">
    <button class="active" onclick="switchTab('apki', this)"><i class="fa-brands fa-medapps"></i><span>Apki</span></button>



    <button id="taski-btn" onclick="switchTab('taski', this)">
    <i class="fa-solid fa-list-check">  
        <?php if ($nowe_taski > 0): ?>
        <span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger">
            <?php echo $nowe_taski; ?></span>
        <span class="visually-hidden">unread tasks</span>
        <?php endif; ?>    
    </i>
    <span>Taski</span> 
</button>

<script>
function updateUnreadTaski() {
    fetch('../modules/taski/unread_count_taski.php')
      .then(res => res.json())
      .then(data => {
        const btn = document.getElementById('taski-btn');
        let badge = btn.querySelector('span.badge');
        if (data.count > 0) {
          if (!badge) {
            badge = document.createElement('span');
            badge.className = 'position-absolute top-0 translate-middle badge rounded-pill bg-danger';
            badge.textContent = data.count;
            btn.querySelector('i').appendChild(badge);
          } else {
            badge.textContent = data.count;
          }
        } else if (badge) {
          badge.remove();
        }
      });
}

setInterval(updateUnreadTaski, 5000);
updateUnreadTaski();
</script>
    
    
    
    
    
            <button id="chat-btn" onclick="switchTab('czat', this)">
                <i class="fa-solid fa-comment">  
                
                    <?php if ($nowe_wiadomosci > 0): ?>
                    <span class="position-absolute top-0  translate-middle badge rounded-pill bg-danger">
                   <?php echo $nowe_wiadomosci; ?></span>
                        <span class="visually-hidden">unread messages</span>
                    <?php endif; ?>    
                    
                </i>
                
                <span>Czat</span> 
            </button>
        

    

    <button onclick="switchTab('profil', this)">
     <?php
require_once __DIR__ . '../modules/czat/connect.php';
$user_id = $_SESSION['id'];
$stmt = $pdo->prepare("SELECT zdjecie_profilowe FROM uzytkownicy WHERE id = ?");
$stmt->execute([$user_id]);
$avatar_path = $stmt->fetchColumn();
$avatar = (!empty($avatar_path) && file_exists(__DIR__ . '/' . $avatar_path)) 
    ? '/' . $avatar_path 
    : '/uploads/default.png';
?>
<img src="<?= htmlspecialchars($avatar) ?>" class="avatar-icon">


      
    </button>
  </div>


      </div>
    </div>





  <script>
    function switchTab(tab, btn) {
      document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
      document.querySelector(`#${tab}`).classList.add('active');
      document.querySelectorAll('.nav-bottom button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }
  </script>
  
  
  <script>
  function updateUnread() {
    fetch('/czat/unread_count.php')
      .then(res => res.json())
      .then(data => {
        const btn = document.getElementById('chat-btn');
        let badge = btn.querySelector('span.badge');
        if (data.count > 0) {
          if (!badge) {
            badge = document.createElement('span');
            badge.className = 'position-absolute top-0 translate-middle badge rounded-pill bg-danger';
            badge.textContent = data.count;
            btn.querySelector('i').appendChild(badge);
          } else {
            badge.textContent = data.count;
          }
        } else if (badge) {
          badge.remove();
        }
      });
  }
  // Odświeżaj co 5 sekund
  setInterval(updateUnread, 5000);
  // I na start
  updateUnread();
</script>


  
  <script>

const triggerTabList = document.querySelectorAll('#myTab a')
triggerTabList.forEach(triggerEl => {
  const tabTrigger = new bootstrap.Tab(triggerEl)

  triggerEl.addEventListener('click', event => {
    event.preventDefault()
    tabTrigger.show()
  })
})
</script>
  

</body>
</html>

