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
		const details = gallery.querySelector('[data-hks-home-gallery-details]');
		const price = gallery.querySelector('[data-hks-home-gallery-price]');
		const route = gallery.querySelector('[data-hks-home-gallery-route]');
		const included = gallery.querySelector('[data-hks-home-gallery-included]');
		const previous = gallery.querySelector('[data-hks-home-gallery-prev]');
		const next = gallery.querySelector('[data-hks-home-gallery-next]');
		const pauseButton = gallery.querySelector('[data-hks-home-gallery-pause]');
		const pauseIcon = gallery.querySelector('[data-hks-home-gallery-pause-icon]');
		const progress = gallery.querySelector('[data-hks-home-gallery-progress]');
		const status = gallery.querySelector('[data-hks-home-gallery-status]');
		const announcer = gallery.querySelector('[data-hks-home-gallery-announcer]');
		const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		const interval = Math.max(5000, Number(gallery.dataset.hksGalleryInterval) || 5000);
		const pauseReasons = new Set();
		let activeIndex = 0;
		let pendingIndex = null;
		let autoTimer = 0;
		let progressAnimation = null;
		let isInView = true;
		let userPaused = false;
		let drag = null;
		let suppressClick = false;
		let isAnimating = false;
		let activeAnimation = null;
		let activeClone = null;
		let transitionSwapTimer = 0;
		let transitionToken = 0;

		if (!track || !stage || !stageImage || !copy || !title || !link || !slides.length) return;

		function circularPosition(index) {
			return (index - activeIndex + slides.length) % slides.length;
		}

		function visibleSlots() {
			if (window.matchMedia('(min-width: 64rem)').matches) return 4;
			if (window.matchMedia('(min-width: 48rem)').matches) return 3;
			return 2;
		}

		function clearProgress() {
			progressAnimation?.cancel();
			progressAnimation = null;
			if (progress) progress.style.transform = 'scaleX(0)';
		}

		function clearAuto() {
			window.clearTimeout(autoTimer);
			autoTimer = 0;
			clearProgress();
		}

		function canAutoAdvance() {
			return !reducedMotion.matches
				&& !userPaused
				&& !pauseReasons.size
				&& isInView
				&& document.visibilityState === 'visible'
				&& slides.length > 1;
		}

		function startProgress() {
			if (!progress || typeof progress.animate !== 'function') return;

			progressAnimation = progress.animate(
				[
					{ transform: 'scaleX(0)' },
					{ transform: 'scaleX(1)' },
				],
				{ duration: interval, easing: 'linear', fill: 'forwards' }
			);
			progressAnimation.finished.catch(() => {});
		}

		function scheduleAuto() {
			clearAuto();
			if (!canAutoAdvance()) return;

			startProgress();
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
			['src', 'srcset', 'width', 'height'].forEach((attribute) => {
				const value = source.getAttribute(attribute);
				if (value) destination.setAttribute(attribute, value);
				else destination.removeAttribute(attribute);
			});
			destination.setAttribute('alt', '');
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
			const selectedTitle = selected.dataset.hksTourLabel || selected.dataset.hksTourTitle || `Tour ${activeIndex + 1}`;

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
			transitionToken += 1;
			window.clearTimeout(transitionSwapTimer);
			transitionSwapTimer = 0;
			activeAnimation?.cancel();
			activeAnimation = null;
			activeClone?.remove();
			activeClone = null;
			pendingIndex = null;
			isAnimating = false;
			gallery.classList.remove('is-changing');
		}

		function updateActiveContent(selected) {
			const selectedImage = selected.querySelector('img');
			if (selectedImage) copyImageAttributes(selectedImage, stageImage);
			if (eyebrow) eyebrow.textContent = selected.dataset.hksTourEyebrow || 'Featured tour';
			title.textContent = selected.dataset.hksTourTitle || '';
			link.href = selected.dataset.hksTourUrl || '#';
			copy.classList.toggle('has-long-title', 'true' === selected.dataset.hksTourLongTitle);
			updateDetail(price, selected.dataset.hksTourPrice || '');
			updateDetail(route, selected.dataset.hksTourRoute || '');
			updateDetail(included, selected.dataset.hksTourIncluded || '');

			if (details) {
				details.hidden = ![price, route, included].some((detail) => detail && !detail.closest('[data-hks-home-gallery-detail-item]')?.hidden);
			}
		}

		function updateDetail(detail, value) {
			if (!detail) return;

			const item = detail.closest('[data-hks-home-gallery-detail-item]');
			detail.textContent = value;
			if (item) item.hidden = !value;
		}

		function commitSelection(selected, nextIndex, announce) {
			updateActiveContent(selected);
			activeIndex = nextIndex;
			pendingIndex = null;
			render(announce);
		}

		function clipPathForSource(sourceRect, targetRect) {
			const top = Math.max(0, sourceRect.top - targetRect.top);
			const right = Math.max(0, targetRect.right - sourceRect.right);
			const bottom = Math.max(0, targetRect.bottom - sourceRect.bottom);
			const left = Math.max(0, sourceRect.left - targetRect.left);

			return `inset(${top}px ${right}px ${bottom}px ${left}px round 12px)`;
		}

		function animateSelection(selected, nextIndex, announce) {
			const selectedImage = selected.querySelector('img');
			if (reducedMotion.matches || !selectedImage || typeof selectedImage.animate !== 'function') {
				cleanupTransition();
				commitSelection(selected, nextIndex, announce);
				return;
			}

			cleanupTransition();
			const token = ++transitionToken;
			const sourceRect = selected.getBoundingClientRect();
			const targetRect = stage.getBoundingClientRect();

			if (!sourceRect.width || !sourceRect.height || !targetRect.width || !targetRect.height) {
				commitSelection(selected, nextIndex, announce);
				return;
			}

			const clone = document.createElement('div');
			const cloneImage = selectedImage.cloneNode(true);
			clone.className = 'hks-home-gallery__transition-image';
			clone.setAttribute('aria-hidden', 'true');
			clone.style.left = `${targetRect.left}px`;
			clone.style.top = `${targetRect.top}px`;
			clone.style.width = `${targetRect.width}px`;
			clone.style.height = `${targetRect.height}px`;
			cloneImage.removeAttribute('loading');
			cloneImage.setAttribute('alt', '');
			clone.appendChild(cloneImage);
			document.body.appendChild(clone);

			activeClone = clone;
			pendingIndex = nextIndex;
			isAnimating = true;
			gallery.classList.add('is-changing');

			const initialClip = clipPathForSource(sourceRect, targetRect);
			activeAnimation = clone.animate(
				[
					{ clipPath: initialClip, opacity: 0.15 },
					{ clipPath: initialClip, opacity: 1, offset: 0.08 },
					{ clipPath: 'inset(0px 0px 0px 0px round 0px)', opacity: 1, offset: 0.82 },
					{ clipPath: 'inset(0px 0px 0px 0px round 0px)', opacity: 0 },
				],
				{ duration: 650, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', fill: 'forwards' }
			);

			let committed = false;
			const commit = () => {
				if (committed || token !== transitionToken) return;
				committed = true;
				commitSelection(selected, nextIndex, announce);
			};

			transitionSwapTimer = window.setTimeout(commit, 455);
			activeAnimation.finished
				.catch(() => {})
				.finally(() => {
					if (token !== transitionToken) return;
					commit();
					window.clearTimeout(transitionSwapTimer);
					transitionSwapTimer = 0;
					activeAnimation = null;
					activeClone?.remove();
					activeClone = null;
					isAnimating = false;
					gallery.classList.remove('is-changing');
				});
		}

		function goTo(index, announce = false) {
			const nextIndex = (index + slides.length) % slides.length;
			if (nextIndex === activeIndex && !isAnimating) {
				if (announce) updateStatus(true);
				scheduleAuto();
				return;
			}

			animateSelection(slides[nextIndex], nextIndex, announce);
			scheduleAuto();
		}

		function showPrevious() {
			const baseIndex = null === pendingIndex ? activeIndex : pendingIndex;
			goTo(baseIndex - 1, true);
		}

		function showNext() {
			const baseIndex = null === pendingIndex ? activeIndex : pendingIndex;
			goTo(baseIndex + 1, true);
		}

		function updatePauseControl() {
			if (!pauseButton) return;
			pauseButton.hidden = reducedMotion.matches || slides.length < 2;
			pauseButton.setAttribute('aria-pressed', userPaused ? 'true' : 'false');
			pauseButton.setAttribute('aria-label', userPaused ? 'Resume featured tour rotation' : 'Pause featured tour rotation');
			if (pauseIcon) pauseIcon.textContent = userPaused ? '\u25B6' : '\u23F8';
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
