import 'alpinejs';
import './bootstrap';
import './echo';

// Only keep non-nav JS (e.g., flash alert)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-flash-alert').forEach((alertEl) => {
        const timeout = Number.parseInt(alertEl.dataset.timeout || '3000', 10);
        if (!Number.isFinite(timeout) || timeout <= 0) {
            return;
        }
        setTimeout(() => {
            alertEl.style.transition = 'opacity 0.3s ease';
            alertEl.style.opacity = '0';
            setTimeout(() => {
                alertEl.remove();
            }, 320);
        }, timeout);
    });
});
