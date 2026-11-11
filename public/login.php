<?php
require_once __DIR__ . '/../core/access_control.php';

optivio_start_session();

$errorMessage = '';
$installRequest = (string) ($_GET['install'] ?? $_POST['install'] ?? '') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $haslo = (string) ($_POST['haslo'] ?? '');

    if ($email === '' || $haslo === '') {
        $errorMessage = 'Uzupełnij email i hasło.';
    } else {
        $stmt = $pdo->prepare('SELECT id, imie, nazwisko, email, haslo, rola FROM uzytkownicy WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($haslo, (string) ($row['haslo'] ?? ''))) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = (int) $row['id'];
            $_SESSION['imie'] = (string) ($row['imie'] ?? '');
            $_SESSION['nazwisko'] = (string) ($row['nazwisko'] ?? '');
            $_SESSION['email'] = (string) ($row['email'] ?? '');
            $_SESSION['rola'] = optivio_normalize_role((string) ($row['rola'] ?? 'user'));
            optivio_refresh_access_cache();
            optivio_touch_activity();

            if ($installRequest) {
                optivio_redirect('views/nav.php?install=1');
            }

            if (in_array($_SESSION['rola'], ['admin', 'owner'], true) && optivio_has_app_access('admin_panel')) {
                optivio_redirect('public/admin/index.php');
            }

            optivio_redirect('views/nav.php');
        } else {
            $errorMessage = 'Nieprawidłowy email lub hasło.';
        }
    }
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> ಠ_ಠ </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/loginstyle.css">
    <link rel="manifest" href="./manifest.json">
    <meta name="theme-color" content="#0b1220">
    <link rel="stylesheet" href="./assets/css/pwa-runtime.css?v=<?= rawurlencode(PWA_APP_VERSION) ?>">
    <script src="./assets/js/pwa-runtime.js?v=<?= rawurlencode(PWA_APP_VERSION) ?>" data-pwa-runtime data-app-id="optivio" data-app-name="Optivio" data-app-version="<?= htmlspecialchars(PWA_APP_VERSION, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-status-url="pwa-status.php" data-worker-url="../service-worker.js" data-worker-scope="../"></script>

    <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>

    <link rel="website icon" type="png" href="./assets/img/kossmo.png">

    <meta name="description" content="Me website">
    
    
    
        <script>
        function togglePassword() {
            var passField = document.getElementById("password");
            var toggleIcon = document.getElementById("toggleIcon");
            if (passField.type === "password") {
                passField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
    
    

</head>

<body data-bs-spy="scroll" data-bs-target="#navId" class="headzdj">

    <div class="headzdj-shadow"></div>



<section class="phone">



    <div class="box-sdw">
        <h1 class="text-center text-light pt-2 pb-2 mb-0">Logowanie</h1>
    </div>


<div class="container mt-5">

    <div class="col-md-4 offset-md-4">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <?php if ($installRequest): ?><input type="hidden" name="install" value="1"><?php endif; ?>
            <div class="mb-3">
                <h4 class="text-center text-light pb-2 mb-0"><label class="form-label text-light">Email lub nick:</label></h4>
                <input type="text" placeholder="Email lub nick" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
               <h4 class="text-center text-light pb-2 mb-0"> <label class="form-label text-light">Hasło:</label></h4>
                <div class="input-group">
                    <input type="password" id="password" name="haslo" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                        <i id="toggleIcon" class="fa fa-eye"></i>
                    </button>
                </div>
            </div>
            
            
                            <div class="" style="height: 2vh;"> </div>

            
            <button type="submit" class="btn button btn-primary py-2 w-100">Zaloguj się</button>
        </form>
    </div>






                    <div class="form-check py-2 px-4">
                        <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                            <label class="form-check-label" for="defaultCheck1">
                                <h6 class="text-center text-light mb-0">Remember me</h6>
                            </label>
                    </div>






                <div class="" style="height: 3vh;"> </div>

                    <a class="text-center text-light py-4" href="./register.php"> <h6 class="text-center"> New around here? Sign up</h6></a>
                            
                    <a class="text-center text-light" href="#"><h6 class="text-center"> Forgot password?</h6></a>
               
            </div>

    <div class="headzdj"> </div>

<?php if ($errorMessage !== ''): ?>
<p class="glass-info container text-center fixed-top py-3" style="color:red;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>


</section>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
    
    <script src="./public/assets/js/script.js"></script>

   
</body>
</html>
