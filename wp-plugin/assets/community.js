document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.thai-forum .eAttach').forEach(function (attachments) {
    var post = attachments.previousElementSibling;
    if (!post || post.querySelector('img')) return;
    attachments.querySelectorAll('a').forEach(function (link) {
      var url = link.getAttribute('href');
      if (!url) return;
      var clear = document.createElement('div');
      clear.className = 'clr';
      var preview = document.createElement('a');
      preview.href = url;
      preview.className = 'ulightbox';
      preview.target = '_blank';
      preview.title = 'Click to watch full size photo...';
      var image = document.createElement('img');
      image.setAttribute('style', 'margin:0;padding:0;border:0;');
      image.src = url;
      image.setAttribute('align', '');
      preview.appendChild(image);
      post.appendChild(clear);
      post.appendChild(preview);
    });
  });

  document.querySelectorAll('.thai-guestbook .sml1[data-code]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      event.preventDefault();
      var field = document.getElementById('message');
      if (!field) return;
      var code = ' ' + (link.getAttribute('data-code') || '') + ' ';
      var start = typeof field.selectionStart === 'number' ? field.selectionStart : field.value.length;
      var end = typeof field.selectionEnd === 'number' ? field.selectionEnd : start;
      field.value = field.value.slice(0, start) + code + field.value.slice(end);
      field.focus();
      field.selectionStart = field.selectionEnd = start + code.length;
    });
  });
  document.querySelectorAll('.thai-forum .ucoz-forum-post img').forEach(function (image) {
    if ((image.getAttribute('src') || '').indexOf('/.s/sm/') !== -1) {
      image.classList.add('smile');
      return;
    }
    var link = image.closest('a[href]');
    if (link) {
      link.classList.add('ulightbox');
      if (!link.getAttribute('target')) link.setAttribute('target', '_blank');
    }
  });
});
// Forum-only viewer: capture before the shared album handler; leave other galleries intact.
document.addEventListener('click', function (event) {
  var link = event.target.closest('.thai-forum a.ulightbox');
  if (!link || typeof HTMLDialogElement === 'undefined') return;
  event.preventDefault();
  event.stopImmediatePropagation();
  var dialog = document.createElement('dialog');
  dialog.className = 'thai-lightbox-dialog thai-forum-lightbox';
  dialog.setAttribute('aria-label', 'Фотография из сообщения форума');
  var close = document.createElement('button');
  close.type = 'button';
  close.className = 'thai-forum-lightbox-close';
  close.setAttribute('aria-label', 'Закрыть фотографию');
  close.textContent = '×';
  var photo = document.createElement('img');
  photo.src = link.href;
  photo.alt = link.querySelector('img')?.alt || 'Фотография из сообщения форума';
  close.addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('click', function (e) {
    var rect = dialog.getBoundingClientRect();
    if (e.target === dialog && (e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom)) dialog.close();
  });
  dialog.addEventListener('close', function () {
    dialog.remove();
    if (link.isConnected) link.focus({preventScroll: true});
  });
  dialog.append(close, photo);
  document.body.appendChild(dialog);
  dialog.showModal();
  close.focus();
}, true);