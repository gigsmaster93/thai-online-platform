(function () {
  'use strict';
  var fields = Array.prototype.slice.call(document.querySelectorAll('.thai-booking-form [data-thai-booking-date]'));
  if (!fields.length) return;

  var refreshers = fields.map(function (field) {
    var formatter = null;
    try {
      formatter = new Intl.DateTimeFormat('en', {
        timeZone: field.getAttribute('data-thai-booking-timezone'),
        calendar: 'gregory', numberingSystem: 'latn',
        year: 'numeric', month: '2-digit', day: '2-digit'
      });
      if (typeof formatter.formatToParts !== 'function') formatter = null;
    } catch (error) { /* Fixed-offset business profiles and older browsers use the offset below. */ }

    function today() {
      var now = new Date();
      if (formatter) {
        var parts = {};
        formatter.formatToParts(now).forEach(function (part) { parts[part.type] = part.value; });
        return parts.year + '-' + parts.month + '-' + parts.day;
      }
      var offset = Number(field.getAttribute('data-thai-booking-offset'));
      if (!Number.isFinite(offset)) return field.min;
      return new Date(now.getTime() + offset * 1000).toISOString().slice(0, 10);
    }
    function refresh() { field.min = today(); }

    ['focus', 'pointerdown', 'input', 'change'].forEach(function (name) {
      field.addEventListener(name, refresh);
    });
    // Refresh before native validation, including keyboard and long-open-page submissions.
    field.form.addEventListener('click', refresh, true);
    field.form.addEventListener('keydown', refresh, true);
    field.form.addEventListener('submit', function (event) {
      refresh();
      if (!field.checkValidity()) {
        event.preventDefault();
        field.reportValidity();
      }
    }, true);
    refresh();
    return refresh;
  });

  function refreshAll() { refreshers.forEach(function (refresh) { refresh(); }); }
  window.addEventListener('pageshow', refreshAll);
  window.addEventListener('focus', refreshAll);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) refreshAll();
  });
  window.setInterval(refreshAll, 60000);
})();
