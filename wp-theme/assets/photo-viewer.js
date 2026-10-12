(function () {
'use strict';
var active = null;
var icons = {
    zoom: '<path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/>',
    page: '<path d="M14 4h6v6M20 4l-9 9M10 5H4v15h15v-6"/>',
    prev: '<path d="M15 4l-8 8 8 8"/>',
    next: '<path d="M9 4l8 8-8 8"/>'
};
function icon(name) { return '<svg viewBox="0 0 24 24" aria-hidden="true">' + icons[name] + '</svg>'; }
function button(name, label, className) {
    var control = document.createElement('button');
    control.type = 'button'; control.className = className; control.title = label; control.setAttribute('aria-label', label);
    if (name) control.innerHTML = icon(name);
    return control;
}
function localUrl(value) {
    if (typeof value !== 'string' || value.trim() === '') return '';
    try { var url = new URL(value, location.href); return url.origin === location.origin && ['http:', 'https:'].indexOf(url.protocol) >= 0 ? url.href : ''; }
    catch (error) { return ''; }
}
function open(link) {
    if (active) return;
    var links = Array.prototype.slice.call(document.querySelectorAll('.thai-gallery a.thai-lightbox'));
    var index = links.indexOf(link), opener = link;
    var dialog = document.createElement('dialog');
    dialog.className = 'thai-photo-viewer'; dialog.setAttribute('aria-label', 'Просмотр фотографии');
    var toolbar = document.createElement('div'); toolbar.className = 'thai-photo-viewer-toolbar';
    var zoom = button('zoom', 'Показать исходный размер', 'thai-photo-viewer-zoom'); zoom.setAttribute('aria-pressed', 'false');
    var detail = document.createElement('a'); detail.innerHTML = icon('page'); detail.title = 'Перейти на страницу фотографии'; detail.setAttribute('aria-label', detail.title); detail.className = 'thai-photo-viewer-detail';
    var close = button(null, 'Закрыть фотографию', 'thai-photo-viewer-close'); close.textContent = '×';
    toolbar.append(zoom, detail, close);
    var frame = document.createElement('div'); frame.className = 'thai-photo-viewer-frame';
    var image = document.createElement('img'); image.className = 'thai-photo-viewer-image'; image.alt = '';
    var status = document.createElement('p'); status.className = 'thai-photo-viewer-status'; status.setAttribute('role', 'status'); status.hidden = true;
    frame.append(image, status);
    var footer = document.createElement('a'); footer.className = 'thai-photo-viewer-footer';
    var prev = button('prev', 'Предыдущая фотография', 'thai-photo-viewer-prev');
    var next = button('next', 'Следующая фотография', 'thai-photo-viewer-next');
    prev.hidden = next.hidden = links.length < 2;
    dialog.append(toolbar, frame, prev, next, footer);
    function resetZoom() {
        dialog.classList.remove('thai-photo-viewer-expanded'); zoom.setAttribute('aria-pressed', 'false'); zoom.title = 'Показать исходный размер'; zoom.setAttribute('aria-label', zoom.title);
        frame.scrollTop = frame.scrollLeft = 0;
    }
    function render() {
        var current = links[index] || link;
        resetZoom(); status.hidden = true; image.hidden = false;
        image.alt = current.getAttribute('data-alt') || current.getAttribute('data-title') || current.querySelector('img')?.alt || 'Фотография';
        image.src = current.href;
        var photoUrl = localUrl(current.getAttribute('data-url') || '');
        detail.hidden = !photoUrl; if (photoUrl) detail.href = photoUrl; else detail.removeAttribute('href');
        var bookingUrl = localUrl(current.getAttribute('data-booking-url') || '');
        if (bookingUrl && current.getAttribute('data-booking-label')) {
            footer.href = bookingUrl; footer.textContent = current.getAttribute('data-booking-label'); footer.hidden = false;
        } else if (photoUrl) {
            footer.href = photoUrl; footer.textContent = 'Перейти на страницу фотографии'; footer.hidden = false;
        } else { footer.removeAttribute('href'); footer.hidden = true; }
    }
    function move(delta) { index = (index + delta + links.length) % links.length; render(); }
    function key(event) {
        if (event.key === 'ArrowLeft' && links.length > 1) { event.preventDefault(); move(-1); }
        if (event.key === 'ArrowRight' && links.length > 1) { event.preventDefault(); move(1); }
    }
    zoom.addEventListener('click', function () {
        var expanded = dialog.classList.toggle('thai-photo-viewer-expanded');
        zoom.setAttribute('aria-pressed', expanded ? 'true' : 'false');
        zoom.title = expanded ? 'Вписать в экран' : 'Показать исходный размер'; zoom.setAttribute('aria-label', zoom.title);
    });
    close.addEventListener('click', function () { dialog.close(); });
    prev.addEventListener('click', function () { move(-1); }); next.addEventListener('click', function () { move(1); });
    image.addEventListener('error', function () { image.hidden = true; status.textContent = 'Не удалось загрузить фотографию. Попробуйте открыть её страницу.'; status.hidden = false; });
    image.addEventListener('load', function () { image.hidden = false; status.hidden = true; });
    dialog.addEventListener('click', function (event) {
        if (event.target !== dialog) return;
        var box = dialog.getBoundingClientRect();
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
    });
    dialog.addEventListener('close', function () {
        document.removeEventListener('keydown', key); dialog.remove(); active = null;
        if (opener.isConnected) opener.focus({ preventScroll: true });
    });
    active = dialog; render(); document.body.appendChild(dialog);
    document.addEventListener('keydown', key); dialog.showModal(); close.focus({ preventScroll: true });
}
document.addEventListener('click', function (event) {
    var link = event.target.closest('.thai-gallery a.thai-lightbox');
    if (!link || typeof HTMLDialogElement === 'undefined') return;
    event.preventDefault(); event.stopImmediatePropagation(); open(link);
}, true);
})();
