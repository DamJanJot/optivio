<?php
session_start();
require_once __DIR__ . '/../core/env_loader.php'; 


$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $haslo = $_POST['haslo'];

    $sql = "SELECT id, imie, nazwisko, haslo, rola FROM uzytkownicy WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($haslo, $row['haslo'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['imie'] = $row['imie'];
            $_SESSION['nazwisko'] = $row['nazwisko'];
            $_SESSION['rola'] = $row['rola'];

            if ($row['rola'] === 'admin') {
                header("Location: pulpit_admin.php?id=" . $row['id']);
            } else {
                header("Location: views/nav.php?id=" . $row['id']);
            }
            exit();
        } else {
            echo "<br><br><p class='glass-info container text-center fixed-top py-3' style='color:red;'>Błędne hasło</p>";
        }
    } else {
        echo "<br><br><p class='glass-info container text-center fixed-top py-3' style='color:red;'>Nie znaleziono użytkownika z tym adresem email</p>";
    }

    $stmt->close();
}

$conn->close();
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

    <link rel="stylesheet" href="./public/assets/css/loginstyle.css">

    <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>

    <link rel="website icon" type="png" href="./img/kossmo.png">

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




</section>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
    
    <script src="./public/assets/js/script.js"></script>

   
</body>
</html>    	