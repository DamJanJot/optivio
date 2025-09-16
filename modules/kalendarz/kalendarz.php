<?php require_once __DIR__ . '/../core/env_loader.php'; ?>
<?php session_start(); if (!isset($_SESSION['loggedin'])) { header('Location: ../login.php'); exit(); } ?>
<!DOCTYPE html><html lang="pl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalendarz</title><style>body{background:#0f0f13;color:white;padding:10px;text-align:center;font-family:sans-serif}
#calendar-container{max-width:260px;margin:auto}.month-nav{display:flex;justify-content:space-between;margin-bottom:10px;font-size:14px;color:#aaa}
.days{display:grid;grid-template-columns:repeat(7,1fr);gap:5px}.day,.empty{padding:6px;background:#2a2a2f;border-radius:8px;cursor:pointer;font-size:12px}
.day:hover{background:#444}#event-list{margin-top:15px;text-align:left;font-size:13px;background:#1d1d21;padding:10px;border-radius:10px}
input, select{padding:6px;margin:4px;width:90%;border-radius:6px;border:none;background:#222;color:white}
button{padding:6px 10px;margin:5px;border:none;border-radius:6px;background:#3478f6;color:white;cursor:pointer}button:hover{background:#5592ff}
h5 a{color:#888;text-decoration:none}h5 a:hover{color:#ccc}
</style></head><body>  <h5 class="mb-4"><a href="../apki/apki.php" style="color: #aaa; text-decoration:none;">← Powrót</a></h5>
<div id="calendar-container"></div><h3 id="selected-day">Wybierz dzień</h3>
<button id="add-toggle">➕ Dodaj wydarzenie</button>
<div id="add-form-container" style="display:none;">
<form id="add-event-form">
<input type="hidden" name="date" id="event-date"><input type="text" id="event-title" name="title" placeholder="Tytuł" required>
<input type="text" id="event-description" name="description" placeholder="Opis" required>
<input type="text" id="event-type" name="type" placeholder="Typ" required>
<input type="text" id="event-hour" name="hour" placeholder="Godzina (hh:mm)" required>
<input type="color" id="event-color" name="color" value="#3478f6" required>
<button type="submit">Dodaj</button></form></div>
<div id="event-list"></div><script src="calendar.js"></script></body></html>