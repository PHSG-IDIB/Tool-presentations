// resources/js/main.js

// 1. Reveal.js Core und Plugins importieren
import Reveal from 'reveal.js';
import Markdown from 'reveal.js/plugin/markdown';
import Highlight from 'reveal.js/plugin/highlight';
import RevealMath from 'reveal.js/plugin/math';

// 2. Initialisierung
const deck = new Reveal({
    hash: true,
    slideNumber: true,
    plugins: [Markdown, Highlight, RevealMath.KaTeX]
});

// 3. Nach dem Start: Navigation und Timer aufbauen
deck.initialize().then(() => {
    setupNavigation();
    setupTimers();
});

// --- Eigene Funktionen ---

function setupNavigation() {
    const slides = document.querySelectorAll('section[data-abschnitt]');
    if (slides.length === 0) return;

    // Erstelle ein Container-Element für die Navigation (z.B. eine Sidebar)
    const navContainer = document.createElement('div');
    navContainer.className = 'presentation-sidebar';

    let navHtml = '<ul>';
    slides.forEach((slide, index) => {
        const title = slide.getAttribute('data-abschnitt');
        // Wir nutzen die Reveal-API für die Folien-Indizes (h = horizontal)
        const slideIndices = deck.getIndices(slide);
        navHtml += `<li onclick="location.hash='#/${slideIndices.h}'">${title}</li>`;
    });
    navHtml += '</ul>';

    navContainer.innerHTML = navHtml;
    document.querySelector('.reveal').appendChild(navContainer);
}

function setupTimers() {
    const timerElements = document.querySelectorAll('.presentation-timer');

    timerElements.forEach(el => {
        const minutes = parseInt(el.getAttribute('data-minutes'), 10);
        let timeRemaining = minutes * 60;
        let timerInterval = null;

        // UI aufbauen
        el.innerHTML = `
      <div class="timer-display">${formatTime(timeRemaining)}</div>
      <button class="timer-btn">Start</button>
    `;

        const display = el.querySelector('.timer-display');
        const btn = el.querySelector('.timer-btn');

        btn.addEventListener('click', () => {
            if (timerInterval) {
                // Stop
                clearInterval(timerInterval);
                timerInterval = null;
                btn.textContent = 'Start';
            } else {
                // Start
                btn.textContent = 'Pause';
                timerInterval = setInterval(() => {
                    timeRemaining--;
                    display.textContent = formatTime(timeRemaining);
                    if (timeRemaining <= 0) {
                        clearInterval(timerInterval);
                        el.classList.add('timer-done');
                    }
                }, 1000);
            }
        });
    });
}

function formatTime(seconds) {
    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
    const s = (seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
}
