<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}
?>

<!doctype html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Live Code Editor – Mobilny UI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
      background: #1e1e2f;
      color: white;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    .tabs {
      display: flex;
      justify-content: space-around;
      margin-bottom: 0.5rem;
    }
    .tab-btn {
      flex: 1;
      padding: 0.5rem;
      border: none;
      background: #1f2130;
      color: #ccc;
      font-weight: 600;
      border-radius: 12px 12px 0 0;
      cursor: pointer;
    }
    .tab-btn.active {
      background: #2f3244;
      color: white;
    }
    textarea {
      width: 100%;
      height: 150px;
      background: #2a2d3b;
      color: white;
      border: none;
      border-radius: 0 0 12px 12px;
      padding: 1rem;
      font-family: monospace;
      resize: none;
    }
    iframe {
      width: 100%;
      height: 200px;
      background: white;
      border: none;
      border-radius: 12px;
    }
  </style>
</head>
<body>
<h6 class="mb-4"><a href="../apki/apki.php" style="color: #aaa; text-decoration:none;">← Powrót</a></h6>


  <div class="tabs">
    <button class="tab-btn active" onclick="switchTab('html')">HTML</button>
    <button class="tab-btn" onclick="switchTab('css')">CSS</button>
    <button class="tab-btn" onclick="switchTab('js')">JS</button>
  </div>

  <textarea id="html" placeholder="Wpisz kod HTML..."><h1>Hello World</h1></textarea>
  <textarea id="css" style="display:none;" placeholder="Wpisz kod CSS...">h1 { color: blue; }</textarea>
  <textarea id="js" style="display:none;" placeholder="Wpisz kod JavaScript...">console.log("Hello");</textarea>

  <iframe id="preview"></iframe>

  <script>
    function switchTab(tab) {
      ['html', 'css', 'js'].forEach(id => {
        document.getElementById(id).style.display = id === tab ? 'block' : 'none';
        document.querySelector(`.tab-btn[onclick*='${id}']`).classList.toggle('active', id === tab);
      });
    }

    const htmlEl = document.getElementById('html');
    const cssEl = document.getElementById('css');
    const jsEl = document.getElementById('js');
    const preview = document.getElementById('preview');

    function updatePreview() {
      const html = htmlEl.value;
      const css = `<style>${cssEl.value}</style>`;
      const js = `<script>${jsEl.value}<\/script>`;
      const content = html + css + js;
      preview.srcdoc = content;
    }

    htmlEl.addEventListener('input', updatePreview);
    cssEl.addEventListener('input', updatePreview);
    jsEl.addEventListener('input', updatePreview);

    updatePreview();
  </script>

</body>
</html>
