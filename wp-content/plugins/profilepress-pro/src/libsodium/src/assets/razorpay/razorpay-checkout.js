/* global ppress_razorpay_vars */
/* global ppressCheckoutForm */
/* global pp_ajax_form */
(function ($) {

    function PPressRazorpay() {

        var _this = this;

        this.init = function () {

            $(document).on('ppress_updated_checkout', function () {
                $('form#ppress_mb_checkout_form').on('ppress_process_checkout_razorpay', _this.processCheckout);
            });
        };

        this.clean_obj = function clean(obj) {
            var propNames = Object.getOwnPropertyNames(obj);

            for (var i = 0; i < propNames.length; i++) {
                var propName = propNames[i];
                if (obj[propName] === null || obj[propName] === undefined || obj[propName] === '') {
                    delete obj[propName];
                }
            }

            return obj;
        };

        this.processCheckout = function (e, response, payment_method) {

            if (window.razorProcessCheckoutFlag !== 'true' && ppressCheckoutForm.is_var_defined(response.gateway_response) === true) {

                window.razorProcessCheckoutFlag = 'true';

                window.razorpay_order_success_url = response.order_success_url;

                var gateway_response = response.gateway_response;

                var options = {
                    "key": ppressCheckoutForm.get_obj_val(gateway_response.key_id, ''),
                    "amount": ppressCheckoutForm.get_obj_val(gateway_response.amount, ''),
                    "currency": ppressCheckoutForm.get_obj_val(gateway_response.currency, ''),
                    "name": ppressCheckoutForm.get_obj_val(gateway_response.business_name, ''),
                    "description": ppressCheckoutForm.get_obj_val(gateway_response.description, ''),
                    "image": ppressCheckoutForm.get_obj_val(gateway_response.image, ''),
                    "handler": function (response) {
                        $(document.body).trigger('ppress_checkout_success', [response, payment_method]);
                        window.location.assign(window.razorpay_order_success_url);
                    },
                    "modal": {
                        'animation': false,
                        "ondismiss": function () {
                            ppressCheckoutForm.remove_spinner();
                        }
                    },
                    "prefill": {
                        "name": ppressCheckoutForm.get_obj_val(gateway_response.customer_name, ''),
                        "email": ppressCheckoutForm.get_obj_val(gateway_response.customer_email, ''),
                        "contact": ppressCheckoutForm.get_obj_val(gateway_response.customer_phone, '')
                    },
                    "theme": {
                        "color": ppressCheckoutForm.get_obj_val(gateway_response.theme_color, '')
                    },
                    "notes": ppressCheckoutForm.get_obj_val(gateway_response.notes, {})
                };

                if (ppressCheckoutForm.is_var_defined(gateway_response.subscription_id)) {
                    options['subscription_id'] = gateway_response.subscription_id;
                } else {
                    options['order_id'] = ppressCheckoutForm.get_obj_val(gateway_response.order_id, '');
                }

                var rzp1 = new Razorpay(_this.clean_obj(options));

                rzp1.on('payment.failed', function (_response) {
                    window.razorProcessCheckoutFlag = false;
                    ppressCheckoutForm.removeAllAlerts();
                    ppressCheckoutForm.createAlertMessage(_response.error.description);
                });

                ppressCheckoutForm.removeAllAlerts();

                rzp1.open();

                return false;
            }
        };
    }

    (new PPressRazorpay()).init();

})(jQuery);