(function () {
	'use strict';

	document.querySelectorAll('[data-hks-home-gallery]').forEach((gallery) => {
		const track = gallery.querySelector('[data-hks-home-gallery-track]');
		const slides = Array.from(gallery.querySelectorAll('[data-hks-home-gallery-slide]'));
		const previous = gallery.querySelector('[data-hks-home-gallery-prev]');
		const next = gallery.querySelector('[data-hks-home-gallery-next]');
		const status = gallery.querySelector('[data-hks-home-gallery-status]');
		const announcer = gallery.querySelector('[data-hks-home-gallery-announcer]');
		const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		const wideLayout = window.matchMedia('(min-width: 48rem)');
		const desktopLayout = window.matchMedia('(min-width: 64rem)');
		const precisePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
		const interval = Math.max(3000, Number(gallery.dataset.hksGalleryInterval) || 3000);
		const pauseReasons = new Set();
		let active = 0;
		let autoTimer = 0;
		let isInView = true;
		let drag = null;
		let suppressClick = false;

		if (!track || !slides.length) return;

		function circularPosition(index) {
			let position = (index - active + slides.length) % slides.length;
			if (position > slides.length / 2) position -= slides.length;

			if (0 === slides.length % 2 && position === slides.length / 2) {
				position = 0 === active % 2 ? position : -position;
			}

			return position;
		}

		function deckPosition(index) {
			if (desktopLayout.matches && 4 === slides.length) {
				const desktopSlots = [-0.5, 0.5, 1.5, -1.5];
				return desktopSlots[(index - active + slides.length) % slides.length];
			}

			return circularPosition(index);
		}

		function updateStatus(announce = false) {
			const selected = slides[active];
			const destination = selected.dataset.hksDestinationName || `Destination ${active + 1}`;
			const tourCount = selected.dataset.hksTourCount || '';

			if (status) status.textContent = `${active + 1} / ${slides.length}`;
			if (announce && announcer) {
				announcer.textContent = `Showing ${destination}${tourCount ? `, ${tourCount}` : ''}; destination ${active + 1} of ${slides.length}.`;
			}
		}

		function render(announce = false) {
			slides.forEach((slide, index) => {
				const position = deckPosition(index);
				const hidden = !wideLayout.matches && Math.abs(position) > 1;
				const link = slide.querySelector('a');

				slide.dataset.hksPosition = String(position);
				slide.classList.toggle('is-active', index === active);
				slide.setAttribute('aria-hidden', hidden ? 'true' : 'false');

				if (link) {
					link.tabIndex = hidden ? -1 : 0;
					if (index === active) link.setAttribute('aria-current', 'true');
					else link.removeAttribute('aria-current');
				}
			});

			gallery.classList.toggle('is-static', slides.length < 2);
			updateStatus(announce);
		}

		function goTo(index, announce = false) {
			active = (index + slides.length) % slides.length;
			render(announce);
		}

		function clearAuto() {
			window.clearTimeout(autoTimer);
			autoTimer = 0;
		}

		function canAutoAdvance() {
			return !reducedMotion.matches
				&& !desktopLayout.matches
				&& !pauseReasons.size
				&& isInView
				&& document.visibilityState === 'visible'
				&& slides.length > 1;
		}

		function scheduleAuto() {
			clearAuto();
			if (!canAutoAdvance()) return;

			autoTimer = window.setTimeout(() => {
				goTo(active + 1);
				scheduleAuto();
			}, interval);
		}

		function pause(reason) {
			pauseReasons.add(reason);
			clearAuto();
		}

		function resume(reason) {
			pauseReasons.delete(reason);
			scheduleAuto();
		}

		function showPrevious() {
			goTo(active - 1, true);
			scheduleAuto();
		}

		function showNext() {
			goTo(active + 1, true);
			scheduleAuto();
		}

		previous?.addEventListener('click', showPrevious);
		next?.addEventListener('click', showNext);

		track.addEventListener('keydown', (event) => {
			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				showPrevious();
			}
			if (event.key === 'ArrowRight') {
				event.preventDefault();
				showNext();
			}
			if (event.key === 'Home') {
				event.preventDefault();
				goTo(0, true);
			}
			if (event.key === 'End') {
				event.preventDefault();
				goTo(slides.length - 1, true);
			}
		});

		gallery.addEventListener('mouseenter', () => pause('hover'));
		gallery.addEventListener('mouseleave', () => {
			slides.forEach((slide) => slide.classList.remove('is-hovered'));
			resume('hover');
		});
		gallery.addEventListener('focusin', () => pause('focus'));
		gallery.addEventListener('focusout', (event) => {
			if (!gallery.contains(event.relatedTarget)) {
				slides.forEach((slide) => slide.classList.remove('is-hovered'));
				resume('focus');
			}
		});

		slides.forEach((slide) => {
			slide.addEventListener('pointerenter', () => {
				if (!precisePointer.matches) return;
				slide.classList.add('is-hovered');
			});

			slide.addEventListener('pointerleave', () => {
				slide.classList.remove('is-hovered');
			});

			slide.addEventListener('focusin', () => {
				slide.classList.add('is-hovered');
			});

			slide.addEventListener('focusout', (event) => {
				if (!slide.contains(event.relatedTarget)) {
					slide.classList.remove('is-hovered');
				}
			});
		});

		track.addEventListener('pointerdown', (event) => {
			if ('mouse' === event.pointerType && 0 !== event.button) return;

			pause('pointer');
			drag = {
				id: event.pointerId,
				startX: event.clientX,
				startY: event.clientY,
				distance: 0,
				moved: false,
			};
			track.setPointerCapture(event.pointerId);
		});

		track.addEventListener('pointermove', (event) => {
			if (!drag || drag.id !== event.pointerId) return;

			const distanceX = event.clientX - drag.startX;
			const distanceY = event.clientY - drag.startY;

			if (!drag.moved && Math.abs(distanceX) > 8 && Math.abs(distanceX) > Math.abs(distanceY)) {
				drag.moved = true;
				track.classList.add('is-dragging');
			}

			if (drag.moved) {
				drag.distance = distanceX;
				track.style.setProperty('--hks-drag-offset', `${Math.max(-42, Math.min(42, distanceX * 0.22))}px`);
				event.preventDefault();
			}
		});

		function endPointer(event) {
			if (!drag || drag.id !== event.pointerId) {
				resume('pointer');
				return;
			}

			const completedDrag = drag.moved;
			const distance = drag.distance;
			suppressClick = completedDrag;
			track.classList.remove('is-dragging');
			track.style.removeProperty('--hks-drag-offset');
			if (track.hasPointerCapture(event.pointerId)) track.releasePointerCapture(event.pointerId);
			drag = null;

			if ('pointercancel' !== event.type && Math.abs(distance) >= 36) {
				goTo(distance < 0 ? active + 1 : active - 1, true);
			}

			window.setTimeout(() => { suppressClick = false; }, 0);
			resume('pointer');
		}

		track.addEventListener('pointerup', endPointer);
		track.addEventListener('pointercancel', endPointer);
		track.addEventListener('click', (event) => {
			if (!suppressClick) return;
			event.preventDefault();
			event.stopPropagation();
		}, true);

		document.addEventListener('visibilitychange', () => {
			if ('hidden' === document.visibilityState) pause('document');
			else resume('document');
		});

		reducedMotion.addEventListener?.('change', scheduleAuto);
		wideLayout.addEventListener?.('change', () => render());
		desktopLayout.addEventListener?.('change', () => {
			render();
			scheduleAuto();
		});

		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries) => {
				isInView = Boolean(entries[0]?.isIntersecting);
				scheduleAuto();
			}, { threshold: 0.35 });
			observer.observe(gallery);
		}

		gallery.classList.add('is-ready');
		render();
		scheduleAuto();
	});
}());
