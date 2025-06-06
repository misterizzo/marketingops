<?php

use ProfilePress\Core\Classes\FormRepository;

$form_id = absint($_GET['id']);

$title     = FormRepository::get_name($form_id, FormRepository::EDIT_PROFILE_TYPE);
$structure = FormRepository::get_form_meta($form_id, FormRepository::EDIT_PROFILE_TYPE, FormRepository::FORM_STRUCTURE);
$css       = FormRepository::get_form_meta($form_id, FormRepository::EDIT_PROFILE_TYPE, FormRepository::FORM_CSS);

$success_message = FormRepository::get_form_meta($form_id, FormRepository::EDIT_PROFILE_TYPE, FormRepository::SUCCESS_MESSAGE);

$title = isset($_POST['eup_title']) ? esc_attr($_POST['eup_title']) : $title;
$content = isset($_POST['eup_structure']) ? stripslashes($_POST['eup_structure']) : $structure;
$shortcode         = sprintf('&lsqb;profilepress-%s id=&quot;%s&quot;&rsqb;', FormRepository::EDIT_PROFILE_TYPE, $form_id);
?>

<form method="post">
    <div class="ppSCB-margin-r">
        <div class="ppSCB-tab-box">
            <div id="titlediv">
                <div id="titlewrap">
                    <label class="screen-reader-text" id="title-prompt-text" for="title"><?= esc_html__('Enter title here', 'profilepress-pro') ?></label>
                    <input name="eup_title" type="text" value="<?= $title ?>" id="title">
                    <input class="ppSCB-save-btn button-primary" type="submit" name="edit_user_profile" value="<?= esc_html__('Save Changes', 'profilepress-pro') ?>">
                </div>
                <div class="inside">
                    <p class="description">
                        <label for="ppress-shortcode">
                            <?php esc_html_e('Copy this shortcode and paste it into your post, page, or text widget content:', 'profilepress-pro') ?>
                        </label>
                        <span class="shortcode wp-ui-highlight">
                        <input type="text" id="ppress-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="<?= $shortcode ?>">
                    </span>
                    </p>
                </div>
            </div>
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab" href="#ppStructure"><?= esc_html__('Structure', 'profilepress-pro') ?></a>
                <a class="nav-tab" href="#ppCSS"><?= esc_html__('CSS', 'profilepress-pro') ?></a>
                <a class="nav-tab" href="#ppSettings"><?= esc_html__('Settings', 'profilepress-pro') ?></a>

                <span class="pp-form-builder-shortcodes-btn">
                    <a href="#" id="ppress-available-shortcodes-btn" class="button button-secondary"><?= esc_html__('Available Shortcodes', 'profilepress-pro') ?></a>
                </span>

            </h2>
            <div class="ppSCB-tab-box-div">
                <div id="ppStructure" class="ppSCB-tab-content">
                    <textarea rows="30" name="eup_structure" id="pp_edit_profile_structure"><?php echo $content ?></textarea>
                    <div class="ppSCB-clear-both"></div>
                </div>
                <div id="ppCSS" class="ppSCB-tab-content">
                    <textarea rows="30" name="eup_css" id="pp_edit_profile_css"><?php echo isset($_POST['eup_css']) ? stripslashes($_POST['eup_css']) : $css; ?></textarea>
                    <div class="ppSCB-clear-both"></div>
                </div>
                <div id="ppSettings" class="ppSCB-tab-content">
                    <h4 class="ppSCB-tab-content-header"><?=esc_html__('General Settings', 'profilepress-pro')?></h4>
                    <table class="form-table">
                        <?php do_action('ppress_shortcode_builder_edit_profile_screen_before', $form_id); ?>
                        <tr>
                            <th scope="row">
                                <label for="processing_label"><?php _e('Processing Label', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="processing_label" id="processing_label" value="<?= FormRepository::get_processing_label($form_id, FormRepository::EDIT_PROFILE_TYPE); ?>"/>
                                <p class="description"><?php _e('This is the text shown on the submit button when the form is submitted.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="message_success"><?=__('Success Message', 'profilepress-pro')?></label>
                            </th>
                            <td>
                                <textarea name="eup_success_edit_profile" id="message_success"><?php echo isset($_POST['eup_success_edit_profile']) ? $_POST['eup_success_edit_profile'] : stripslashes(esc_textarea($success_message)); ?></textarea>
                                <p class="description"><?=__('Message to display when a user profile is edited.', 'profilepress-pro')?></p>
                            </td>
                        </tr>
                        <?php do_action('ppress_shortcode_builder_edit_profile_screen_after', $form_id); ?>
                    </table>
                    <div class="ppSCB-clear-both"></div>
                </div>
            </div>
        </div>
        <div class="ppSCB-sidebar">
            <h3><?= esc_html__('Preview', 'profilepress-pro') ?></h3>
            <iframe id="indexIframe"></iframe>
        </div>
    </div>
</form>