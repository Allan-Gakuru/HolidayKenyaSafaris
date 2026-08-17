(function () {
	'use strict';
	var modal = document.querySelector('[data-quote-modal]');
	if (!modal) return;
	var openers = document.querySelectorAll('[data-open-quote]');
	var close = modal.querySelector('[data-close-quote]');
	var form = modal.querySelector('form');
	var status = modal.querySelector('[data-form-status]') || modal.querySelector('.form-status');
	var lastOpener = null;
	function getFocusable() {
		return Array.prototype.slice.call(modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')).filter(function (element) {
			return !element.disabled && element.getAttribute('aria-hidden') !== 'true';
		});
	}
	function openModal(event) {
		lastOpener = event && event.currentTarget ? event.currentTarget : document.activeElement;
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		var first = modal.querySelector('input');
		if (first) first.focus();
	}
	function closeModal() {
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		if (lastOpener && document.contains(lastOpener)) lastOpener.focus();
		lastOpener = null;
	}
	openers.forEach(function (button) { button.addEventListener('click', openModal); });
	if (close) close.addEventListener('click', closeModal);
	modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(); });
	modal.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			event.preventDefault();
			closeModal();
			return;
		}
		if (event.key !== 'Tab') return;
		var focusable = getFocusable();
		if (!focusable.length) return;
		var first = focusable[0];
		var last = focusable[focusable.length - 1];
		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	});
	if (form) form.addEventListener('submit', function (event) {
		event.preventDefault();
		if (status) {
			status.textContent = 'Demo only: the next step would review your message before WhatsApp opens. Nothing has been sent.';
			status.focus();
		}
	});
}());
