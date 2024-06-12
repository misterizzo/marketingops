<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\ExportPage;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage extends AbstractSettingsPage
{
    function __construct()
    {
        add_action('ppress_admin_settings_page_export', [$this, 'settings_page_function']);
        add_filter('ppress_membership_dashboard_settings_page_title', [$this, 'admin_page_title']);

        add_action('admin_init', [$this, 'handle_export']);
    }

    public function admin_page_title($title = '')
    {
        if (ppressGET_var('view') == 'export') {
            $title = esc_html__('Export', 'wp-user-avatar');
        }

        return $title;
    }

    public function settings_page_function()
    {
        add_action('wp_cspa_main_content_area', [$this, 'admin_settings_page_callback'], 10, 2);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->page_header($this->admin_page_title());
        $instance->remove_page_form_tag();
        $instance->build(true);
    }

    public function handle_export()
    {
        if (isset($_POST['ppress_do_export'])) {

            if (current_user_can('manage_options')) {

                if (check_admin_referer('ppress_export_' . ppressPOST_var('ppress_export_type'))) {

                    $class = sprintf(
                        '\ProfilePress\Core\Admin\SettingsPages\Membership\ExportPage\%s',
                        sanitize_text_field(ppressPOST_var('ppress_export_type', ''))
                    );

                    if (class_exists($class) && method_exists($class, 'execute')) {
                        (new $class(ppress_clean($_POST)))->execute();
                    }
                }
            }
        }
    }

    private function render_form_view($path)
    {
        $membership_plans = ppress_cache_transform('ppress_export_membership_plans', function () {
            return PlanRepository::init()->retrieveAll();
        });

        $payment_methods = ppress_cache_transform('ppress_export_payment_methods', function () {
            return PaymentMethods::get_instance()->get_all();
        });

        $order_statuses = ppress_cache_transform('ppress_export_order_statuses', function () {
            return OrderStatus::get_all();
        });

        $subscription_statuses = ppress_cache_transform('ppress_export_subscription_statuses', function () {
            return SubscriptionStatus::get_all();
        });

        ob_start();
        require dirname(__FILE__) . '/views/' . $path . '.php';

        return ob_get_clean();
    }

    public function admin_settings_page_callback()
    {
        $exports = apply_filters('ppress_csv_exports', [
            'SalesEarningsExport' => [
                'title'       => esc_html__('Export Sales and Earnings', 'wp-user-avatar'),
                'description' => esc_html__('Download a CSV of all sales or earnings on a day-by-day basis.', 'wp-user-avatar'),
                'form'        => $this->render_form_view('sales-earnings')
            ],
            'ProductSalesExport'  => [
                'title'       => esc_html__('Export Plan Sales', 'wp-user-avatar'),
                'description' => esc_html__('Download a CSV file containing a record of each sale of a plan along with the customer information.', 'wp-user-avatar'),
                'form'        => $this->render_form_view('product-sales')
            ],
            'OrdersExport'        => [
                'title'       => esc_html__('Export Orders', 'wp-user-avatar'),
                'description' => esc_html__('Download a CSV of all orders.', 'wp-user-avatar'),
                'form'        => $this->render_form_view('orders')
            ],
            'SubscriptionsExport' => [
                'title'       => esc_html__('Export Subscriptions', 'wp-user-avatar'),
                'description' => esc_html__('Download a CSV of subscriptions.', 'wp-user-avatar'),
                'form'        => $this->render_form_view('subscriptions')
            ],
            'CustomersExport'     => [
                'title'       => esc_html__('Export Customers', 'wp-user-avatar'),
                'description' => esc_html__('Download a CSV of customers.', 'wp-user-avatar'),
                'form'        => $this->render_form_view('customers')
            ],
            'PlansExport'         => [
                'title'       => esc_html__('Export Plans', 'wp-user-avatar'),
                'description' => esc_html__('Download a CSV of membership plans.', 'wp-user-avatar'),
                'form'        => $this->render_form_view('plans')
            ]
        ], $this);
        ?>
        <div class="ppress-export-cards-wrap">

            <dl class="ppress-export-card-item-wrap">

                <?php foreach ($exports as $export_id => $export) : ?>

                    <div class="ppress-export-card-item">
                        <dt class="ppress-export-card-item-header">
                            <p><?php esc_html_e($export['title']); ?></p>
                        </dt>
                        <dd class="ppress-export-card-item-inside">
                            <p><?php esc_html_e($export['description']); ?></p>
                            <form id="ppress-export-sales-earnings" class="ppress-export-form" method="post">
                                <?php echo $export['form']; ?>
                                <?php wp_nonce_field('ppress_export_' . $export_id); ?>
                                <input type="hidden" name="ppress_export_type" value="<?php esc_attr_e($export_id); ?>">
                                <input class="button" type="submit" name="ppress_do_export" value="<?php esc_html_e('Export', 'wp-user-avatar'); ?>">
                            </form>
                        </dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
        <?php
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}