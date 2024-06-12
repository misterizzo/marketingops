/* global ppressPayPalVars */
/* global ppressCheckoutForm */
/* global pp_ajax_form */
(function ($) {

    function PPressPayPal() {

        let _this = this;

        this.init = function () {

            $(document).on('ppress_updated_checkout', _this.mountPayPalElement);

            $(document).on('ppress_update_checkout', _this.unmountPayPalElement);
        };

        this.mountPayPalElement = function () {

            if ($('#ppress-paypal-button-element').length === 0) return;

            const createFunc = ('subscription' === ppressPayPalVars.intent) ? 'createSubscription' : 'createOrder';

            const button_config = {
                onApprove: function (data, actions) {

                    ppressCheckoutForm.add_spinner();

                    var formData = new FormData();
                    formData.append('action', 'ppress_capture_paypal_order');
                    formData.append("ppress_checkout_nonce", $('#ppress_checkout_nonce').val());
                    formData.append("ppress_is_subscription", 'subscription' === ppressPayPalVars.intent ? 'true' : 'false');

                    if (typeof data.orderID !== 'undefined') {
                        formData.append('paypal_order_id', data.orderID);
                    }

                    if (typeof data.subscriptionID !== 'undefined') {
                        formData.append('paypal_subscription_id', data.subscriptionID);
                    }

                    return fetch(pp_ajax_form.ajaxurl, {
                        method: 'POST',
                        body: formData
                    }).then(function (response) {
                        return response.json();
                    }).then(function (responseData) {
                        if (
                            ppressCheckoutForm.is_var_defined(responseData.success) &&
                            ppressCheckoutForm.is_var_defined(responseData.data) &&
                            ppressCheckoutForm.is_var_defined(responseData.data.redirect_url)) {
                            window.location.assign(responseData.data.redirect_url)
                        } else {

                            var error = ppressCheckoutForm.is_var_defined(responseData.data.message) ? responseData.data.message : ppressPayPalVars.defaultError;

                            ppressCheckoutForm.createAlertMessage(error);
                            ppressCheckoutForm.remove_spinner();

                            // @link https://developer.paypal.com/docs/checkout/standard/customize/handle-funding-failures/
                            if (ppressCheckoutForm.is_var_defined(responseData.data.retry)) {
                                return actions.restart();
                            }
                        }
                    });
                },
                onError: function (error) {

                    ppressCheckoutForm.createAlertMessage(error);
                    ppressCheckoutForm.remove_spinner();
                    //necessary because on second checkout error, onError() callback isn't triggered.
                    _this.unmountPayPalElement();
                    _this.mountPayPalElement();
                },
                onCancel: function (data) {
                    ppressCheckoutForm.remove_spinner();
                }
            };
            button_config['style'] = ppressPayPalVars.style;
            button_config[createFunc] = _this.createOrderSubscription;

            paypal.Buttons(button_config).render('#ppress-paypal-button-element');
        };

        this.createOrderSubscription = function (data, actions) {

            ppressCheckoutForm.add_spinner();

            let formData = new FormData(document.getElementById('ppress_mb_checkout_form'));
            formData.append("action", "ppress_paypal_process_checkout");
            formData.append("ppress_checkout_nonce", $('#ppress_checkout_nonce').val());

            ppressCheckoutForm.removeAllAlerts();

            return fetch(pp_ajax_form.ajaxurl, {
                method: 'POST',
                body: formData
            }).then(function (response) {
                return response.json();
            }).then(function (orderSubData) {
                if (ppressCheckoutForm.is_var_defined(orderSubData.gateway_response)) {
                    ppressCheckoutForm.remove_spinner();
                    return orderSubData.gateway_response;
                } else {

                    var error = '';

                    if (typeof orderSubData.data === "string") {
                        error = orderSubData.data;
                    } else if ('string' === typeof orderSubData) {
                        error = orderSubData;
                    }

                    return new Promise(function (resolve, reject) {
                        reject(error);
                    });
                }
            });
        };

        this.unmountPayPalElement = function () {
            const cache = $('#ppress-paypal-button-element');
            if (cache.length === 0) return;
            cache.empty();
        };
    }

    (new PPressPayPal()).init();

})
(jQuery);