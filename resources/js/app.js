import './bootstrap';
import './echo';

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-navbar]').forEach((navbar) => {
		const toggle = navbar.querySelector('[data-navbar-toggle]');
		const menu = navbar.querySelector('[data-navbar-menu]');

		if (!toggle || !menu) {
			return;
		}

		const closeMenu = () => {
			toggle.classList.remove('is-open');
			menu.classList.remove('is-open');
			toggle.setAttribute('aria-expanded', 'false');
		};

		toggle.addEventListener('click', () => {
			const willOpen = !menu.classList.contains('is-open');
			toggle.classList.toggle('is-open', willOpen);
			menu.classList.toggle('is-open', willOpen);
			toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
		});

		menu.querySelectorAll('a').forEach((link) => {
			link.addEventListener('click', () => {
				if (window.innerWidth <= 900) {
					closeMenu();
				}
			});
		});

		window.addEventListener('resize', () => {
			if (window.innerWidth > 900) {
				closeMenu();
			}
		});
	});
});
