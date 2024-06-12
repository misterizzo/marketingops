<?php

use ProfilePress\Core\Classes\FormRepository;

$form_id = absint($_GET['id']);

$title     = FormRepository::get_name($form_id, FormRepository::REGISTRATION_TYPE);
$structure = FormRepository::get_form_meta($form_id, FormRepository::REGISTRATION_TYPE, FormRepository::FORM_STRUCTURE);
$css       = FormRepository::get_form_meta($form_id, FormRepository::REGISTRATION_TYPE, FormRepository::FORM_CSS);

$new_user_role                = FormRepository::get_form_meta($form_id, FormRepository::REGISTRATION_TYPE, FormRepository::REGISTRATION_USER_ROLE);
$success_message              = FormRepository::get_form_meta($form_id, FormRepository::REGISTRATION_TYPE, FormRepository::SUCCESS_MESSAGE);
$disable_username_requirement = FormRepository::get_form_meta($form_id, FormRepository::REGISTRATION_TYPE, FormRepository::DISABLE_USERNAME_REQUIREMENT) ? 'yes' : 'no';

$selected = get_option('default_role');
if ( ! empty($new_user_role)) $selected = $new_user_role;

if (isset($_POST['rfb_new_user_role'])) $selected = sanitize_text_field($_POST['rfb_new_user_role']);

$title = isset($_POST['rfb_title']) ? sanitize_title($_POST['rfb_title']) : $title;

$content   = isset($_POST['rfb_structure']) ? stripslashes($_POST['rfb_structure']) : $structure;
$shortcode = sprintf('&lsqb;profilepress-%s id=&quot;%s&quot;&rsqb;', FormRepository::REGISTRATION_TYPE, $form_id);

?>

<form method="post">
    <div class="ppSCB-margin-r">
        <div class="ppSCB-tab-box">
            <div id="titlediv">
                <div id="titlewrap">
                    <label class="screen-reader-text" id="title-prompt-text" for="title"><?= esc_html__('Enter title here', 'profilepress-pro') ?></label>
                    <input name="rfb_title" type="text" value="<?= $title ?>" id="title">
                    <input class="ppSCB-save-btn button-primary" type="submit" name="edit_registration" value="<?= esc_html__('Save Changes', 'profilepress-pro') ?>">
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
                    <textarea rows="30" name="rfb_structure" id="pp_registration_structure"><?php echo $content ?></textarea>
                    <div class="ppSCB-clear-both"></div>
                </div>
                <div id="ppCSS" class="ppSCB-tab-content">
                    <textarea rows="30" name="rfb_css" id="pp_registration_css"><?php echo isset($_POST['rfb_css']) ? stripslashes($_POST['rfb_css']) : $css; ?></textarea>
                    <div class="ppSCB-clear-both"></div>
                </div>
                <div id="ppSettings" class="ppSCB-tab-content">
                    <h4 class="ppSCB-tab-content-header"><?= esc_html__('General Settings', 'profilepress-pro') ?></h4>
                    <table class="form-table">
                        <?php do_action('ppress_shortcode_builder_registration_screen_before', $form_id); ?>
                        <tr>
                            <th scope="row">
                                <label for="processing_label"><?php _e('Processing Label', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="processing_label" id="processing_label" value="<?= FormRepository::get_processing_label($form_id, FormRepository::REGISTRATION_TYPE); ?>"/>
                                <p class="description"><?php _e('This is the text shown on the submit button when the form is submitted.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="message_success"><?php esc_attr_e('Success message', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <textarea name="rfb_success_registration" id="message_success"><?= $success_message ?></textarea>
                                <p class="description"><?php esc_attr_e('Message to display on successful user registration.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="new_user_role"><?php esc_attr_e('New User Role', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <select name="rfb_new_user_role" id="new_user_role"><?php wp_dropdown_roles($selected); ?></select>
                                <p class="description"><?php esc_attr_e('Role of users registered through this form.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="disable_username_requirement"><?php esc_attr_e('Disable Username Requirement', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="rfb_disable_username_requirement" id="disable_username_requirement" value="yes" <?php checked('yes', $disable_username_requirement); ?> />
                                <label for="disable_username_requirement"><strong><?php _e('Check to Disable', 'profilepress-pro'); ?></strong></label>

                                <p class="description">
                                    <?php _e('Disable requirement for users to enter a username during registration. Usernames will automatically be generated from their email addresses.', 'profilepress-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        <?php do_action('ppress_shortcode_builder_registration_screen_after', $form_id); ?>
                    </table>
                    <div class="ppSCB-clear-both"></div>
                    <?php do_action('ppress_shortcode_builder_registration_screen_settings', $form_id); ?>
                </div>
            </div>
        </div>
        <div class="ppSCB-sidebar">
            <h3><?= esc_html__('Preview', 'profilepress-pro') ?></h3>
            <iframe id="indexIframe"></iframe>
        </div>
    </div>
</form>