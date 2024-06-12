<?php
/**
 * Provide a license area view for the plugin 
 *
 * This file is used to markup the admin-license aspects of the plugin. 
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-deal-per-order
 * @subpackage hubspot-deal-per-order/admin/partials
 */
?>
<?php
    $message = __('Please enter the license key for this product to activate it. You were given a license key when you purchased this item in the confirmation email.','hubwoo');
    Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_deals_notice( $message, 'update-nag' );
?>
<div class="hubwoo_ms_deals_main_wrapper">
    <div class="wrap woocommerce hub-ms-deals">
    <h1 class="hubdeals_plugin_title"><?php _e( 'License Activation Panel', 'hubwoo' ); ?></h1>
        <form action="" method="post" id="hubwoo-ms-deals-license">
            <table class="form-table">
                <tr>
                    <th style="width:100px;"><label for="hubwoo_ms_deals_license_key"><?php _e( 'License Key', 'hubwoo' )?></label></th>
                    <td>
                        <input required="" class="regular-text" type="text" id="hubwoo_ms_deals_license_key" name="hubwoo_ms_deals_license_key"  value="<?php echo get_option('hubwoo_ms_deals_license_key'); ?>" >
                        <img id="hubwoo-ms-deals-lic-loader" class="hubwoo_ms_deals_hide" src="<?php echo HUBWOO_MS_DEAL_URL . 'admin/images/ajax-loader.gif' ?>"/>
                    </td>
                </tr>
            </table>
            <p class="hubwoo_ms_deals_license_activation_status" style="font-size:18px;color:blue"></p>
            <p class="submit">
                <input type="submit" id="hubwoo_ms_deals_activate_license" name="hubwoo_ms_deals_activate_license" value="<?php _e("Activate","hubwoo")?>" class="button-primary" />
            </p>
        </form>
    </div>
</div>