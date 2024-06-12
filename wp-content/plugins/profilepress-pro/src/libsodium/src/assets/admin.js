(function ($) {

    $(function () {

        var cache = $('#2fa_enforce_user_roles_message_row, #2fa_page_url_row'),
            cache2 = $('.ppress_add_restriction');

        $("#2fa_enforce_user_roles_row select").on('change.select2', function () {
            if (_.isEmpty($(this).val())) {
                cache.hide();
            } else {
                cache.show();
            }
        }).trigger('change');

        if (cache2.length > 0) {

            var template = wp.template('ppress-metered-paywall-row');

            cache2.on('click', function (e) {
                e.preventDefault();

                var cachez,
                    parent = $(this).parents('.ppress-metered-paywall-restriction-wrap table').find('tbody'),
                    index = 0;

                cachez = $('tr', parent);

                if (cachez.length > 0) {
                    index = cachez.last().data('row-index') + 1;
                }

                parent.append(template({index: index++}));
            });

            $(document).on('click', '.ppress-metered-paywall-restriction-wrap .ppress-table-repeater-icon', function (e) {
                e.preventDefault();
                $(this).parents('.ppress-metered-paywall-table-actions').parent().remove();
            });
        }
    });
}(jQuery));