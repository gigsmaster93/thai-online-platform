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
  document.querySelectorAll('.thai-forum .ucoz-forum-post img').forEach(function (image) {
    if ((image.getAttribute('src') || '').indexOf('/.s/sm/') !== -1) image.classList.add('smile');
  });
});