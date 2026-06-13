(function () {
  var storageKey = 'crmTheme';
  var toggle = document.querySelector('[data-theme-toggle]');

  function setTheme(theme) {
    var isDark = theme === 'dark';
    document.body.classList.toggle('theme-dark', isDark);

    if (toggle) {
      toggle.textContent = isDark ? 'Light' : 'Dark';
      toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    }
  }

  try {
    setTheme(localStorage.getItem(storageKey) === 'dark' ? 'dark' : 'light');
  } catch (error) {
    setTheme('light');
  }

  if (!toggle) {
    return;
  }

  toggle.addEventListener('click', function () {
    var nextTheme = document.body.classList.contains('theme-dark') ? 'light' : 'dark';

    try {
      localStorage.setItem(storageKey, nextTheme);
    } catch (error) {
    }

    setTheme(nextTheme);
  });
})();

(function () {
  var form = document.querySelector('.invoice-settings-form');
  var preview = document.querySelector('[data-invoice-number-preview]');

  if (!form || !preview) {
    return;
  }

  var numberingType = form.querySelector('[name="numbering_type"]');
  var prefix = form.querySelector('[name="numbering_prefix"]');
  var separator = form.querySelector('[name="numbering_separator"]');
  var padding = form.querySelector('[name="numbering_padding"]');

  if (!numberingType || !prefix || !separator || !padding) {
    return;
  }

  function trim(value) {
    return String(value || '').trim();
  }

  function padSequence(length) {
    var safeLength = parseInt(length, 10);

    if (!Number.isFinite(safeLength)) {
      safeLength = 0;
    }

    safeLength = Math.max(1, Math.min(10, safeLength));
    return '1'.padStart(safeLength, '0');
  }

  function dateParts() {
    var now = new Date();
    var day = String(now.getDate()).padStart(2, '0');
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var year = String(now.getFullYear());

    return {
      dayMonthYear: day + month + year,
      dayMonthShortYear: day + month + year.slice(-2),
      year: year
    };
  }

  function renderPreview() {
    var type = numberingType.value || 'yearly_sequence';
    var cleanPrefix = trim(prefix.value);
    var cleanSeparator = trim(separator.value);
    var sequence = padSequence(padding.value);
    var currentDate = dateParts();

    if (type === 'manual') {
      preview.textContent = 'Manuāls numurs';
      return;
    }

    if (type === 'daily_sequence') {
      preview.textContent = cleanPrefix + currentDate.dayMonthYear + cleanSeparator + sequence;
      return;
    }

    if (type === 'daily_short_year') {
      preview.textContent = cleanPrefix + currentDate.dayMonthShortYear + cleanSeparator + sequence;
      return;
    }

    if (type === 'continuous_sequence') {
      preview.textContent = (cleanPrefix !== '' ? cleanPrefix + cleanSeparator : '') + sequence;
      return;
    }

    preview.textContent = (cleanPrefix !== '' ? cleanPrefix + cleanSeparator : '') + currentDate.year + cleanSeparator + sequence;
  }

  [numberingType, prefix, separator, padding].forEach(function (field) {
    field.addEventListener('input', renderPreview);
    field.addEventListener('change', renderPreview);
  });

  renderPreview();
})();
