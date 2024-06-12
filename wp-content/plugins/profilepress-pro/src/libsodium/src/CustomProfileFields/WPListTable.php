<?php

namespace ProfilePress\Libsodium\CustomProfileFields;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;

class WPListTable extends \WP_List_Table
{
    function __construct()
    {
        parent::__construct(array(
            'singular' => esc_html__('field', 'profilepress-pro'),
            'plural'   => esc_html__('fields', 'profilepress-pro'),
            'ajax'     => false
        ));

    }

    function no_items()
    {
        _e('No custom field available.', 'profilepress-pro');
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'label_name':
            case 'description':
            case 'field_key':
                return $item[$column_name];
            case 'type':
                if ($item[$column_name] == 'agreeable') {
                    return esc_html__('Single Checkbox', 'profilepress-pro');
                }

                if ($item[$column_name] == 'checkbox') {
                    return esc_html__('Multiple Checkbox', 'profilepress-pro');
                }

                if ($item[$column_name] == 'tel') {
                    return esc_html__('Telephone Number', 'profilepress-pro');
                }

                if ($item[$column_name] == 'date') {
                    return esc_html__('Date / Time', 'profilepress-pro');
                }

                return ucwords($item[$column_name]);
        }
    }

    public function print_column_headers($with_id = true)
    {
        echo '<th class="custom-field-anchor"><span class="dashicons dashicons-menu"></span></th>';
        parent::print_column_headers($with_id);
    }

    public function single_row_columns($item)
    {
        echo '<td class="custom-field-anchor"><span class="dashicons dashicons-menu"></span></td>';
        parent::single_row_columns($item);
    }

    function get_columns()
    {
        $columns = array(
            'label_name'  => esc_html__('Field Name', 'profilepress-pro'),
            'description' => esc_html__('Description', 'profilepress-pro'),
            'field_key'   => esc_html__('Field Key', 'profilepress-pro'),
            'type'        => esc_html__('Field Type', 'profilepress-pro')
        );

        return $columns;
    }

    function column_label_name($item)
    {
        $nonce_delete = wp_create_nonce('pp_delete_field');

        $id = absint($item['id']);

        $edit_link   = esc_url(add_query_arg(['action' => 'edit', 'field' => $id]));
        $delete_link = esc_url(add_query_arg(['action' => 'delete', 'field' => $id, '_wpnonce' => $nonce_delete]));

        $actions = [
            'edit'   => sprintf('<a href="%s">%s</a>', $edit_link, esc_html__('Edit', 'profilepress-pro')),
            'delete' => sprintf('<a class="pp-confirm-delete" href="%s">%s</a>', $delete_link, esc_html__('Delete', 'profilepress-pro')),
        ];

        $a = '<a href="' . $edit_link . '">' . ppress_decode_html_strip_tags($item['label_name']) . '</a>';

        return '<strong>' . $a . '</strong>' . $this->row_actions($actions);
    }

    function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $this->items = PROFILEPRESS_sql::get_profile_custom_fields();
    }

    function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            check_admin_referer('pp_delete_field');

            if ( ! current_user_can('manage_options')) return;

            $field_id = absint($_GET['field']);

            PROFILEPRESS_sql::delete_profile_custom_field($field_id);
            do_action('ppress_delete_custom_field_db', $field_id);

            wp_safe_redirect(PPRESS_CUSTOM_FIELDS_SETTINGS_PAGE);
            exit;
        }
    }

    /**
     * Extra controls to be displayed between bulk actions and pagination
     *
     * @param string $which
     */
    public function extra_tablenav($which)
    {
        do_action('ppress_custom_fields_extra_tablenav', $which);
    }

    /**
     * Add ppview to a list of css classes included in the table
     *
     * THis method overrides that of the parent class
     *
     * @return array List of CSS classes for the table tag.
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'custom_profile_fields', 'ppview');
    }

}
