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
		const interval = Math.max(3000, Number(gallery.dataset.hksGalleryInterval) || 3000);
		const pauseReasons = new Set();
		let active = 0;
		let autoTimer = 0;
		let isInView = true;
		let scrollTimer = 0;
		let drag = null;
		let suppressClick = false;

		if (!track || !slides.length) return;

		function visibleCount() {
			const value = parseInt(window.getComputedStyle(track).getPropertyValue('--hks-gallery-visible'), 10);
			return Math.max(1, Math.min(slides.length, Number.isFinite(value) ? value : 1));
		}

		function maximumIndex() {
			return Math.max(0, slides.length - visibleCount());
		}

		function slideLeft(index) {
			return slides[index].offsetLeft - slides[0].offsetLeft;
		}

		function nearestIndex() {
			let nearest = 0;
			let distance = Number.POSITIVE_INFINITY;

			for (let index = 0; index <= maximumIndex(); index += 1) {
				const nextDistance = Math.abs(track.scrollLeft - slideLeft(index));
				if (nextDistance < distance) {
					nearest = index;
					distance = nextDistance;
				}
			}

			return nearest;
		}

		function updateStatus(announce = false) {
			const first = active + 1;
			const last = Math.min(slides.length, active + visibleCount());
			const range = first === last ? String(first) : `${first}\u2013${last}`;

			if (status) status.textContent = `${range} / ${slides.length}`;
			if (announce && announcer) {
				announcer.textContent = first === last
					? `Showing Featured Tour ${first} of ${slides.length}.`
					: `Showing Featured Tours ${first} through ${last} of ${slides.length}.`;
			}
		}

		function updateStaticState() {
			gallery.classList.toggle('is-static', slides.length <= visibleCount());
			active = Math.min(active, maximumIndex());
			updateStatus();
		}

		function goTo(index, announce = false, behavior = 'smooth') {
			const maximum = maximumIndex();
			let target = index;

			if (target > maximum) target = 0;
			if (target < 0) target = maximum;
			active = target;
			track.scrollTo({
				left: slideLeft(active),
				behavior: reducedMotion.matches ? 'auto' : behavior,
			});
			updateStatus(announce);
		}

		function clearAuto() {
			window.clearTimeout(autoTimer);
			autoTimer = 0;
		}

		function canAutoAdvance() {
			return !reducedMotion.matches
				&& !pauseReasons.size
				&& isInView
				&& document.visibilityState === 'visible'
				&& maximumIndex() > 0;
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
				goTo(maximumIndex(), true);
			}
		});

		track.addEventListener('scroll', () => {
			window.clearTimeout(scrollTimer);
			scrollTimer = window.setTimeout(() => {
				active = nearestIndex();
				updateStatus();
			}, 80);
		}, { passive: true });

		gallery.addEventListener('mouseenter', () => pause('hover'));
		gallery.addEventListener('mouseleave', () => resume('hover'));
		gallery.addEventListener('focusin', () => pause('focus'));
		gallery.addEventListener('focusout', (event) => {
			if (!gallery.contains(event.relatedTarget)) resume('focus');
		});

		track.addEventListener('pointerdown', (event) => {
			pause('pointer');
			if ('mouse' !== event.pointerType || 0 !== event.button) return;

			drag = {
				id: event.pointerId,
				startX: event.clientX,
				startScroll: track.scrollLeft,
				moved: false,
			};
			track.setPointerCapture(event.pointerId);
		});

		track.addEventListener('pointermove', (event) => {
			if (!drag || drag.id !== event.pointerId) return;
			const movement = event.clientX - drag.startX;

			if (Math.abs(movement) > 5) {
				drag.moved = true;
				track.classList.add('is-dragging');
				event.preventDefault();
			}

			if (drag.moved) track.scrollLeft = drag.startScroll - movement;
		});

		function endPointer(event) {
			if (drag && drag.id === event.pointerId) {
				suppressClick = drag.moved;
				track.classList.remove('is-dragging');
				if (track.hasPointerCapture(event.pointerId)) track.releasePointerCapture(event.pointerId);
				drag = null;
				active = nearestIndex();
				goTo(active);
				window.setTimeout(() => { suppressClick = false; }, 0);
			}
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

		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries) => {
				isInView = Boolean(entries[0]?.isIntersecting);
				scheduleAuto();
			}, { threshold: 0.35 });
			observer.observe(gallery);
		}

		if ('ResizeObserver' in window) {
			const resizeObserver = new ResizeObserver(() => {
				updateStaticState();
				goTo(active, false, 'auto');
				scheduleAuto();
			});
			resizeObserver.observe(track);
		} else {
			window.addEventListener('resize', () => {
				updateStaticState();
				goTo(active, false, 'auto');
				scheduleAuto();
			});
		}

		gallery.classList.add('is-ready');
		updateStaticState();
		scheduleAuto();
	});
}());
