<?php

use ProfilePress\Core\Classes\FormRepository;

$form_id = absint($_GET['id']);

$title     = FormRepository::get_name($form_id, FormRepository::MELANGE_TYPE);
$structure = FormRepository::get_form_meta($form_id, FormRepository::MELANGE_TYPE, FormRepository::FORM_STRUCTURE);
$css       = FormRepository::get_form_meta($form_id, FormRepository::MELANGE_TYPE, FormRepository::FORM_CSS);

$registration_success_message   = FormRepository::get_form_meta($form_id, FormRepository::MELANGE_TYPE, FormRepository::MELANGE_REGISTRATION_SUCCESS_MESSAGE);
$edit_profile_success_message   = FormRepository::get_form_meta($form_id, FormRepository::MELANGE_TYPE, FormRepository::MELANGE_EDIT_PROFILE_SUCCESS_MESSAGE);
$password_reset_success_message = FormRepository::get_form_meta($form_id, FormRepository::MELANGE_TYPE, FormRepository::MELANGE_PASSWORD_RESET_SUCCESS_MESSAGE);
$disable_username_requirement   = FormRepository::get_form_meta($form_id, FormRepository::MELANGE_TYPE, FormRepository::DISABLE_USERNAME_REQUIREMENT) ? 'yes' : 'no';

$title     = isset($_POST['mfb_title']) ? esc_attr($_POST['mfb_title']) : $title;
$content   = isset($_POST['mfb_structure']) ? stripslashes($_POST['mfb_structure']) : $structure;
$shortcode = sprintf('&lsqb;profilepress-%s id=&quot;%s&quot;&rsqb;', FormRepository::MELANGE_TYPE, $form_id);

?>

<form method="post">
    <div class="ppSCB-margin-r">
        <div class="ppSCB-tab-box">
            <div id="titlediv">
                <div id="titlewrap">
                    <label class="screen-reader-text" id="title-prompt-text" for="title"><?= esc_html__('Enter title here', 'profilepress-pro') ?></label>
                    <input name="mfb_title" type="text" value="<?= $title ?>" id="title">
                    <input class="ppSCB-save-btn button-primary" type="submit" name="edit_melange" value="<?= esc_html__('Save Changes', 'profilepress-pro') ?>">
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
                    <textarea rows="30" name="mfb_structure" id="pp_melange_structure"><?php echo $content ?></textarea>
                    <div class="ppSCB-clear-both"></div>
                </div>
                <div id="ppCSS" class="ppSCB-tab-content">
                    <textarea rows="30" name="mfb_css" id="pp_melange_css"><?php echo isset($_POST['mfb_css']) ? stripslashes($_POST['mfb_css']) : $css; ?></textarea>
                    <div class="ppSCB-clear-both"></div>
                </div>
                <div id="ppSettings" class="ppSCB-tab-content">
                    <h4 class="ppSCB-tab-content-header"><?= esc_html__('General Settings', 'profilepress-pro') ?></h4>
                    <table class="form-table">
                        <?php do_action('ppress_shortcode_builder_melange_screen_before', $form_id); ?>
                        <tr>
                            <th scope="row">
                                <label for="processing_label"><?php _e('Processing Label', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="processing_label" id="processing_label" value="<?= FormRepository::get_processing_label($form_id, FormRepository::MELANGE_TYPE); ?>"/>
                                <p class="description"><?php _e('This is the text shown on the submit button when the form is submitted.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="reg_message_success"><?php _e('Registration Success Message', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <textarea name="mfb_success_registration" id="reg_message_success"><?php echo isset($_POST['mfb_success_registration']) ? $_POST['mfb_success_registration'] : $registration_success_message; ?></textarea>

                                <p class="description"><?php _e('Message displayed on successful user registration.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="mfb_success_password_reset"><?php _e('Password-reset Success Message', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <textarea name="mfb_success_password_reset" id="mfb_success_password_reset"><?php echo isset($_POST['mfb_success_password_reset']) ? $_POST['mfb_success_password_reset'] : $password_reset_success_message; ?></textarea>

                                <p class="description"><?php _e('Message displayed on successful user password reset.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="mfb_success_edit_profile"><?php _e('Edit-profile Success Message', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <textarea name="mfb_success_edit_profile" id="mfb_success_edit_profile"><?php echo isset($_POST['mfb_success_edit_profile']) ? $_POST['mfb_success_edit_profile'] : $edit_profile_success_message; ?></textarea>

                                <p class="description"><?php _e('Message displayed on users successfully editing their profile', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="disable_username_requirement_melange"><?php _e('Disable Username Requirement', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="mfb_disable_username_requirement" id="disable_username_requirement_melange" value="yes" <?php checked('yes', $disable_username_requirement); ?> />
                                <label for="disable_username_requirement_melange"><strong><?php _e('Check to Disable', 'profilepress-pro'); ?></strong></label>

                                <p class="description"><?php _e('Disable requirement for users to enter a username during registration. Usernames will automatically be generated from their email addresses.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <?php do_action('ppress_shortcode_builder_melange_screen_after', $form_id); ?>
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