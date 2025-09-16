function fetchMessages() {
  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get('id');
  fetch('pobierz_wiadomosci.php?id=' + id)
    .then(res => res.text())
    .then(html => {
      document.querySelector('#chat-box').innerHTML = html;
    });
}

function markAsRead() {
  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get('id');
  fetch('odczytaj.php?id=' + id);
}

setInterval(() => {
  fetchMessages();
  markAsRead();
}, 3000);

window.onload = () => {
  fetchMessages();
  markAsRead();
};