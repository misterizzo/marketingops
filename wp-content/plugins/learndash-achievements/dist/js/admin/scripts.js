/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!****************************************!*\
  !*** ./src/assets/js/admin/scripts.js ***!
  \****************************************/
/**
 * LearnDash Achievements admin scripts.
 *
 * @since 1.0
 */
jQuery(document).ready(function ($) {
  // eslint-disable-next-line camelcase, no-var -- Kept for backward compatibility.
  var LD_Achievements = LD_Achievements || {};

  /**
   * Admin object.
   *
   * @since 1.0
   */
  // eslint-disable-next-line camelcase -- Kept for backward compatibility.
  LD_Achievements.admin = {
    /**
     * Initializes admin scripts.
     *
     * @since 1.0
     *
     * @return {void}
     */
    init() {
      this.toggle_child_input();
      this.select_image();
      this.settings_page();
      this.submit_disabled_fields();
      this.ajax_get_children_list();
    },
    /**
     * Toggles child input fields.
     *
     * @since 1.0
     *
     * @return {void}
     */
    toggle_child_input() {
      if ($('.ld_achievements_metabox_settings').length > 0) {
        $('select[name="trigger"]').change(function () {
          // eslint-disable-next-line camelcase -- Kept for backward compatibility.
          LD_Achievements.admin.update_select_values();
          const optionClass = $(this).val();
          if (optionClass === '') {
            return;
          }
          $('.sfwd_input.' + optionClass).show();
          $('.sfwd_input.child-input').not('.' + optionClass).hide();
          $('.sfwd_input.hide_on_' + optionClass).hide();
        });
        $(window).load(function () {
          // eslint-disable-next-line camelcase -- Kept for backward compatibility.
          LD_Achievements.admin.update_select_values_onload();
          const optionClass = $('select[name="trigger"]').val();
          if (optionClass === '') {
            return;
          }
          $('.sfwd_input.' + optionClass).show();
          $('.sfwd_input.child-input').not('.' + optionClass).hide();
          $('.sfwd_input.hide-empty-select').hide();
        });
      }
    },
    /**
     * Selects image.
     *
     * @since 1.0
     *
     * @return {void}
     */
    select_image() {
      const imageField = $('#image-field');
      if (imageField.length === 0) {
        return;
      }
      const imagePreviewHolder = $('#image-preview-holder');
      const imagePreview = $('#image-preview-holder img');
      const imageSelectorButtons = $('.image-selector-buttons');
      const iconSelection = $('.icon-selection');
      $(document).on('click', '.select-image-btn', function (e) {
        e.preventDefault();
        iconSelection.toggle();
      });
      $(window).load(function () {
        const image = $('#image-field').val();
        const icon = $('img.radio-btn[src="' + image + '"]');
        if (image.length === 0 && icon.length > 0) {
          icon.addClass('selected');
          $('.icon-selection').show();
        } else if (image.length > 0) {
          imageSelectorButtons.hide();
          imageField.val(image);
          imagePreview.attr('src', image);
          imagePreviewHolder.show();
        }
      });
      $(document).on('click', '.icon-selection .radio-btn', function (e) {
        e.preventDefault();
        $('.icon-selection input[type=radio]').removeAttr('checked');
        $('.icon-selection .radio-btn').removeClass('selected');
        $(this).prev().attr('checked', 'checked');
        $(this).addClass('selected');
        $('#image-field').val($(this).attr('src'));
      });
      let uploader;
      $(document).on('click', '#upload-image', function (e) {
        e.preventDefault();
        if (uploader) {
          uploader.open();
          return;
        }
        uploader = wp.media.frames.file_frame = wp.media({
          title: 'Choose Image',
          button: {
            text: 'Choose Image'
          },
          multiple: false
        });
        uploader.on('select', function () {
          const attachment = uploader.state().get('selection').first().toJSON();
          imageField.val(attachment.url);
          imagePreview.attr('src', attachment.url);
          imagePreviewHolder.show();
          $('.radio-btn.selected').removeClass('selected');
          imageSelectorButtons.hide();
          iconSelection.hide();
        });
        uploader.open();
      });
      $(document).on('click', '#remove-image-btn', function (e) {
        e.preventDefault();
        imagePreviewHolder.hide();
        imagePreview.attr('src', '');
        imageField.val('');
        imageSelectorButtons.show();
      });
    },
    /**
     * Settings page scripts.
     *
     * @since 1.0
     *
     * @return {void}
     */
    settings_page() {
      $('.color-picker').wpColorPicker();
    },
    /**
     * Enables some disabled fields on form submit.
     *
     * @since 1.0
     *
     * @return {void}
     */
    submit_disabled_fields() {
      $('form').on('submit', function () {
        $(this).find(':input').prop('disabled', false);
      });
    },
    /**
     * Gets children list via AJAX.
     *
     * @since 1.0
     *
     * @return {void}
     */
    ajax_get_children_list() {
      $('.parent_field select').change(function () {
        const el = $(this);
        let parentType = '';
        const val = $(this).val();
        const name = $(this).attr('name');
        switch (name) {
          case 'course_id':
            parentType = 'course';
            break;
          case 'lesson_id':
            parentType = 'lesson';
            break;
          case 'topic_id':
            parentType = 'topic';
            break;
        }
        const courseId = $('select[name="course_id"]').val();
        $.ajax({
          url: ajaxurl,
          type: 'POST',
          data: {
            action: 'ld_achievements_get_children_list',
            course_id: courseId,
            parent_type: parentType,
            parent_id: val,
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            nonce: LD_Achievements_Admin_Data.nonce
          }
        }).done(function (data) {
          let response = data;
          response = JSON.parse(response);
          if (el.attr('name').indexOf('course') !== -1) {
            $('select[name="topic_id"]').html('<option>' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.select_lesson_first + '</option>');
            $('select[name="quiz_id"]').html('<option>' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.select_topic_first + '</option>');
            $('select[name="lesson_id"]').html('<option>' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.select_lesson + '</option>' + '<option value="all">' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.all_lessons + '</option>');
            $.each(response, function (i, responseValue) {
              $('select[name="lesson_id"]').append('<option value="' + i + '">' + responseValue + '</option>');
            });
          }
          if (el.attr('name').indexOf('lesson') !== -1) {
            $('select[name="quiz_id"]').html('<option>' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.select_topic_first + '</option>');
            $('select[name="topic_id"]').html('<option>' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.select_topic + '</option>' + '<option value="all">' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.all_topics + '</option>');
            $.each(response, function (i, responseValue) {
              $('select[name="topic_id"]').append('<option value="' + i + '">' + responseValue + '</option>');
            });
          }
          if (el.attr('name').indexOf('topic') !== -1) {
            $('select[name="quiz_id"]').html('<option>' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.select_quiz + '</option>' + '<option value="all">' +
            // eslint-disable-next-line camelcase -- Kept for backward compatibility.
            LD_Achievements_Admin_Data.all_quizzes + '</option>');
            $.each(response, function (i, responseValue) {
              $('select[name="quiz_id"]').append('<option value="' + i + '">' + responseValue + '</option>');
            });
          }
        });
      });
    },
    /**
     * Updates select fields values.
     *
     * @since 1.0
     *
     * @return {void}
     */
    update_select_values() {
      $('select[name="course_id"]').prop('selectedIndex', 0);
      $('select[name="lesson_id"]').html('<option>' +
      // eslint-disable-next-line camelcase -- Kept for backward compatibility.
      LD_Achievements_Admin_Data.select_course_first + '</option>');
      $('select[name="topic_id"]').html('<option>' +
      // eslint-disable-next-line camelcase -- Kept for backward compatibility.
      LD_Achievements_Admin_Data.select_lesson_first + '</option>');
      $('select[name="quiz_id"]').html('<option>' +
      // eslint-disable-next-line camelcase -- Kept for backward compatibility.
      LD_Achievements_Admin_Data.select_topic_first + '</option>');
    },
    /**
     * Updates select fields values on page load.
     *
     * @since 1.0
     *
     * @return {void}
     */
    update_select_values_onload() {
      if ($('select[name="course_id"]').val() === '') {
        $('select[name="course_id"]').prop('selectedIndex', 0);
      }
      if ($('select[name="lesson_id"]').val() === '') {
        $('select[name="lesson_id"]').html('<option>' +
        // eslint-disable-next-line camelcase -- Kept for backward compatibility.
        LD_Achievements_Admin_Data.select_course_first + '</option>');
      }
      if ($('select[name="topic_id"]').val() === '') {
        $('select[name="topic_id"]').html('<option>' +
        // eslint-disable-next-line camelcase -- Kept for backward compatibility.
        LD_Achievements_Admin_Data.select_lesson_first + '</option>');
      }
      if ($('select[name="quiz_id"]').val() === '') {
        $('select[name="quiz_id"]').html('<option>' +
        // eslint-disable-next-line camelcase -- Kept for backward compatibility.
        LD_Achievements_Admin_Data.select_topic_first + '</option>');
      }
    }
  };

  // eslint-disable-next-line camelcase -- Kept for backward compatibility.
  LD_Achievements.admin.init();
});
/******/ })()
;