(function () {
	'use strict';

	const article = document.querySelector('[data-hks-article-id]');
	if (!article) return;

	const articleDetails = {
		article_id: Number(article.dataset.hksArticleId || 0),
		article_format: article.dataset.hksArticleFormat || 'guide',
		primary_tour_id: Number(article.dataset.hksPrimaryTourId || 0)
	};

	function track(eventName, ctaLocation) {
		const payload = {
			event: eventName,
			event_contract_version: '1.0',
			...articleDetails,
			cta_location: ctaLocation || ''
		};
		if (Array.isArray(window.dataLayer)) window.dataLayer.push(payload);
		document.dispatchEvent(new CustomEvent('hks:analytics', { detail: payload }));
	}

	track('view_article', 'article_view');

	article.querySelectorAll('[data-hks-article-primary-tour-click]').forEach((link) => {
		link.addEventListener('click', () => track('article_primary_tour_click', link.dataset.hksCtaLocation || 'article_content'));
	});

	// A proxy records its real placement before the shared intake trigger handles it.
	document.addEventListener('click', (event) => {
		const proxy = event.target.closest('[data-hks-quote-proxy][data-hks-cta-location]');
		if (!proxy || !article.contains(proxy)) return;
		const inquiry = article.querySelector('[data-hks-inquiry]');
		if (inquiry) inquiry.dataset.ctaLocation = proxy.dataset.hksCtaLocation || 'article_content';
	}, true);

	const mobileBar = article.querySelector('[data-hks-article-mobile-quote]');
	const earlyQuote = article.querySelector('[data-hks-article-early-quote]');
	if (!mobileBar || !earlyQuote) return;

	const mobileViewport = window.matchMedia('(max-width: 48rem)');
	const stopTargets = Array.from(document.querySelectorAll('[data-hks-article-quote-stop], .hks-site-footer'));
	const visibleStops = new Set();
	let earlyQuotePassed = false;

	function updateMobileBar() {
		const show = mobileViewport.matches && earlyQuotePassed && visibleStops.size === 0;
		mobileBar.classList.toggle('is-visible', show);
		mobileBar.setAttribute('aria-hidden', show ? 'false' : 'true');
	}

	if ('IntersectionObserver' in window) {
		const earlyObserver = new IntersectionObserver((entries) => {
			const entry = entries[0];
			earlyQuotePassed = !entry.isIntersecting && entry.boundingClientRect.bottom < 0;
			updateMobileBar();
		}, { threshold: 0 });
		earlyObserver.observe(earlyQuote);

		const stopObserver = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) visibleStops.add(entry.target);
				else visibleStops.delete(entry.target);
			});
			updateMobileBar();
		}, { rootMargin: '0px 0px 80px 0px', threshold: 0 });
		stopTargets.forEach((target) => stopObserver.observe(target));
	} else {
		window.addEventListener('scroll', () => {
			earlyQuotePassed = earlyQuote.getBoundingClientRect().bottom < 0;
			const nearStop = stopTargets.some((target) => target.getBoundingClientRect().top < window.innerHeight);
			visibleStops.clear();
			if (nearStop) visibleStops.add('fallback');
			updateMobileBar();
		}, { passive: true });
	}

	mobileViewport.addEventListener('change', updateMobileBar);
	updateMobileBar();
}());
