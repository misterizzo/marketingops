<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\PlanIntegrationsMetabox;
use ProfilePress\Core\Admin\SettingsPages\Membership\SettingsFieldsParser;
use ProfilePress\Core\Classes\ExtensionManager;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;

$plan_data   = ppress_get_plan(absint(ppressGET_var('id')));
$plan_extras = $plan_data->get_meta($plan_data::PLAN_EXTRAS);

if (ppressGET_var('ppress_subp_action') == 'edit' && ! $plan_data->exists()) {
    ppress_content_http_redirect(PPRESS_MEMBERSHIP_PLANS_SETTINGS_SLUG);

    return;
}

$append = ['create_new' => esc_html__('Create user role for this membership plan', 'wp-user-avatar')];

if ( ! is_null(get_role('ppress_plan_' . $plan_data->id))) {
    $append = [];
}

$user_roles = $append + (function () {
        $core_roles = ppress_wp_roles_key_value();
        unset($core_roles['administrator']);
        unset($core_roles['editor']);
        unset($core_roles['author']);
        unset($core_roles['contributor']);
        unset($core_roles['subscriber']);

        return $core_roles;
    })();

$plan_details = [
    [
        'id'    => 'name',
        'type'  => 'text',
        'label' => esc_html__('Plan Name', 'wp-user-avatar')
    ],
    [
        'id'          => 'description',
        'type'        => 'wp_editor',
        'label'       => esc_html__('Plan Description', 'wp-user-avatar'),
        'description' => esc_html__('A description of this plan. This will be displayed on the checkout page.', 'wp-user-avatar')
    ],
    [
        'id'          => 'order_note',
        'type'        => 'textarea',
        'label'       => esc_html__('Purchase Note', 'wp-user-avatar'),
        'description' => esc_html__('Enter an optional note or special instructions to send the customer after purchase. These will be added to the order receipt.', 'wp-user-avatar')
    ],
    [
        'id'          => 'user_role',
        'type'        => 'select',
        'options'     => $user_roles,
        'label'       => esc_html__('User Role', 'wp-user-avatar'),
        'description' => esc_html__('Select the user role to associate with this membership plan. Users that subscribe to this plan will be assigned this user role.', 'wp-user-avatar')
    ],
    [
        'id'          => 'price',
        'type'        => 'price',
        'label'       => esc_html__('Price', 'wp-user-avatar') . sprintf(' (%s)', ppress_get_currency_symbol()),
        'description' => esc_html__('The price of this membership plan. Enter 0 to make this plan free.', 'wp-user-avatar')
    ]
];

$subscription_settings = [
    [
        'id'      => 'billing_frequency',
        'type'    => 'select',
        'label'   => esc_html__('Billing Frequency', 'wp-user-avatar'),
        'options' => SubscriptionBillingFrequency::get_all()
    ],
    [
        'id'      => 'subscription_length',
        'type'    => 'select',
        'label'   => esc_html__('Subscription Length', 'wp-user-avatar'),
        'options' => [
            'renew_indefinitely' => esc_html__('Renew indefinitely until member cancels', 'wp-user-avatar'),
            'fixed'              => esc_html__('Fixed number of payments', 'wp-user-avatar')
        ]
    ],
    [
        'id'          => 'total_payments',
        'type'        => 'number',
        'label'       => esc_html__('Total Payments', 'wp-user-avatar'),
        'description' => esc_html__('The total number of recurring billing cycles including the trial period (if applicable).  Keep in mind that once a member has completed the last payment, the subscription will not expire — essentially giving them lifetime access.', 'wp-user-avatar')
    ],
    [
        'id'          => 'signup_fee',
        'type'        => 'price',
        'label'       => esc_html__('Signup Fee', 'wp-user-avatar') . sprintf(' (%s)', ppress_get_currency_symbol()),
        'description' => esc_html__('Optional signup fee to charge subscribers for the first billing cycle.', 'wp-user-avatar')
    ],
    [
        'id'          => 'free_trial',
        'type'        => 'select',
        'options'     => SubscriptionTrialPeriod::get_all(),
        'label'       => esc_html__('Free Trial', 'wp-user-avatar'),
        'description' => esc_html__('Allow members free access for a specified duration of time before charging them.', 'wp-user-avatar')
    ]
];

$file_downloads_setting_url = add_query_arg(['view' => 'payments', 'section' => 'file-downloads'], PPRESS_SETTINGS_SETTING_PAGE);

$meta_box_settings = apply_filters('ppress_admin_membership_plan_metabox_settings', [
    'digital_products' => [
        'tab_title' => esc_html__('Digital Products', 'wp-user-avatar'),
        [
            'id'          => 'df',
            'type'        => 'digital_files',
            'label'       => esc_html__('Product Files', 'wp-user-avatar'),
            'description' => esc_html__('Upload eBooks, music, videos, software or anything else digital.', 'wp-user-avatar'),
            'priority'    => 5
        ],
        [
            'id'          => 'df_download_limit',
            'type'        => 'number',
            'label'       => esc_html__('Download Limit', 'wp-user-avatar'),
            'description' => sprintf(
                esc_html__('Set to 0 for unlimited re-downloads. Leave blank to use %sglobal setting%s', 'wp-user-avatar'),
                '<a target="_blank" href="' . $file_downloads_setting_url . '">', '</a>'
            ),
            'priority'    => 10
        ],
        [
            'id'          => 'df_download_expiry',
            'type'        => 'number',
            'label'       => esc_html__('Download Expiry', 'wp-user-avatar'),
            'description' => sprintf(
                esc_html__('Enter the number of days before a download link expires. Set to 0 for no expiration. Leave blank to use %sglobal setting%s.', 'wp-user-avatar'),
                '<a target="_blank" href="' . $file_downloads_setting_url . '">', '</a>'
            ),
            'priority'    => 15
        ]
    ]
]);

if ( ! ExtensionManager::is_premium()) {
    $pro_features = [
        'LearnDash'        => [
            esc_html__("Sell access to LearnDash courses, and enroll users after registration to specific courses.", 'wp-user-avatar')
        ],
        'Mailchimp'        => [
            esc_html__("Subscribe members to your Mailchimp audiences when they register or subscribe to a membership and sync membership and profile changes with Mailchimp.", 'wp-user-avatar')
        ],
        'Campaign Monitor' => [
            esc_html__("Subscribe members to your Campaign Monitor lists when they register or subscribe to a membership plan and sync membership and profile changes with Campaign Monitor.", 'wp-user-avatar')
        ],
        'WooCommerce' => [
            esc_html__("Sell paid memberships via WooCommerce, and create members-only discounts.", 'wp-user-avatar')
        ]
    ];
    ob_start();
    ?>
    <div class="ppress-pro-features-wrap">
        <?php foreach ($pro_features as $label => $feature): ?>
            <div class="ppress-pro-features">
                <strong><?php echo esc_html($label) ?>:</strong> <?php echo esc_html(implode(', ', $feature)) ?>
            </div>
        <?php endforeach; ?>
        <div>
            <a href="https://profilepress.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=edit_plan_page_integration_metabox" target="__blank" class="button-primary">
                <?php esc_html_e('Get ProfilePress Premium →', 'wp-user-avatar') ?>
            </a>
        </div>
    </div>
    <?php

    $content = ob_get_clean();

    $meta_box_settings['pro_upsell'] = [
        'tab_title' => esc_html__('Pro Integrations', 'wp-user-avatar'),
        [
            'label'   => esc_html__('Pro Features', 'wp-user-avatar'),
            'type'    => 'custom',
            'content' => $content
        ]
    ];
}

add_action('add_meta_boxes', function () use ($subscription_settings, $plan_details, $plan_data, $plan_extras, $meta_box_settings) {
    add_meta_box(
        'ppress-membership-plan-content',
        esc_html__('Plan Details', 'wp-user-avatar'),
        function () use ($plan_details, $plan_data) {
            echo '<div class="ppress-membership-plan-details">';
            (new SettingsFieldsParser($plan_details, $plan_data))->build();
            echo '</div>';
        },
        'ppmembershipplan'
    );

    add_meta_box(
        'ppress-subscription-plan-settings',
        esc_html__('Subscription Settings', 'wp-user-avatar'),
        function () use ($subscription_settings, $plan_data) {
            echo '<div class="ppress-subscription-plan-settings">';
            (new SettingsFieldsParser($subscription_settings, $plan_data))->build();
            echo '</div>';
        },
        'ppmembershipplan'
    );

    add_meta_box(
        'pp-form-builder-metabox',
        esc_html__('Downloads & Integrations', 'wp-user-avatar'),
        function () use ($meta_box_settings, $plan_extras) {
            echo '<div class="ppress-plan-integrations">';
            (new PlanIntegrationsMetabox($meta_box_settings, $plan_extras))->build();
            echo '</div>';
        },
        'ppmembershipplan'
    );

    add_meta_box(
        'submitdiv',
        __('Publish', 'wp-user-avatar'),
        function () {
            require dirname(__FILE__) . '/plans-page-sidebar.php';
        },
        'ppmembershipplan',
        'sidebar'
    );

    add_meta_box(
        'ppress-subscription-plan-summary',
        __('Summary', 'wp-user-avatar'),
        function () {
            ?>
            <div class="ppress-subscription-plan-summary-content">
            </div>
            <?php
        },
        'ppmembershipplan',
        'sidebar'
    );

    if ($plan_data->exists()) {
        add_meta_box(
            'ppress-subscription-plan-links',
            __('Checkout URL', 'wp-user-avatar'),
            function () use ($plan_data) {
                $checkout_url = $plan_data->get_checkout_url();
                ?>
                <div class="ppress-subscription-plan-payment-links">
                    <p>
                        <input type="text" onfocus="this.select();" readonly="readonly" value="<?= esc_url($checkout_url) ?>"/>
                    </p>
                </div>
                <?php
            },
            'ppmembershipplan',
            'sidebar'
        );
    }
});

do_action('add_meta_boxes', 'ppmembershipplan', new WP_Post(new stdClass()));
?>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-1" class="postbox-container">
                <?php do_meta_boxes('ppmembershipplan', 'sidebar', ''); ?>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <?php do_meta_boxes('ppmembershipplan', 'advanced', ''); ?>
            </div>
        </div>
        <br class="clear">
    </div>

<?php add_action('admin_footer', function () { ?>
    <script type="text/javascript">
        (function ($) {

            $('#billing_frequency').on('change', function () {

                if ($(this).val() !== 'lifetime') {

                    $('#field-role-signup_fee').show();
                    $('#field-role-free_trial').show();

                    $('#field-role-subscription_length').show()
                        .find('.ppress-plan-control').trigger('change');
                } else {
                    $('#field-role-subscription_length').hide();
                    $('#field-role-total_payments').hide();
                    $('#field-role-signup_fee').hide();
                    $('#field-role-free_trial').hide();
                }
            });

            $('#subscription_length').on('change', function () {
                $('#field-role-total_payments').toggle($(this).val() === 'fixed');
            });

            $('#billing_frequency').trigger('change');

            $(window).on('load', function () {
                var tmpl = wp.template('ppress-plan-summary');

                $('.ppress-plan-control').on('change', function () {
                    $('#ppress-subscription-plan-summary .ppress-subscription-plan-summary-content').html(
                        tmpl({
                            'price': $('.form-field #price').val(),
                            'billing_frequency': $('.form-field #billing_frequency').val(),
                            'total_payments': $('.form-field #total_payments').val(),
                            'signup_fee': $('.form-field #signup_fee').val(),
                            'subscription_length': $('.form-field #subscription_length').val(),
                            'free_trial': $('.form-field #free_trial').val(),
                        })
                    );
                }).trigger('change');

            });
        })(jQuery);
    </script>
    <?php
});