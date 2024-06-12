<?php
function wo_add_client_page() {
	?>
    <div class="wrap">

        <h2><?php _e( 'Create Client', 'wp-oauth' ); ?>
            <a class="add-new-h2 "
               href="<?php echo admin_url( 'admin.php?page=wo_manage_clients' ); ?>"
               title="Batch"><?php _e( 'Back to Clients', 'wp-oauth' ); ?></a>
        </h2>

        <hr/>

        <form class="wo-form" action="" method="post">
			
			<?php wp_nonce_field( 'create_client', 'nonce' ); ?>
            <input type="hidden" name="create_client" value="1"/>

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
                                   value="authorization_code" checked/>
                            <small class="description">
                                Allows authorization code grant type for this client.
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
                                           value="" required/>
                                </label>

                                <label> Redirect URI
                                    <small> (Optional -
                                        Recommend: <a href="https://wp-oauth.com/kb/redirect-uri-recommend/"
                                                      title="Why this is recommended" target="_blank">Why?)(</a>
                                    </small>
                                    <input class="emuv-input" type="text" name="redirect_uri"
                                           value="" placeholder=""/>
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
										// $user_id = get_post_meta( $client->ID, 'user_id', true );
										wp_dropdown_users(
											array(
												// 'selected'         => $user_id,
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
                                               value="" placeholder="basic"/>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			
			<?php submit_button( __( 'Create Client', 'wp-oauth' ) ); ?>

        </form>

    </div>
	
	<?php
}
