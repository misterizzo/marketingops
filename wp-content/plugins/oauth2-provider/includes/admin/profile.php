<?php
/**
 * Addes the Ability for User Generated Access Tokens
 * @updated 4.3.0
 * @author Justin Greer <justin@dash10.digital>
 */

add_action( 'show_user_profile', 'wp_oauth_profile_oauth_info' );
add_action( 'edit_user_profile', 'wp_oauth_profile_oauth_info' );
function wp_oauth_profile_oauth_info( $user ) {

	// Only a user can do this of thier own free will.
	if ( $user->data->ID != get_current_user_id() ) {
		return;
	}
	?>
<br />
<h3>OAuth2 Authentication / Application Password</h3>
<p>OAuth2 Application Passwords work a bit differently than normal applications passwords. OAuth2 Passwords work
    with the OAuth2 resource server specific to OAuth2.Read more about <a href="https://wp-oauth.com"
       target="_blank">OAuth2 Application
        Passwords</a>.</p>
<div>
    <?php if ( is_null( wo_ap_et_access_token_for_user() ) ) : ?>
    <table class="form-table">
        <tr>
            <p class="submit">
                <input type="submit" name="generate_token" id="submit" class="button" value="Generate Token">
                <input type="hidden" name="generate_token_nonce"
                       value="< ?php print wp_create_nonce( 'generate_token' ); ?>" />
            </p>
        </tr>
    </table>
    <?php else : ?>
    <?php $user_token = wo_ap_et_access_token_for_user(); ?>
    <table class="form-table">
        <tr>
            <th>Access Token</th>
            <td>
                <?php print sanitize_text_field( $user_token->access_token ); ?> <br /> <br />
                <input type="submit" name="generate_token" id="regenerate" class="button button-secondary"
                       value="Regenerate Token">
                <input type="hidden" name="generate_token_nonce"
                       value="< ?php print wp_create_nonce( 'generate_token' ); ?>" />
                | <a href="#" id="revoke-token" data-nonce="<?php print wp_create_nonce(); ?>">Revoke Token</a>
            </td>
        </tr>
    </table>

    <?php endif; ?>

</div>
<?php
}

add_action( 'user_profile_update_errors', 'wo_user_profile_update_token_gen', 10, 3 );
function wo_user_profile_update_token_gen( $errors, $update, $user ) {
	if ( ! empty( $_POST['generate_token'] ) && is_user_logged_in() && wp_verify_nonce( $_POST['generate_token_nonce'], 'generate_token' ) ) {

		$client_id = wo_ap_create_user_generated_client_id();
		$access_token = wp_ap_generate_access_token();

		/*
		 * Set the expire time to 99 years from now
		 *
		 * @todo Make this an option in the settings for the admin. Allow them to modify this.
		 */
		$expires = date( 'Y-m-d H:i:s', strtotime( date( 'Y-m-d H:i:s', time() ) . ' + 10 year' ) );

		/*
		 * Set the access token for the user generated client. generated clients only allow for basic scope.
		 */
		wo_ap_token_gen_set_access_token( $access_token, $client_id, get_current_user_id(), $expires, 'basic' );
	}
}

/**
 * @param $access_token
 * @param $client_id
 * @param $user_id
 * @param $expires
 * @param null $scope
 *
 * @return bool|int
 */
function wo_ap_token_gen_set_access_token( $access_token, $client_id, $user_id, $expires, $scope = null ) {
	global $wpdb;

	/*
	 * Delete old token(s) to limit security issues
	 */
	$wpdb->query( "DELETE FROM {$wpdb->prefix}oauth_access_tokens WHERE user_id = {$user_id} AND ap_generated = 1" );

	/*
	 * Insert the new token info
	 */
	$insert = $wpdb->insert(
		"{$wpdb->prefix}oauth_access_tokens",
		array(
			'access_token' => $access_token,
			'client_id' => $client_id,
			'user_id' => $user_id,
			'expires' => $expires,
			'scope' => 'basic',
			'ap_generated' => '1',
		)
	);

	// exit($insert);

	// Return Results
	return $insert;
}


function wo_ap_create_user_generated_client_id() {
	// Check to see if there is a client already. If so, skip creating one
	$client = get_page_by_title( 'user_generated_' . get_current_user_id(), OBJECT, 'wo_client' );
	if ( is_null( $client ) ) {

		$client_id = wo_gen_key();
		$client_secret = wo_gen_key();

		$client = array(
			'post_title' => wp_strip_all_tags( 'user_generated_' . get_current_user_id() ),
			'post_content' => ' ',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_type' => 'wo_client',
			'comment_status' => 'closed',
			'meta_input' => array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'grant_types' => array( 'authorization_code' ),
				'redirect_uri' => '',
				'user_id' => get_current_user_id(),
				'scope' => 'basic',
			),

		);
		wp_insert_post( $client );

	} else {

		// Use the client id that already exists
		$client_id = get_post_meta( $client->ID, 'client_id', true );

	}

	return $client_id;
}

function wp_ap_generate_access_token() {
	$token_length = wo_setting( 'token_length' );

	return strtolower( wp_generate_password( $token_length, false, $extra_special_chars = false ) );
}

function wo_ap_et_access_token_for_user() {
	global $wpdb;

	$current_user = get_current_user_id();
	$check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}oauth_access_tokens WHERE user_id = %d", array( $current_user ) ) );

	return $check;
}