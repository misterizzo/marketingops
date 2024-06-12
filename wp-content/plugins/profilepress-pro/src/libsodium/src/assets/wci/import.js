(function ($, pp_wci_var) {
    $(function () {
        $(".wc_import_fields").on('click', function () {

            if (confirm(pp_form_builder.confirm_delete)) {

                var _this = this;

                $(this).next('span.spinner').show();

                $.post(ajaxurl, {action: 'pp_import_wc_fields', ajax_nonce: pp_wci_var.nonce}, function () {
                    $('span.spinner').hide();
                    $(_this).nextAll('span.dashicons-yes').show().fadeOut(1000);

                    setTimeout(function () {
                        location.reload()
                    }, 1300);
                });
            }
        });
    });
}(jQuery, pp_wci_var));