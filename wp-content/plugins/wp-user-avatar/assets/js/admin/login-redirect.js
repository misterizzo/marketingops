(function ($) {

    $(function () {

        var redirect_item_tmpl = wp.template('ppress-login-redirect-item');

        $(document).on('click', '.ppress-login-redirect-add-rule button', function (e) {
            e.preventDefault();

            var _this = $(this),
                rule_select_field_obj = _this.prev(),
                selected_rule = $('option:selected', rule_select_field_obj),
                parent_wrapper = _this.parents('.ppress-login-redirect-rules-wrap');

            if (selected_rule.val() === "") return;

            $('.ppress-login-redirect-items-wrap', parent_wrapper).prepend(
                redirect_item_tmpl({
                    label: selected_rule.text(),
                    redirect_type: parent_wrapper.data('redirect-type'),
                    redirect_target: selected_rule.val()
                })
            );

            selected_rule.prop('disabled', true);
            $('option', rule_select_field_obj).first().prop('selected', true);
        });

        $(document).on('click', '.ppress-login-redirect--remove-field', function (e) {
            e.preventDefault();
            var wrapper = $(this).parents('.ppress-login-redirect--repeater-item'),
                removed_redirect_target = wrapper.data('redirect-target');

            $(this).parents('.ppress-login-redirect-rules-wrap')
                .find('.ppress-login-redirect-select-field option[value="' + removed_redirect_target + '"]')
                .prop('disabled', false);

            wrapper.remove();
        });

        $(document).on('click', '#ppress-login-redirect-submit-btn', function (e) {
            e.preventDefault();
            $('#ppress-login-redirect-form').trigger('submit');
        });
    });
})(jQuery);