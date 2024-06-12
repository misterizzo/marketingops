<?php
/*
 * general.php
 *
 * @author Justin Greer <justin@justin-greer.com
 * @copyright Justin Greer Interactive, LLC
 *
 * @package WP-Nightly
 */

$options = get_option( 'wo_license_information' );
?>
<table class="form-table">
    <tr>
        <th style="text-align:left;">
            <?php _e( 'WordPress Version ', 'wp-oauth' ); ?>:
        </th>
        <td>
            <?php
			global $wp_version;
			echo $wp_version;
			?>
        </td>
    </tr>

    <tr>
        <th style="text-align:left;">
            <?php _e( 'PHP Version', 'wp-oauth' ); ?>:
        </th>
        <td>
            <?php echo version_compare( PHP_VERSION, '5.4' ) >= 0 ? "<span style='color:green;'>Ok</span>" : " <span style='color:red;'>Failed</span> - <small>Please upgrade PHP to 5.4 or greater.</small>"; ?>
            - Version
            <?php echo PHP_VERSION; ?>
        </td>
    </tr>

    <tr>
        <th style="text-align:left;">
            <?php _e( 'Running CGI', 'wp-oauth' ); ?> :
        </th>
        <td>
            <?php echo substr( php_sapi_name(), 0, 3 ) != 'cgi' ? " <span style='color:green;'>NO (OK)</span>" : " <span style='color:orange;'>Notice</span> - <small>Header 'Authorization Basic' may not work as expected. Visit <a href='https://wp-oauth.com/docs/common-issues/header-authorization-may-not-work-as-expected/' target='_blank'>https://wp-oauth.com/docs/common-issues/header-authorization-may-not-work-as-expected/</a></small>"; ?>
        </td>
    </tr>

    <tr>
        <th style="text-align:left;">
            <?php _e( 'Certificates Generated', 'wp-oauth' ); ?>:
        </th>
        <td>
            <?php echo ! wp_oauth_has_certificates() ? " <span style='color:red;'>Issues found with certificates.</span>" : "<span style='color:green;'>Certificates Found</span>"; ?>
            <?php
			$wpoauth_generate_cert_nonce = wp_create_nonce( 'wo_nonce' );
			?>
            |
            <a href="<?php echo admin_url( 'admin-post.php?action=wpoauth_regenerate_certificates&wo_nonce=' . $wpoauth_generate_cert_nonce ); ?>"
               onclick="return confirm('Are you sure? All valid tokens will be invalid.')">Regenerate
                Certificates</a>
            <br />
            <?php
			if ( $cert_sizes = wpoauth_get_cetificate_filesizes() ) {
				print 'Public Key: ' . $cert_sizes['public']['size'] . ' Bytes (' . $cert_sizes['public']['modified'] . ')<br/>';
				print 'Private Key: ' . $cert_sizes['private']['size'] . ' Bytes (' . $cert_sizes['private']['modified'] . ')';
			}
			?>
        </td>
    </tr>

    <tr>
        <th style="text-align:left;">Secure Server:</th>
        <td>
            <?php if ( false == wo_is_protocol_secure() ) : ?>
            <span style="color:red;">NOT SECURE - <a href="https://www.thesslstore.com?aid=52913785"
                   title="Get A SSL Certificate">Get A SSL Certificate</a></span>
            <?php else : ?>
            <span style="color:green;">SECURE</span>
            <?php endif; ?>
        </td>
    </tr>

    <tr>
        <th style="text-align:left;">
            <?php _e( 'Running Windows OS', 'wp-oauth' ); ?>:
        </th>
        <td>
            <?php echo wo_os_is_win() ? " <span style='color:orange;'>Yes" : "<span style='color:green;'>No</span>"; ?>
        </td>
    </tr>

    <tr>
        <th style="text-align:left;">Updates:</th>
        <td>
            <?php if ( ! empty( $options['license'] ) && $options['license'] == 'invalid' ) : ?>
            <span style="color:red;">Disabled</span>
            <?php elseif ( ! empty( $options['license'] ) && $options['license'] == 'valid' ) : ?>
            <span style="color:green;">Enabled</span>
            <?php else : ?>
            <span style="color:red;">Disabled</span>
            <?php endif; ?>
        </td>
    </tr>

    <tr>
        <th style="text-align:left;">
            <?php _e( 'Installation Key', 'wp-oauth' ); ?>:
        </th>
        <td>
            <?php echo get_option( 'wp_oauth_activation_time', 'N/A' ); ?>
        </td>
    </tr>

    <tr>
        <th>OPEN SSL TEXT</th>
        <td>
            <?php echo OPENSSL_VERSION_TEXT; ?>
        </td>
    </tr>

    <tr>
        <th>PHP cURL</th>
        <td>
            <?php foreach ( curl_version() as $key => $value ) : ?>
            <?php
				if ( $key == 'protocols' ) {
					continue;
				}
				print $key . ' : ' . $value . '<br/>';
				?>
            <?php endforeach; ?>
        </td>
    </tr>


    <tr>
        <th>Active Plugins</th>
        <td>
            <?php
			$all_plugins = get_plugins();
			$active_plugins = get_option( 'active_plugins' );

			foreach ( $active_plugins as $index => $plugin ) {
				if ( array_key_exists( $plugin, $all_plugins ) ) {
					// var_export( $all_plugins[ $plugin ] );
					echo '<span>', $all_plugins[ $plugin ]['Name'], ' (' . $all_plugins[ $plugin ]['Version'] . ')</span><br/> ';
				}
			}
			?>
        </td>
    </tr>

</table>