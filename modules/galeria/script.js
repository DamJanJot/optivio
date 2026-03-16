function toggleForm(id) {
    var form = document.getElementById(id);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleInfo(button) {
    var info = button.nextElementSibling;
    info.style.display = info.style.display === 'none' ? 'block' : 'none';
}

function showPreview(src, info) {
    var modal = document.createElement('div');
    modal.id = 'previewModal';
    modal.innerHTML = '<img src="' + src + '"><span>' + info + '</span>';
    modal.onclick = function() { document.body.removeChild(modal); };
    modal.style.display = 'flex';
    document.body.appendChild(modal);
}
