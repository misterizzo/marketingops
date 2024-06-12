<?php

namespace ProfilePress\Libsodium\MailchimpIntegration\Admin;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Libsodium\MailchimpIntegration\Init;

class WPListTable extends \WP_List_Table
{
    public $items;

    public function __construct()
    {
        $this->items = PROFILEPRESS_sql::get_meta_data_by_key('mc_audience');

        parent::__construct(array(
            'singular' => 'ppress-mailchimp-audience',
            'plural'   => 'ppress-mailchimp-audiences',
            'ajax'     => false
        ));
    }

    public function no_items()
    {
        printf(
            esc_html__('No Mailchimp audience has been added. %sClick here%s to add one.', 'profilepress-pro'),
            '<a href="' . SettingsPage::add_new_audience_url() . '">', '</a>'
        );
    }

    public function get_columns()
    {
        $columns = [
            'cb'          => '<input type="checkbox" />',
            'title'       => esc_html__('Title', 'profilepress-pro'),
            'enabled'     => esc_html__('Enabled', 'profilepress-pro'),
            'audience'    => esc_html__('Audience', 'profilepress-pro'),
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
            '<input type="checkbox" name="mc_audience_id[]" value="%s" />', absint($item['id'])
        );
    }

    public function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }

    public function column_title($item)
    {
        $id = absint($item['id']);

        $edit_url = SettingsPage::edit_audience_url($id);

        $title = '<strong><a href="' . esc_url($edit_url) . '">' . esc_html($item['meta_value']['mc_audience_title']) . '</a></strong>';

        $actions = [
            'edit'   => sprintf('<a href="%s">%s</a>', $edit_url, esc_html__('Edit', 'profilepress-pro')),
            'delete' => sprintf(
                '<a class="pp-confirm-delete" href="%s">%s</a>',
                SettingsPage::delete_audience_url($id), esc_html__('Delete', 'profilepress-pro')
            )
        ];

        return $title . $this->row_actions($actions);
    }

    public function column_audience($item)
    {
        $audience_id = isset($item['meta_value']['mc_audience_select']) ? $item['meta_value']['mc_audience_select'] : false;

        return Init::get_mc_audience_data($audience_id, 'name');
    }

    public function column_enabled($item)
    {
        $status = ppress_var($item['meta_value'], 'mc_audience_enable') == 'true';

        $icon = '<span class="dashicons dashicons-no" style="font-size:28px;color:#C74A4A;"></span>';

        if ($status === true) {
            $icon = '<span class="dashicons dashicons-yes" style="font-size:28px;color:#46b450;"></span>';
        }

        return $icon;
    }

    public function column_subscribers($item)
    {
        $audience_id = isset($item['meta_value']['mc_audience_select']) ? $item['meta_value']['mc_audience_select'] : false;

        return Init::get_mc_audience_data($audience_id, 'member_count');
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

            check_admin_referer('ppress_mc_delete_audience');

            PROFILEPRESS_sql::delete_meta_data(absint($_GET['mc-audience-id']));

            wp_safe_redirect(SettingsPage::settings_page_url());
            exit;
        }

        // Detect when a bulk action is being triggered...
        if ('bulk-delete' == $this->current_action()) {
            check_admin_referer('bulk-' . $this->_args['plural']);
            $ids = $_POST['mc_audience_id'];

            foreach ($ids as $id) {
                $id = absint($id);
                PROFILEPRESS_sql::delete_meta_data($id);
            }
            wp_safe_redirect(SettingsPage::settings_page_url());
            exit;
        }
    }
}
