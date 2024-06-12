<?php

$field_id = absint(@$_GET['field']);

// get the profile fields row for the id
$profile_fields = \ProfilePress\Core\Classes\PROFILEPRESS_sql::get_profile_custom_field_by_id($field_id);

$field_key = ppress_var($profile_fields, 'field_key');

// select multi option selectable.
$is_multi_selectable = get_option('ppress_cpf_select_multi_selectable', array());
$is_multi_selectable = false !== $field_key && array_key_exists($field_key, $is_multi_selectable) ? 'yes' : '';


$cpf_field_type_selected = function ($field_type) use ($profile_fields) {
    return isset($_POST['cpf_type']) ?
        selected(sanitize_text_field($_POST['cpf_type']), $field_type, false) :
        selected(ppress_var($profile_fields, 'type'), $field_type, false);
};

$submit_name = 'edit_field';
if (isset($_GET['post']) && $_GET['post'] == 'new') {
    $submit_name = 'add_new_field';
}

$date_formats = [
    'Y-m-d'       => 'Y-m-d - (Ex: 2018-04-28)',
    'd-M-y'       => 'd-M-y - (Ex: 28-Apr-18)',
    'm/d/Y'       => 'm/d/Y - (Ex: 04/28/2018)', // USA
    'd/m/Y'       => 'd/m/Y - (Ex: 28/04/2018)', // Canada, UK
    'd.m.Y'       => 'd.m.Y - (Ex: 28.04.2019)', // Germany
    'n/j/y'       => 'n/j/y - (Ex: 4/28/18)',
    'm/d/y'       => 'm/d/y - (Ex: 04/28/18)',
    'M/d/Y'       => 'M/d/Y - (Ex: Apr/28/2018)',
    'y/m/d'       => 'y/m/d - (Ex: 18/04/28)',
    'm/d/Y h:i K' => 'm/d/Y h:i K - (Ex: 04/28/2018 08:55 PM)', // USA
    'm/d/Y H:i'   => 'm/d/Y H:i - (Ex: 04/28/2018 20:55)', // USA
    'd/m/Y h:i K' => 'd/m/Y h:i K - (Ex: 28/04/2018 08:55 PM)', // Canada, UK
    'd/m/Y H:i'   => 'd/m/Y H:i - (Ex: 28/04/2018 20:55)', // Canada, UK
    'd.m.Y h:i K' => 'd.m.Y h:i K - (Ex: 28.04.2019 08:55 PM)', // Germany
    'd.m.Y H:i'   => 'd.m.Y H:i - (Ex: 28.04.2019 20:55)', // Germany
    'h:i K'       => sprintf('h:i K (%s Ex: 08:55 PM)', esc_html__('Only Time', 'profilepress-pro')),
    'H:i'         => sprintf('H:i (%s Ex: 20:55)', esc_html__('Only Time', 'profilepress-pro'))
];
?>
<div class="postbox">
    <form method="post">
        <div class="inside">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="label_name"><?php _e('Field Label', 'profilepress-pro'); ?>*</label>
                    </th>
                    <td>
                        <input required type="text" id="label_name" name="cpf_label_name" class="regular-text code" value="<?php echo isset($_POST['cpf_label_name']) ? esc_attr($_POST['cpf_label_name']) : esc_attr(ppress_var($profile_fields, 'label_name')); ?>"/>
                        <p class="description"><?php _e('This is the text that is displayed to users.', 'profilepress-pro'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="key"><?php _e('Field Key', 'profilepress-pro'); ?>*</label>
                    </th>
                    <td>
                        <input required type="text" id="key" name="cpf_key" class="regular-text code" value="<?php echo isset($_POST['cpf_key']) ? esc_attr($_POST['cpf_key']) : esc_attr($field_key); ?>" pattern="[a-z0-9_]+"/>
                        <p class="description">
                            <?php _e('Your field key must be unique for each field. It must be lower-case letters only with only underscore allowed between them.', 'profilepress-pro'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="description"><?php _e('Field Description', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <textarea name="cpf_description" id="description"><?php echo isset($_POST['cpf_description']) ? esc_textarea($_POST['cpf_description']) : esc_textarea(ppress_var($profile_fields, 'description')); ?></textarea>
                        <p class="description"><?php _e('Description of the field for display to users', 'profilepress-pro'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="cpf_type"><?php _e('Field Type', 'profilepress-pro'); ?>*</label>
                    </th>
                    <td>
                        <select required id="cpf_type" name="cpf_type">
                            <option value="text" <?= $cpf_field_type_selected('text'); ?>>
                                <?php _e('Text Field', 'profilepress-pro'); ?>
                            </option>
                            <option value="password" <?= $cpf_field_type_selected('password'); ?>>
                                <?php _e('Password Field', 'profilepress-pro'); ?>
                            </option>
                            <option value="email" <?= $cpf_field_type_selected('email'); ?>>
                                <?php _e('Email Field', 'profilepress-pro'); ?>
                            </option>
                            <option value="tel" <?= $cpf_field_type_selected('tel'); ?>>
                                <?php _e('Phone Number Field', 'profilepress-pro'); ?>
                            </option>
                            <option value="hidden" <?= $cpf_field_type_selected('hidden'); ?>>
                                <?php _e('Hidden Field', 'profilepress-pro'); ?>
                            </option>
                            <option value="number" <?= $cpf_field_type_selected('number'); ?>>
                                <?php _e('Number Field', 'profilepress-pro'); ?>
                            </option>
                            <option value="date" <?= $cpf_field_type_selected('date'); ?>>
                                <?php _e('Date/Time Field', 'profilepress-pro'); ?>
                            </option>
                            <option value="country" <?= $cpf_field_type_selected('country'); ?>>
                                <?php _e('Country Field', 'profilepress-pro'); ?>
                            </option>
                            <option value="textarea" <?= $cpf_field_type_selected('textarea'); ?>>
                                Textarea
                            </option>
                            <option value="agreeable" <?= $cpf_field_type_selected('agreeable'); ?>>
                                <?php _e('Single Checkbox (Agreeable)', 'profilepress-pro'); ?>
                            </option>
                            <option value="file" <?= $cpf_field_type_selected('file'); ?>>
                                <?php _e('File Upload', 'profilepress-pro'); ?>
                            </option>
                            <option value="select" <?= $cpf_field_type_selected('select'); ?>>
                                Multiple Choice: Select Box
                            </option>
                            <option value="radio" <?= $cpf_field_type_selected('radio'); ?>>
                                Multiple Choice: Radio Buttons
                            </option>
                            <option value="checkbox" <?= $cpf_field_type_selected('checkbox'); ?>>
                                Multiple Choice: Check Box
                            </option>
                        </select>

                        <p class="description"><?php _e('If "Agreeable" is selected, only "Field Key" and "Field Label" (the text shown beside the checkbox) are required. Others are optional.', 'profilepress-pro'); ?></p>

                        <p style="display:none" id="cpf-multi-select">
                            <label>
                                <input type="hidden" name="cpf_multi_select" value="no">
                                <input type="checkbox" name="cpf_multi_select" value="yes" <?php isset($_POST['cpf_multi_select']) ? checked(sanitize_text_field($_POST['cpf_multi_select']), 'yes') : checked($is_multi_selectable, 'yes'); ?>>
                                <strong><?php _e('Check to make this select dropdown "multiple options selectable".'); ?></strong>
                            </label>
                        </p>
                    </td>
                </tr>

                <tr id="pp-custom-field-date-format-row">
                    <th scope="row">
                        <label for="date_format"><?php _e('Date/Time Format', 'profilepress-pro'); ?></label>
                    </th>
                    <td>
                        <select required id="date_format" name="date_format">
                            <?php foreach ($date_formats as $key => $value): ?>
                                <option value="<?= $key ?>" <?php isset($_POST['date_format']) ? selected(sanitize_text_field($_POST['date_format']), $key) : selected(ppress_var($profile_fields, 'options', ''), $key); ?>>
                                    <?= $value ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr id="pp-custom-field-options-row">
                    <th scope="row">Options</th>
                    <td>
                        <input type="text" name="cpf_options" class="regular-text code" value="<?php echo isset($_POST['cpf_options']) ? esc_attr($_POST['cpf_options']) : esc_attr(ppress_var($profile_fields, 'options', '')); ?>"/>

                        <p class="description">
                            <?php _e('Only for use by "File upload" and Multiple Choice field types. Separate multiple options with a comma.', 'profilepress-pro'); ?>
                        </p>

                        <p class="description">
                            <?php printf(
                                __('Say you want to add a radio button with options "yes" and "no"; Select radio buttons in %1$sType%2$s add the options to the %1$sOptions%2$s field separated with a comma ( i.e. yes,no ).', 'profilepress-pro'),
                                '<strong>', '</strong>'
                            ); ?>
                        </p>

                        <p class="description"><?php printf(
                                __('For %1$sfile upload%2$s, specify the file extension the uploader
                            should accept separated by comma(,). E.g. the following is the file
                            extension for pictures / images:
                            %3$spng, jpg, gif%4$s', 'profilepress-pro'),
                                '<strong>', '</strong>', '<code>', '</code>'
                            ); ?>
                        </p>
                    </td>
                </tr>
                <?php do_action('ppress_add_profile_field_settings', $field_id); // backward compat ?>
                <?php do_action('ppress_edit_profile_field_settings', $field_id); ?>
            </table>
            <p>
                <?php wp_nonce_field('pp_custom_profile_fields'); ?>
                <input class="button-primary" type="submit" name="<?= $submit_name; ?>" value="<?php _e('Save Changes', 'profilepress-pro'); ?>">
            </p>
        </div>
    </form>
</div>