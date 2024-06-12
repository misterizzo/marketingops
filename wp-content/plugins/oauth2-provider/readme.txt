=== WP OAuth Server (OAuth Authentication) ===

Contributors: justingreerbbi, askjayson
Donate link: http://justin-greer.com/ 
Tags: OAuth2 Service, oauth2, OAuth provider, Provider, OAuth, OAuth client, Single Sign On, SSO, OpenID Connect, OIDC, OpenID, Connect
Requires at least: 4.7.2
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 4.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Authentication through OAuth 2. Provides the ability for Single Sign On for websites & Mobile Applications.

== Description ==

Connect your app to WordPress or use SSO to connect multiple websites with the same username and passwords. No 3rd party servers are needed with WP OAuth Server. Everything you need is in this plugin.

[youtube https://youtu.be/ZOUsY4Kp_6U]

= Features =

* WP REST API Authentication. Provides ability to make authorized calls to protected REST API endpoints.
* WP REST API Lock Down. Prevent any calls to the REST API unless authorized
* Unlimited OAuth 2.0 Clients
* Support for Implicit Flow
* Built-In Resource Server
* Automated Authorization Flow (User does not have to see authorization screen)
* Easily Extend/ Modify the Endpoints
* OAuth 2.0 PKCE
* Modern and Legacy JWT authorization support. OAuth 2.0 JSON Web Token Support

= Supported Grant Types =

* Authentication Code w/Implicit
* User Credentials (Pro)
* Client Credentials (Pro)
* Refresh Token (Pro)
* OpenID Connect (Pro)
* OpenID Discovery
* Public Clients (Pro)
* Public Client Proof of Key Exchange (PKCE)

= Supports =

* Connecting any Custom Mobile and Desktop Application to WordPress's Backend.
* Any software or web platform utilizing OAuth 2.0.
* Allows RocketChat to use WordPress as a Backend.
* Connects Moodle LMS and use WordPress users.
* Alexa Skills Authentication
* Tribe.so Community OAuth 2 SSO Support

= How to Use =

Visit https://wp-oauth.com/support/documentation/ for detailed documentation on installing, configuring and using
WordPress OAuth Server.

= Licensing = 

WP OAuth Server is free to use. Please support the project by licensing. You can view more information at
https://wp-oauth.com.

= Minimum Requirements =

* PHP 5.6.4 or greater *(latest version recommended)*
* OpenSSL installed and enabled if you plan on using OpenID Connect

= Other Information =

* NOTE: As of 3.0.0, there are no backward compatibility for any version older than 3.0.0
* NOTE: Due to IIS's inability play nice, WP OAuth Server may work but is very limited for Windows OS.

= Support =

Support requests should be made by opening a support request at https://wp-oauth.com/support/submit-ticket/.

== Installation ==

1. Upload `oauth-provider` to the `/wp-content/plugins/` directory or use the built in plugin install by WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click 'Settings' and then 'permalinks'. Then simply click 'Save Changes' to flush the rewrite rules so that OAuth2 Provider
1. You're Ready to Rock

== Frequently Asked Questions ==

= Do I need OAuth2 or App Passwords? =
This depends. If you project requires random users access an application, then OAuth2 is the route you need. If you are making
a server that handles a one time client id and secert for authorization, then Application Passwords is for you.

= How do I add a APP/Client? =
Click on `Settings->OAuth Server`. Click on the `Clients` tab and then `Add New Client`. Enter the client information and your are done.

= Does WordPress OAuth Server Support SSO (Single Sign On) =
Yes, WordPress OAuth Server does support Single Sign On for both Traditional OAuth2 Flow and OpenID Connect.

= Is there support for this plugin? Can you help me? =
You can visit our https://wp-oauth.com/support/submit-ticket/ to open up a support request directly with developers.

= Can you set this up for me on my current website? =
* DRINKS COFFEE * Can I? "YES". You are more than welcome to contact us with if you should ever need assistance.

= How do I use WordPress OAuth Server? =
You can visit https://wp-oauth.com/support/documentation/. You will find in-depth documentation as well as examples of how to get started.

== Upgrade Notice ==

Version 2.0.0 and lower are not compatible with version 3.0.0. If you have built your service using version 2.0.0 or lower, visit https://wp-oauth.com/support/submit-ticket/ to open a new request support request.

For any upgrade or modification, PLEASE PLEASE PLEASE make a full backup of your data. 

== Screenshots ==

1. Adding a Client

== Changelog ==

= 4.4.0 =
* (Security Update) Refactored the "destroy" endpoint to remove the auto redirect in favor for a manual checkpoint.
* Cleaned up misc functions. This should not effect any exsisting implementations.
* Tested with 6.4 installed.

= 4.3.4 =
* Updated to fix deprecated messages for PHP 8.1 and WP 6.2
* Added prepare statements in CRON cleanup
* Adjustments to make some options more clear.

= 4.3.3 =
* Updated `wpoauth_authenicate_bypass` to return false.
* Tested with WP 6.2

= 4.2.5 =
* Updated sanity checks in AJAX

= 4.2.3 =
* Tested WP 6.0.3

= 4.2.2 =
* Refactored codebase with PHPCS and PHPCBF WordPress Standards
* Security updates to data handling.
* General cleanup of unneeded functionality

= 4.2.0 =
* Updated/fixed subdirectory issues during Authorization Code Grant Type requests. (duplicate subdirectory issue)
* PHP 8 Compatibility Check

= 4.1.8 =
* Updated ME method to now return user roles by default with an Oauth 2.0 access token
* Tested with WordPress version 5.8 and PHP 8. All Test Passed.

= 4.1.7 =
* Updated S256 code challenge method logic for OAuth 2.0 PKCE
* Fixed issue with headers during initial activation by moving db update to admin_init hook after any activation.

= 4.1.6 =
* User info mapping options added. Custom map user data points easily in the editor.
* Role based restriction logic has been added with filter use.

= 4.1.5 =
* Added new DB version check to ensure DB upgrades are triggered as needed

= 4.1.4 =
* Patched security concern with file include

= 4.1.3 =
* Fixed SQL syntax on normal plugin activation

= 4.1.2 =
* Table update and install fixed for code challenge
* Full support for PKCE has been added.

= 4.1.1 =
* Refactored base code to reconcile to 4.1.1
* Improvements include typeahead.js, JWT support, PKCE support (Proof Key of Code Exchange)

= 3.8.2 =
* Fixed client table listing bug that displayed user generated clients

= 3.8.1 =
* Function name updates to adhere to best practices
* Added sanitation for inputs
* Cleaned up unneeded files
* Removed plugin core validation checks as they are redundant and not needed.

= 3.8.0 =
* Updated broken links and added documentation
* Tested with the latest version of WordPress

= 3.7.92 =
* Added sub directory fix to options.
* Synced Main Options with Pro Version.

= 3.7.91 =
* Changed content in the admin of plugin to reflect changes on support site.

= 3.7.9 =
* Removed UNIQUE INDEX from access token to allow for longer JWT.
* ADDED: Check for required DB upgrade.
* ADDED: Hooks check


= 3.7.8 =
* ADDED: Hooks file check
* UPDATED: MD5 Hash
* FIXED: Version Headers

= 3.7.7 =
* ADDED: Added more discovery to the discovery endpoint.


= 3.7.6 =
* FIXED: Although not used the community version, there was a bug in the Server controller for a missing grant type.


= 3.7.5 =
* ADDED: Defines DOING_OAUTH for easier hooks
* ADDED: Select2 for client management

= 3.7.1 =
* FIXED: Implicit Method (invalid grant type issues)
* UPDATED: Storage method
* Tested with WP 5.1

= 3.6.0 =
* UPDATED: OAuth2 namespace changed to WPOauth2 for better compatibility
* FIXED: Bug in API authorization block functionality
* FIXED: Default scope of basic was not being imitated correctly
* UPDATED: Formatting updates

= 3.5.9 =
* Fixed possible conflict with CSS in admin that prevented buttons from working or displaying
* Content updates

= 3.5.8 =
* NEW: Hourly cleanup of expired access tokens and auth codes.

= 3.5.7 =
* TESTED: With new security release

= 3.4.6 =
* UPDATE: Code for 7.2 stricter standards.
* UPDATE: Updated admin UI for better experience in settings.
* FIX: Admin Notice Fix for settings.
* NEW: Added feature to disable entire REST API from non authenticated users.
* NEW: Live chat support added to backend of plugin.

= 3.4.5 =
* UPDATE: Database are not set to 191 char limits for unique to provide better backward compatibility.
* FIX: Bug fix in refresh token expire time.

= 3.4.4 =
* NEW: Added filter wo_get_access_token_expires_return to the getAccessToken storage method.
* UPDATE: Client type public setting has been updated.
* UPDATE: Destroy endpoint. Now does not require two separate methods for different outcomes.

= 3.4.3 =
* ENHANCEMENT: UX while editing clients
* UPDATE: Base code updates
* UPDATE: /oauth/destroy/ endpoint was modified to handle OpenID Session Management
* UPDATED: /oauth/me/ to return proper OpenID required fields when scope "openid" was used to authorize the access access token
* NEW: Added "wpo_well_known_openid_configuration" filter for OpenID Connect .well-known configuration
* FIXED: Consent Prompt redirect issues.
* UPDATED: Private & Public Key handling on activation.
* NEW: Added single function for Server certificates locations.

= 3.4.3 =
* FIXED: OpenID well-known key bug for PHP namespace has been fixed.

= 3.4.2 =
* FIX: License error using older PHP version
* FIX: Clash with thickbox in admin area

= 3.4.1 =
* NEW: Added prompt parameter support for "login", "consent", and "none"
* NEW: Added User consent window for Authorization code flow.
* NEW: Added filter "wo_use_grant_request" to enable user consent dialog. Boolean.

= 3.4.0 =
* NEW: Basic support for Token Introspection. RFC 7662 ( https://tools.ietf.org/html/rfc7662 )
* ENHANCEMENT: Start of framework for add ons.
* FIX: Bug in a token being delivered even with invalid client credentials.
* FIX: Proper return from resource server during invalid request.

= 3.3.81 - MAY 1ST, 2017 =
* ENHANCEMENT: current_time( 'timestamp' ) used in favor of time(). This allows for time to follow WP setting
* ENHANCEMENT: Native tabs in admin.
* ENHANCEMENT: "wo_updater" filter added to allow plugin updater control.
* FIX: Bug in redirect URI during authentication code flow. Now uses home_url()
* FIX: Bug that allowed for client ID's to be search able.

= 3.3.8 =
* Added public function "wo_public_insert_client" for adding clients.
* Deprecated "wo_get_access_token" in favor of "wo_public_get_access_token"
* apply_filters( 'wo_unset_refresh_token_after_use', false )
* Minor tweak to the status license box
* Bug Fix: Invalid JSON return for "me" endpoint

= 3.3.7 =
* Performance improvement for front-end.
* Modified the default values for plugin.
* Fixed array_merge in the settings that caused warnings in some cases.
* Added wo_me_resource_return filter to default "me" resource.
* Removed debug information for access tokens.
* Updated "n" return encoding for public keys.

= 3.3.5 =
* Added user assigning functionality to client options.
* Removed memory scope storage form API.
* Added restricted scopes to individual clients.
* Default scopes are now functional.

= 3.3.4 =
* Update update class to latest version

= 3.3.3 =
* Fixed error with write context return
* Fixed implicit issue for clients

= 3.3.2 =
* Fixed undefined index for invalid client.
* Fixed redirect_uri issue with getClientDetails(). Threw missing redirect uri error.
* Fixed Bearer token authorization for self resource server calls.

= 3.3.1 =
* Patched clients showing up in admin menu.

= 3.3.0 =
* Refactored style names.
* Updater class was updated to version 1.6.7
* Optimized DB structure.
* wo_create_client now returns insert id instead boolean. This may break compatibility to some add-ons.
* Changed menu order and naming.
* Broke out client management for ease of use and future development.
* Simple Spanish Translation started.
* Extra security has not been added to by restricting/binding individual clients to a set grant types.
* Client querying now uses WP_Query. This provides faster response times for the API.

= 3.2.87 =
* Added filters: wo_allow_credentials_in_request_body, wo_allow_public_clients, wo_always_issue_new_refresh_token, wo_redirect_status_code, wo_create_from_globals_json, wo_create_from_globals_urlencoded, wo_strict_api_lockdown
* Renamed "User Credentials" to Password Credentials for uniformity of code and GUI.
* Updated the readme.
* Added server secure status check.
* API security halt now uses 403 forbidden header.
* Strict API can now be enabled and disabled.
* Increased _wo_authenicate_bypass filter to 9999 to prevent plugin conflicts.
* Tested with 4.7-beta4-39322.

= 3.2.86 =
* Changed instances from site_url to home_url for registered routes.
* Changed openID Connect variables to use home_url instead of site_url

= 3.2.85 =
* Added 'wo_login_check' filter
* Minor UI changes
* Tested with 4.7-alpha

= 3.2.84 =
* Removed deprecated mysql_string function in favor for esc_sql
* Tested with 4.6-RC1-38175

= 3.2.83 =
* Added oauth2 to WP JSON index.
* Tested with WP 4.6-beta3-38065.
* Cleaned up old code.

= 3.2.82 =
* Fixed bug in development system.
* Remove exit code that was causing authorization code to fail silently.
* Tested with WP 4.6-beta2-38008.
* Random code improvements.
* Changed certificate language when there is an error with any of the certificates.

= 3.2.81 = 
* Updated broken links in the readme.
* Fixed issue with redirect_uri bug.
* Fixed do_action('wo_authorization_code_authorize', $user_id ); issue after authorization code authorization
* Updated user claims to be included in id_token token and token id_token implicit responses.
* Minor standard updates to code base.

= 3.2.8 =
* Fixed user Claims with OpenID and ID Token calls.
* Added wo_get_access_token function for public use.

= 3.2.7 =
* Added wo_authorization_code_authorize action. Passed user_id as a single parameter.
* Added wo_restrict_single_access_token filter. Returns true or false / Defaults to false.
* Added wo_auth_code_lifetime filter. Defaults to 30.
* Fixed possible XSS issue with client description.

= 3.2.6 =
* Fixed updater error

= 3.2.5 =
* Fixed bug in security checks that caused displaying of error.
* Client secrets can not be regenerated.

= 3.2.4 =
* Added ssl_verify parameter to wp_remote_get() during license check.
* Security fix that could allow for someone to call wo_create_client and hook action before being validated first.
* Server now return 503 header when server is unavailable.
* Security fix that allowed Ajax calls to be called by any logged in user.

= 3.2.3 =
* Updated license calls
* Added more information on the server page

= 3.2.2 =
* Added error catches to license activation functionality
* Expanded clean check to API to prevent exploits gaining access to user information through the OAuth2 Sever API.
* Fix bug/oversight in key lengths that could be stored in tables over 40 chars. Updated script added for backward fixes as well.

= 3.2.0 =
* Introduced new wo_setting function to return formatted options
* Merged firewall and brute protect into one plugin.
* Updated license functionality
* Corrected token length and added support for 2000 Char keys.

= 3.1.97 =
* Added temp fix to paging issue with clients. 

= 3.1.96 =
* Restructuring and clean up.
* Refresh token controller now accepts parameters properly.
* Rewrote rewrite functionality to fix issues regarding rewrites on ever load.

= 3.1.95 =
* Removed ALTER query. There is no need and someone updating from older version will experience issues anyways. Step by step upgrading is required.
* Fixed issues when updating and options key is missing. This caused header errors that have full error reporting on.

= 3.1.94 =
* Updated generateAuthorizationCode() to use wp_generate_password()
* Fixed bug with expires_in not retuning as integer

= 3.1.93 =
* Updated OAuth2 Library and re-ported to WP.
* Updated AuthorizationCode handler to manage id_token delivery.
* Fixed invalid id_token issue.

= 3.1.92 =
* Moved located of do_action('wo_before_authorize_method'); add added $_REQUEST parameter.
* Rearranged OAuth Server menu for flexibility
* Added $_REQUEST parameter to wo_before_api action
* Add wo_failed_login action when login fails for OAuth2\Stoarge::checkPassword during user credentials grant type
* Added wo_user_not_found action when user is not found when using user credentials

= 3.1.91 =
* Added action wo_endpoint_user_authenticated which runs before resource method but after access token authentication.

= 3.1.9 =
* Changed default refresh token lifetime to 10 days
* Permalinks now check before re-writing
* Minor code refactoring
* Added action wo_set_access_token that runs before creating an access token

= 3.1.8 =
* Optimized activate hooks for better performance and consolidation of code.
* Started minimization of the code to unneeded overhead,
* Added removal of access tokens when a user resets password.
* Fixed issue with refresh tokens not being returned when using refresh_token grant type
* Added functionality to allow for public endpoints.

= 3.1.7 =
* Added MySQL check during install
* Fixed 404 bug for unset permalinks
* Minor security improvements

= 3.1.6 =
* Fixed 404 errors when adding/editing clients

= 3.1.5 =
* Addressed security issues on older PHP versions as well as Windows OS.
* Added checks to help ensure that the environment is supported before WP OAuth Server can be ran.
* Add filter 'wo_scopes' to allow for extendability.

= 3.1.4 =
* Fixed bug in refresh token that prevented use of refresh tokens

= 3.1.3 =
* Forced all expires_in parameter in JSON to be an integer
* Add determine_current_user hook for WP core authentication functionality
* Added authentication support for WP REST API

= 3.1.2 =
* Patch to possible exploit when editing a client.
* Slight UI changes.
* Patched auth code table for large id_tokens.
* Fixed security issue with token lifetime.

= 3.1.1 =
* Client name is not click able to show edit popup
* Fixed issue with missing exits in API

= 3.1.0 =
* Added specific OpenSSL bit length for systems that are not create keys at 2048 by default.
* Added urlSafeBase64 encoding to Modulus and Exponent on delivery.
* Tweak redirect location in API when a user is not logged in

= 3.0.9 =
* Added userinfo endpoint to /.well-known/openid-configuration 
* Fixed improper return of keys when for public facing /.well-known
* Auto generation of new certificates during activation to ensure all server have a different signature

= 3.0.8 =
* Switched JWT Signing to uses RS256 instead of HS256.
* Added OpenID Discovery with REQUIRED fields and values.
* "sub" now complies with OpenID specs for format type.
* Added JWT return for public key when using OpenID Discovery.

= 3.0.7 =
* Bug fix in OpenID

= 3.0.6 =
* Fixed "Undefined Error" in Authorization Controller. Credit to Frédéric. Thank You!
* Remove "Redirect URI" Column from clients table to clean up table on smaller screens.
* Updated banner and plugin icon.

= 3.0.5 =
* Removed permalink check. OAuth Server now works without the use of permalinks.
* Fixed install functionality. Not all tables were being installed.
* Added support for cytpto tokens.
* Added OpenID Connect abilities.
* Mapped OpenID Claims to default user values
* Added index to token table and increased access_token length to support crypto tokens in the future.
* Added "email" to default me resource to support OpenID Connect 1.0
* Added generic key signing for all clients.
* Added public endpoint for verifying id_token (/oauth/public_key)

= 3.0.4 = 
* Updated Readme.txt content
* Add more descriptive text during PHP version check
* Fixed license links
* Added Access Token and Refresh Token lifetime settings
* Added upgrade method to ensure proper installing of new features

= 3.0.3 =
* Modified how clients are added and edited
* Add Pro Features
* Added additional information to "Server Status" Tab
* Minor Clean Up

= 3.0.2 =
* Re added Authorization Code Enable Option
* API unavailable error now uses OAuth Response object
* API now reports when access token is not provided during resource calls

= 3.0.1 =
* Updated cover image.
* Fixed documentation links.
* Added "Server Status" tab
* Cleaned up "Advanced Configuration" contents.

= 3.0.0 =
* Updated and rebuilt structure.
* Visit <a href="http://wp-oauth.com">http://wp-oauth.com</a> for documentation and more information.

= 2.0.0 =
* Rebuild init plugin code structure for more flexibility and scalability.
* Added prefix to all DB connections
* Changed install query to use the InnoDB engine for better support and performance.
* Fixed improper loading of plugin style sheet.
* Removed garbage data when plugin is activated. It was not being used and cluttering the code base as well as the database.
* Move action template_redirect to rewrites file
* Added login form support for installs that are installed in sub directory
* Added missing in documentation for when calling requesting_token
* Suppressed some errors that was preventing a proper JSON return when `WP_DEBUG` was enabled.
* Added a client sample script to help learn the basics of connecting to the provider plugin.
* Add legacy installer that will hopefully keep old data in tacked while updating to the new structure with no data loss.
* Removed plugin logging as it was not really needed and caused more issues that it was worth.

= 1.0.3 =
* Fixed Admin URL links for plugin dashboard

= 1.0.2 = 
* Fixed Broken login redirect

= 1.0.1 =
* Re-worked Readme.txt
* Fixed absolute paths causing 404 Error when WordPress is running under a sub directory (Using admin_url() currently)

= 1.0.0 =
* INITIAL BUILD