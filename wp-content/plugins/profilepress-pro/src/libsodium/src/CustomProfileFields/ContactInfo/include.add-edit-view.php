<?php

use ProfilePress\Core\Classes\PROFILEPRESS_sql as PROFILEPRESS_sql;

$field_key = $label = '';

if ( ! empty($_GET['edit'])) {
    $field_key = sanitize_text_field($_GET['edit']);
    $label     = PROFILEPRESS_sql::get_contact_info_field_label($field_key);
}
?>
<br/>
<form method="post">
    <div class="inside">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="label_name"><?php _e('Field Label', 'profilepress-pro'); ?>*</label>
                </th>
                <td>
                    <input type="text" id="label_name" name="ci_label_name" class="regular-text code" value="<?php echo isset($_POST['ci_label_name']) ? esc_attr($_POST['ci_label_name']) : esc_html($label); ?>" required="required"/>
                    <p class="description"><?php _e('This is the text that is displayed to users.', 'profilepress-pro'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="label_name"><?php _e('Field Key', 'profilepress-pro'); ?>*</label></th>
                <td>
                    <input type="text" id="label_name" name="ci_key" class="regular-text code" value="<?= isset($_POST['ci_key']) ? esc_attr($_POST['ci_key']) : $field_key; ?>" <?= ! empty($field_key) ? 'disabled' : '' ?> required/>

                    <p class="description">
                        <?php _e('Your field key must be unique for each field. It must be lower-case letters only with only underscore allowed between them.', 'profilepress-pro'); ?>
                    </p>
                </td>
            </tr>

        </table>
        <p>
            <?php wp_nonce_field('pp-save-contact-info'); ?>
            <input class="button-primary" type="submit" name="save_contact_info" value="<?php _e('Save Changes', 'profilepress-pro'); ?>">
        </p>
    </div>
</form>