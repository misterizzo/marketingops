/* global ppress_paystack_vars */
/* global ppressCheckoutForm */
/* global pp_ajax_form */
(function ($) {

    function PPressPayStack() {

        var _this = this;

        this.init = function () {

            $(document).on('ppress_updated_checkout', function () {
                $('form#ppress_mb_checkout_form').on('ppress_process_checkout_paystack', _this.processCheckout);
            });
        };

        this.processCheckout = function (e, response, payment_method) {

            if (window.paystackProcessCheckoutFlag !== 'true' && ppressCheckoutForm.is_var_defined(response.gateway_response) === true) {

                window.paystackProcessCheckoutFlag = 'true';

                window.paystack_order_success_url = response.order_success_url;

                ppressCheckoutForm.removeAllAlerts();

                var gateway_response = response.gateway_response;

                /** @see https://paystack.com/docs/guides/migrating-from-inlinejs-v1-to-v2 */
                var paystack = new PaystackPop();

                var options = {
                    "key": ppressCheckoutForm.get_obj_val(gateway_response.public_key, ''),
                    "email": ppressCheckoutForm.get_obj_val(gateway_response.email, ''),
                    "amount": ppressCheckoutForm.get_obj_val(gateway_response.amount, ''),
                    "ref": ppressCheckoutForm.get_obj_val(gateway_response.ref, ''),
                    "currency": ppressCheckoutForm.get_obj_val(gateway_response.currency, ''),
                    "metadata": ppressCheckoutForm.get_obj_val(gateway_response.metadata, ''),
                    onCancel: function () {
                        window.paystackProcessCheckoutFlag = false;
                        ppressCheckoutForm.remove_spinner();
                    },
                    onSuccess: function (transaction) {
                        $(document.body).trigger('ppress_checkout_success', [transaction, payment_method]);
                        window.location.assign(window.paystack_order_success_url);
                    }
                };

                if (gateway_response.is_recurring === 'true') {
                    options['channels'] = ['card'];
                }

                paystack.newTransaction(options);

                return false;
            }
        };
    }

    (new PPressPayStack()).init();

})(jQuery);