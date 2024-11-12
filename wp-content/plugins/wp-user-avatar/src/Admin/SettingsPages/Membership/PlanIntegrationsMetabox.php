<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership;


class PlanIntegrationsMetabox
{
    protected $args;
    protected $saved_values;
    protected $plan_id;

    /**
     * @param array $args
     */
    public function __construct($args, $saved_values)
    {
        $this->args         = $args;
        $this->saved_values = $saved_values;
    }

    private function saved_values_bucket($key)
    {
        return isset($this->saved_values[$key]) ? $this->saved_values[$key] : '';
    }

    public function text($name, $options)
    {
        $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
        printf(
            '<input type="text" class="short" name="%1$s" id="%1$s" value="%3$s" placeholder="%2$s">',
            esc_attr($name), esc_attr($placeholder), esc_attr($this->saved_values_bucket($name))
        );
    }

    public function number($name, $options)
    {
        $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
        printf(
            '<input type="number" class="short" name="%1$s" id="%1$s" value="%3$s" placeholder="%2$s">',
            esc_attr($name), esc_attr($placeholder), esc_attr($this->saved_values_bucket($name))
        );
    }

    public function upload($name, $options)
    {
        $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
        echo '<div class="pp_upload_field_container">';
        printf(
            '<input type="text" class="pp_upload_field short large-text" name="%1$s" id="%1$s" value="%3$s" placeholder="%2$s">',
            esc_attr($name), esc_attr($placeholder), esc_attr($this->saved_values_bucket($name))
        );
        printf('<span class="pp_upload_file"><a href="#" class="pp_upload_button">%s</a></span>', esc_html__('Upload Image', 'wp-user-avatar'));
        echo '</div>';
    }

    private function digital_files_row($name, $file_name = '', $file_url = '')
    {
        ?>
        <tr>
            <td class="sort"></td>
            <td class="file_name">
                <input type="text" class="input_text" placeholder="<?php esc_html_e('File name', 'wp-user-avatar') ?>" name="<?= esc_attr($name) ?>_names[]" value="<?= esc_attr($file_name) ?>">
            </td>
            <td class="file_url">
                <input type="text" class="input_text" placeholder="https://" name="<?= esc_attr($name) ?>_urls[]" value="<?= esc_url($file_url) ?>">
            </td>
            <td class="file_url_choose">
                <a href="#" class="button upload_file_button" data-choose="<?php esc_html_e('Choose file', 'wp-user-avatar') ?>" data-update="<?php esc_html_e('Insert file URL', 'wp-user-avatar') ?>"><?php esc_html_e('Choose file', 'wp-user-avatar') ?></a>
            </td>
            <td><a href="#" class="delete">x</a></td>
        </tr>
        <?php
    }

    public function digital_files($name, $options)
    {
        $url_field_id  = sprintf('%s_urls', $name);
        $name_field_id = sprintf('%s_names', $name);
        ?>
        <table class="widefat downloadable_files">
            <thead>
            <tr>
                <th class="sort">&nbsp;</th>
                <th><?php esc_html_e('Name', 'wp-user-avatar') ?></th>
                <th colspan="2"><?php esc_html_e('File URL', 'wp-user-avatar') ?></span></th>
                <th style="width:20px;">&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php if (isset($this->saved_values[$url_field_id]) && is_array($this->saved_values[$url_field_id]) && ! empty($this->saved_values[$url_field_id])) : ?>
                <?php foreach ($this->saved_values[$url_field_id] as $index => $file_url) : ?>
                    <?php $file_name = isset($this->saved_values[$name_field_id]) && is_array($this->saved_values[$name_field_id]) ? $this->saved_values[$name_field_id][$index] : ''; ?>
                    <?php $this->digital_files_row($name, $file_name, $file_url); ?>
                <?php endforeach; ?>
            <?php else : ?>
                <?php $this->digital_files_row($name); ?>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2">
                    <a href="#" class="button insert"><?php esc_html_e('Add File', 'wp-user-avatar') ?></a>
                </th>
                <th colspan="3">
                </th>
            </tr>
            </tfoot>
        </table>

        <script type="text/html" id="tmpl-ppress-add-digital-file">
            <?php $this->digital_files_row($name); ?>
        </script>
        <?php
    }

    public function textarea($name, $options)
    {
        $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
        printf(
            '<textarea class="short" name="%1$s" id="%1$s" placeholder="%2$s">%3$s</textarea>',
            esc_attr($name), esc_attr($placeholder), esc_textarea($this->saved_values_bucket($name))
        );
    }

    public function checkbox($name, $options)
    {
        $checkbox_label = isset($options['checkbox_label']) ? $options['checkbox_label'] : '';

        printf('<input type="hidden" style="display: none" name="%1$s" value="false">', esc_attr($name));

        printf(
            '<input type="checkbox" class="checkbox" name="%1$s" id="%1$s" value="true" %2$s>',
            esc_attr($name), checked('true', $this->saved_values_bucket($name), false)
        );

        printf('<span class="description">%s</span>', $checkbox_label);
    }

    public function select($name, $options)
    {
        printf('<select id="%1$s" name="%1$s" class="select short">', esc_attr($name));

        if (
            isset($options['options']) &&
            ($options['options'] instanceof \Generator || (is_array($options['options']) && ! empty($options['options'])))
        ) {
            foreach ($options['options'] as $id => $val) {
                if (is_array($val)) {
                    echo "<optgroup label='$id'>";
                    foreach ($val as $id2 => $val2) {
                        printf('<option value="%1$s" %3$s>%2$s</option>', $id2, $val2, selected($id2, $this->saved_values_bucket($name), false));
                    }
                    echo "</optgroup>";
                } else {
                    printf('<option value="%1$s" %3$s>%2$s</option>', $id, $val, selected($id, $this->saved_values_bucket($name), false));
                }
            }
        }

        echo '</select>';
    }

    public function custom($name, $options)
    {
        echo isset($options['content']) ? wp_kses_post($options['content']) : '';
    }

    private function select2_selected($id, $name)
    {
        $bucket = $this->saved_values_bucket($name);

        return in_array($id, is_array($bucket) ? $bucket : []) ? 'selected="selected"' : '';
    }

    public function select2($name, $options)
    {
        printf('<input name="%1$s[]" type="hidden" value="">', esc_attr($name));
        printf('<select data-placeholder="%2$s" id="%1$s" name="%1$s[]" class="select ppselect2 short" multiple>', esc_attr($name), esc_html__('Select...', 'wp-user-avatar'));

        if (
            isset($options['options']) &&
            ($options['options'] instanceof \Generator || (is_array($options['options']) && ! empty($options['options'])))
        ) {
            foreach ($options['options'] as $id => $val) {
                if (is_array($val)) {
                    echo "<optgroup label='$id'>";
                    foreach ($val as $id2 => $val2) {
                        printf('<option value="%1$s" %3$s>%2$s</option>', $id2, $val2, $this->select2_selected($id2, $name));
                    }
                    echo "</optgroup>";
                } else {
                    printf('<option value="%1$s" %3$s>%2$s</option>', $id, $val, $this->select2_selected($id, $name));
                }
            }
        }

        echo '</select>';
    }

    public function build()
    {
        $tabs         = [];
        $tab_settings = [];
        foreach ($this->args as $key => $value) {
            $tabs[$key] = $value['tab_title'];
            unset($value['tab_title']);
            $tab_settings[$key] = wp_list_sort($value, ['priority' => 'ASC']);
        }
        ob_start();
        ?>
        <style>
            #pp-form-builder-metabox .ppress-plan-integrations ul.pp-tabs li a::before {
                font-family: none;
                content: none;
            }

            #pp-form-builder-metabox .pp-form-builder_options_panel label {
                width: 140px;
            }

            .ppview .postbox#pp-form-builder-metabox input {
                width: 100% !important;
                max-width: 100% !important;
            }

            .ppview .postbox#pp-form-builder-metabox .downloadable_files input {
                width: auto !important;
                max-width: none !important;
            }

            .pp-form-builder_options_panel fieldset.form-field, .pp-form-builder_options_panel .form-field {
                padding-right: 0 !important;
            }

            .pp-form-builder_options_panel .pp-field-row-content {
                width: 98% !important;
            }

            .ppview .postbox#pp-form-builder-metabox .widefat th,
            .ppview .postbox#pp-form-builder-metabox .widefat td {
                padding-right: 0;
                padding-left: 5px;
            }

            .ppview .postbox#pp-form-builder-metabox .widefat td input {
                width: 100% !important;
            }

            .ppview .postbox#pp-form-builder-metabox .widefat th.sort {
                width: 15px;
                padding: 5px !important;
            }

            .ppview .postbox#pp-form-builder-metabox .widefat td.sort {
                width: 17px;
                cursor: move;
                font-size: 15px;
                text-align: center;
                background: #f9f9f9;
                padding-right: 7px !important;
            }

            .ppview .postbox#pp-form-builder-metabox .widefat td.sort::before {
                content: "\f333";
                font-family: Dashicons;
                text-align: center;
                line-height: 1;
                color: #999;
                display: block;
                width: 17px;
                float: left;
                height: 100%;
            }

            .ppress-plan-integrations .pp-field-row-content .delete {
                display: block;
                text-indent: -9999px;
                position: relative;
                height: 1em;
                width: 1em;
                font-size: 1.2em;
            }

            .ppress-plan-integrations .pp-field-row-content .delete::before {
                font-family: Dashicons;
                speak: never;
                font-weight: 400;
                font-variant: normal;
                text-transform: none;
                line-height: 1;
                -webkit-font-smoothing: antialiased;
                margin: 0;
                text-indent: 0;
                position: absolute;
                top: 5px;
                left: 0;
                width: 100%;
                height: 100%;
                text-align: center;
                content: "\f153";
                color: #999;
            }

        </style>
        <?php echo ppress_minify_css(ob_get_clean()); ?>
        <div class="panel-wrap pp-form-builder-mb-data">
            <ul class="pp-form-builder-mb-data_tabs pp-tabs" style="width:22%">
                <?php foreach ($tabs as $key => $value) : $key = esc_attr($key); ?>
                    <?php if (empty($tab_settings[$key])) continue; ?>
                    <li class="<?= $key ?>_options <?= $key ?>_tab">
                        <a href="#<?= $key ?>_data"><span><?= $value ?></span></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php foreach ($tab_settings as $key => $fields) {
                echo '<div id="' . esc_attr($key) . '_data" class="panel pp-form-builder_options_panel hidden" style="width: 78%">';

                foreach ($fields as $options) {
                    $field_id = $options['id'] ?? '';

                    echo sprintf('<div class="form-field %s_wrap">', $field_id);
                    echo "<label for=\"$field_id\">" . wp_kses_post($options['label'] ?? '') . '</label>';
                    echo '<div class="pp-field-row-content">';
                    $this->{$options['type']}($field_id, $options);
                    echo '</div>';
                    if ( ! empty($options['description'])) {
                        printf('<p class="description">%s</p>', wp_kses_post($options['description']));
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
            ?>
        </div>
        <?php
    }
}