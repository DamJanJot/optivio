<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}
?>

<!doctype html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Notatnik</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h5 class="mb-4"><a href="../apki/apki.php" style="color: #aaa; text-decoration:none;">← Powrót</a></h5>
<!--  <h4 class="text-center mb-4">Notatnik <img width="28" height="28" src="https://img.icons8.com/arcade/64/document.png" alt="document"/> </h4> -->

  <div id="notes-container"></div>

  <div class="add-note mt-3" onclick="addNote()">
    <i class="fa-solid fa-plus fa-lg me-2"></i>Dodaj nową notatkę
  </div>

  <script>
    function fetchNotes() {
      fetch('notatki.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=fetch'
      })
      .then(res => res.json())
      .then(data => {
        const container = document.getElementById('notes-container');
        container.innerHTML = '';
        data.forEach(note => {
          const el = document.createElement('div');
          el.className = 'note-card';
          el.innerHTML = `
            <textarea rows='3' data-id='${note.id}' ondblclick='this.removeAttribute("readonly")' readonly>${note.content}</textarea>
            <div class='note-actions'>
              <button class='btn btn-sm btn-light' onclick='saveNote(${note.id})'><i class='fa-solid fa-save'></i></button>
              <button class='btn btn-sm btn-danger' onclick='deleteNote(${note.id})'><i class='fa-solid fa-trash'></i></button>
            </div>
          `;
          container.appendChild(el);
        });
      });
    }

    function addNote() {
      fetch('notatki.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&content=Nowa notatka'
      }).then(() => fetchNotes());
    }

    function saveNote(id) {
      const textarea = document.querySelector(`textarea[data-id='${id}']`);
      const content = encodeURIComponent(textarea.value);
      fetch('notatki.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=update&id=' + id + '&content=' + content
      }).then(() => textarea.setAttribute('readonly', true));
    }

    function deleteNote(id) {
      fetch('notatki.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete&id=' + id
      }).then(() => fetchNotes());
    }

    fetchNotes();
  </script>

</body>
</html>
