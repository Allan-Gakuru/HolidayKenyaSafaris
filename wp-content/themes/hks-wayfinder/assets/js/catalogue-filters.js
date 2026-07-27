(function () {
	'use strict';

	const root = document.querySelector('[data-hks-catalogue-filters]');

	if (!root) {
		return;
	}

	const dialog = root.querySelector('[data-hks-filter-dialog]');
	const openButton = root.querySelector('[data-hks-filter-open]');
	const closeButton = root.querySelector('[data-hks-filter-close]');
	const desktopQuery = window.matchMedia('(min-width: 64rem)');
	let returnFocus = null;

	if (!dialog || !openButton || typeof dialog.showModal !== 'function') {
		return;
	}

	const closeFilters = (restoreFocus = true) => {
		if (!dialog.open) {
			return;
		}

		dialog.close();
		document.documentElement.classList.remove('hks-filter-is-open');
		openButton.setAttribute('aria-expanded', 'false');

		if (restoreFocus && returnFocus instanceof HTMLElement) {
			returnFocus.focus();
		}
	};

	const openFilters = () => {
		if (dialog.open || desktopQuery.matches) {
			return;
		}

		returnFocus = document.activeElement;
		dialog.showModal();
		document.documentElement.classList.add('hks-filter-is-open');
		openButton.setAttribute('aria-expanded', 'true');
		closeButton?.focus();
	};

	openButton.addEventListener('click', openFilters);
	closeButton?.addEventListener('click', () => closeFilters());

	dialog.addEventListener('cancel', (event) => {
		event.preventDefault();
		closeFilters();
	});

	dialog.addEventListener('click', (event) => {
		if (event.target === dialog) {
			closeFilters();
		}
	});

	desktopQuery.addEventListener('change', (event) => {
		if (event.matches) {
			closeFilters(false);
		}
	});
})();
