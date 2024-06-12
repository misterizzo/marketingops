import Backbone from 'backbone';
import $ from 'jquery';

export default Backbone.View.extend({
    el: '#pp-form-builder-metabox',

    events: {
        "click .pp_upload_button": "media_upload",
    },

    initialize() {

        new jBox('Tooltip', {
            attach: '.pp-form-builder-help-tip',
            maxWidth: 200,
            theme: 'TooltipDark'
        });

        // Makes tooltip close to the color picker field
        $('.form-field .wp-picker-container', this.$el).parent('.pp-field-row-content').css('width', 'auto');
    },

    media_upload(e) {

        e.preventDefault();

        let frame, _this = $(e.target);

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media.frames.file_frame = wp.media({
            frame: 'select',
            multiple: false,
            library: {
                type: 'image' // limits the frame to show only images
            },
        });

        frame.on('select', function () {
            let attachment = frame.state().get('selection').first().toJSON();
            _this.parents('.pp_upload_field_container').find('.pp_upload_field').val(attachment.url);

        });

        frame.open();
    }
});