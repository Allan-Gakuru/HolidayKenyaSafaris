(function () {
	'use strict';

	document.querySelectorAll('[data-hks-home-gallery]').forEach((gallery) => {
		const track = gallery.querySelector('[data-hks-home-gallery-track]');
		const slides = Array.from(gallery.querySelectorAll('[data-hks-home-gallery-slide]'));
		const stage = gallery.querySelector('[data-hks-home-gallery-stage]');
		const stageImage = gallery.querySelector('[data-hks-home-gallery-active-image]');
		const copy = gallery.querySelector('[data-hks-home-gallery-copy]');
		const eyebrow = gallery.querySelector('[data-hks-home-gallery-eyebrow]');
		const title = gallery.querySelector('[data-hks-home-gallery-title]');
		const link = gallery.querySelector('[data-hks-home-gallery-link]');
		const previous = gallery.querySelector('[data-hks-home-gallery-prev]');
		const next = gallery.querySelector('[data-hks-home-gallery-next]');
		const pauseButton = gallery.querySelector('[data-hks-home-gallery-pause]');
		const pauseIcon = gallery.querySelector('[data-hks-home-gallery-pause-icon]');
		const status = gallery.querySelector('[data-hks-home-gallery-status]');
		const announcer = gallery.querySelector('[data-hks-home-gallery-announcer]');
		const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		const interval = Math.max(2500, Number(gallery.dataset.hksGalleryInterval) || 2500);
		const pauseReasons = new Set();
		let activeIndex = 0;
		let autoTimer = 0;
		let isInView = true;
		let userPaused = false;
		let drag = null;
		let suppressClick = false;
		let isAnimating = false;
		let activeAnimation = null;
		let activeClone = null;
		let transitionToken = 0;

		if (!track || !stage || !stageImage || !copy || !title || !link || !slides.length) return;

		function circularPosition(index) {
			return (index - activeIndex + slides.length) % slides.length;
		}

		function visibleSlots() {
			if (window.matchMedia('(min-width: 80rem)').matches) return 4;
			if (window.matchMedia('(min-width: 64rem)').matches) return 4;
			if (window.matchMedia('(min-width: 48rem)').matches) return 3;
			return 2;
		}

		function clearAuto() {
			window.clearTimeout(autoTimer);
			autoTimer = 0;
		}

		function canAutoAdvance() {
			return !reducedMotion.matches
				&& !userPaused
				&& !pauseReasons.size
				&& isInView
				&& document.visibilityState === 'visible'
				&& slides.length > 1;
		}

		function scheduleAuto() {
			clearAuto();
			if (!canAutoAdvance()) return;

			autoTimer = window.setTimeout(() => {
				goTo(activeIndex + 1, false);
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

		function copyImageAttributes(source, destination) {
			['src', 'srcset', 'alt', 'width', 'height'].forEach((attribute) => {
				const value = source.getAttribute(attribute);
				if (value) destination.setAttribute(attribute, value);
				else destination.removeAttribute(attribute);
			});
			destination.setAttribute('sizes', '100vw');
			destination.setAttribute('loading', 'eager');
			destination.setAttribute('decoding', 'async');
			destination.setAttribute('draggable', 'false');
		}

		function preloadNext() {
			if (slides.length < 2) return;
			const upcoming = slides[(activeIndex + 1) % slides.length].querySelector('img');
			if (!upcoming) return;

			const preload = new Image();
			if (upcoming.srcset) preload.srcset = upcoming.srcset;
			preload.sizes = '100vw';
			preload.src = upcoming.src;
			const decodePromise = preload.decode?.();
			decodePromise?.catch(() => {});
		}

		function updateStatus(announce = false) {
			const selected = slides[activeIndex];
			const selectedTitle = selected.dataset.hksTourTitle || `Tour ${activeIndex + 1}`;

			if (status) status.textContent = `${activeIndex + 1} / ${slides.length}`;
			if (announce && announcer) {
				announcer.textContent = `Showing ${selectedTitle}, featured tour ${activeIndex + 1} of ${slides.length}.`;
			}
		}

		function render(announce = false) {
			const slots = visibleSlots();

			slides.forEach((slide, index) => {
				const position = circularPosition(index);
				const isActive = 0 === position;
				const isVisible = !isActive && position <= slots;

				slide.dataset.hksPosition = String(position);
				slide.classList.toggle('is-active', isActive);
				slide.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
				slide.tabIndex = isVisible ? 0 : -1;
			});

			gallery.classList.toggle('is-static', slides.length < 2);
			updateStatus(announce);
			preloadNext();
		}

		function cleanupTransition() {
			if (!isAnimating && !activeAnimation && !activeClone) {
				gallery.classList.remove('is-changing');
				return;
			}

			transitionToken += 1;
			activeAnimation?.cancel();
			activeAnimation = null;
			activeClone?.remove();
			activeClone = null;
			isAnimating = false;
			gallery.classList.remove('is-changing');
		}

		function updateActiveContent(selected) {
			const selectedImage = selected.querySelector('img');
			if (selectedImage) copyImageAttributes(selectedImage, stageImage);
			if (eyebrow) eyebrow.textContent = selected.dataset.hksTourEyebrow || 'Featured tour';
			title.textContent = selected.dataset.hksTourTitle || '';
			link.href = selected.dataset.hksTourUrl || '#';
		}

		function animateSelection(selected) {
			const selectedImage = selected.querySelector('img');
			if (reducedMotion.matches || !selectedImage || typeof selectedImage.animate !== 'function') {
				updateActiveContent(selected);
				return;
			}

			cleanupTransition();
			const token = transitionToken;
			const sourceRect = selected.getBoundingClientRect();
			const targetRect = stage.getBoundingClientRect();

			if (!sourceRect.width || !sourceRect.height || !targetRect.width || !targetRect.height) {
				updateActiveContent(selected);
				return;
			}

			const clone = selectedImage.cloneNode(true);
			clone.className = 'hks-home-gallery__transition-image';
			clone.removeAttribute('loading');
			clone.setAttribute('aria-hidden', 'true');
			clone.style.left = `${sourceRect.left}px`;
			clone.style.top = `${sourceRect.top}px`;
			clone.style.width = `${sourceRect.width}px`;
			clone.style.height = `${sourceRect.height}px`;
			document.body.appendChild(clone);
			activeClone = clone;
			isAnimating = true;
			gallery.classList.add('is-changing');
			updateActiveContent(selected);

			const deltaX = targetRect.left - sourceRect.left;
			const deltaY = targetRect.top - sourceRect.top;
			const scaleX = targetRect.width / sourceRect.width;
			const scaleY = targetRect.height / sourceRect.height;

			activeAnimation = clone.animate(
				[
					{ borderRadius: '12px', opacity: 1, transform: 'translate3d(0, 0, 0) scale(1, 1)' },
					{ borderRadius: '0', opacity: 0.96, offset: 0.78, transform: `translate3d(${deltaX}px, ${deltaY}px, 0) scale(${scaleX}, ${scaleY})` },
					{ borderRadius: '0', opacity: 0, transform: `translate3d(${deltaX}px, ${deltaY}px, 0) scale(${scaleX}, ${scaleY})` },
				],
				{ duration: 600, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', fill: 'forwards' }
			);

			activeAnimation.finished
				.catch(() => {})
				.finally(() => {
					if (token !== transitionToken) return;
					activeAnimation = null;
					activeClone?.remove();
					activeClone = null;
					isAnimating = false;
					gallery.classList.remove('is-changing');
				});
		}

		function goTo(index, announce = false) {
			const nextIndex = (index + slides.length) % slides.length;
			if (nextIndex === activeIndex && !announce) {
				scheduleAuto();
				return;
			}

			const selected = slides[nextIndex];
			animateSelection(selected);
			activeIndex = nextIndex;
			render(announce);
			scheduleAuto();
		}

		function showPrevious() {
			goTo(activeIndex - 1, true);
		}

		function showNext() {
			goTo(activeIndex + 1, true);
		}

		function updatePauseControl() {
			if (!pauseButton) return;
			pauseButton.hidden = reducedMotion.matches || slides.length < 2;
			pauseButton.setAttribute('aria-pressed', userPaused ? 'true' : 'false');
			pauseButton.setAttribute('aria-label', userPaused ? 'Resume featured tour rotation' : 'Pause featured tour rotation');
			if (pauseIcon) pauseIcon.textContent = userPaused ? '\u25B6' : '\u2161';
		}

		previous?.addEventListener('click', showPrevious);
		next?.addEventListener('click', showNext);
		pauseButton?.addEventListener('click', () => {
			userPaused = !userPaused;

			if (!userPaused) {
				pauseReasons.delete('focus');
			}

			updatePauseControl();
			scheduleAuto();
		});

		slides.forEach((slide, index) => {
			slide.addEventListener('click', () => {
				if (suppressClick) return;
				goTo(index, true);
				if (document.activeElement === slide) track.focus({ preventScroll: true });
			});
		});

		track.addEventListener('keydown', (event) => {
			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				showPrevious();
			} else if (event.key === 'ArrowRight') {
				event.preventDefault();
				showNext();
			} else if (event.key === 'Home') {
				event.preventDefault();
				goTo(0, true);
			} else if (event.key === 'End') {
				event.preventDefault();
				goTo(slides.length - 1, true);
			}
		});

		gallery.addEventListener('mouseenter', () => pause('hover'));
		gallery.addEventListener('mouseleave', () => resume('hover'));
		gallery.addEventListener('focusin', () => pause('focus'));
		gallery.addEventListener('focusout', (event) => {
			if (!gallery.contains(event.relatedTarget)) resume('focus');
		});

		track.addEventListener('pointerdown', (event) => {
			if ('mouse' === event.pointerType && 0 !== event.button) return;

			pause('pointer');
			drag = {
				id: event.pointerId,
				startX: event.clientX,
				startY: event.clientY,
				lastX: event.clientX,
				lastTime: event.timeStamp,
				distance: 0,
				velocity: 0,
				moved: false,
				captured: false,
			};
		});

		track.addEventListener('pointermove', (event) => {
			if (!drag || drag.id !== event.pointerId) return;

			const distanceX = event.clientX - drag.startX;
			const distanceY = event.clientY - drag.startY;

			if (!drag.moved && Math.abs(distanceX) > 12 && Math.abs(distanceX) > Math.abs(distanceY)) {
				drag.moved = true;
				track.classList.add('is-dragging');
				track.setPointerCapture(event.pointerId);
				drag.captured = true;
			}

			if (!drag.moved) return;

			const elapsed = Math.max(1, event.timeStamp - drag.lastTime);
			drag.velocity = (event.clientX - drag.lastX) / elapsed;
			drag.lastX = event.clientX;
			drag.lastTime = event.timeStamp;
			drag.distance = distanceX;
			gallery.style.setProperty('--hks-gallery-drag', `${Math.max(-56, Math.min(56, distanceX * 0.28))}px`);
			event.preventDefault();
		});

		function endPointer(event) {
			if (!drag || drag.id !== event.pointerId) {
				resume('pointer');
				return;
			}

			const completedDrag = drag.moved;
			const projectedDistance = drag.distance + (drag.velocity * 180);
			suppressClick = completedDrag;
			track.classList.remove('is-dragging');
			gallery.style.removeProperty('--hks-gallery-drag');
			if (drag.captured && track.hasPointerCapture(event.pointerId)) track.releasePointerCapture(event.pointerId);
			drag = null;

			if ('pointercancel' !== event.type && Math.abs(projectedDistance) >= 40) {
				goTo(projectedDistance < 0 ? activeIndex + 1 : activeIndex - 1, true);
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

		reducedMotion.addEventListener?.('change', () => {
			cleanupTransition();
			updatePauseControl();
			scheduleAuto();
		});
		window.addEventListener('resize', () => render());

		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries) => {
				isInView = Boolean(entries[0]?.isIntersecting);
				scheduleAuto();
			}, { threshold: 0.25 });
			observer.observe(gallery);
		}

		gallery.classList.add('is-ready');
		render();
		updatePauseControl();
		scheduleAuto();
	});
}());
