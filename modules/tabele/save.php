<?php
session_start();
if (!isset($_SESSION['loggedin'])) exit();

$user_id = $_SESSION['id'];
$data = json_decode(file_get_contents("php://input"), true);

$id = (int)$data['id'];
$content = json_encode($data['content']);

$db = new SQLite3('tabele.db');
$db->exec("UPDATE sheets SET content = '$content', updated_at = CURRENT_TIMESTAMP WHERE id = $id AND user_id = $user_id");
?>
