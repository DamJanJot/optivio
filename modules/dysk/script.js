document.addEventListener("DOMContentLoaded", () => {
  // Elementy DOM
  const folderContent   = document.getElementById('folder-content');
  const pathText        = document.getElementById('path-text');
  const editor          = document.getElementById('editor');
  const saveFileButton  = document.getElementById('save-file');
  const uploadForm      = document.getElementById('upload-form');
  const createForm      = document.getElementById('create-form');
  const showUploadBtn   = document.getElementById('show-upload-form');
  const showCreateBtn   = document.getElementById('show-create-form');
  const goBackButton    = document.getElementById('go-back');
  const fileEditorDiv   = document.getElementById('file-editor');

  // Stos historii ścieżek
  let history = [''];

  // Aktualizuje tekst ścieżki i widoczność strzałki
  function updatePathText() {
    const current = history[history.length - 1];
    pathText.textContent = current === '' ? ' /' : current;
    if (history.length > 1 && current !== '') {
      goBackButton.classList.remove('d-none');
    } else {
      goBackButton.classList.add('d-none');
    }
  }

  // Ukrywa wszystkie panele: upload, create, edytor
  function hideAllPanels() {
    uploadForm.classList.add('d-none');
    createForm.classList.add('d-none');
    fileEditorDiv.classList.add('d-none');
  }

  // Ładuje zawartość folderu
  function loadFolder(path = '') {
    hideAllPanels();
    const last = history[history.length - 1];
    if (path !== last) {
      history.push(path);
    }
    updatePathText();
    fetch(`file_manager.php?action=list&path=${encodeURIComponent(path)}`)
      .then(res => res.json())
      .then(data => renderItems(data));
  }

  // Rysuje foldery/pliki z ikonami
  function renderItems(items) {
    folderContent.innerHTML = '';
    items.forEach(item => {
      const div = document.createElement('div');
      const iconHTML = item.type === 'folder'
        ? '<img width="24" height="24" src="https://img.icons8.com/arcade/64/folder-invoices.png" alt="folder-invoices"/>'
        : '<img width="24" height="24" src="https://img.icons8.com/arcade/64/file.png" alt="file"/>';
      div.innerHTML = iconHTML + item.name;
      div.className = `py-1 border rounded ${item.type === 'folder' ? 'bg-secondary' : 'bg-dark'} text-white mb-2`;
      div.style.cursor = 'pointer';
      div.addEventListener('click', () => {
        if (item.type === 'folder') {
          loadFolder(item.path);
        } else {
          fetchFileContent(item.path);
        }
      });
      folderContent.appendChild(div);
    });
  }

  // Odczyt pliku do edytora
  function fetchFileContent(path) {
    hideAllPanels();
    fetch(`file_manager.php?action=read&path=${encodeURIComponent(path)}`)
      .then(res => res.text())
      .then(content => {
        editor.value = content;
        editor.dataset.filePath = path;
        fileEditorDiv.classList.remove('d-none');
      });
  }

  // Zapis edytowanego pliku
  saveFileButton.addEventListener('click', () => {
    const path = editor.dataset.filePath;
    const content = editor.value;
    fetch('file_manager.php?action=save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ path, content })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      hideAllPanels();
      const current = history[history.length - 1];
      fetch(`file_manager.php?action=list&path=${encodeURIComponent(current)}`)
        .then(res => res.json())
        .then(d => renderItems(d));
    });
  });

  // Upload pliku
  uploadForm.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(uploadForm);
    const current = history[history.length - 1];
    formData.append('path', current);
    fetch('file_manager.php?action=upload', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        hideAllPanels();
        fetch(`file_manager.php?action=list&path=${encodeURIComponent(current)}`)
          .then(res => res.json())
          .then(d => renderItems(d));
      });
  });

  // Tworzenie nowego folderu/pliku
  createForm.addEventListener('submit', e => {
    e.preventDefault();
    const name = document.getElementById('new-name').value;
    const type = document.getElementById('type-select').value;
    const current = history[history.length - 1];
    fetch('file_manager.php?action=create', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ path: current, name, type })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      hideAllPanels();
      fetch(`file_manager.php?action=list&path=${encodeURIComponent(current)}`)
        .then(res => res.json())
        .then(d => renderItems(d));
    });
  });

  // Powrót do folderu nadrzędnego
  goBackButton.addEventListener('click', () => {
    if (history.length > 1) {
      history.pop();
      const parent = history[history.length - 1];
      hideAllPanels();
      updatePathText();
      fetch(`file_manager.php?action=list&path=${encodeURIComponent(parent)}`)
        .then(res => res.json())
        .then(data => renderItems(data));
    }
  });

  // Przełączanie widoczności formularzy
  showUploadBtn.addEventListener('click', e => {
    e.preventDefault();
    uploadForm.classList.toggle('d-none');
    createForm.classList.add('d-none');
  });
  showCreateBtn.addEventListener('click', e => {
    e.preventDefault();
    createForm.classList.toggle('d-none');
    uploadForm.classList.add('d-none');
  });

  // Start: tylko render, bez zapisu w historii
  updatePathText();
  fetch(`file_manager.php?action=list&path=`)
    .then(res => res.json())
    .then(data => renderItems(data));
});
