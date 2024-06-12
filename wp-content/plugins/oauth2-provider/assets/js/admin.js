(function ($) {
    $(document).ready(function () {
        $('#revoke-token').on('click', function (e) {
            e.preventDefault();

            // Confirm
            if (!confirm('Are you sure you want to revoke this token? All application using it will need to be updated.')) {
                return;
            }

            var data = {
                'action': 'wo_remove_self_generated_token',
                'nonce': $(this).data('nonce')
            };

            // listen back for JSON and change the secret then show it.
            jQuery.post(ajaxurl, data, function (response) {
                location.reload();
            });

        });

        $('#wo_tabs').tabs({
                beforeActivate: function (event, ui) {
                    var scrollTop = $(window).scrollTop();
                    window.location.hash = ui.newPanel.selector;
                    $(window).scrollTop(scrollTop);
                }
            }
        );
        $('.chosen-search-select').chosen();
        $('.select2').select2();
    });
})(jQuery);

/**
 * [wo_remove_client description]
 *
 * @param  {[type]} client_id [description]
 * @return {[type]}           [description]
 */
function wo_remove_client(client_id, nonce) {

    // Confirm
    if (!confirm('Are you sure you want to delete this client?')) {
        return;
    }

    var data = {
        'action': 'wo_remove_client',
        'client_id': client_id,
        'nonce': nonce
    };

    // listen back for JSON and change the secret then show it.
    jQuery.post(
        ajaxurl,
        data,
        function (response) {
            if (response != '1') {
                alert(response);
            } else {
                jQuery("#record_" + client_id + "").remove();
            }
        }
    );
}
