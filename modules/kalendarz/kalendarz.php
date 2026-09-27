<?php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalendarz</title>
<style>
body{background:#1a1c2c;color:white;margin:0;font-family:Segoe UI,sans-serif}
a{color:#aaa;text-decoration:none}a:hover{color:#fff}
.back{margin:18px}
.shell{min-height:calc(100vh - 74px);display:flex;justify-content:center;align-items:flex-start;padding:28px 16px 96px}
.card{width:min(820px,100%);background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.25)}
.top{display:flex;justify-content:space-between;gap:16px;align-items:center;border-bottom:1px solid rgba(255,255,255,.12);padding-bottom:18px;margin-bottom:18px}
h1{margin:0;color:#66ccff;font-size:30px}#calendar-container{width:100%}.month-nav{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;color:#dbeafe;font-weight:700}
.month-nav button{width:38px;height:34px}.days{display:grid;grid-template-columns:repeat(7,1fr);gap:8px}.weekday{font-size:12px;color:#9ca3af;text-align:center;padding:4px}
.day,.empty{min-height:48px;padding:8px;background:#25283a;border:1px solid rgba(255,255,255,.08);border-radius:10px;cursor:pointer;font-size:14px;color:#fff}
.day:hover{background:#343850}.day.selected{outline:2px solid #38bdf8}.empty{cursor:default;opacity:.35}
#event-list{margin-top:18px;text-align:left;font-size:14px;background:rgba(0,0,0,.16);padding:14px;border-radius:12px;border:1px solid rgba(255,255,255,.08)}
.event{padding:10px;border-bottom:1px solid rgba(255,255,255,.08)}.event:last-child{border-bottom:0}
input{padding:10px;margin:5px 0;width:100%;border-radius:9px;border:1px solid rgba(255,255,255,.12);background:#202334;color:white;box-sizing:border-box}
button{padding:9px 12px;margin:4px;border:none;border-radius:9px;background:#3478f6;color:white;cursor:pointer}button:hover{background:#5592ff}
#add-form-container{margin-top:14px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.form-grid .wide{grid-column:1/-1}
@media(max-width:700px){.top,.form-grid{grid-template-columns:1fr;display:grid}.days{gap:5px}.day,.empty{min-height:40px}}
</style>
</head>
<body>
<h5 class="back"><a href="../apki/apki.php">← Powrót</a></h5>
<main class="shell">
<section class="card">
<div class="top">
  <h1>Kalendarz</h1>
  <button id="add-toggle" type="button">+ Dodaj wydarzenie</button>
</div>
<div id="calendar-container"></div>
<h3 id="selected-day">Wybierz dzień</h3>
<div id="add-form-container" style="display:none;">
  <form id="add-event-form">
    <input type="hidden" name="date" id="event-date">
    <div class="form-grid">
      <input type="text" id="event-title" name="title" placeholder="Tytuł" required>
      <input type="text" id="event-type" name="type" placeholder="Typ">
      <input class="wide" type="text" id="event-description" name="description" placeholder="Opis">
      <input type="time" id="event-hour" name="hour">
      <input type="color" id="event-color" name="color" value="#3478f6" required>
    </div>
    <button type="submit">Dodaj</button>
  </form>
</div>
<div id="event-list"></div>
</section>
</main>
<script src="calendar.js"></script>
</body>
</html>
