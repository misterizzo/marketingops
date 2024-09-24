<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('MCWP2FA')) :

require_once dirname(__FILE__) . '/authenticator.php';
require_once dirname(__FILE__) . '/utils.php';

class MCWP2FA {
	const FLAG_META_KEY = 'mc_2fa_enabled';
	const SECRET_META_KEY = 'mc_2fa_secret';

	public static $cipher_algo = 'aes-256-cbc';
	public static $wp_2fa_option = 'mcWp2faConf';
	private $settings;
	private $config;

	public function __construct() {
		$this->settings = new MCWPSettings();
		$this->config = $this->settings->getOption(self::$wp_2fa_option);
	}

	private function can_init() {
		if (is_array($this->config) && array_key_exists('enabled', $this->config) &&
				$this->config['enabled'] === true) {

			return true;
		}
		return false;
	}

	public function init() {
		if ($this->can_init() === false) {
			return;
		}

		add_filter('authenticate', array($this, 'authenticate'), 25, 3);
		add_action('login_form', array($this, 'custom_login_form'));
	}

	public function authenticate($user, $username, $password) {
		if (!($user instanceof WP_User)) {
			return $user;
		}

		$has_2fa = get_user_meta($user->ID, MCWP2FA::FLAG_META_KEY, true);

		if ('1' === $has_2fa) {
			if (empty($_POST['twofa_code'])) {
				wp_send_json_success(array('twofa_enabled' => true));
				exit;
			} else {
				$encoded_secret_info = get_user_meta($user->ID, MCWP2FA::SECRET_META_KEY, true);

				$secret_info = MCWP2FAUtils::getSecretInfo($encoded_secret_info);
				$secret = $secret_info['secret'];
				$is_secret_encrypted = $secret_info['is_encrypted'];

				if (is_null($secret) || is_null($is_secret_encrypted)) {
					return new WP_Error('invalid_2fa_configuration', __('Please contact your administrator to login.'));
				}

				if (defined('SECURE_AUTH_KEY') && $is_secret_encrypted === true) {
					$decryption_result = MCHelper::opensslDecrypt($secret, self::$cipher_algo, SECURE_AUTH_KEY);
					if ($decryption_result[0] === false) {
						return new WP_Error('2fa_secret_key_decryption_error', __('Please contact your administrator to login.'));
					}
					$secret = $decryption_result[1];
				}

				if (empty($secret) || !is_string($secret) || 32 !== strlen($secret)) {
					return new WP_Error('invalid_2fa_configuration', __('Please contact your administrator to login.'));
				}

				$submitted_code = sanitize_text_field($_POST['twofa_code']);

				if (is_string($submitted_code) && ctype_digit($submitted_code) &&
						true === MCWP2FAAuthenticator::verifyCode($secret, $submitted_code)) {

					return $user;
				} else {
					return new WP_Error('invalid_2fa_code', __('The 2FA code you entered is incorrect.'));
				}
			}
		}

		return $user;
	}

	function custom_login_form() {
?>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				const loginForm = document.getElementById('loginform');
				const usernameField = document.getElementById('user_login');
				const passwordField = document.getElementById('user_pass');
				let loginError = document.getElementById('login_error');
				let isTwoFAEnabled = false;

				if (loginForm && usernameField && passwordField) {
					loginForm.addEventListener('submit', handleSubmit);
				}

				function handleSubmit(event) {
					event.preventDefault();

					const formData = new FormData(loginForm);

					fetch(loginForm.action, {
						method: 'POST',
						body: formData,
						credentials: 'same-origin'
					}).then(response => response.text())
					.then(text => {
						try {
							return JSON.parse(text);
						} catch (e) {
							return { success: false, html: text };
						}
					})
					.then(data => {
						if (data.success && data.data && data.data.twofa_enabled) {
							isTwoFAEnabled = true;
							showTwoFAField();
							clearLoginError();
						} else {
							if (data.html) {
								handleHtmlResponse(data.html);
							} else if (data.data && data.data.message) {
								displayError(data.data.message);
							} else {
								displayError('An unknown error occurred');
							}
							if (isTwoFAEnabled) {
								showTwoFAField();
							}
						}
					})
					.catch(error => {
						displayError('An error occurred while processing your request');
						if (isTwoFAEnabled) {
							showTwoFAField();
						}
					});
				}

				function handleHtmlResponse(html) {
					const parser = new DOMParser();
					const doc = parser.parseFromString(html, 'text/html');
					const errorElement = doc.getElementById('login_error');
					if (errorElement) {
						displayError(errorElement.innerText.trim());
					} else {
						proceedWithLogin();
					}
				}

				function showTwoFAField() {
					let twofaField = document.getElementById('twofa_code_field');
					if (!twofaField) {
						twofaField = document.createElement('p');
						twofaField.id = 'twofa_code_field';
						twofaField.innerHTML = '<label for="twofa_code">2FA Code<br><input type="text" name="twofa_code" id="twofa_code" class="input" value="" size="20"></label>';
						passwordField.parentNode.insertBefore(twofaField, passwordField.nextSibling);
					}
					twofaField.style.display = 'block';
					document.getElementById('twofa_code').value = '';
				}

				function clearLoginError() {
					if (loginError) {
						loginError.style.display = 'none';
					}
				}

				function displayError(message) {
					if (!loginError) {
						loginError = document.createElement('div');
						loginError.id = 'login_error';
						loginForm.parentNode.insertBefore(loginError, loginForm);
					}
					loginError.innerHTML = message;
					loginError.style.display = 'block';
				}

				function proceedWithLogin() {
					loginForm.removeEventListener('submit', handleSubmit);
					loginForm.submit();
				}
			});
			</script>
<?php
	}
}
endif;

$wp_2fa = new MCWP2FA();
$wp_2fa->init();