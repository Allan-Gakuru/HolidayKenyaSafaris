(function () {
	'use strict';
	const tourRoot = document.querySelector('[data-hks-tour-id]');

	function track(eventName, details = {}) {
		const payload = {
			event: eventName,
			event_contract_version: '1.0',
			tour_id: Number(tourRoot?.dataset.hksTourId || 0),
			...details
		};
		if (Array.isArray(window.dataLayer)) window.dataLayer.push(payload);
		document.dispatchEvent(new CustomEvent('hks:analytics', { detail: payload }));
	}

	const sectionSet = document.querySelector('[data-hks-tour-sections]');
	if (sectionSet) {
		const sections = Array.from(sectionSet.querySelectorAll('[data-hks-tour-section]'));
		const tabs = sectionSet.querySelector('[data-hks-tour-tabs]');
		const media = window.matchMedia('(min-width: 769px)');
		let activeIndex = 0;

		sections.forEach((section, index) => {
			section.addEventListener('toggle', (event) => {
				if (event.isTrusted && section.open) {
					track('tour_section_open', { section: section.dataset.hksSection || String(index) });
				}
			});
		});

		function activate(index, focus = false, record = false) {
			activeIndex = index;
			sections.forEach((section, sectionIndex) => {
				section.open = sectionIndex === index;
				section.hidden = media.matches && sectionIndex !== index;
			});
			if (!tabs) return;
			Array.from(tabs.querySelectorAll('[role="tab"]')).forEach((tab, tabIndex) => {
				tab.setAttribute('aria-selected', tabIndex === index ? 'true' : 'false');
				tab.tabIndex = tabIndex === index ? 0 : -1;
				if (focus && tabIndex === index) tab.focus();
			});
			if (record) {
				track('tour_section_open', { section: sections[index]?.dataset.hksSection || String(index) });
			}
		}

		function buildTabs() {
			if (!tabs) return;
			tabs.setAttribute('role', 'tablist');
			if (tabs.childElementCount) {
				sections.forEach((section, index) => {
					section.setAttribute('role', 'tabpanel');
					section.setAttribute('aria-labelledby', `hks-tour-tab-${index}`);
				});
				return;
			}
			sections.forEach((section, index) => {
				const button = document.createElement('button');
				const panelId = section.id || `hks-tour-panel-${index}`;
				const tabId = `hks-tour-tab-${index}`;
				section.id = panelId;
				button.type = 'button';
				button.id = tabId;
				button.setAttribute('role', 'tab');
				button.setAttribute('aria-controls', panelId);
				button.textContent = section.dataset.hksSectionLabel || `Section ${index + 1}`;
				button.addEventListener('click', () => activate(index, false, true));
				button.addEventListener('keydown', (event) => {
					let next = index;
					if (event.key === 'ArrowRight') next = (index + 1) % sections.length;
					else if (event.key === 'ArrowLeft') next = (index - 1 + sections.length) % sections.length;
					else if (event.key === 'Home') next = 0;
					else if (event.key === 'End') next = sections.length - 1;
					else return;
					event.preventDefault();
					activate(next, true, true);
				});
				tabs.appendChild(button);
				section.setAttribute('role', 'tabpanel');
				section.setAttribute('aria-labelledby', tabId);
			});
		}

		function updateMode() {
			if (media.matches) {
				sectionSet.classList.add('is-tabbed');
				buildTabs();
				activate(activeIndex);
			} else {
				sectionSet.classList.remove('is-tabbed');
				sections.forEach((section, index) => {
					section.hidden = false;
					section.removeAttribute('role');
					section.removeAttribute('aria-labelledby');
					section.open = index === activeIndex;
				});
			}
		}

		media.addEventListener('change', updateMode);
		updateMode();
	}

	document.querySelectorAll('[data-hks-itinerary-controls]').forEach((controls) => {
		const itinerary = controls.closest('[data-hks-itinerary]');
		const days = itinerary ? Array.from(itinerary.querySelectorAll('[data-hks-itinerary-day]')) : [];
		controls.addEventListener('click', (event) => {
			const button = event.target.closest('button[data-action]');
			if (!button) return;
			const expand = button.dataset.action === 'expand';
			days.forEach((day) => { day.open = expand; });
			track('itinerary_toggle', { state: expand ? 'expanded' : 'collapsed' });
		});
	});

	document.querySelectorAll('[data-hks-gallery]').forEach((gallery) => {
		const dialog = gallery.querySelector('[data-hks-gallery-dialog]');
		const slides = dialog ? Array.from(dialog.querySelectorAll('[data-hks-gallery-slide]')) : [];
		const counter = dialog?.querySelector('[data-hks-gallery-counter]');
		let active = 0;
		let returnFocus = null;

		function show(index) {
			active = (index + slides.length) % slides.length;
			slides.forEach((slide, slideIndex) => { slide.hidden = slideIndex !== active; });
			if (counter) counter.textContent = `${active + 1} / ${slides.length}`;
		}

		gallery.addEventListener('click', (event) => {
			const opener = event.target.closest('[data-hks-gallery-open]');
			if (opener && dialog && slides.length) {
				returnFocus = opener;
				show(Number(opener.dataset.hksGalleryOpen || 0));
				dialog.showModal();
				dialog.querySelector('[data-hks-gallery-close]')?.focus();
				track('tour_gallery_open', { image_index: active + 1 });
			}
			if (event.target.closest('[data-hks-gallery-close]')) dialog?.close();
			if (event.target.closest('[data-hks-gallery-next]')) show(active + 1);
			if (event.target.closest('[data-hks-gallery-prev]')) show(active - 1);
		});
		dialog?.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
		dialog?.addEventListener('close', () => { if (returnFocus) returnFocus.focus(); });
		dialog?.addEventListener('keydown', (event) => {
			if (event.key === 'ArrowRight') {
				event.preventDefault();
				show(active + 1);
			}
			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				show(active - 1);
			}
		});
	});

	document.querySelectorAll('[data-hks-related-tour]').forEach((link) => {
		link.addEventListener('click', (event) => {
			if (event.target.closest('a')) {
				track('related_tour_select', { related_tour_id: Number(link.dataset.hksRelatedTour || 0) });
			}
		});
	});
}());
