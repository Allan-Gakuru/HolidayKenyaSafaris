(function () {
	'use strict';

	const header = document.querySelector('[data-hks-site-header]');
	if (!header) return;

	const menu = header.querySelector('[data-hks-mobile-menu]');
	const openButton = header.querySelector('[data-hks-menu-open]');
	const closeButton = header.querySelector('[data-hks-menu-close]');
	let returnFocus = null;

	function openMenu() {
		if (!menu || typeof menu.showModal !== 'function') return;
		returnFocus = document.activeElement;
		menu.showModal();
		document.documentElement.classList.add('hks-menu-is-open');
		openButton?.setAttribute('aria-expanded', 'true');
		closeButton?.focus();
	}

	function closeMenu(restoreFocus = true) {
		if (!menu?.open) return;
		menu.close();
		document.documentElement.classList.remove('hks-menu-is-open');
		openButton?.setAttribute('aria-expanded', 'false');
		if (restoreFocus && returnFocus instanceof HTMLElement) returnFocus.focus();
	}

	openButton?.addEventListener('click', openMenu);
	closeButton?.addEventListener('click', () => closeMenu());
	menu?.addEventListener('cancel', (event) => {
		event.preventDefault();
		closeMenu();
	});
	menu?.addEventListener('click', (event) => {
		if (event.target === menu) closeMenu();
		if (event.target.closest('a')) closeMenu(false);
	});

	const desktopMenus = Array.from(header.querySelectorAll('[data-hks-nav-menu]'));
	desktopMenus.forEach((item) => {
		item.addEventListener('toggle', () => {
			if (!item.open) return;
			desktopMenus.forEach((other) => {
				if (other !== item) other.open = false;
			});
		});
	});

	document.addEventListener('click', (event) => {
		if (!event.target.closest('[data-hks-nav-menu]')) {
			desktopMenus.forEach((item) => { item.open = false; });
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') return;
		const openItem = desktopMenus.find((item) => item.open);
		if (openItem) {
			openItem.open = false;
			openItem.querySelector('summary')?.focus();
		}
	});

	document.addEventListener('click', (event) => {
		const proxy = event.target.closest('[data-hks-quote-proxy]');
		if (!proxy) return;

		const target = document.querySelector('[data-hks-primary-quote] [data-hks-inquiry-open], .hks-inquiry [data-hks-inquiry-open]');
		if (!target) return;

		event.preventDefault();
		closeMenu(false);
		window.setTimeout(() => target.click(), 0);
	});
}());
