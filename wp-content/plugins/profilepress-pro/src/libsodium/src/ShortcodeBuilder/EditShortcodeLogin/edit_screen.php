<?php

use ProfilePress\Core\Classes\FormRepository;

$form_id = absint($_GET['id']);

$title     = FormRepository::get_name($form_id, FormRepository::LOGIN_TYPE);
$structure = FormRepository::get_form_meta($form_id, FormRepository::LOGIN_TYPE, FormRepository::FORM_STRUCTURE, true);
$css       = FormRepository::get_form_meta($form_id, FormRepository::LOGIN_TYPE, FormRepository::FORM_CSS, true);

$make_passwordless = FormRepository::is_login_passwordless($form_id) ? 'yes' : 'no';
$content           = isset($_POST['lfb_structure']) ? stripslashes($_POST['lfb_structure']) : $structure;
$shortcode         = sprintf('&lsqb;profilepress-%s id=&quot;%s&quot;&rsqb;', FormRepository::LOGIN_TYPE, $form_id);
?>

<form method="post">
    <div class="ppSCB-margin-r">
        <div class="ppSCB-tab-box">
            <div id="titlediv">
                <div id="titlewrap">
                    <label class="screen-reader-text" id="title-prompt-text" for="title"><?= esc_html__('Enter title here', 'profilepress-pro') ?></label>
                    <input name="lfb_title" type="text" value="<?= $title ?>" id="title">
                    <input class="ppSCB-save-btn button-primary" type="submit" name="edit_login" value="<?= esc_html__('Save Changes', 'profilepress-pro') ?>">
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
                    <textarea rows="30" name="lfb_structure" id="pp_login_structure"><?php echo $content ?></textarea>
                    <div class="ppSCB-clear-both"></div>
                </div>
                <div id="ppCSS" class="ppSCB-tab-content">
                    <textarea rows="30" name="lfb_css" id="pp_login_css"><?php echo isset($_POST['lfb_css']) ? stripslashes($_POST['lfb_css']) : $css; ?></textarea>
                    <div class="ppSCB-clear-both"></div>
                </div>
                <div id="ppSettings" class="ppSCB-tab-content">
                    <h4 class="ppSCB-tab-content-header"><?= esc_html__('General Settings', 'profilepress-pro') ?></h4>
                    <table class="form-table">
                        <?php do_action('ppress_shortcode_builder_login_screen_before', $form_id); ?>
                        <tr>
                            <th scope="row">
                                <label for="processing_label"><?php _e('Processing Label', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="processing_label" id="processing_label" value="<?= FormRepository::get_processing_label($form_id, FormRepository::LOGIN_TYPE); ?>"/>
                                <p class="description"><?php _e('This is the text shown on the submit button when the form is submitted.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="description"><?php _e('Passwordless Login', 'profilepress-pro'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="lfb_make_passwordless" id="make-login-passwordless" value="yes" <?php checked('yes', $make_passwordless); ?> />
                                <label for="make-login-passwordless"><strong><?php _e('Make this a passwordless login', 'profilepress-pro'); ?></strong></label>
                                <p class="description"><?php _e('Passwordless login allows users to sign in to your WordPress site via a one-time URL sent to their email address.', 'profilepress-pro'); ?></p>
                            </td>
                        </tr>
                        <?php do_action('ppress_shortcode_builder_login_screen_after', $form_id); ?>
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