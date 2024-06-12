<?php
/**
 * Provide a license area view for the plugin  
 *
 * This file is used to markup the admin-license aspects of the plugin. 
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-field-to-field-sync
 * @subpackage hubspot-field-to-field-sync/admin/partials
 */
?>
<?php
    $message = __('Please enter the license key for this product to activate it. You were given a license key when you purchased this item in the confirmation email.','hubwoo');
    Hubspot_Field_To_Field_Sync::hubwoo_ftf_notice( $message, 'update-nag' );
?>
<div class="hubwoo_ftf_main_wrapper">
    <div class="wrap woocommerce hubwoo_ftf_settings">
    <h1 class="hubftf_plugin_title"><?php _e( 'License Activation Panel', 'hubwoo' ); ?></h1>
        <form action="" method="post" id="hubwoo-ftf-license">
            <table class="form-table">
                <tr>
                    <th style="width:100px;"><label for="hubwoo_ftf_license_key"><?php _e( 'License Key', 'hubwoo' )?></label></th>
                    <td><input required="" class="regular-text" type="text" id="hubwoo_ftf_license_key" name="hubwoo_ftf_license_key"  value="<?php echo get_option('hubwoo_ftf_license_key'); ?>" >
                    <img id="hubwoo-ftf-lic-loader" class="hubwoo_ftf_hide" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/ajax-loader.gif'?>"/></td>
                </tr>
            </table>
            <p class="hubwoo_ftf_license_activation_status" style="font-size:18px;color:blue"></p>
            <p class="submit">
                <input type="submit" id="hubwoo_ftf_activate_license" name="hubwoo_ftf_activate_license" value="<?php _e("Activate","hubwoo")?>" class="button-primary" />
            </p>
        </form>
    </div>
</div>