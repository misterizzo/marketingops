<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

if ( isset( $_POST["hubwoo_ftf_settings"] ) ) {

	unset( $_POST["hubwoo_ftf_settings"] );

	if( !isset( $_POST["hubwoo_count_rows"] ) ) {

		$_POST["hubwoo_count_rows"] = array();
	}

	foreach( $_POST as $key => $value ) {

		if( isset( $_POST[$key] ) ) {

			update_option( $key, $value );
		}
	}

	?>
		<div class="notice notice-success is-dismissible"> 
			<p><strong><?php _e('Settings saved','hubwoo'); ?></strong></p>
		</div>
	<?php
}
?>

<?php

global $hubwoo_ftf;

if ( !class_exists( 'HubWooConnectionMananager' ) ) {
	
	if ( in_array( 'hubspot-woocommerce-integration-pro/hubspot-woocommerce-integration-pro.php', apply_filters('active_plugins', get_option('active_plugins') ) ) ) {

		if( file_exists( HUBWOO_FTF_PLUGINS_PATH . 'hubspot-woocommerce-integration-pro/includes/class-hubwoo-connection-manager.php' ) ) {

			require_once ( HUBWOO_FTF_PLUGINS_PATH . 'hubspot-woocommerce-integration-pro/includes/class-hubwoo-connection-manager.php' );
		}
	}
	elseif ( in_array( 'hubwoo-integration/hubspot-woocommerce-integration.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_FTF_PLUGINS_PATH . 'hubwoo-integration/includes/class-hubwoo-connection-manager.php' ) ) {

			require_once ( HUBWOO_FTF_PLUGINS_PATH . 'hubwoo-integration/includes/class-hubwoo-connection-manager.php' );
		}
	}
	elseif ( in_array( 'hubspot-woocommerce-integration-starter/hubspot-woocommerce-integration-starter.php', apply_filters('active_plugins', get_option('active_plugins') ) ) ) {

		if( file_exists( HUBWOO_FTF_PLUGINS_PATH . 'hubspot-woocommerce-integration-starter/includes/class-hubwoo-connection-manager.php' ) ) {

			require_once ( HUBWOO_FTF_PLUGINS_PATH . 'hubspot-woocommerce-integration-starter/includes/class-hubwoo-connection-manager.php' );
		}
	}
	elseif ( in_array( 'hubspot-woocommerce-integration-complimentary/hubspot-woocommerce-integration-complimetary.php', apply_filters('active_plugins', get_option('active_plugins') ) ) ) {

		if( file_exists( HUBWOO_FTF_PLUGINS_PATH . 'hubspot-woocommerce-integration-complimentary/includes/class-hubwoo-connection-manager.php' ) ) {

			require_once ( HUBWOO_FTF_PLUGINS_PATH . 'ubspot-woocommerce-integration-complimentary/includes/class-hubwoo-connection-manager.php' );
		}
	}
	elseif( in_array( 'hubspot-for-woocommerce/hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_FTF_PLUGINS_PATH . 'hubspot-for-woocommerce/includes/class-hubwooconnectionmananager' ) ) {

			require_once ( HUBWOO_FTF_PLUGINS_PATH . 'hubspot-for-woocommerce/includes/class-hubwooconnectionmananager.php' );
		}
	}
	elseif( in_array( 'makewebbetter-hubspot-for-woocommerce/makewebbetter-hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_FTF_PLUGINS_PATH . 'makewebbetter-hubspot-for-woocommerce/includes/class-hubwooconnectionmananager.php' ) ) {

			require_once ( HUBWOO_FTF_PLUGINS_PATH . 'makewebbetter-hubspot-for-woocommerce/includes/class-hubwooconnectionmananager.php' );
		}
	}	
}

if( class_exists( 'HubWooConnectionMananager' ) ) {
	
	$HubWooConnectionMananager = new HubWooConnectionMananager();
}

if( isset( $HubWooConnectionMananager ) ) {

	$contact_properties = $HubWooConnectionMananager->get_all_hubspot_properties('contacts');
	if ( array_key_exists( 'body', $contact_properties ) ) {
		$contact_properties = $contact_properties['body'];
		$contact_properties = json_decode($contact_properties);
		$contact_properties = $contact_properties->results;
	}
	update_option( "hubwoo_site_properties", $contact_properties );
}
else {

	$contact_properties = array();
}

if( isset( $hubwoo_ftf ) ) {

	$user_fields = $hubwoo_ftf->hubwoo_ftf_get_all_user_fields();
}
else {

	$user_fields = array();
}

$rows_added = get_option( "hubwoo_count_rows", array() );

$hubwoo_selected_prop_fields = get_option( "hubwoo_selected_prop_fields", array() );

$hubwoo_selected_user_fields = get_option( "hubwoo_selected_user_fields", array() );
	
?>
<div style="display: none;" class="loading-style-bg" id="hubwoo_ftf_loader">
	<img src="<?php echo plugin_dir_url( __FILE__ ).'images/loader.gif';?>">
</div>
<div class="wrap woocommerce hubwoo_ftf">
	<h1><?php _e("Map your HubSpot Contact Properties with your User's Custom Fields","hubwoo") ?></h1>
	<form method="post" action="">
		<table id="hubwoo_ftf_table">
			<thead>
				<th scope="row" class="titledesc">
					<h4><?php _e("Your HubSpot Property",'hubwoo') ?></h4>
				</th>
				<th>
					<h4><?php _e("Your Wordpress Field",'hubwoo') ?></h4>
				</th>
			</thead>
			<tbody>
				<tr valign="top" data-id = "0">
					<td class="forminp forminp-text">
						<select class="hubwoo_map" name="hubwoo_selected_prop_fields[0]">
							<option value="<?php echo "select" ?>"><?php _e("--Select from HubSpot properties--","hubwoo");?></option>
							<?php
							$prop_option_value = '';

							if( isset( $hubwoo_selected_prop_fields[0] ) ) {

								$prop_option_value = $hubwoo_selected_prop_fields[0];
							}
							else {
								
								$prop_option_value = '';
							}

							$field_option_value = '';

							if( isset( $hubwoo_selected_user_fields[0] ) ) {

								$field_option_value = $hubwoo_selected_user_fields[0];
							}
							else {

								$field_option_value = '';
							}

							if( is_array( $contact_properties ) && count( $contact_properties ) ) {

								foreach( $contact_properties as $single_property ) {

									if( !$single_property->modificationMetadata->readOnlyValue ) {
										
										if( $prop_option_value == $single_property->name )
										{ 
											?>
											<option selected="" value="<?php echo $single_property->name ?>">
											<?php echo $single_property->label ?>
											</option>
											<?php
										}
										else
										{
											?>
											<option value="<?php echo $single_property->name ?>">
											<?php echo $single_property->label ?>
											</option>
											<?php
										}
									}
								}
							}
							?>
						</select>
					</td>
					<td class="forminp forminp-text">
						<select class="hubwoo_map" name="hubwoo_selected_user_fields[0]">
							<option value="<?php echo "select" ?>"><?php _e(" -- Select from WordPress User's fields -- ","hubwoo");?>
							</option>
							<?php 
							if( is_array( $user_fields ) && count( $user_fields ) ) {

								foreach( $user_fields as $key => $value ) {

									if( $field_option_value == $value )
									{
										?>
										<option selected="" value="<?php echo $value ?>">
											<?php echo $value ?>
										</option>
										<?php
									}
									else
									{
										?>
										<option value="<?php echo $value ?>">
											<?php echo $value ?>
										</option>
										<?php
									}
								}
							}
							?>
						</select>
					</td>
				</tr>
				<?php

				$prop_option = '';
				$wp_fields = '';
				$html = '';

				if( count( $rows_added ) ) {

					foreach( $rows_added as $single_row ) {

						$prop_option_value = '';
						$field_option_value = '';
						
						if( isset( $hubwoo_selected_prop_fields[$single_row] ) ) {
							$prop_option_value = $hubwoo_selected_prop_fields[$single_row];
						}
						else {
							$prop_option_value = '';
						}

						if( isset( $hubwoo_selected_user_fields[$single_row] ) ) {
							$field_option_value = $hubwoo_selected_user_fields[$single_row];
						}
						else {
							$field_option_value = '';
						}

						if( is_array( $contact_properties ) && count( $contact_properties ) ) {

							$prop_option = '<option value="select">'.__("--Select from HubSpot properties--","hubwoo").'</option>';

							foreach( $contact_properties as $single_property ) {

								if( !$single_property->modificationMetadata->readOnlyValue ) {

									if( $prop_option_value == $single_property->name  ) {
										
										$prop_option .='<option selected="" value="'.$single_property->name.'">'.$single_property->label.'</option>';
									}
									else
									{
										$prop_option .='<option value="'.$single_property->name.'">'.$single_property->label.'</option>';
									}
								}
							}
						}

						if( is_array( $user_fields ) && count( $user_fields ) )
						{
							$wp_fields = '<option value="select">'.__(" -- Select from Wordpress User Custom fields -- ","hubwoo").'</option>';

							foreach( $user_fields as $single_field )
							{
								if( $field_option_value == $single_field )
								{
									$wp_fields .= '<option selected="" value="'.$single_field.'">'.$single_field.'</option>';
								}
								else
								{
									$wp_fields .= '<option value="'.$single_field.'">'.$single_field.'</option>';
								}
							}
						}

						$html .=   '<tr valign="top" data-id="'.$single_row.'">
									<td class="forminp forminp-text"><select class="hubwoo_map" name="hubwoo_selected_prop_fields['.$single_row.']">
									'.$prop_option.'</select></td>
									<td class="forminp forminp-text"><select class="hubwoo_map" name="hubwoo_selected_user_fields['.$single_row.']">'.$wp_fields.'</select></td>
									<td><button data-id="'.$single_row.'" class="hubwoo_delete_row">'.__("Delete","hubwoo").'</button></td><input type="hidden" name="hubwoo_count_rows['.$single_row.']" value="'.$single_row.'"></tr>';
					}
				}
				echo $html;
				?>
			</tbody>
		</table>
		<div class="hubwoo_new_rule">
			<button type="button" class="" id="hubwoo_new_row">
				<?php _e('Add New Row','hubwoo');?>
			</button>
		</div>
		<p class="submit">
			<input type="submit" value="<?php _e('Save Changes', 'hubwoo'); ?>" class="button-primary woocommerce-save-button" name="hubwoo_ftf_settings" id="hubwoo_ftf_settings">
		</p>
	</form>
</div>