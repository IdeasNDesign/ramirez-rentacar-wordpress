/**
 * BTM Translator — client-side translation application.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */
(function() {
	'use strict';

	window.btmatTranslator = {
		dictionary: {},

		init: function() {
			if (!window.btmatConfig) return;
			
			// Load dictionary if available
			this.dictionary = window.btmatConfig.dictionary || {};
			
			// Remove loading state on body
			document.documentElement.classList.remove('btm-language-loading');
		},

		translateNode: function(node) {
			if (node.nodeType === Node.TEXT_NODE) {
				const val = node.nodeValue.trim();
				if (this.dictionary[val]) {
					node.nodeValue = this.dictionary[val];
				}
			}
		}
	};

	document.addEventListener('DOMContentLoaded', function() {
		window.btmatTranslator.init();
	});
})();
