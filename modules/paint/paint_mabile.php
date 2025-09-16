<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Paint</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { margin: 0; background: #111; color: white; font-family: sans-serif; text-align: center; }
    h1 { margin-top: 10px; color: #66ccff; }
    canvas { background: #fff; display: block; margin: 20px auto; border-radius: 8px; touch-action: none; }
    .tools { margin: 10px; }
    button { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; background: #3478f6; color: white; cursor: pointer; }
    button:hover { background: #5592ff; }
  </style>
</head>
<body>
<!--  <h1>🎨 Rysuj</h1> -->

  <h5 class="mb-4"><a href="../apki/apki.php" style="color: #aaa; text-decoration:none;">← Powrót</a></h5>

  <div class="tools">
    <input type="color" id="colorPicker" value="#000000">
    <input type="range" id="sizePicker" min="1" max="20" value="3">
    <button onclick="clearCanvas()">🧹 Wyczyść</button>
    <button onclick="saveImage()">💾 Zapisz</button>
  </div>
  <canvas id="paintCanvas" width="300" height="480"></canvas>

  <script>
    const canvas = document.getElementById('paintCanvas');
    const ctx = canvas.getContext('2d');
    const colorPicker = document.getElementById('colorPicker');
    const sizePicker = document.getElementById('sizePicker');
    let drawing = false;

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mouseup', stop);
    canvas.addEventListener('mouseout', stop);
    canvas.addEventListener('mousemove', draw);

    canvas.addEventListener('touchstart', e => start(e.touches[0]));
    canvas.addEventListener('touchend', stop);
    canvas.addEventListener('touchmove', e => draw(e.touches[0]));

    function start(e) {
      drawing = true;
      ctx.beginPath();
      ctx.moveTo(e.offsetX || e.clientX - canvas.offsetLeft, e.offsetY || e.clientY - canvas.offsetTop);
    }

    function stop() {
      drawing = false;
    }

    function draw(e) {
      if (!drawing) return;
      ctx.lineWidth = sizePicker.value;
      ctx.strokeStyle = colorPicker.value;
      ctx.lineCap = 'round';
      ctx.lineTo(e.offsetX || e.clientX - canvas.offsetLeft, e.offsetY || e.clientY - canvas.offsetTop);
      ctx.stroke();
    }

    function clearCanvas() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function saveImage() {
      const link = document.createElement('a');
      link.download = 'rysunek.png';
      link.href = canvas.toDataURL();
      link.click();
    }
  </script>
</body>
</html>
