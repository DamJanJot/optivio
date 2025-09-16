<?php
session_start();
session_destroy();
header("Location: portfolio_app.php");
exit();
?>


