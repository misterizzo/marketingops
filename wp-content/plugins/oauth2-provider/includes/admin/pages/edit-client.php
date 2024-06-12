<?php
function wo_admin_edit_client_page() {
	if ( ! isset( $_REQUEST['id'] ) ) {
		return;
	}
	
	$message = null;
	if ( isset( $_POST['edit_client'] ) && wp_verify_nonce( $_POST['nonce'], 'edit_client_' . $_POST['edit_client'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$update_client = wo_update_client( $_POST );
		$message       = __( 'Client Updated', 'wp-oauth' );
	}
	
	$client = wo_get_client( intval( $_REQUEST['id'] ) );
	if ( ! $client ) {
		exit( 'Client not found' );
	}
	?>
    <div class="wrap">

        <h2><?php _e( 'Edit Client', 'wp-oauth' ); ?>
            <small>( id: <?php echo esc_html( $client->ID ); ?> )</small>
            <a class="add-new-h2 "
               href="<?php echo admin_url( 'admin.php?page=wo_manage_clients' ); ?>"
               title="Batch"><?php _e( 'Back to Clients', 'wp-oauth' ); ?></a>
        </h2>

        <hr/>
		
		<?php if ( ! is_null( $message ) ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html( $message ); ?></p>
            </div>
		<?php endif; ?>

        <form class="wo-form" action="" method="post">
			
			<?php wp_nonce_field( 'edit_client_' . $client->ID, 'nonce' ); ?>
            <input type="hidden" name="edit_client" value="<?php echo esc_attr( $client->ID ); ?>"/>
            <input type="hidden" name="post_id" value="<?php echo esc_attr( $client->ID ); ?>"/>

            <div class="section group">

                <div class="col span_2_of_6">

                    <label class="checkbox-grid"> Allowed Grant Types
						<?php
						_e(
							'Choosing the correct grant type for your client is important. For security reasons, a single
							grant type should be used per client. To learn more about which grant type you will need,
							please visit <a href="https://wp-oauth.com/docs/general/grant-types/?utm_source=plugin-admin&utm_medium=settings-page"
							                title="Learn more about which grant type to use" target="_blank">
							                https://wp-oauth.com/docs/general/grant-types/
							                </a>.',
							'wp-oauth'
						);
						?>

                        </p>
                        <hr/>

                        <label> <strong>Authorization Code</strong>
                            <input type="checkbox" name="grant_types[]"
                                   value="authorization_code"
								<?php
								if ( in_array( 'authorization_code', $client->grant_types ) ) {
									echo ' checked';
								}
								?>
                            />
                            <small class="description">
                                Allows authorization code grant type for this client. This includes the implicit method.
                            </small>
                        </label>

                        <label> <strong>User Credentials (Pro Only)</strong>
                            <input type="checkbox" name="grant_types[]"
                                   value="password" disabled/>
                            <small class="description">
                                Allows the client to use user credentials to authorize.
                            </small>
                        </label>

                        <label> <strong>Client Credentials (Pro Only)</strong>
                            <input type="checkbox" name="grant_types[]"
                                   value="client_credentials" disabled/>
                            <small class="description">
                                Client can use the client ID and Client Secret to authorize.
                            </small>
                        </label>

                        <label> <strong>Refresh Token (Pro Only)</strong>
                            <input type="checkbox" name="grant_types[]"
                                   value="refresh_token" disabled/>
                            <small class="description">
                                Allows the client to request a refresh token.
                            </small>
                        </label>
                    </label>
                </div>

                <div class="col span_4_of_6">
                    <div class="wo-background">

                        <h3><?php _e( 'Client Information', 'wp-oauth' ); ?></h3>
                        <hr/>

                        <div class="section group">
                            <div class="col span_6_of_6">
                                <label> Client Name
                                    <input class="emuv-input" type="text" name="name"
                                           value="<?php echo esc_html( $client->post_title ); ?>" required/>
                                </label>

                                <label> Redirect URI
                                    <input class="emuv-input" type="text" name="redirect_uri"
                                           value="<?php echo esc_html( get_post_meta( $client->ID, 'redirect_uri', true ) ); ?>"/>
                                </label>

                                <hr/>

                                <label> Client ID
                                    <input class="emuv-input" type="text" name="client_id"
                                           value="<?php echo esc_html( get_post_meta( $client->ID, 'client_id', true ) ); ?>"/>
                                </label>

                                <label> Client Secret
                                    <input class="emuv-input" type="text" name="client_secret"
                                           value="<?php echo esc_html( get_post_meta( $client->ID, 'client_secret', true ) ); ?>"/>
                                </label>

                                <div style="margin-top: 2.5em" class="advanced-options">
                                    <h3>Advanced Options</h3>
                                    <hr/>

                                    <label>
                                        Client Credential Assigned User
                                        <p class="description">
                                            The "client credential" grant types does not have a user id assigned to it
                                            making it hard for an application to perform protected endpoints.
                                            The client will then have the same privileges as the selected user.
                                        </p>
										<?php
										$user_id = get_post_meta( $client->ID, 'user_id', true );
										wp_dropdown_users(
											array(
												'selected'         => $user_id,
												'name'             => 'user_id',
												'show_option_none' => '--- No User ---',
												'class'            => 'select2',
											)
										);
										?>
                                    </label>

                                    <label> Client Scope(s)
                                        <p class="description">
                                            You can restrict scopes to the following for this client. The default scope
                                            is <strong>"basic"</strong> and allows for access to general information.
                                        </p>
                                        <input class="emuv-input" type="text" name="scope"
                                               value="<?php echo esc_html( get_post_meta( $client->ID, 'scope', true ) ); ?>"
                                               placeholder="basic"/>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			
			<?php submit_button( __( 'Update Client', 'wp-oauth' ) ); ?>

        </form>

    </div>
	
	<?php
}
