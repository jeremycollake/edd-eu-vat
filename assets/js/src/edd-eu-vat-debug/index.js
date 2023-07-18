(function (window, document, $, undefined) {
	'use strict';

	const EUVATDebug = {};

	/**
	 * Initialize the script.
	 */
	EUVATDebug.init = function () {
		updatePaymentNote()
	};

	function updatePaymentNote() {

		const loggedData = localStorage.getItem('euVatLogger')

		if (loggedData === null || loggedData === undefined) {
			return;
		}

		const paymentID = document.getElementById("eddeuvat-order-debug").dataset?.orderId ?? false

		var postData = {
			action: 'edd_debug_payment_note',
			logged: loggedData,
			payment_id: paymentID,
			nonce: euvat_debug.nonce
		};

		$.ajax({
				type: 'POST',
				data: postData,
				dataType: 'json',
				url: euvat_debug.ajax,
				xhrFields: {
					withCredentials: true
				}
			})
			.done(function (response, textStatus, jqXHR) {
				// Nothing to do here.
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				console.error(errorThrown)
			})
			.always(function () {
				localStorage.removeItem('euVatLogger');
			});
	}

	EUVATDebug.init();

}(window, document, jQuery));
