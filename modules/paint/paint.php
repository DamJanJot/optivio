
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <title>Paint + Notes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body { margin: 0; background: #111; color: white; font-family: sans-serif; overflow: hidden; }
    .wrapper { position: relative; width: 100%; height: 100vh; }
    canvas { background: #fff; display: block; margin: auto; border-radius: 8px; position: absolute; top: 0; left: 0; z-index: 0; }
    .notes-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; pointer-events: none; }
    .note { position: absolute; background: yellow; padding: 10px; border-radius: 6px; cursor: move; width: 120px; pointer-events: auto; }
    .toolbar { position: fixed; bottom: 10px; left: 10px; z-index: 20; }
    .toolbar button { margin-right: 10px; padding: 8px 12px; background: #3478f6; color: white; border: none; border-radius: 5px; cursor: pointer; }
    .toolbar input[type=color], .toolbar input[type=range] { margin-right: 10px; }
  </style>
</head>
<body>
  <div class="wrapper">
    <canvas id="canvas" width="800" height="600"></canvas>
    <div class="notes-layer" id="notesLayer"></div>
    <div class="toolbar">
      <input type="color" id="color" value="#000000" />
      <input type="range" id="size" min="1" max="20" value="4" />
      <button onclick="clearCanvas()">🧹</button>
      <button onclick="saveCanvas()">💾</button>
      <button onclick="addNote()">📝</button>
    </div>
  </div>

  <script>
    const canvas = document.getElementById("canvas");
    const ctx = canvas.getContext("2d");
    let painting = false;

    // === Rysowanie ===
    canvas.addEventListener("mousedown", (e) => {
      painting = true;
      ctx.beginPath();
      ctx.moveTo(e.offsetX, e.offsetY);
    });
    canvas.addEventListener("mouseup", () => painting = false);
    canvas.addEventListener("mousemove", draw);

    function draw(e) {
      if (!painting) return;
      ctx.lineWidth = document.getElementById("size").value;
      ctx.strokeStyle = document.getElementById("color").value;
      ctx.lineCap = "round";
      ctx.lineTo(e.offsetX, e.offsetY);
      ctx.stroke();
    }

    function clearCanvas() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function saveCanvas() {
      const dataURL = canvas.toDataURL('image/png');
      fetch('save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'img=' + encodeURIComponent(dataURL)
      }).then(res => res.text()).then(msg => alert(msg));
    }

    // === Ładowanie rysunku z bazy ===
    fetch('load.php')
      .then(res => res.blob())
      .then(blob => {
        const img = new Image();
        img.onload = () => ctx.drawImage(img, 0, 0);
        img.src = URL.createObjectURL(blob);
      });

    // === Karteczki (notes) ===
    const layer = document.getElementById("notesLayer");

    function addNote() {
      const note = document.createElement("div");
      note.className = "note";
      note.contentEditable = true;
      note.style.left = "100px";
      note.style.top = "100px";
      note.innerText = "Nowa notka";
      makeDraggable(note);
      layer.appendChild(note);
      saveNotes();
    }

    function makeDraggable(el) {
      let offsetX, offsetY;
      el.addEventListener("mousedown", function(e) {
        offsetX = e.offsetX;
        offsetY = e.offsetY;
        function move(ev) {
          el.style.left = (ev.pageX - offsetX) + "px";
          el.style.top = (ev.pageY - offsetY) + "px";
        }
        function up() {
          document.removeEventListener("mousemove", move);
          document.removeEventListener("mouseup", up);
          saveNotes();
        }
        document.addEventListener("mousemove", move);
        document.addEventListener("mouseup", up);
      });
    }

    function saveNotes() {
      const notes = [...document.querySelectorAll(".note")].map(n => ({
        left: n.style.left,
        top: n.style.top,
        text: n.innerText
      }));
      localStorage.setItem("paint_notes", JSON.stringify(notes));
    }

    function loadNotes() {
      const data = JSON.parse(localStorage.getItem("paint_notes") || "[]");
      data.forEach(n => {
        const note = document.createElement("div");
        note.className = "note";
        note.contentEditable = true;
        note.style.left = n.left;
        note.style.top = n.top;
        note.innerText = n.text;
        makeDraggable(note);
        layer.appendChild(note);
      });
    }

    loadNotes();
  </script>
</body>
</html>
