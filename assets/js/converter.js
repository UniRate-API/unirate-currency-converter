/* UniRate Currency Converter — frontend widget */
( function () {
	'use strict';

	function initWidget( el ) {
		var restUrl = el.dataset.restUrl;
		var form    = el.querySelector( '.unirate-widget__form' );
		var result  = el.querySelector( '.unirate-widget__result' );

		if ( ! form || ! result || ! restUrl ) {
			return;
		}

		var amountInput = form.querySelector( '.unirate-widget__amount' );
		var fromInput   = form.querySelector( '.unirate-widget__from' );
		var toInput     = form.querySelector( '.unirate-widget__to' );

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();

			var from   = fromInput.value.trim().toUpperCase();
			var to     = toInput.value.trim().toUpperCase();
			var amount = parseFloat( amountInput.value ) || 1;

			if ( ! from || ! to ) {
				return;
			}

			result.textContent = '…'; // ellipsis while loading

			var url = restUrl + '?from=' + encodeURIComponent( from ) +
				'&to=' + encodeURIComponent( to ) +
				'&amount=' + encodeURIComponent( amount );

			fetch( url )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) {
					if ( data.result !== undefined ) {
						result.textContent = amount + ' ' + from + ' = ' +
							parseFloat( data.result ).toFixed( 2 ) + ' ' + to;
					} else {
						result.textContent = data.error || 'Error fetching rate.';
					}
				} )
				.catch( function () {
					result.textContent = 'Network error.';
				} );
		} );
	}

	function init() {
		document.querySelectorAll( '.unirate-widget' ).forEach( initWidget );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
