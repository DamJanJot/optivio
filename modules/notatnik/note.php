<?php
require_once __DIR__ . '/../../core/access_control.php';
optivio_require_login();
optivio_require_app_access('notatnik');
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
  <link rel="stylesheet" href="style.css?v=20260614-2">
</head>
<body>
  <header class="notes-header">
    <a href="../apki/apki.php" class="back-link">← Powrót</a>
    <h1>Notatnik</h1>
    <button class="add-note" type="button" onclick="addNote()" title="Dodaj nową notatkę">
      <i class="fa-solid fa-plus"></i>
      <span>Dodaj</span>
    </button>
  </header>

  <main id="notes-container"></main>

  <template id="note-template">
    <article class="note-card">
      <textarea rows="4" readonly></textarea>
      <div class="note-footer">
        <time></time>
        <div class="note-actions">
          <button class="pin-note" type="button" title="Przypnij"><i class="fa-solid fa-thumbtack"></i></button>
          <button class="edit-note" type="button" title="Edytuj"><i class="fa-solid fa-pen"></i></button>
          <button class="save-note" type="button" title="Zapisz"><i class="fa-solid fa-floppy-disk"></i></button>
          <button class="delete-note" type="button" title="Usuń"><i class="fa-solid fa-trash"></i></button>
        </div>
      </div>
    </article>
  </template>

  <script>
    function request(action, values = {}) {
      return fetch('notatki.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
        body: new URLSearchParams({action, ...values})
      });
    }

    function formatDate(value) {
      if (!value) return 'Brak informacji o edycji';
      return 'Edytowano: ' + value.replace(' ', ' · ');
    }

    function fetchNotes() {
      request('fetch')
        .then(res => res.json())
        .then(notes => {
          const container = document.getElementById('notes-container');
          const template = document.getElementById('note-template');
          container.innerHTML = '';

          if (notes.length === 0) {
            container.innerHTML = '<div class="empty-notes">Nie masz jeszcze notatek.</div>';
            return;
          }

          notes.forEach(note => {
            const card = template.content.firstElementChild.cloneNode(true);
            const textarea = card.querySelector('textarea');
            const pinButton = card.querySelector('.pin-note');
            textarea.value = note.content;
            card.classList.toggle('is-pinned', Number(note.pinned) === 1);
            pinButton.classList.toggle('active', Number(note.pinned) === 1);
            card.querySelector('time').textContent = formatDate(note.updated_at);

            card.querySelector('.edit-note').addEventListener('click', () => {
              textarea.readOnly = false;
              textarea.focus();
            });
            card.querySelector('.save-note').addEventListener('click', () => {
              request('update', {id: note.id, content: textarea.value}).then(fetchNotes);
            });
            pinButton.addEventListener('click', () => {
              request('pin', {id: note.id}).then(fetchNotes);
            });
            card.querySelector('.delete-note').addEventListener('click', () => {
              request('delete', {id: note.id}).then(fetchNotes);
            });
            container.appendChild(card);
          });
        });
    }

    function addNote() {
      request('add', {content: 'Nowa notatka'}).then(fetchNotes);
    }

    fetchNotes();
  </script>
</body>
</html>
