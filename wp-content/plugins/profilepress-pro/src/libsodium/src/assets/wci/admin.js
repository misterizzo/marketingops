(function ($, ppress_wci_admin_var) {
    $(function () {

        var cache = $('#wci_checkout_fields_required_row select'),
            old_checkout_fields_required_val = cache.val();

        $("#wci_checkout_fields_row select").on('change.select2', function () {

            var data = [];

            $.each($(this).val(), function (index, item) {
                data.push({id: item, text: ppress_wci_admin_var.custom_fields[item]});
            });

            /** @see https://select2.org/data-sources/arrays */
            cache.html('').select2({data: data})
                .val(old_checkout_fields_required_val)
                .trigger('change');
        });
    });
}(jQuery, ppress_wci_admin_var));