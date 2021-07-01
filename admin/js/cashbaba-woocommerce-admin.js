(function( $ ) {
	'use strict';

	'use strict';
	/**
	 * Object to handle PayPal admin functions.
	 */
	var wc_cashbaba_admin = {
		isTestMode: function () {
			return $('#woocommerce_cashbaba_testmode').is(':checked');
		},

		/**
		 * Initialize.
		 */
		init: function () {
			$(document.body).on('change', '#woocommerce_cashbaba_testmode', function () {
				var test_client_id = $('#woocommerce_cashbaba_test_client_id').parents('tr').eq(0),
					test_client_secret = $('#woocommerce_cashbaba_test_client_secret').parents('tr').eq(0),
					test_merchant_id = $('#woocommerce_cashbaba_test_merchant_id').parents('tr').eq(0),
					test_customer_id = $('#woocommerce_cashbaba_test_customer_id').parents('tr').eq(0),
					live_client_id = $('#woocommerce_cashbaba_live_client_id').parents('tr').eq(0),
					live_client_secret = $('#woocommerce_cashbaba_live_client_secret').parents('tr').eq(0),
					live_merchant_id = $('#woocommerce_cashbaba_live_merchant_id').parents('tr').eq(0),
					live_customer_id = $('#woocommerce_cashbaba_live_customer_id').parents('tr').eq(0);


				if ($(this).is(':checked')) {
					test_client_id.show();
					test_client_secret.show();
					test_merchant_id.show();
					test_customer_id.show();
					live_client_id.hide();
					live_client_secret.hide();
					live_merchant_id.hide();
					live_customer_id.hide();

				} else {
					test_client_id.hide();
					test_client_secret.hide();
					test_merchant_id.hide();
					test_customer_id.hide();
					live_client_id.show();
					live_client_secret.show();
					live_merchant_id.show();
					live_customer_id.show();
				}
			});

			$('#woocommerce_cashbaba_testmode').trigger('change');
		}
	};

	wc_cashbaba_admin.init();

})( jQuery );
