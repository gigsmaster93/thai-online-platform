(function () {
'use strict';
var dialog = document.getElementById('foundCheaperDialog');
var container = dialog && dialog.querySelector('[data-thai-recaptcha]');
var trigger = document.getElementById('foundCheaper');
if (!dialog || !container || !trigger) return;
var form = container.closest('form');
var submit = form.querySelector('button[type="submit"]');
var status = form.querySelector('[data-thai-recaptcha-status]');
var shade = document.createElement('div');
shade.className = 'thai-recaptcha-shade';
shade.hidden = true;
shade.setAttribute('aria-hidden', 'true');
document.body.appendChild(dialog);
document.body.appendChild(shade);
dialog.setAttribute('data-thai-recaptcha-dialog', '');
var widget = null, requested = false, timer = null, apiScript = null;

function message(text) {
    status.textContent = text || '';
    status.hidden = !text;
}
function disabled(value) {
    submit.disabled = value;
    submit.setAttribute('aria-disabled', value ? 'true' : 'false');
}
function unavailable() {
    clearTimeout(timer);
    disabled(true);
    message('Проверка не загрузилась. Обновите страницу и попробуйте ещё раз.');
}
function mount() {
    clearTimeout(timer);
    if (!dialog.open || widget !== null) return;
    try {
        widget = window.grecaptcha.render(container, {
            sitekey: container.getAttribute('data-sitekey'),
            size: container.clientWidth >= 304 ? 'normal' : 'compact',
            callback: function (token) { disabled(!token); message(token ? '' : 'Подтвердите, что вы не робот.'); },
            'expired-callback': function () { disabled(true); message('Проверка истекла. Подтвердите ещё раз, что вы не робот.'); },
            'error-callback': unavailable
        });
        message('');
    } catch (error) { unavailable(); }
}
function load() {
    if (window.grecaptcha && typeof window.grecaptcha.render === 'function') { mount(); return; }
    if (requested) return;
    requested = true;
    message('Загрузка проверки…');
    window.thaiFoundCheaperRecaptchaReady = mount;
    apiScript = document.createElement('script');
    apiScript.src = 'https://www.google.com/recaptcha/api.js?onload=thaiFoundCheaperRecaptchaReady&render=explicit&hl=ru';
    apiScript.async = true;
    apiScript.defer = true;
    apiScript.onerror = unavailable;
    timer = window.setTimeout(unavailable, 15000);
    document.head.appendChild(apiScript);
}
function challengeVisible() {
    return Array.prototype.some.call(document.querySelectorAll('iframe[src*="/recaptcha/"][src*="/bframe"]'), function (frame) {
        for (var node = frame; node && node !== document.body; node = node.parentElement) {
            var css = window.getComputedStyle(node);
            if (css.visibility === 'hidden' || css.display === 'none' || css.opacity === '0') return false;
        }
        return frame.getClientRects().length > 0;
    });
}
function close() {
    if (typeof dialog.close === 'function') dialog.close();
    else { dialog.removeAttribute('open'); cleanup(); }
}
function cleanup() {
    shade.hidden = true;
    document.documentElement.classList.remove('thai-recaptcha-dialog-open');
    disabled(true);
    message('');
    if (widget !== null && window.grecaptcha) {
        try { window.grecaptcha.reset(widget); } catch (error) {}
    }
    trigger.focus({preventScroll: true});
}
trigger.addEventListener('click', function (event) {
    event.preventDefault();
    event.stopImmediatePropagation();
    if (dialog.open) return;
    // Google's challenge is appended to body. A modal top layer would hide it.
    if (typeof dialog.show === 'function') dialog.show();
    else dialog.setAttribute('open', '');
    shade.hidden = false;
    document.documentElement.classList.add('thai-recaptcha-dialog-open');
    disabled(true);
    var first = form.querySelector('input:not([type="hidden"]):not([name="website"])');
    if (first) first.focus({preventScroll: true});
    load();
}, true);
shade.addEventListener('click', close);
dialog.addEventListener('close', cleanup);
document.addEventListener('keydown', function (event) {
    if (!dialog.open || challengeVisible()) return;
    if (event.key === 'Escape') { event.preventDefault(); close(); return; }
    if (event.key !== 'Tab') return;
    var controls = Array.prototype.filter.call(dialog.querySelectorAll('a[href],button:not([disabled]),input:not([type="hidden"]):not([disabled]),select:not([disabled]),textarea:not([disabled]),iframe,[tabindex]:not([tabindex="-1"])'), function (element) {
        return element.getClientRects().length && window.getComputedStyle(element).visibility !== 'hidden';
    });
    if (!controls.length) return;
    var first = controls[0], last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
});
form.addEventListener('submit', function (event) {
    var token = widget !== null && window.grecaptcha ? window.grecaptcha.getResponse(widget) : '';
    if (!token) { event.preventDefault(); disabled(true); message('Подтвердите, что вы не робот.'); return; }
    disabled(true);
});
})();
