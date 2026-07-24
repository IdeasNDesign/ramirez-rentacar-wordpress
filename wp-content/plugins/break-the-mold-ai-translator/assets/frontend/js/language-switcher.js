/**
 * BTM Language Switcher — handles language change interactions.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */
(function () {
	'use strict';

	const COOKIE_NAME = 'BTMAT_LANGUAGE';
	const LS_KEY      = 'btmat_language';

	/**
	 * Set a cookie.
	 */
	function setCookie(name, value, days) {
		const secure = location.protocol === 'https:' ? ';Secure' : '';
		if (days > 0) {
			const d = new Date();
			d.setTime(d.getTime() + days * 86400000);
			document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax' + secure;
		} else {
			document.cookie = name + '=' + value + ';path=/;SameSite=Lax' + secure;
		}
	}

	/**
	 * Get a cookie value.
	 */
	function getCookie(name) {
		const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
		return v ? v.pop() : null;
	}

	/**
	 * Switch language.
	 */
	function switchLanguage(lang) {
		if (lang !== 'es' && lang !== 'en') return;

		let cookieDays = parseInt(window.btmatConfig?.cookieDuration, 10);
		if (isNaN(cookieDays) || cookieDays <= 0) {
			cookieDays = 365; // Default fallback to 365 days to ensure persistence
		}
		setCookie(COOKIE_NAME, lang, cookieDays);

		try { localStorage.setItem(LS_KEY, lang); } catch (e) { /* silent */ }

		// Update all switcher UI states.
		document.querySelectorAll('.btm-lang-btn').forEach(function (btn) {
			const isActive = btn.getAttribute('data-btm-lang') === lang;
			btn.classList.toggle('active', isActive);
			btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});

		document.querySelectorAll('.btm-lang-select').forEach(function (sel) {
			sel.value = lang;
		});

		// Reload the page to apply server-side translation, forcing cache bypass by appending a timestamp
		const url = new URL(window.location.href);
		url.searchParams.set('lang', lang);
		url.searchParams.set('refresh', Date.now());
		window.location.href = url.toString();
	}

	/**
	 * Initialize all switchers on the page.
	 */
	function init() {
		// Clean up the URL query parameters so the user has a clean URL in the browser bar
		if (window.history.replaceState && (window.location.search.includes('lang=') || window.location.search.includes('refresh='))) {
			const cleanUrl = window.location.pathname + window.location.hash;
			window.history.replaceState({}, document.title, cleanUrl);
		}

		// Open Tours Roatán link in a new tab
		document.querySelectorAll('a').forEach(function (link) {
			const text = link.textContent.trim().toLowerCase();
			if (text.includes('tours roatán') || text.includes('tours roatan') || text.includes('roatan tours') || link.href.includes('/tours') || link.href.includes('tours')) {
				if (text.includes('tour') || link.href.includes('tour')) {
					link.setAttribute('target', '_blank');
					link.setAttribute('rel', 'noopener noreferrer');
				}
			}
		});

		// Button clicks
		document.querySelectorAll('[data-btm-lang]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				switchLanguage(this.getAttribute('data-btm-lang'));
			});
		});

		// Dropdown changes
		document.querySelectorAll('[data-btm-lang-select]').forEach(function (sel) {
			sel.addEventListener('change', function () {
				switchLanguage(this.value);
			});
		});
	}

	// Run on DOMContentLoaded
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
