<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\SettingsFieldsParser;
use ProfilePress\Core\Membership\Models\Group\GroupFactory;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

$group_details = [
    [
        'id'    => 'name',
        'type'  => 'text',
        'label' => esc_html__('Group Name', 'wp-user-avatar'),
    ],
    [
        'id'          => 'plan_ids',
        'type'        => 'select2',
        'label'       => esc_html__('Membership Plans', 'wp-user-avatar'),
        'options'     => (function () {
            $bucket = [];
            foreach (PlanRepository::init()->retrieveAll() as $plan) {
                $bucket[$plan->get_id()] = $plan->get_name();
            }

            return $bucket;
        })(),
        'description' => esc_html__('Select membership plans to add to this group so customers can switch between the plans.', 'wp-user-avatar')
    ],
    [
        'id'          => 'plans_display_field',
        'type'        => 'select',
        'label'       => esc_html__('Plans Checkout Display', 'wp-user-avatar'),
        'options'     => [
            'radio'  => esc_html__('Radio Buttons', 'wp-user-avatar'),
            'select' => esc_html__('Select Dropdown', 'wp-user-avatar'),
        ],
        'description' => esc_html__('Select how you want to display the membership plans for selection on the Group checkout page.', 'wp-user-avatar')
    ]
];

$group_data = GroupFactory::fromId(absint(ppressGET_var('id')));

if (ppressGET_var('ppress_group_action') == 'edit' && ! $group_data->exists()) {
    ppress_content_http_redirect(PPRESS_MEMBERSHIP_GROUPS_SETTINGS_PAGE);

    return;
}

add_action('add_meta_boxes', function () use ($group_details, $group_data) {

    add_meta_box(
        'ppress-membership-group-content',
        esc_html__('Group Details', 'wp-user-avatar'),
        function () use ($group_details, $group_data) {
            echo '<div class="ppress-membership-group-details">';
            (new SettingsFieldsParser($group_details, $group_data, 'ppress-group-control'))->build();
            echo '</div>';
        },
        'ppmembershipgroup'
    );

    add_meta_box(
        'submitdiv',
        __('Publish', 'wp-user-avatar'),
        function () {
            require dirname(__FILE__) . '/groups-page-sidebar.php';
        },
        'ppmembershipgroup',
        'sidebar'
    );


    if ($group_data->exists()) {
        add_meta_box(
            'ppress-plan-group-checkout-url',
            __('Checkout URL', 'wp-user-avatar'),
            function () use ($group_data) {
                ?>
                <div class="ppress-subscription-plan-payment-links">
                    <p>
                        <input type="text" onfocus="this.select();" readonly="readonly" value="<?= esc_url($group_data->get_checkout_url()) ?>"/>
                    </p>
                </div>
                <?php
            },
            'ppmembershipgroup',
            'sidebar'
        );
    }
});

do_action('add_meta_boxes', 'ppmembershipgroup', new WP_Post(new stdClass()));
?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

        <div id="postbox-container-1" class="postbox-container">
            <?php do_meta_boxes('ppmembershipgroup', 'sidebar', ''); ?>
        </div>
        <div id="postbox-container-2" class="postbox-container">
            <?php do_meta_boxes('ppmembershipgroup', 'advanced', ''); ?>
        </div>
    </div>
    <br class="clear">
</div>