<?php

use ProfilePress\Core\Classes\ExtensionManager;

$payment_methods = ProfilePress\Core\Membership\PaymentMethods\PaymentMethods::get_instance()->get_all(true); ?>

<div class="ppress-payment-methods-wrap<?= ExtensionManager::is_premium() ? ' is-premium' : '' ?>">
    <table cellspacing="0" class="widefat">
        <thead>
        <tr>
            <th class="ppress-payment-method-table-sort"></th>
            <th class="ppress-payment-method-table-title"><?php esc_html_e('Method', 'wp-user-avatar') ?></th>
            <th class="ppress-payment-method-table-enabled"><?php esc_html_e('Enabled', 'wp-user-avatar') ?></th>
            <th class="ppress-payment-method-table-description"><?php esc_html_e('Description', 'wp-user-avatar') ?></th>
            <th class="ppress-payment-method-table-subscription-support"><?php esc_html_e('Subscription Support', 'wp-user-avatar') ?></th>
            <th class="ppress-payment-method-table-actions"></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($payment_methods as $payment_method) : ?>
            <?php $config_url = esc_url(add_query_arg('method', $payment_method->get_id())); ?>
            <tr id="<?php echo $payment_method->get_id() ?>">
                <td class="ppress-payment-method-table-sort">
                    <span class="gateway-sort"><span class="dashicons dashicons-menu"></span></span>
                </td>
                <td class="ppress-payment-method-table-title">
                    <a href="<?= $config_url ?>"><?php echo $payment_method->get_method_title() ?></a>
                </td>
                <td class="ppress-payment-method-table-enabled">
                    <?php echo $payment_method->is_enabled() ?
                        '<span class="ppress-payment-method-icon ico-yes"><span class="dashicons dashicons-yes"></span></span>' :
                        '<span class="ppress-payment-method-icon"><span class="dashicons dashicons-no-alt"></span></span>'
                    ?>
                </td>
                <td class="ppress-payment-method-table-description">
                    <?php echo $payment_method->get_method_description() ?>
                </td>
                <td class="ppress-payment-method-table-subscription-support">
                    <?php echo $payment_method->supports($payment_method::SUBSCRIPTIONS) ?
                        '<span class="ppress-payment-method-icon ico-yes"><span class="dashicons dashicons-yes"></span></span>' :
                        '<span class="ppress-payment-method-icon"><span class="dashicons dashicons-no-alt"></span></span>'
                    ?>
                </td>
                <td class="ppress-payment-method-table-actions">
                    <a href="<?= $config_url ?>" class="button"><?php esc_html_e('Configure', 'wp-user-avatar'); ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ( ! ProfilePress\Core\Classes\ExtensionManager::is_premium()) :

            $pro_payment_methods = [
                'paypal'   => [
                    'name'        => 'PayPal',
                    'description' => esc_html__('Wish to accept payments and sell memberships via PayPal? %supgrade to premium%s.', 'wp-user-avatar')
                ],
                'mollie'   => [
                    'name'        => 'Mollie',
                    'description' => esc_html__('%sUpgrade to premium%s to accept one-time and recurring payments via iDEAL, Credit Card, Apple Pay, Klarna, Bancontact, in3 etc with Mollie.', 'wp-user-avatar')
                ],
                'razorpay' => [
                    'name'        => 'Razorpay',
                    'description' => esc_html__('%sUpgrade to premium%s to accept one-time and recurring payments via Razorpay.', 'wp-user-avatar')
                ]
            ];

            foreach ($pro_payment_methods as $payment_method_id => $pm_args) {

                $payment_method_upsell_url = sprintf('https://profilepress.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=%s-gateway-method', $payment_method_id);

                $payment_method_addon_url = sprintf('https://profilepress.com/addons/%1$s/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=%1$s-gateway-method', $payment_method_id);

                ?>
                <tr>
                    <td class="ppress-payment-method-table-sort">
                    <span class="gateway-sort"><span class="dashicons dashicons-menu"></span>
                    </td>
                    <td class="ppress-payment-method-table-title">
                        <a target="_blank" href="<?= $payment_method_addon_url ?>"><?= $pm_args['name'] ?></a>
                    </td>
                    <td class="ppress-payment-method-table-enabled">
                        <span class="ppress-payment-method-icon"><span class="dashicons dashicons-no-alt"></span></span>
                    </td>
                    <td class="ppress-payment-method-table-description">
                        <?php echo sprintf(
                            $pm_args['description'],
                            '<a target="_blank" href="' . $payment_method_upsell_url . '">', '</a>'
                        ) ?>
                    </td>
                    <td class="ppress-payment-method-table-subscription-support">
                        <span class="ppress-payment-method-icon ico-yes"><span class="dashicons dashicons-yes"></span></span>
                    </td>
                    <td class="ppress-payment-method-table-actions">
                        <a target="_blank" href="<?= $payment_method_addon_url ?>" class="button"><?php esc_html_e('Configure', 'wp-user-avatar'); ?></a>
                    </td>
                </tr>
                <?php
            }
            ?>
        <?php endif ?>
        </tbody>
    </table>
</div>