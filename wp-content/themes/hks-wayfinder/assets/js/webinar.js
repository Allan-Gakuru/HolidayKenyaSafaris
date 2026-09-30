/* Presentation registration and calendar actions. No libraries, personal analytics, or storage of answers. */
(function () {
	'use strict';
	const root = document.querySelector('[data-hks-webinar]');
	if (!root) return;
	const bands = ['60000_79999', '80000_99999', '100000_plus'];
	const track = (event, additions) => {
		const payload = Object.assign({ event, event_contract_version: '1.0', page_type: 'webinar', webinar_key: root.dataset.hksWebinar }, additions || {});
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(payload);
		document.dispatchEvent(new CustomEvent('hks:analytics', { detail: payload }));
		if (event === 'webinar_registration_complete' && typeof window.fbq === 'function') {
			window.fbq('trackCustom', 'WebinarRegistration', { webinar_key: root.dataset.hksWebinar });
		}
	};
	root.querySelectorAll('[data-webinar-cta]').forEach((cta) => cta.addEventListener('click', () => {
		track('webinar_registration_cta_click', { cta_location: cta.dataset.webinarCta });
		if (cta.getAttribute('href') === '#registration') root.querySelector('#registration')?.focus();
	}));
	const calendar = root.querySelector('.hks-webinar-calendar');
	calendar?.addEventListener('toggle', () => { if (calendar.open) track('webinar_calendar_cta_click', { calendar_provider: 'menu' }); });
	root.querySelectorAll('[data-calendar-choice]').forEach((link) => link.addEventListener('click', () => track('webinar_calendar_cta_click', { calendar_provider: link.dataset.calendarChoice })));
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && calendar?.open) { calendar.open = false; calendar.querySelector('summary').focus(); }
	});
	document.addEventListener('click', (event) => { if (calendar?.open && !calendar.contains(event.target)) calendar.open = false; });
	const form = root.querySelector('[data-webinar-form]');
	if (!form) return;
	const startedAt = Date.now();
	const button = form.querySelector('[type=submit]');
	const buttonLabel = button.innerHTML;
	const status = form.querySelector('[data-form-status]');
	let busy = false;
	const clearErrors = () => {
		status.hidden = true;
		form.querySelectorAll('[data-error-for]').forEach((element) => { element.hidden = true; element.textContent = ''; });
		form.querySelectorAll('[aria-invalid]').forEach((element) => element.removeAttribute('aria-invalid'));
	};
	const showError = (field, message) => {
		const error = Array.from(form.querySelectorAll('[data-error-for]')).find((element) => element.dataset.errorFor === field);
		if (error) {
			error.textContent = message;
			error.hidden = false;
			form.elements.namedItem(field).setAttribute('aria-invalid', 'true');
		} else {
			status.textContent = message;
			status.hidden = false;
		}
	};
	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		if (busy || button.disabled) return;
		clearErrors();
		const answers = Object.fromEntries(new FormData(form).entries());
		answers.name = answers.name.trim();
		answers.email = answers.email.trim();
		const errors = [];
		if (!answers.name || Array.from(answers.name).length > 140) errors.push(['name', 'Enter your name (up to 140 characters).']);
		if (!answers.email || !form.elements.email.validity.valid) errors.push(['email', 'Enter a valid email address.']);
		if (!bands.includes(answers.payment_band)) errors.push(['payment_band', 'Select one of the booking-payment amounts.']);
		if (Array.from(answers.priority).length > 500) errors.push(['priority', 'Keep your answer to 500 characters or fewer.']);
		if (errors.length) {
			errors.forEach(([field, message]) => showError(field, message));
			status.textContent = 'Please check the highlighted answers.';
			status.hidden = false;
			form.elements.namedItem(errors[0][0]).focus();
			return;
		}
		busy = true;
		button.disabled = true;
		button.textContent = 'Reserving your place…';
		form.setAttribute('aria-busy', 'true');
		const controller = new AbortController();
		const timeout = window.setTimeout(() => controller.abort(), 25000);
		try {
			const contextResponse = await fetch(form.dataset.contextUrl, { credentials: 'same-origin', cache: 'no-store', signal: controller.signal });
			const context = await contextResponse.json();
			if (!contextResponse.ok) throw { publicMessage: context.message, field: context.data?.field };
			const response = await fetch(form.dataset.submitUrl, {
				method: 'POST', credentials: 'same-origin', cache: 'no-store', signal: controller.signal,
				headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.assign(answers, { form_token: context.form_token, started_at: startedAt }))
			});
			const result = await response.json();
			if (!response.ok || result.stored !== true) throw { publicMessage: result.message, field: result.data?.field };
			const redirect = new URL(result.redirect_url, window.location.origin);
			if (redirect.origin !== window.location.origin || redirect.search || redirect.hash) throw new Error('Unexpected redirect.');
			if (result.created === true) track('webinar_registration_complete');
			button.textContent = 'Place reserved. Opening your details…';
			window.location.assign(redirect.href);
		} catch (error) {
			showError(error.field || 'request', error.publicMessage || 'We couldn’t confirm your registration. Your answers are still here. Please try again.');
			if (error.field && form.elements.namedItem(error.field)) form.elements.namedItem(error.field).focus();
			else status.focus();
			busy = false;
			button.disabled = false;
			button.innerHTML = buttonLabel;
			form.removeAttribute('aria-busy');
		} finally {
			window.clearTimeout(timeout);
		}
	});
	// Enable only after the submit handler is installed; unavailable JS stays safe.
	button.disabled = form.dataset.registrationOpen !== 'true';
})();
