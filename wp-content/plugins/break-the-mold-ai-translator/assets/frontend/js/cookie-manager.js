/**
 * Cookie Manager — JS Engine
 * Author: Break The Mold
 */

(function($) {
	'use strict';

	const COOKIE_NAME = 'rrc_consent_settings';
	const COOKIE_EXPIRY_DAYS = 365;

	// Cookie Helper Functions
	function setCookie(name, value, days) {
		let expires = '';
		if (days) {
			const date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = '; expires=' + date.toUTCString();
		}
		document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
	}

	function getCookie(name) {
		const nameEQ = name + '=';
		const ca = document.cookie.split(';');
		for (let i = 0; i < ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) === ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}

	// Load Saved Consent Settings
	function getConsentSettings() {
		const saved = getCookie(COOKIE_NAME);
		if (saved) {
			try {
				return JSON.parse(saved);
			} catch (e) {
				return null;
			}
		}
		return null;
	}

	// Apply Consent Mode and Block/Unblock script categories
	function applyConsent(settings) {
		if (!settings) return;

		// Sincronizar Google Consent Mode si es necesario
		if (typeof gtag === 'function') {
			gtag('consent', 'update', {
				'analytics_storage': settings.analytics ? 'granted' : 'denied',
				'ad_storage': settings.marketing ? 'granted' : 'denied',
				'personalization_storage': settings.functional ? 'granted' : 'denied'
			});
		}

		// Trigger custom events for other scripts to hook into
		$(document).trigger('rrc_cookie_consent_updated', [settings]);
	}

	// Initialize UI
	function init() {
		const settings = getConsentSettings();
		const banner = $('#rrc-cookie-consent-banner');

		// 1. Show banner if no consent is saved
		if (!settings) {
			banner.fadeIn(400);
		} else {
			applyConsent(settings);
			// Update page switches if they exist
			$('#rrc-pref-functional').prop('checked', !!settings.functional);
			$('#rrc-pref-analytics').prop('checked', !!settings.analytics);
			$('#rrc-pref-marketing').prop('checked', !!settings.marketing);
		}

		// Inject Configurar cookies link dynamically in the footer
		const footerTarget = $('footer, .site-footer, .footer, .elementor-location-footer, #colophon');
		if (footerTarget.length && !$('#rrc-footer-config-cookies-link').length) {
			const copyright = footerTarget.find('p:contains("©"), span:contains("©"), div:contains("©"), p:contains("Ramirez"), p:contains("Ramírez"), .copyright, .footer-copyright-text').first();
			if (copyright.length) {
				copyright.append(' | <a href="#" id="rrc-footer-config-cookies-link" style="color:inherit; text-decoration:underline; font-size:inherit; margin-left: 10px;">Configurar cookies</a>');
			} else {
				footerTarget.append('<div style="text-align:center; padding:15px; font-size:12px; opacity:0.8;"><a href="#" id="rrc-footer-config-cookies-link" style="color:inherit; text-decoration:underline;">Configurar cookies</a></div>');
			}
		}

		// ── Banner Button Event Handlers ──────────────────────

		// Accept All (from banner or page panel)
		$('#rrc-banner-accept-all, #rrc-accept-all-pref-btn').on('click', function(e) {
			e.preventDefault();
			const newSettings = { functional: true, analytics: true, marketing: true };
			setCookie(COOKIE_NAME, JSON.stringify(newSettings), COOKIE_EXPIRY_DAYS);
			applyConsent(newSettings);
			banner.fadeOut(300);

			// Update switches on page
			$('#rrc-pref-functional, #rrc-pref-analytics, #rrc-pref-marketing').prop('checked', true);

			alertUser('Preferencias guardadas: Has aceptado todas las cookies.');
		});

		// Reject All non-essential
		$('#rrc-banner-reject-all, #rrc-reject-all-pref-btn').on('click', function(e) {
			e.preventDefault();
			const newSettings = { functional: false, analytics: false, marketing: false };
			setCookie(COOKIE_NAME, JSON.stringify(newSettings), COOKIE_EXPIRY_DAYS);
			applyConsent(newSettings);
			banner.fadeOut(300);

			// Update switches on page
			$('#rrc-pref-functional, #rrc-pref-analytics, #rrc-pref-marketing').prop('checked', false);

			alertUser('Preferencias guardadas: Has rechazado las cookies opcionales.');
		});

		// Save Preferences Custom selection (from policy page panel)
		$('#rrc-save-preferences-btn').on('click', function(e) {
			e.preventDefault();
			const newSettings = {
				functional: $('#rrc-pref-functional').is(':checked'),
				analytics: $('#rrc-pref-analytics').is(':checked'),
				marketing: $('#rrc-pref-marketing').is(':checked')
			};
			setCookie(COOKIE_NAME, JSON.stringify(newSettings), COOKIE_EXPIRY_DAYS);
			applyConsent(newSettings);
			banner.fadeOut(300);

			alertUser('Preferencias personalizadas guardadas con éxito.');
		});

		// Scroll to panel / Open panel button
		$('#rrc-open-panel-btn').on('click', function(e) {
			e.preventDefault();
			const panel = $('#rrc-cookie-preference-panel');
			if (panel.length) {
				$('html, body').animate({
					scrollTop: panel.offset().top - 120
				}, 600);
			}
		});

		// Banner "Personalizar" button
		$('#rrc-banner-settings').on('click', function(e) {
			e.preventDefault();
			const panel = $('#rrc-cookie-preference-panel');
			if (panel.length) {
				banner.fadeOut(200);
				$('html, body').animate({
					scrollTop: panel.offset().top - 120
				}, 600);
			} else {
				// Redirect to Policy Page
				window.location.href = rrcCookieConfig.policyUrl;
			}
		});

		// Configure cookies trigger link in footer (global)
		$(document).on('click', '#rrc-footer-config-cookies-link, .rrc-config-cookies-trigger', function(e) {
			e.preventDefault();
			const panel = $('#rrc-cookie-preference-panel');
			if (panel.length) {
				$('html, body').animate({
					scrollTop: panel.offset().top - 120
				}, 600);
			} else {
				// Show banner again
				banner.fadeIn(300);
			}
		});
	}

	// Helper to display a subtle premium alert notification
	function alertUser(msg) {
		const alertBox = $('<div class="rrc-cookie-toast">' + msg + '</div>');
		$('body').append(alertBox);
		alertBox.css({
			position: 'fixed',
			bottom: '40px',
			right: '40px',
			background: '#1A202C',
			color: '#FFFFFF',
			padding: '12px 24px',
			borderRadius: '30px',
			fontSize: '14px',
			fontWeight: '600',
			boxShadow: '0 10px 25px rgba(0,0,0,0.15)',
			zIndex: 9999999,
			opacity: 0,
			transform: 'translateY(20px)',
			transition: 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)'
		});
		
		setTimeout(() => {
			alertBox.css({ opacity: 1, transform: 'translateY(0)' });
		}, 100);

		setTimeout(() => {
			alertBox.css({ opacity: 0, transform: 'translateY(20px)' });
			setTimeout(() => alertBox.remove(), 300);
		}, 3000);
	}

	// DOM ready
	$(document).ready(init);

})(jQuery);
