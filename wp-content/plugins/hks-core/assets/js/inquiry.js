(function () {
	'use strict';

	const ATTRIBUTION_KEY = 'hks_attribution_v1';
	const ATTRIBUTION_FIELDS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
	const viewedContexts = new Set();

	function safeText(value, maxLength) {
		return String(value || '').replace(/[\u0000-\u001f\u007f]/g, ' ').trim().slice(0, maxLength);
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

	function buildMessage(form, packageLabel, reference, source) {
		const data = new FormData(form);
		const lines = [
			'Hi Holiday Kenya Safaris, my name is ' + safeText(data.get('name'), 100) + '.',
			'',
			'I am interested in ' + packageLabel + '.',
			'Preferred travel date/month: ' + safeText(data.get('preferred_date'), 80) + '.',
			'Travelers: ' + safeText(data.get('travelers'), 3) + '.',
			'Phone: ' + safeText(data.get('phone'), 30) + '.'
		];

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

		const campaign = safeText(source.campaignLabel, 100);
		const sourceParts = [safeText(source.attribution.utm_source, 80), safeText(source.attribution.utm_campaign, 100)].filter(Boolean);
		if (campaign) lines.push('Campaign: ' + campaign + '.');
		if (sourceParts.length) lines.push('Source: ' + sourceParts.join(' / ') + '.');

		lines.push('', 'Please confirm availability, the current KSh price, what is included, and the next booking step.');
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
			consent_version: safeText(data.get('consent_version'), 40),
			contact_consent: data.get('contact_consent') === '1',
			website: safeText(data.get('website'), 120),
			name: safeText(data.get('name'), 100),
			phone: safeText(data.get('phone'), 30),
			preferred_date: safeText(data.get('preferred_date'), 80),
			travelers: Number(data.get('travelers')),
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
		const trigger = root.querySelector('[data-hks-inquiry-open]');
		const dialog = root.querySelector('[data-hks-inquiry-dialog]');
		const close = root.querySelector('[data-hks-inquiry-close]');
		const form = root.querySelector('[data-hks-inquiry-form]');
		const status = root.querySelector('[data-hks-inquiry-status]');
		const formStep = root.querySelector('[data-hks-form-step]');
		const reviewStep = root.querySelector('[data-hks-review-step]');
		const back = root.querySelector('[data-hks-inquiry-back]');
		const launch = root.querySelector('[data-hks-whatsapp-launch]');
		const message = root.querySelector('[data-hks-message]');
		const reference = root.querySelector('[data-hks-reference]');
		const requestKey = form.elements.request_key;
		const startedAt = form.elements.started_at;
		const sourceAttribution = attribution();
		let formStarted = false;

		if (!trigger || !dialog || !form) return;

		const viewKey = root.dataset.pageType + ':' + root.dataset.campaignId + ':' + root.dataset.tourId;
		if (!viewedContexts.has(viewKey)) {
			viewedContexts.add(viewKey);
			track(root, root.dataset.pageType === 'campaign' ? 'view_campaign' : 'view_tour');
		}

		function openDialog() {
			if (!requestKey.value) requestKey.value = uuid();
			if (!startedAt.value) startedAt.value = String(Date.now());
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

		trigger.addEventListener('click', openDialog);
		close.addEventListener('click', closeDialog);
		dialog.addEventListener('click', function (event) {
			if (event.target === dialog) closeDialog();
		});
		dialog.addEventListener('close', function () {
			trigger.focus();
		});

		form.addEventListener('input', function () {
			if (!formStarted) {
				formStarted = true;
				track(root, 'quote_form_start');
			}
		}, { once: true });

		form.addEventListener('submit', async function (event) {
			event.preventDefault();
			status.textContent = '';

			if (!form.checkValidity()) {
				const invalid = form.querySelector(':invalid');
				track(root, 'quote_form_error', { field_name: invalid ? invalid.name : 'form', error_type: 'client_validation' });
				form.reportValidity();
				if (invalid) invalid.focus();
				return;
			}

			const submit = form.querySelector('button[type="submit"]');
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

				const reviewedMessage = buildMessage(form, safeText(result.package_label, 160), safeText(result.reference, 30), {
					campaignLabel: root.dataset.campaignLabel,
					attribution: sourceAttribution
				});
				message.textContent = reviewedMessage;
				reference.textContent = safeText(result.reference, 30);
				launch.href = 'https://wa.me/' + root.dataset.whatsappNumber + '?text=' + encodeURIComponent(reviewedMessage);
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
				if (field && typeof field.focus === 'function') field.focus();
			} finally {
				submit.disabled = false;
				if (!reviewStep.hidden) status.textContent = '';
			}
		});

		back.addEventListener('click', function () {
			reviewStep.hidden = true;
			formStep.hidden = false;
			form.elements.name.focus();
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
	}

	document.querySelectorAll('[data-hks-inquiry]').forEach(init);
})();
