(function () {
	'use strict';

	const ATTRIBUTION_KEY = 'hks_attribution_v1';
	const ATTRIBUTION_FIELDS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
	const viewedContexts = new Set();

	function safeText(value, maxLength) {
		return String(value || '').replace(/[\u0000-\u001f\u007f]/g, ' ').trim().slice(0, maxLength);
	}

	function todayInKenya() {
		const parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Africa/Nairobi', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
		const part = (type) => parts.find((item) => item.type === type).value;
		return part('year') + '-' + part('month') + '-' + part('day');
	}

	function validTravelDate(value) {
		const parts = /^(\d{4})-(\d{2})(?:-(\d{2}))?$/.exec(value);
		if (!parts || Number(parts[1]) < 1) return false;
		const year = Number(parts[1]);
		const month = Number(parts[2]);
		const day = Number(parts[3] || 1);
		const leap = year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0);
		const days = [31, leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		return month >= 1 && month <= 12 && day >= 1 && day <= days[month - 1] && value >= todayInKenya().slice(0, value.length);
	}

	function validPhone(value) {
		if (value.length > 30 || !/^\+?[0-9 () .-]+$/.test(value)) return false;
		const digits = value.replace(/[ ().-]/g, '');
		if (digits.startsWith('+254')) return /^\+254(?:[17][0-9]{8}|[2-6][0-9]{7,8})$/.test(digits);
		return /^0[17][0-9]{8}$/.test(digits) || /^0[2-6][0-9]{7,8}$/.test(digits) || /^\+[1-9][0-9]{7,14}$/.test(digits);
	}

	function validEmail(value) {
		if (value.length > 254) return false;
		const parts = value.split('@');
		if (parts.length !== 2 || !/^[a-z0-9!#$%&'*+\/=?^_`{|}~.-]+$/i.test(parts[0])) return false;
		if (parts[0].length > 64 || parts[0].startsWith('.') || parts[0].endsWith('.') || parts[0].includes('..')) return false;
		const labels = parts[1].split('.');
		return labels.length >= 2 && labels.every((label) => label.length <= 63 && /^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i.test(label));
	}

	function initValidation(form, status) {
		const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]), select')).filter((field) => field.name !== 'website');
		function showError(field, message) {
			let error = document.getElementById(field.id + '-error');
			if (!error) {
				error = document.createElement('small');
				error.id = field.id + '-error';
				error.className = 'hks-inquiry__error';
				error.setAttribute('aria-live', 'polite');
				field.closest('.hks-inquiry__field').append(error);
				field.setAttribute('aria-describedby', ((field.getAttribute('aria-describedby') || '') + ' ' + error.id).trim());
			}
			error.textContent = message;
			error.hidden = !message;
			field.setAttribute('aria-invalid', message ? 'true' : 'false');
		}
		function validate(field, reveal) {
			field.setCustomValidity('');
			const value = field.value.trim();
			let error = '';
			if (!field.disabled) {
				if (field.required && !value) error = 'Please complete this field.';
				else if (value && field.name === 'phone' && !validPhone(value)) error = 'Use a Kenyan number such as 0712 345 678, or include + and your country code.';
				else if (value && field.name === 'email' && !validEmail(value)) error = 'Enter an email address such as you@example.com.';
				else if (value && field.name === 'preferred_date' && !validTravelDate(value)) error = 'Choose today or a future date (YYYY-MM-DD), or this month or a future month (YYYY-MM).';
				else if (value && field.name === 'name' && value.length < 2) error = 'Please enter your name.';
				else if (!field.validity.valid) error = field.validationMessage;
			}
			field.setCustomValidity(error);
			if (reveal) showError(field, error);
			return !error;
		}
		fields.forEach(function (field) {
			field.addEventListener('blur', () => validate(field, true));
			field.addEventListener('input', () => validate(field, field.getAttribute('aria-invalid') === 'true'));
			field.addEventListener('change', () => validate(field, true));
		});
		return {
			check: function () {
				fields.forEach((field) => { if (['phone', 'email', 'preferred_date'].includes(field.name)) field.value = field.value.trim(); });
				const valid = fields.map((field) => validate(field, true)).every(Boolean);
				if (!valid) status.textContent = 'Please check the highlighted fields.';
				return valid;
			},
			showError: showError
		};
	}

	function initDatePicker(form) {
		const wrapper = form.querySelector('[data-hks-date]');
		if (!wrapper) return;
		const input = form.elements.preferred_date;
		const toggle = wrapper.querySelector('[data-hks-date-toggle]');
		const calendar = wrapper.querySelector('[data-hks-calendar]');
		let month = todayInKenya().slice(0, 7);
		let returningFocus = false;
		let rendering = false;
		const iso = (date) => date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
		function close(restore) {
			calendar.hidden = true;
			toggle.setAttribute('aria-expanded', 'false');
			if (restore) { returningFocus = true; input.focus(); returningFocus = false; }
		}
		function select(value) {
			input.value = value;
			input.dispatchEvent(new Event('input', { bubbles: true }));
			input.dispatchEvent(new Event('change', { bubbles: true }));
			close(true);
		}
		function button(label, action, text) {
			const element = document.createElement('button');
			element.type = 'button';
			element.textContent = text || label;
			element.setAttribute('aria-label', label);
			element.addEventListener('click', action);
			return element;
		}
		function render(focusDay) {
			rendering = true;
			calendar.replaceChildren();
			const first = new Date(month + '-01T12:00:00');
			const title = first.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
			const header = document.createElement('div');
			header.className = 'hks-inquiry__calendar-header';
			function move(delta) {
				first.setMonth(first.getMonth() + delta);
				month = iso(first).slice(0, 7);
				render();
				const target = calendar.querySelector(delta < 0 ? '[data-previous]' : '[data-next]');
				(target.disabled ? calendar.querySelector('[data-date]:not(:disabled)') : target).focus();
			}
			const previous = button('Previous month', () => move(-1), 'Previous');
			previous.dataset.previous = '';
			previous.disabled = month <= todayInKenya().slice(0, 7);
			const next = button('Next month', () => move(1), 'Next');
			next.dataset.next = '';
			next.disabled = month === '9999-12';
			const heading = document.createElement('strong');
			heading.textContent = title;
			heading.setAttribute('aria-live', 'polite');
			header.append(previous, heading, next);
			const grid = document.createElement('div');
			grid.className = 'hks-inquiry__calendar-days';
			['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'].forEach((day) => {
				const label = document.createElement('span'); label.textContent = day; label.setAttribute('aria-hidden', 'true'); grid.append(label);
			});
			const offset = (first.getDay() + 6) % 7;
			for (let i = 0; i < offset; i++) grid.append(document.createElement('span'));
			const days = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate();
			const tabDay = focusDay || (input.value.length === 10 && input.value.startsWith(month) && validTravelDate(input.value) ? input.value : (month === todayInKenya().slice(0, 7) ? todayInKenya() : month + '-01'));
			for (let day = 1; day <= days; day++) {
				const date = month + '-' + String(day).padStart(2, '0');
				const label = new Date(date + 'T12:00:00').toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
				const cell = button(label, () => select(date), String(day));
				cell.dataset.date = date;
				cell.tabIndex = date === tabDay ? 0 : -1;
				cell.disabled = date < todayInKenya();
				cell.setAttribute('aria-pressed', String(date === input.value));
				if (date === todayInKenya()) cell.setAttribute('aria-current', 'date');
				grid.append(cell);
			}
			const footer = document.createElement('div');
			footer.className = 'hks-inquiry__calendar-footer';
			footer.append(button('Choose ' + title + ' — month only', () => select(month), 'Choose this month'), button('Close calendar', () => close(true), 'Done'));
			calendar.append(header, grid, footer);
			if (focusDay) calendar.querySelector('[data-date="' + focusDay + '"]')?.focus();
			rendering = false;
		}
		function open() {
			if (returningFocus || !calendar.hidden) return;
			month = validTravelDate(input.value) ? input.value.slice(0, 7) : todayInKenya().slice(0, 7);
			render();
			calendar.hidden = false;
			toggle.setAttribute('aria-expanded', 'true');
		}
		input.addEventListener('focus', open);
		input.addEventListener('click', open);
		toggle.addEventListener('click', () => { if (calendar.hidden) { open(); calendar.querySelector('[data-date]:not(:disabled)').focus(); } else close(true); });
		input.addEventListener('keydown', (event) => {
			if (event.key === 'ArrowDown') { event.preventDefault(); open(); calendar.querySelector('[data-date]:not(:disabled)').focus(); }
		});
		wrapper.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && !calendar.hidden) { event.preventDefault(); event.stopPropagation(); close(true); return; }
			if (!event.target.dataset.date) return;
			const delta = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[event.key];
			if (!delta) return;
			event.preventDefault();
			const date = new Date(event.target.dataset.date + 'T12:00:00');
			date.setDate(date.getDate() + delta);
			const value = iso(date);
			if (!validTravelDate(value)) return;
			month = value.slice(0, 7); render(value);
		});
		wrapper.addEventListener('focusout', (event) => { if (!rendering && !wrapper.contains(event.relatedTarget)) close(false); });
		document.addEventListener('pointerdown', (event) => { if (!wrapper.contains(event.target)) close(false); });
		form.closest('dialog')?.addEventListener('close', () => close(false));
	}

	function attribution() {
		try {
			const existing = window.sessionStorage.getItem(ATTRIBUTION_KEY);
			if (existing) {
				return JSON.parse(existing);
			}
		} catch (error) {
			// Storage is optional; continue with the current page context.
		}

		const params = new URLSearchParams(window.location.search);
		const captured = { landing_path: window.location.pathname.slice(0, 160) };

		ATTRIBUTION_FIELDS.forEach(function (field) {
			const value = safeText(params.get(field), 160);
			if (value) {
				captured[field] = value;
			}
		});

		if (document.referrer) {
			try {
				captured.referrer_host = new URL(document.referrer).hostname.slice(0, 160);
			} catch (error) {
				// Ignore malformed referrers.
			}
		}

		try {
			window.sessionStorage.setItem(ATTRIBUTION_KEY, JSON.stringify(captured));
		} catch (error) {
			// The inquiry still works when storage is disabled.
		}

		return captured;
	}

	function eventContext(root) {
		return {
			event_contract_version: '1.0',
			tour_id: Number(root.dataset.tourId || 0),
			tour_slug: safeText(root.dataset.tourSlug, 100),
			article_id: Number(root.dataset.articleId || 0),
			article_format: safeText(root.dataset.articleFormat, 20),
			campaign_id: Number(root.dataset.campaignId || 0),
			campaign_label: safeText(root.dataset.campaignLabel, 100),
			page_type: safeText(root.dataset.pageType, 30),
			cta_location: safeText(root.dataset.ctaLocation, 40)
		};
	}

	function track(root, eventName, additions) {
		const payload = Object.assign({ event: eventName }, eventContext(root), additions || {});
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(payload);
		document.dispatchEvent(new CustomEvent('hks:analytics', { detail: payload }));

		if (eventName === 'quote_form_complete') {
			trackMetaLead(payload);
		}
	}

	function trackMetaLead(payload) {
		if (!window.FacebookSignal || typeof window.FacebookSignal.trackEvent !== 'function') return;

		const parameters = {
			content_name: payload.tour_slug || payload.campaign_label || 'quote_request',
			content_category: payload.page_type || 'quote',
			hks_event: 'quote_form_complete',
			event_contract_version: payload.event_contract_version || '1.0'
		};

		['tour_id', 'campaign_id', 'cta_location', 'traveler_count_bucket'].forEach(function (name) {
			if (payload[name]) parameters[name] = payload[name];
		});

		window.FacebookSignal.trackEvent(
			'Lead',
			parameters,
			{},
			'track',
			'hks_lead_' + uuid()
		);
	}

	function uuid() {
		if (window.crypto && typeof window.crypto.randomUUID === 'function') {
			return window.crypto.randomUUID();
		}

		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (character) {
			const random = Math.floor(Math.random() * 16);
			const value = character === 'x' ? random : (random & 0x3) | 0x8;
			return value.toString(16);
		});
	}

	function travelerBucket(value) {
		const count = Number(value || 0);
		if (count <= 1) return '1';
		if (count === 2) return '2';
		if (count <= 5) return '3-5';
		if (count <= 9) return '6-9';
		return '10+';
	}

	function selectedLabel(form, name) {
		const field = form.elements[name];
		if (!field || !field.value) return '';
		if (field.tagName === 'SELECT') return safeText(field.selectedOptions[0].textContent, 120);
		return safeText(field.value, 120);
	}

	function buildMessage(form, packageLabel, reference, context) {
		const data = new FormData(form);
		const destination = selectedLabel(form, 'destination_selection');
		const lines = [
			'Hi Holiday Kenya Safaris, my name is ' + safeText(data.get('name'), 100) + '.',
			'',
			'I am interested in ' + packageLabel + '.',
			'Preferred travel date/month: ' + safeText(data.get('preferred_date'), 80) + '.',
			'Travelers: ' + safeText(data.get('travelers'), 3) + '.',
			'Phone: ' + safeText(data.get('phone'), 30) + '.',
			'Email: ' + safeText(data.get('email'), 254) + '.'
		];
		if (destination) lines.splice(3, 0, 'Destination: ' + destination + '.');

		const optionalLabels = {
			departure_town: 'Departure town',
			adults: 'Adults',
			children: 'Children',
			residency: 'Residency',
			vehicle_preference: 'Vehicle preference',
			accommodation_preference: 'Accommodation preference',
			budget_range: 'Budget range'
		};

		Object.keys(optionalLabels).forEach(function (name) {
			const value = selectedLabel(form, name);
			if (value) lines.push(optionalLabels[name] + ': ' + value + '.');
		});

		lines.push('', 'Request reference: ' + reference + '.');

		if (safeText(context.pageType, 30) === 'group_travel') {
			lines.push('', 'Please confirm availability and send a tailored KSh quote for this group, including what is included and the next booking step.');
		} else {
			lines.push('', 'Please confirm availability, the current KSh price, what is included, and the next booking step.');
		}
		return lines.join('\n');
	}

	function formPayload(form, root, sourceAttribution) {
		const data = new FormData(form);
		const payload = {
			tour_id: Number(data.get('tour_id')),
			campaign_id: Number(data.get('campaign_id')),
			form_token: safeText(data.get('form_token'), 500),
			request_key: safeText(data.get('request_key'), 36),
			started_at: Number(data.get('started_at')),
			website: safeText(data.get('website'), 120),
			name: safeText(data.get('name'), 100),
			phone: safeText(data.get('phone'), 30),
			email: safeText(data.get('email'), 254),
			preferred_date: safeText(data.get('preferred_date'), 80),
			travelers: Number(data.get('travelers')),
			destination_id: Number(data.get('destination_selection') || 0),
			inquiry_route: safeText(root.dataset.pageType, 30),
			attribution: sourceAttribution
		};

		['departure_town', 'residency', 'vehicle_preference', 'accommodation_preference', 'budget_range'].forEach(function (name) {
			if (form.elements[name]) payload[name] = safeText(data.get(name), 120);
		});
		['adults', 'children'].forEach(function (name) {
			if (form.elements[name]) payload[name] = data.get(name) === '' ? '' : Number(data.get(name));
		});

		return payload;
	}

	function init(root) {
		const inline = root.hasAttribute('data-hks-inquiry-inline');
		const trigger = root.querySelector('[data-hks-inquiry-open]');
		const dialog = root.querySelector('[data-hks-inquiry-dialog]');
		const close = root.querySelector('[data-hks-inquiry-close]');
		const form = root.querySelector('[data-hks-inquiry-form]');
		const status = root.querySelector('[data-hks-inquiry-status]');
		const formStep = root.querySelector('[data-hks-form-step]');
		const reviewStep = root.querySelector('[data-hks-review-step]');
		const back = root.querySelector('[data-hks-inquiry-back]');
		const launch = root.querySelector('[data-hks-whatsapp-launch]');
		const emailLaunch = root.querySelector('[data-hks-email-launch]');
		const message = root.querySelector('[data-hks-message]');
		const reference = root.querySelector('[data-hks-reference]');

		if (!form || !status || !formStep || !reviewStep || !back || !launch || !emailLaunch || !message || !reference) return;
		if (!inline && (!trigger || !dialog || !close)) return;
		const validation = initValidation(form, status);
		initDatePicker(form);

		const requestKey = form.elements.request_key;
		const startedAt = form.elements.started_at;
		const destination = form.elements.destination_selection;
		const tour = form.elements.tour_selection;
		const packageLabel = root.querySelector('[data-hks-package-label]');
		const sourceAttribution = attribution();
		let formStarted = false;

		function ensureRequestContext() {
			if (!requestKey.value) requestKey.value = uuid();
			if (!startedAt.value) startedAt.value = String(Date.now());
		}

		function syncGroupTour() {
			if (!tour) return;
			const selected = tour.value ? tour.selectedOptions[0] : null;
			form.elements.tour_id.value = selected ? selected.value : '';
			form.elements.form_token.value = selected ? safeText(selected.dataset.formToken, 500) : '';
			root.dataset.tourId = selected ? selected.value : '0';
			root.dataset.tourSlug = selected ? safeText(selected.dataset.tourSlug, 100) : '';
			if (packageLabel) packageLabel.textContent = selected ? safeText(selected.textContent, 160) : 'Choose a Tour to continue';
		}

		function filterGroupTours() {
			if (!destination || !tour) return;
			const destinationId = destination.value;
			let available = 0;

			Array.from(tour.options).forEach(function (option) {
				if (!option.value) return;
				const matches = destinationId && (option.dataset.destinations || '').split(',').includes(destinationId);
				option.hidden = !matches;
				option.disabled = !matches;
				if (matches) available += 1;
			});

			tour.disabled = !destinationId || available === 0;
			tour.options[0].textContent = destinationId ? 'Choose a Tour' : 'Choose a destination first';
			if (tour.selectedOptions[0] && tour.selectedOptions[0].disabled) tour.value = '';
			syncGroupTour();
		}

		if (destination && tour) {
			destination.addEventListener('change', filterGroupTours);
			tour.addEventListener('change', syncGroupTour);
			filterGroupTours();
		}

		const viewKey = root.dataset.pageType + ':' + root.dataset.campaignId + ':' + root.dataset.tourId;
		if (!viewedContexts.has(viewKey)) {
			viewedContexts.add(viewKey);
			const pageType = root.dataset.pageType;
			track(root, pageType === 'campaign' ? 'view_campaign' : (pageType === 'tour' ? 'view_tour' : 'page_view'));
		}

		function openDialog() {
			ensureRequestContext();
			status.textContent = '';
			if (typeof dialog.showModal === 'function') dialog.showModal();
			else dialog.setAttribute('open', '');
			track(root, 'quote_cta_click');
			window.setTimeout(function () {
				const firstField = form.elements.name;
				if (firstField) firstField.focus();
			}, 0);
		}

		function closeDialog() {
			if (typeof dialog.close === 'function') dialog.close();
			else dialog.removeAttribute('open');
			trigger.focus();
		}

		if (!inline) {
			trigger.addEventListener('click', openDialog);
			close.addEventListener('click', closeDialog);
			dialog.addEventListener('click', function (event) {
				if (event.target === dialog) closeDialog();
			});
			dialog.addEventListener('close', function () {
				trigger.focus();
			});
		}

		form.addEventListener('focusin', ensureRequestContext, { once: true });

		form.addEventListener('input', function () {
			ensureRequestContext();
			if (!formStarted) {
				formStarted = true;
				track(root, 'quote_form_start');
			}
		}, { once: true });

		form.addEventListener('submit', async function (event) {
			event.preventDefault();
			status.textContent = '';
			ensureRequestContext();

			if (!validation.check() || !form.checkValidity()) {
				const invalid = form.querySelector(':invalid');
				track(root, 'quote_form_error', { field_name: invalid ? invalid.name : 'form', error_type: 'client_validation' });
				if (invalid) invalid.focus();
				return;
			}

			const submit = form.querySelector('button[type="submit"]');
			if (inline) track(root, 'quote_cta_click');
			submit.disabled = true;
			status.textContent = 'Saving your request…';

			try {
				const response = await window.fetch(root.dataset.captureEndpoint, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					credentials: 'same-origin',
					body: JSON.stringify(formPayload(form, root, sourceAttribution))
				});
				const result = await response.json();

				if (!response.ok || !result.saved) {
					const error = new Error(result.message || 'We could not save your request. Please try again.');
					error.field = result.data && result.data.field ? result.data.field : 'request';
					error.type = response.status === 429 ? 'rate_limit' : 'server_validation';
					throw error;
				}

				const reviewedPackage = safeText(result.package_label, 160);
				const reviewedReference = safeText(result.reference, 30);
				const reviewedMessage = buildMessage(form, reviewedPackage, reviewedReference, {
					pageType: root.dataset.pageType
				});
				const emailRecipient = safeText(root.dataset.emailRecipient, 254).replace(/[^a-z0-9@._+-]/gi, '');
				const emailSubject = 'Quote request – ' + reviewedPackage + ' – ' + reviewedReference;
				message.textContent = reviewedMessage;
				reference.textContent = reviewedReference;
				launch.href = 'https://wa.me/' + root.dataset.whatsappNumber + '?text=' + encodeURIComponent(reviewedMessage);
				emailLaunch.href = 'mailto:' + emailRecipient + '?subject=' + encodeURIComponent(emailSubject) + '&body=' + encodeURIComponent(reviewedMessage);
				formStep.hidden = true;
				reviewStep.hidden = false;
				reviewStep.setAttribute('tabindex', '-1');
				reviewStep.focus();
				track(root, 'quote_inquiry_saved', { request_reference: safeText(result.reference, 30) });
				track(root, 'quote_form_complete', { traveler_count_bucket: travelerBucket(form.elements.travelers.value) });
			} catch (error) {
				status.textContent = error.message || 'We could not save your request. Please try again.';
				track(root, 'quote_form_error', { field_name: safeText(error.field || 'request', 40), error_type: safeText(error.type || 'network', 40) });
				const field = form.elements[error.field];
				if (field && typeof field.focus === 'function') { validation.showError(field, status.textContent); field.focus(); }
			} finally {
				submit.disabled = false;
				if (!reviewStep.hidden) status.textContent = '';
			}
		});

		back.addEventListener('click', function () {
			reviewStep.hidden = true;
			formStep.hidden = false;
			const firstField = destination || form.elements.name;
			if (firstField) firstField.focus();
		});

		launch.addEventListener('click', function () {
			const utms = {};
			ATTRIBUTION_FIELDS.forEach(function (field) {
				if (sourceAttribution[field]) utms[field] = safeText(sourceAttribution[field], 160);
			});
			track(root, 'whatsapp_launch', utms);

			const endpoint = root.dataset.launchEndpoint + encodeURIComponent(requestKey.value) + '/whatsapp-open';
			window.fetch(endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				keepalive: true
			}).catch(function () {
				// The inquiry is already saved; launch-state recovery is best effort.
			});
		});

		emailLaunch.addEventListener('click', function () {
			const utms = {};
			ATTRIBUTION_FIELDS.forEach(function (field) {
				if (sourceAttribution[field]) utms[field] = safeText(sourceAttribution[field], 160);
			});
			track(root, 'email_launch', utms);
		});
	}

	document.querySelectorAll('[data-hks-inquiry]').forEach(init);
})();
