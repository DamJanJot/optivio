function fetchMessages(scrollToBottom = false) {
  const id = new URLSearchParams(window.location.search).get('id');
  const box = document.querySelector('#chat-box');

  fetch('pobierz_wiadomosci.php?id=' + encodeURIComponent(id))
    .then((res) => res.text())
    .then((html) => {
      const wasNearBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 120;
      box.innerHTML = html;
      if (scrollToBottom || wasNearBottom) {
        box.scrollTop = box.scrollHeight;
      }
    });
}

function markAsRead() {
  const id = new URLSearchParams(window.location.search).get('id');
  fetch('odczytaj.php?id=' + encodeURIComponent(id));
}

document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('tresc');
  const imageInput = document.getElementById('chat-image-input');
  const imageName = document.getElementById('image-name');

  document.querySelectorAll('.emoji').forEach((emoji) => {
    emoji.addEventListener('click', () => {
      input.value += emoji.textContent;
      input.focus();
      document.getElementById('emoji-picker')?.classList.remove('is-open');
    });
  });

  imageInput?.addEventListener('change', () => {
    imageName.textContent = imageInput.files?.[0]?.name || '';
    document.querySelector('.image-button')?.classList.toggle('has-file', Boolean(imageInput.files?.length));
  });

  fetchMessages(true);
  markAsRead();
  setInterval(() => {
    fetchMessages();
    markAsRead();
  }, 3000);
});
