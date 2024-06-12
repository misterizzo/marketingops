(function ($) {

    String.prototype.ucwords = function () {
        return this.toLowerCase().replace(/\b[a-z]/g, function (letter) {
            return letter.toUpperCase();
        });
    };

    $(document).on('click', '#ppesp_sync_tool_btn', function () {

        if (typeof window.ppespJboxInstance != 'undefined') {
            window.ppespJboxInstance.destroy();
        }

        var myModal = new jBox('Modal', {
            id: 'ppesp-sync-tool-modal',
            closeButton: 'title',
            maxHeight: 400,
            repositionOnContent: true
        });

        window.ppespJboxInstance = myModal;

        myModal.setTitle(ppress_esp_globals.sync_tool_label);
        myModal.setContent($('#ppesp-sync-tool-modal').html());
        myModal.open();
    });

    $(document).on('click', '#ppesp-sync-users-now-btn', function () {

        var wp_list_id = $('#ppesp-sync-tool-modal.jBox-wrapper #ppesp-sync-list').val(),
            esp = $(this).parents('.ppesp-sync-tool-modal-content').data('pp-esp'),
            data = {_wpnonce: ppress_esp_globals.nonce},
            button_label = $(this).attr('value'),
            esp_label = esp.replace('_', ' ').ucwords(),
            status_message = ppress_esp_globals.sync_success.replace('%esp%', esp_label);

        if (wp_list_id === '' || wp_list_id === '0') return;

        data['role'] = $('#ppesp-sync-tool-modal.jBox-wrapper #ppesp-sync-role').val();
        data['wp_list_id'] = wp_list_id;
        data['action'] = 'pp_' + esp + '_batch_subscribe';

        $(this).attr('value', ppress_esp_globals.processing_label)
            .prop("disabled", true);

        $.post(ajaxurl, data, function (response) {
            $(this).attr('value', button_label).prop("disabled", false);

            if (!'success' in response || response.success === false) {
                status_message = ppress_esp_globals.sync_error;
            }

            alert(status_message);

            if (typeof window.ppespJboxInstance !== "undefined") {
                window.ppespJboxInstance.close();
            }
        });
    });
})(jQuery);
