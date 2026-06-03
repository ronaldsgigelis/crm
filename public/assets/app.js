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
