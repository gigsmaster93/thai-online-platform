const fs = require('fs');
const path = require('path');
const puppeteer = require('/home/thaionline/.npm/_npx/4b4c857f6efdfb61/node_modules/puppeteer');
const mode = process.argv[2] || 'fixture';
const out = process.argv[3];
if (!out || !['fixture', 'candidate', 'live'].includes(mode)) throw new Error('fixture|candidate|live OUTPUT_DIRECTORY required');
fs.mkdirSync(out, { recursive: true, mode: 0o700 });
const checks = [], pages = [], failures = [];
const asset = path.resolve(__dirname, '../wp-plugin/assets/booking-dates.js');
function check(name, pass, actual) { const r = { name, pass: !!pass, actual }; checks.push(r); if (!pass) failures.push(r); }
function clockSetup() {
  const NativeDate = Date;
  window.__bookingClock = null;
  window.__bookingIntervals = [];
  window.Date = class extends NativeDate {
    constructor(...args) { super(...(args.length ? args : [window.__bookingClock === null ? NativeDate.now() : window.__bookingClock])); }
    static now() { return window.__bookingClock === null ? NativeDate.now() : window.__bookingClock; }
  };
  const originalInterval = window.setInterval;
  window.setInterval = function (callback, ms, ...args) {
    if (ms === 60000) { window.__bookingIntervals.push(callback); return 999; }
    return originalInterval(callback, ms, ...args);
  };
}
async function setDate(page, date) {
  return page.$eval('.thai-booking-form input[name=date]', (field, value) => {
    field.value = value;
    field.dispatchEvent(new Event('input', { bubbles: true }));
    return { min: field.min, value: field.value, valid: field.checkValidity(), underflow: field.validity.rangeUnderflow };
  }, date);
}
(async () => {
  const browser = await puppeteer.launch({
    executablePath: '/home/thaionline/.cache/puppeteer/chrome/linux-153.0.8010.36/chrome-linux64/chrome',
    args: ['--no-sandbox', '--disable-dev-shm-usage'], headless: true
  });
  try {
    if (mode === 'fixture') {
      for (const clientZone of ['America/Los_Angeles', 'Asia/Vladivostok']) {
        const page = await browser.newPage();
        await page.emulateTimezone(clientZone);
        await page.setContent('<form class="thai-booking-form"><input type="date" name="date" min="2026-10-09" required data-thai-booking-date data-thai-booking-timezone="Asia/Bangkok" data-thai-booking-offset="25200"><button>Заказать</button></form>');
        await page.evaluate(clockSetup);
        await page.evaluate(() => { window.__bookingClock = Date.parse('2026-10-10T16:59:59Z'); });
        await page.addScriptTag({ path: asset });
        const prefix = clientZone + ': ';
        check(prefix + 'stale cached min refreshed in business timezone', await page.$eval('input', f => f.min) === '2026-10-10');
        let r = await setDate(page, '2026-10-09'); check(prefix + 'past is invalid', !r.valid && r.underflow, r);
        r = await setDate(page, '2026-10-10'); check(prefix + 'today allowed', r.valid, r);
        r = await setDate(page, '2026-10-11'); check(prefix + 'tomorrow allowed', r.valid, r);
        await page.evaluate(() => { window.__bookingClock = Date.parse('2026-10-10T17:00:00Z'); window.dispatchEvent(new Event('pageshow')); });
        check(prefix + 'business midnight updates min', await page.$eval('input', f => f.min) === '2026-10-11');
        r = await setDate(page, '2026-10-10'); check(prefix + 'newly past date invalid', !r.valid && r.underflow, r);
        r = await setDate(page, '2026-10-11'); check(prefix + 'new business today allowed', r.valid, r);
        check(prefix + 'submit event cannot bypass past date', await page.$eval('form', form => {
          form.querySelector('input').value = '2026-10-10';
          return !form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        }));
        await page.evaluate(() => { window.__bookingClock = Date.parse('2026-12-31T17:00:00Z'); document.querySelector('form').dispatchEvent(new MouseEvent('click', { bubbles: true })); });
        check(prefix + 'pre-validation click updates year rollover', await page.$eval('input', f => f.min) === '2027-01-01');
        await page.evaluate(() => { window.__bookingClock = Date.parse('2027-01-01T17:00:00Z'); document.querySelector('form').dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true })); });
        check(prefix + 'pre-validation Enter updates day', await page.$eval('input', f => f.min) === '2027-01-02');
        await page.evaluate(() => { window.__bookingClock = Date.parse('2027-01-02T17:00:00Z'); window.__bookingIntervals.forEach(f => f()); });
        check(prefix + 'long-open page timer updates day', await page.$eval('input', f => f.min) === '2027-01-03');
        await page.close();
      }
      const fallback = await browser.newPage();
      await fallback.setContent('<form class="thai-booking-form"><input type="date" name="date" min="2026-10-09" required data-thai-booking-date data-thai-booking-timezone="+07:00" data-thai-booking-offset="25200"></form>');
      await fallback.evaluate(clockSetup);
      await fallback.evaluate(() => { window.__bookingClock = Date.parse('2026-10-10T17:00:00Z'); });
      await fallback.addScriptTag({ path: asset });
      check('fixed-offset Intl fallback uses Bangkok business day', await fallback.$eval('input', f => f.min) === '2026-10-11');
      await fallback.close();
    } else {
      for (const [width, kind, url] of [
        [1440, 'checkout', 'https://new.thai-online.org/shop/checkout?product=511&quantity=2'],
        [402, 'checkout', 'https://new.thai-online.org/shop/checkout?product=511&quantity=2'],
        [320, 'checkout', 'https://new.thai-online.org/shop/checkout?product=511&quantity=2'],
        [390, 'inline', 'https://new.thai-online.org/shop/470/desc/la-galleria-pattaya-2026']
      ]) {
        const page = await browser.newPage(), errors = [], nonReads = [], assets = [];
        await page.setViewport({ width, height: 874 });
        await page.emulateTimezone('America/Los_Angeles');
        page.on('pageerror', e => errors.push(e.message));
        page.on('response', r => { if (r.url().includes('/booking-dates.js')) assets.push({ url: r.url(), status: r.status() }); });
        await page.setRequestInterception(true);
        page.on('request', r => {
          if (!['GET', 'HEAD'].includes(r.method())) { nonReads.push({ method: r.method(), url: r.url() }); r.abort(); } else r.continue();
        });
        const response = await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
        await page.waitForSelector('.thai-booking-form input[name=date]');
        if (mode === 'candidate') {
          await page.$eval('.thai-booking-form input[name=date]', f => {
            f.setAttribute('data-thai-booking-date', '');
            f.setAttribute('data-thai-booking-timezone', 'Asia/Bangkok');
            f.setAttribute('data-thai-booking-offset', '25200');
            f.min = '2026-10-09';
          });
          await page.addScriptTag({ path: asset });
        }
        const state = await page.$eval('.thai-booking-form input[name=date]', f => {
          const now = new Date(), formatter = new Intl.DateTimeFormat('en', { timeZone: 'Asia/Bangkok', year: 'numeric', month: '2-digit', day: '2-digit' });
          const parts = {}; formatter.formatToParts(now).forEach(p => { parts[p.type] = p.value; });
          const today = parts.year + '-' + parts.month + '-' + parts.day;
          const utc = new Date(today + 'T00:00:00Z');
          const yesterday = new Date(utc.getTime() - 86400000).toISOString().slice(0, 10);
          const tomorrow = new Date(utc.getTime() + 86400000).toISOString().slice(0, 10);
          return { min: f.min, today, yesterday, tomorrow, zone: f.dataset.thaiBookingTimezone, offset: f.dataset.thaiBookingOffset, required: f.required };
        });
        const prefix = kind + ' ' + width + ': ';
        check(prefix + 'HTTPS page 200', response.status() === 200);
        check(prefix + 'min is Bangkok today', state.min === state.today, state);
        check(prefix + 'required and business timezone preserved', state.required && state.zone === 'Asia/Bangkok' && state.offset === '25200');
        let r = await setDate(page, state.yesterday);
        check(prefix + 'yesterday cannot pass native validation', !r.valid && r.underflow, r);
        r = await setDate(page, state.today); check(prefix + 'today allowed', r.valid, r);
        r = await setDate(page, state.tomorrow); check(prefix + 'tomorrow allowed', r.valid, r);
        r = await setDate(page, '2027-02-29'); check(prefix + 'impossible date cannot pass native validation', !r.valid && r.value === '', r);
        await setDate(page, state.today);
        check(prefix + 'form date geometry within viewport', await page.$eval('.thai-booking-form input[name=date]', f => {
          const box = f.getBoundingClientRect(); return box.width > 100 && box.left >= -1 && box.right <= window.innerWidth + 1;
        }));
        check(prefix + 'page has no horizontal overflow', await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
        if (kind === 'checkout') check(prefix + 'payment choices retained', await page.$$eval('select[name=payment] option', opts => opts.length === 4 && opts.map(o => o.value).join(',') === ',mir_sbp,thai_bank,office'));
        check(prefix + 'JS errors absent', errors.length === 0, errors);
        check(prefix + 'no POST or booking submitted', nonReads.length === 0, nonReads);
        if (mode === 'live') check(prefix + 'actual deployed date asset loaded', assets.length === 1 && [200, 304].includes(assets[0].status), assets);
        await page.$eval('.thai-booking-form input[name=date]', f => f.scrollIntoView({ block: 'center' }));
        await page.screenshot({ path: path.join(out, kind + '-' + width + '.png') });
        pages.push({ kind, width, state, assets, errors, nonReads });
        await page.close();
      }
    }
  } finally { await browser.close(); }
  const result = { mode, tests: checks.length, passed: checks.filter(c => c.pass).length, failed: failures, pages, checks, production_form_submissions: 0 };
  fs.writeFileSync(path.join(out, 'results.json'), JSON.stringify(result, null, 2));
  console.log(JSON.stringify({ mode, tests: result.tests, passed: result.passed, failed: failures }));
  if (failures.length) process.exitCode = 1;
})().catch(e => { console.error(e.stack); process.exitCode = 1; });
