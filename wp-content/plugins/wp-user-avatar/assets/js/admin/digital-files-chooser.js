(function ($) {

    $(function () {

        var tmpl = wp.template('ppress-add-digital-file');

        $('.ppress-plan-integrations .pp-field-row-content .insert').on('click', function (e) {
            e.preventDefault();
            $('.ppress-plan-integrations .pp-field-row-content tbody').append(tmpl());
        });

        // Uploading files.
        var downloadable_file_frame,
            file_path_field,
            file_name_field;

        $(document.body).on('click', '.upload_file_button', function (event) {

            var $el = $(this);

            file_path_field = $el.closest('tr').find('td.file_url input');
            file_name_field = $el.closest('tr').find('td.file_name input');

            event.preventDefault();

            // If the media frame already exists, reopen it.
            if (downloadable_file_frame) {
                downloadable_file_frame.open();
                return;
            }

            var downloadable_file_states = [
                // Main states.
                new wp.media.controller.Library({
                    library: wp.media.query(),
                    multiple: true,
                    title: $el.data('choose'),
                    priority: 20,
                    filterable: 'uploaded'
                })
            ];

            // Create the media frame.
            downloadable_file_frame = wp.media.frames.downloadable_file = wp.media({
                // Set the title of the modal.
                title: $el.data('choose'),
                library: {
                    type: ''
                },
                button: {
                    text: $el.data('update')
                },
                multiple: true,
                states: downloadable_file_states
            });

            // When an image is selected, run a callback.
            downloadable_file_frame.on('select', function () {
                var file_path = '',
                    file_name = '',
                    selection = downloadable_file_frame.state().get('selection');

                selection.map(function (attachment) {
                    attachment = attachment.toJSON();
                    if (attachment.url) {
                        file_path = attachment.url;
                        file_name = attachment.title.length > 0 ? attachment.title : attachment.filename;
                    }
                });

                file_path_field.val(file_path);
                file_name_field.val(file_name);
            });

            // Set post to 0 and set our custom type.
            downloadable_file_frame.on('ready', function () {
                downloadable_file_frame.uploader.options.uploader.params = {
                    type: 'ppress_downloadable_plan'
                };
            });

            // Finally, open the modal.
            downloadable_file_frame.open();
        });

        $('.df_wrap tbody').sortable({
            items: 'tr',
            cursor: 'move',
            axis: 'y',
            handle: 'td.sort',
            scrollSensitivity: 40,
            helper: 'clone',
            opacity: 0.65
        });

        $('.ppress-plan-integrations').on('click', '.df_wrap a.delete', function (e) {
            e.preventDefault();
            $(this).closest('tr').remove();
        });
    });
})(jQuery);