const languageSwitcher = document.getElementById('language-switcher');

// Function to load JSON file
async function loadLanguage(lang) {
    const res = await fetch(`languages/${lang}.json`);
    const data = await res.json();

    // Update elements
    document.querySelectorAll('[data-key]').forEach(el => {
        const key = el.getAttribute('data-key');
        if(data[key]) el.textContent = data[key];
    });
}

// Event listener
languageSwitcher.addEventListener('change', (e) => {
    loadLanguage(e.target.value);
});

// Load default language (Swahili)
loadLanguage(languageSwitcher.value);
