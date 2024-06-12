<?php

namespace ProfilePress\Libsodium\CampaignMonitorIntegration\Admin;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Libsodium\CampaignMonitorIntegration\Init;

class WPListTable extends \WP_List_Table
{
    public $items;

    public function __construct()
    {
        $this->items = PROFILEPRESS_sql::get_meta_data_by_key('cm_email_list');

        parent::__construct(array(
            'singular' => 'ppress-campaignmonitor-list',
            'plural'   => 'ppress-campaignmonitor-lists',
            'ajax'     => false
        ));
    }

    public function no_items()
    {
        printf(
            esc_html__('No Campaign Monitor list has been added. %sClick here%s to add one.', 'profilepress-pro'),
            '<a href="' . SettingsPage::add_new_email_list_url() . '">', '</a>'
        );
    }

    public function get_columns()
    {
        $columns = [
            'cb'          => '<input type="checkbox" />',
            'title'       => esc_html__('Title', 'profilepress-pro'),
            'enabled'      => esc_html__('Enabled', 'profilepress-pro'),
            'email_list'    => esc_html__('List', 'profilepress-pro'),
            'subscribers' => esc_html__('Subscribers', 'profilepress-pro')
        ];

        return $columns;
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'bulk-delete' => esc_html__('Delete', 'profilepress-pro'),
        );

        return $actions;
    }

    protected function display_tablenav($which)
    {
        if ($which == 'bottom') return;

        if ('top' === $which) {
            wp_nonce_field('bulk-' . $this->_args['plural']);
        }
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

            <?php if ($this->has_items()) : ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions($which); ?>
                </div>
            <?php
            endif;
            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>

            <br class="clear"/>
        </div>
        <?php
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="cm_list_id[]" value="%s" />', absint($item['id'])
        );
    }

    public function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }

    public function column_title($item)
    {
        $id = absint($item['id']);

        $edit_url = SettingsPage::edit_email_list_url($id);

        $title = '<strong><a href="' . esc_url($edit_url) . '">' . esc_html($item['meta_value']['cm_email_list_title']) . '</a></strong>';

        $actions = [
            'edit'   => sprintf('<a href="%s">%s</a>', $edit_url, esc_html__('Edit', 'profilepress-pro')),
            'delete' => sprintf(
                '<a class="pp-confirm-delete" href="%s">%s</a>',
                SettingsPage::delete_email_list_url($id), esc_html__('Delete', 'profilepress-pro')
            )
        ];

        return $title . $this->row_actions($actions);
    }

    public function column_email_list($item)
    {
        $cm_list_id = isset($item['meta_value']['cm_email_list_select']) ? $item['meta_value']['cm_email_list_select'] : false;

        return Init::get_cm_list_data($cm_list_id, 'name');
    }

    public function column_enabled($item)
    {
        $status = ppress_var($item['meta_value'], 'cm_email_list_enable') == 'true';

        $icon = '<span class="dashicons dashicons-no" style="font-size:28px;color:#C74A4A;"></span>';

        if ($status === true) {
            $icon = '<span class="dashicons dashicons-yes" style="font-size:28px;color:#46b450;"></span>';
        }

        return $icon;
    }

    public function column_subscribers($item)
    {
        $cm_list_id = isset($item['meta_value']['cm_email_list_select']) ? $item['meta_value']['cm_email_list_select'] : false;

        return Init::get_cm_list_data($cm_list_id, 'member_count');
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_actions();
    }

    public function process_actions()
    {
        if ( ! current_user_can('manage_options')) return;

        if ('delete' === $this->current_action()) {

            check_admin_referer('ppress_cm_delete_email_list');

            PROFILEPRESS_sql::delete_meta_data(absint($_GET['cm-email-list-id']));

            wp_safe_redirect(SettingsPage::settings_page_url());
            exit;
        }

        // Detect when a bulk action is being triggered...
        if ('bulk-delete' == $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);
            $ids = $_POST['cm_list_id'];

            foreach ($ids as $id) {
                PROFILEPRESS_sql::delete_meta_data(absint($id));
            }
            wp_safe_redirect(SettingsPage::settings_page_url());
            exit;
        }
    }
}
