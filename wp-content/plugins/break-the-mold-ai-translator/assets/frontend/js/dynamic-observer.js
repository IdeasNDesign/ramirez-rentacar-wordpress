/**
 * BTM Dynamic Observer — MutationObserver for dynamic page elements.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */
(function() {
	'use strict';

	let observer = null;

	function translateTree(node) {
		if (!window.btmatTranslator || !window.btmatTranslator.dictionary) return;

		// Skip script, style tags
		if (node.nodeType === Node.ELEMENT_NODE) {
			const tag = node.tagName.toLowerCase();
			if (['script', 'style', 'code', 'pre', 'textarea'].includes(tag)) {
				return;
			}
			
			// Walk children
			node.childNodes.forEach(translateTree);
		} else if (node.nodeType === Node.TEXT_NODE) {
			window.btmatTranslator.translateNode(node);
		}
	}

	function handleMutations(mutations) {
		if (!observer) return;
		
		// Temporarily disconnect observer to prevent infinite loops
		observer.disconnect();

		mutations.forEach(function(mutation) {
			mutation.addedNodes.forEach(function(node) {
				translateTree(node);
			});
		});

		// Reconnect
		connect();
	}

	function connect() {
		if (!observer) {
			observer = new MutationObserver(handleMutations);
		}
		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	}

	document.addEventListener('DOMContentLoaded', function() {
		// Only run if the active language is not the base language
		if (window.btmatConfig && window.btmatConfig.currentLang !== 'es') {
			connect();
		}
	});
})();
