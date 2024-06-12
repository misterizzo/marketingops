<?php
/**
 * Server Options Page
 */
function wo_server_options_page() {
	$scopes = apply_filters( 'wo_scopes', array() );
	add_thickbox();
	
	$options = wo_setting();
	?>
    <div class="wrap">
        <h2>WP OAuth Server
            <small>
                CE
                | <?php echo _WO()->version; ?>
            </small>
        </h2>
		<?php settings_errors(); ?>
        <div class="section group">
            <div class="col span_4_of_6">

                <form method="post" action="options.php">
					<?php settings_fields( 'wo-options-group' ); ?>

                    <div id="wo_tabs">
                        <ul>
                            <li><a href="#general-settings">General Settings</a></li>
                            <li><a href="#advanced-configuration">Advanced Configuration</a></li>
                        </ul>

                        <!-- GENERAL SETTINGS -->
                        <div id="general-settings">
                            <h3>General Settings</h3>
                            <hr/>
                            <table class="form-table wp-oauth-form-table">
                                <tr valign="top">
                                    <th scope="row">
                                        <input type="checkbox" name="wo_options[enabled]"
                                               value="1" <?php echo $options['enabled'] == '1' ? "checked='checked'" : ''; ?> />
                                    </th>
                                    <td>
                                        Enable OAuth Server
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><input type="checkbox"
                                                           name="wo_options[block_all_unauthenticated_rest_request]"
                                                           value="1" <?php echo $options['block_all_unauthenticated_rest_request'] == '1' ? "checked='checked'" : ''; ?> />
                                    </th>
                                    <td>
                                        Block Unauthenticated Requests to the <strong>ENTIRE</strong> REST API
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">
                                        <input type="checkbox" name="wo_options[home_url_modify]"
                                               value="1" <?php echo $options['home_url_modify'] == '1' ? "checked='checked'" : ''; ?> />
                                    </th>
                                    <td>
                                        Check this is your are running a subdirectory and having issues with redirects.
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">
                                        <input type="checkbox" name="wo_options[enable_ssl_verify]"
                                               value="1" <?php echo $options['enable_ssl_verify'] == '1' ? "checked='checked'" : ''; ?> />
                                    </th>
                                    <td>
                                        Check this to force WP OAuth Server to use <strong>SSL_VERIFY</strong> for
                                        remote calls.
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- ADVANCED CONFIGURATION -->
                        <div id="advanced-configuration">

                            <h3>Token Type
                                <hr>
                            </h3>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Use JSON Web Tokens (JWT):</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[jwt_bearer_enabled]"
                                               value="1" <?php echo $options['jwt_bearer_enabled'] == '1' ? "checked='checked'" : ''; ?> />
                                    </td>
                                </tr>
                            </table>

                            <h3>Misc Settings
                                <small>(Global)</small>
                                <hr>
                            </h3>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Token Length</th>
                                    <td>
                                        <input type="number" name="wo_options[token_length]" min="10" max="255"
                                               value="<?php echo intval( $options['token_length'] ); ?>"
                                               placeholder="40"/>
                                        <p class="description">Length of tokens</p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Require Exact Redirect URI:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[require_exact_redirect_uri]"
                                               value="1" <?php echo $options['require_exact_redirect_uri'] == '1' ? "checked='checked'" : ''; ?> />
                                        <p class="description">Enable if exact redirect URI is required when
                                            authenticating.</p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Enforce State Parameter:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[enforce_state]"
                                               value="1" <?php echo @$options['enforce_state'] == '1' ? "checked='checked'" : ''; ?>/>
                                        <p class="description">Enable if the "state" parameter is required when
                                            authenticating. </p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Allow Implicit:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[implicit_enabled]"
                                               value="1" <?php echo $options['implicit_enabled'] == '1' ? "checked='checked'" : ''; ?> />
                                        <p class="description">Enable "Authorization Code (Implicit)" <a
                                                    href="https://wp-oauth.com/kb/grant-types/">What's this?</a></p>
                                    </td>
                                </tr>
                            </table>

                            <!-- OpenID Connect -->
                            <h3>OpenID Connect 1.0a
                                <small>(Global)</small>
                                <hr>
                            </h3>
                            <p>
                                The OpenID Connect 1.0a works with other systems like Drupal and Moodle. <a
                                        href="https://wp-oauth.com/downloads/wp-oauth-server/?discount=PROME"
                                        target="_blank">Upgrade to PRO to enable.</a>
                            </p>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Allow OpenID Connect:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[use_openid_connect]"
                                               value="1" disabled/>
                                        <p class="description">Enable if your server should generate a id_token when
                                            OpenID request is made.</p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">ID Token Lifetime</th>
                                    <td>
                                        <input type="number" name="wo_options[id_token_lifetime]"
                                               value="" placeholder="3600" disabled/>
                                        <p class="description">How long an id_token is valid (in seconds).</p>
                                    </td>
                                </tr>
                            </table>

                            <h3>Token Lifetimes
                                <small>(Global)</small>
                                <hr>
                            </h3>
                            <p>
                                By default Access Tokens are valid for 1 hour and Refresh Tokens are valid for 24 hours.
                                <a href="https://wp-oauth.com/downloads/wp-oauth-server/?discount=PROME"
                                   target="_blank">Upgrade to PRO to enable.</a>
                            </p>

                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Access Token Lifetime</th>
                                    <td>
                                        <input type="number" name="wo_options[access_token_lifetime]"
                                               value=""
                                               placeholder="3600" disabled/>
                                        <p class="description">How long an access token is valid (seconds) - Leave blank
                                            for default (1 hour)</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Refresh Token Lifetime</th>
                                    <td>
                                        <input type="number" name="wo_options[refresh_token_lifetime]"
                                               value=""
                                               placeholder="86400" disabled/>
                                        <p class="description">
                                            How long a refresh token is valid (seconds) - Leave blank for default (24
                                            hours)
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </div>
                        <!-- / END - Advance Configuration Content -->

                    </div>
                    <!-- END - #Tabs Content -->

                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>"/>
                    </p>
                </form>

            </div>
            <!-- END- col 4 of 6 -->

            <!-- SIDEBAR -->
            <div class="col span_2_of_6 sidebar">
                <div class="module">
                    <h3>Plugin Documentation</h3>
                    <div class="inner">
                        <p>
                            Our robust documentation will help you through the process is need be. You can view the
                            documentation by visiting <a
                                    href="https://wp-oauth.com/documentation/?utm_source=plugin-admin&utm_medium=settings-page"
                                    target="_blank">https://wp-oauth.com/documentation/</a>.
                        </p>

                        <strong>Build <?php echo _WO()->version; ?></strong>
                    </div>
                </div>
                <div class="module hire-us">
                    <h3>Upgrading to PRO is Easy</h3>
                    <div class="inner">
                        <p>Support the Project and Unlock Everything PRO has to offer.</p>
                        <ul>
                            <li>Unlimited Sites</li>
                            <li>All Grant Types</li>
                            <li>FULL PKCE Support</li>
                            <li>Public Clients</li>
                            <li>Premium Support</li>
                            <li>Edge Updates</li>
                            <li>All Extensions Free</li>
                        </ul>
                        <p>
                            <a href="https://wp-oauth.com/downloads/wp-oauth-server/?discount=PROME" target="_blank" class="button button-primary">
                                <strong>Switch to PRO</strong> for 20% OFF
                            </a>
                        </p>

                        <strong>Build 4.3.3</strong>
                    </div>
                </div>
            </div>

        </div>
        <!-- END OF SECTION -->

    </div>
	<?php
}
