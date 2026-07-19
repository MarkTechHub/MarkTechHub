const languageSwitcher = document.getElementById('language-switcher');
const defaultLanguage = 'sw';
const storedLanguage = localStorage.getItem('preferredLanguage') || defaultLanguage;

async function loadLanguage(lang) {
  try {
    const res = await fetch(`languages/${lang}.json`);
    const data = await res.json();

    document.querySelectorAll('[data-key]').forEach(el => {
      const key = el.getAttribute('data-key');
      if (data[key]) el.textContent = data[key];
    });

    document.querySelectorAll('[data-key-placeholder]').forEach(el => {
      const key = el.getAttribute('data-key-placeholder');
      if (data[key]) el.placeholder = data[key];
    });

    document.querySelectorAll('[data-key-value]').forEach(el => {
      const key = el.getAttribute('data-key-value');
      if (data[key]) el.value = data[key];
    });

    if (data.page_title) {
      document.title = data.page_title;
    }

    document.documentElement.lang = lang;
    localStorage.setItem('preferredLanguage', lang);
    if (languageSwitcher) {
      languageSwitcher.value = lang;
    }
  } catch (error) {
    console.error('Could not load language file:', error);
  }
}

if (languageSwitcher) {
  languageSwitcher.value = storedLanguage;
  languageSwitcher.addEventListener('change', (e) => {
    loadLanguage(e.target.value);
  });
}

loadLanguage(storedLanguage);
