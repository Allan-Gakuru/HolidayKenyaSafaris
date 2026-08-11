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
		const stage = gallery.querySelector('[data-hks-gallery-stage]');
		const stageImage = stage?.querySelector('img');
		const viewButton = gallery.querySelector('[data-hks-gallery-view]');
		const thumbnails = Array.from(gallery.querySelectorAll('[data-hks-gallery-thumb]'));
		const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		const desktopThumbnailRail = window.matchMedia('(min-width: 56.0625rem)');
		const autoplayInterval = Math.max(1000, Number(gallery.dataset.hksGalleryInterval || 5000));
		let active = 0;
		let previewIndex = 0;
		let returnFocus = null;
		let autoplayTimer = 0;
		let inViewport = true;
		let isHovered = false;
		let hasFocus = false;
		let previewAnimation = null;

		function stopAutoplay() {
			if (!autoplayTimer) return;
			window.clearTimeout(autoplayTimer);
			autoplayTimer = 0;
		}

		function canAutoplay() {
			return thumbnails.length > 1
				&& !reducedMotion.matches
				&& inViewport
				&& !isHovered
				&& !hasFocus
				&& !document.hidden
				&& !dialog?.open;
		}

		function preloadPreview(index) {
			if (!thumbnails.length) return;
			const normalizedIndex = (index + thumbnails.length) % thumbnails.length;
			const thumbnail = thumbnails[normalizedIndex];
			const nextSrc = thumbnail?.dataset.hksGalleryStageSrc;
			if (!nextSrc) return;
			const preload = new Image();
			preload.decoding = 'async';
			preload.sizes = stageImage?.sizes || '';
			preload.srcset = thumbnail.dataset.hksGalleryStageSrcset || '';
			preload.src = nextSrc;
		}

		function scheduleAutoplay() {
			stopAutoplay();
			if (!canAutoplay()) return;
			preloadPreview(previewIndex + 1);
			autoplayTimer = window.setTimeout(() => {
				autoplayTimer = 0;
				selectPreview(previewIndex + 1);
				scheduleAutoplay();
			}, autoplayInterval);
		}

		function selectPreview(index) {
			const normalizedIndex = (index + thumbnails.length) % thumbnails.length;
			const thumbnail = thumbnails[normalizedIndex];
			const nextSrc = thumbnail?.dataset.hksGalleryStageSrc;
			if (!thumbnail || !stage || !stageImage || !nextSrc) return;

			previewIndex = normalizedIndex;
			const nextSrcset = thumbnail.dataset.hksGalleryStageSrcset || '';
			if (nextSrcset) stageImage.setAttribute('srcset', nextSrcset);
			else stageImage.removeAttribute('srcset');
			stageImage.src = nextSrc;
			stageImage.alt = thumbnail.dataset.hksGalleryStageAlt || '';
			if (!reducedMotion.matches && typeof stageImage.animate === 'function') {
				previewAnimation?.cancel();
				previewAnimation = stageImage.animate(
					[{ opacity: 0.55 }, { opacity: 1 }],
					{ duration: 240, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
				);
			}

			const stageLabel = thumbnail.dataset.hksGalleryStageLabel || '';
			if (stageLabel) stage.setAttribute('aria-label', stageLabel);
			stage.dataset.hksGalleryOpen = String(normalizedIndex);
			if (viewButton) viewButton.dataset.hksGalleryOpen = String(normalizedIndex);

			thumbnails.forEach((item) => {
				const itemIndex = Number(item.dataset.hksGalleryThumb || 0);
				const representsMore = desktopThumbnailRail.matches
					&& item.hasAttribute('data-hks-gallery-more-open')
					&& normalizedIndex >= itemIndex;
				item.setAttribute('aria-pressed', itemIndex === normalizedIndex || representsMore ? 'true' : 'false');
			});
		}

		thumbnails.forEach((thumbnail) => {
			thumbnail.addEventListener('keydown', (event) => {
				const navigableThumbnails = desktopThumbnailRail.matches
					? thumbnails.filter((item) => !item.classList.contains('hks-tour-gallery__thumbnail--desktop-overflow'))
					: thumbnails;
				const position = navigableThumbnails.indexOf(thumbnail);
				let next = position;
				if (event.key === 'ArrowDown' || event.key === 'ArrowRight') next = (position + 1) % navigableThumbnails.length;
				else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') next = (position - 1 + navigableThumbnails.length) % navigableThumbnails.length;
				else if (event.key === 'Home') next = 0;
				else if (event.key === 'End') next = navigableThumbnails.length - 1;
				else return;
				event.preventDefault();
				const nextThumbnail = navigableThumbnails[next];
				selectPreview(Number(nextThumbnail.dataset.hksGalleryThumb || 0));
				nextThumbnail.focus();
				scheduleAutoplay();
			});
		});

		function show(index) {
			active = (index + slides.length) % slides.length;
			slides.forEach((slide, slideIndex) => { slide.hidden = slideIndex !== active; });
			if (counter) counter.textContent = `${active + 1} / ${slides.length}`;
		}

		function openDialogAt(opener, index) {
			if (!opener || !dialog || !slides.length) return;
			stopAutoplay();
			returnFocus = opener;
			show(index);
			dialog.showModal();
			dialog.querySelector('[data-hks-gallery-close]')?.focus();
			track('tour_gallery_open', { image_index: active + 1 });
		}

		gallery.addEventListener('click', (event) => {
			const moreOpener = event.target.closest('[data-hks-gallery-more-open]');
			if (moreOpener && desktopThumbnailRail.matches) {
				openDialogAt(moreOpener, Number(moreOpener.dataset.hksGalleryMoreOpen || 0));
				return;
			}

			const thumbnail = event.target.closest('[data-hks-gallery-thumb]');
			if (thumbnail) {
				selectPreview(Number(thumbnail.dataset.hksGalleryThumb || 0));
				scheduleAutoplay();
				return;
			}

			if (event.target.closest('[data-hks-gallery-stage-prev]')) {
				selectPreview(previewIndex - 1);
				scheduleAutoplay();
				return;
			}

			if (event.target.closest('[data-hks-gallery-stage-next]')) {
				selectPreview(previewIndex + 1);
				scheduleAutoplay();
				return;
			}

			const opener = event.target.closest('[data-hks-gallery-open]');
			if (opener) openDialogAt(opener, Number(opener.dataset.hksGalleryOpen || 0));
			if (event.target.closest('[data-hks-gallery-close]')) dialog?.close();
			if (event.target.closest('[data-hks-gallery-next]')) show(active + 1);
			if (event.target.closest('[data-hks-gallery-prev]')) show(active - 1);
		});
		gallery.addEventListener('mouseenter', () => {
			isHovered = true;
			stopAutoplay();
		});
		gallery.addEventListener('mouseleave', () => {
			isHovered = false;
			scheduleAutoplay();
		});
		gallery.addEventListener('focusin', () => {
			hasFocus = true;
			stopAutoplay();
		});
		gallery.addEventListener('focusout', () => {
			window.setTimeout(() => {
				hasFocus = gallery.contains(document.activeElement);
				scheduleAutoplay();
			}, 0);
		});
		dialog?.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
		dialog?.addEventListener('close', () => {
			if (returnFocus) returnFocus.focus();
			scheduleAutoplay();
		});
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

		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries) => {
				inViewport = Boolean(entries[0]?.isIntersecting);
				if (inViewport) scheduleAutoplay();
				else stopAutoplay();
			}, { threshold: 0.35 });
			observer.observe(gallery);
		}

		document.addEventListener('visibilitychange', scheduleAutoplay);
		reducedMotion.addEventListener('change', scheduleAutoplay);
		desktopThumbnailRail.addEventListener('change', () => selectPreview(previewIndex));
		scheduleAutoplay();
	});

	document.querySelectorAll('[data-hks-related-tour]').forEach((link) => {
		link.addEventListener('click', (event) => {
			if (event.target.closest('a')) {
				track('related_tour_select', { related_tour_id: Number(link.dataset.hksRelatedTour || 0) });
			}
		});
	});
}());
