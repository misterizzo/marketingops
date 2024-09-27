<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('MCWP2FA')) :

require_once dirname(__FILE__) . '/authenticator.php';
require_once dirname(__FILE__) . '/utils.php';

class MCWP2FA {
	const FLAG_META_KEY = 'mc_2fa_enabled';
	const SECRET_META_KEY = 'mc_2fa_secret';
	const INVALID_CODE_MESSAGE = 'The 2FA code you entered is incorrect.';
	const TOOLTIP_MESSAGE = 'Please contact your administrator if you need assistance.';

	public static $cipher_algo = 'aes-256-cbc';
	public static $wp_2fa_option = 'mcWp2faConf';
	
	private $bvinfo;
	private $settings;
	private $invalid_code_message = self::INVALID_CODE_MESSAGE;
	private $tooltip_message = self::TOOLTIP_MESSAGE;

	public function __construct() {
		$this->settings = new MCWPSettings();
		$this->bvinfo = new MCInfo($this->settings);

		$whitelabel_info = $this->bvinfo->getLPWhitelabelInfo();

		if (isset($whitelabel_info['2fa_error_message']) && is_string($whitelabel_info['2fa_error_message'])) {
			$this->invalid_code_message = $whitelabel_info['2fa_error_message'];
		}

		if (isset($whitelabel_info['2fa_tooltip']) && is_string($whitelabel_info['2fa_tooltip'])) {
			$this->tooltip_message = $whitelabel_info['2fa_tooltip'];
		}
	}

	public static function isEnabled($settings) {
		$config = $settings->getOption(self::$wp_2fa_option);

		return (is_array($config) && array_key_exists('enabled', $config) &&
				$config['enabled'] === true);
	}

	public function init() {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_dashicons'));
		add_filter('authenticate', array($this, 'authenticate'), 25, 3);
		add_action('login_form', array($this, 'custom_login_form'));
	}

	public function enqueue_dashicons() {
		wp_enqueue_style('dashicons');
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
					return new WP_Error('invalid_2fa_code', __(esc_html($this->invalid_code_message)));
				}
			}
		}

		return $user;
	}

	function custom_login_form() {
		$tooltip_message = $this->tooltip_message;
		$is_url = filter_var($tooltip_message, FILTER_VALIDATE_URL);

		$icon_css = 'font-size: 20px; color: #2271b1; cursor: pointer;';
		$icon_html = '<span
				id="twofa_help_icon"
				class="dashicons dashicons-editor-help"
				style="' . esc_attr($icon_css) . '"></span>';

		if ($is_url) {
			$tooltip_html = '<a
					href="' . esc_url($tooltip_message) . '"
					target="_blank"
					style="text-decoration: none;">' . $icon_html . '</a>';
		} else {
			$tooltip_html = '<span
					id="twofa_help_icon"
					class="dashicons dashicons-editor-help"
					title="' . esc_attr($tooltip_message) . '"
					style="' . esc_attr($icon_css) . '"></span>';
		}
?>
		<style>
			.wp2fa-progress-bar {
				width: 100%;
				background-color: #f3f3f3;
				display: none;
				margin-bottom: 10px;
			}

			.wp2fa-progress-bar.show {
				display: block;
			}

			.wp2fa-progress-bar .progress-bar-inner {
				width: 0;
				height: 5px;
				background-color: #2271b1;
				animation: loader 1s ease infinite;
			}

			@keyframes loader {
				100% {width: 100%}
			}
		</style>

		<div class="wp2fa-progress-bar">
			<div class="progress-bar-inner"></div>
		</div>

		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				const loginForm = document.getElementById('loginform');
				const usernameField = document.getElementById('user_login');
				const passwordField = document.getElementById('user_pass');
				const loginButton = document.getElementById('wp-submit');
				let loginError = document.getElementById('login_error');
				let isTwoFAEnabled = false;
				const progressBar = document.getElementsByClassName('wp2fa-progress-bar')[0];

				if (loginForm && usernameField && passwordField && loginButton) {
					loginForm.addEventListener('submit', handleSubmit);
				}

				function handleSubmit(event) {
					event.preventDefault();
					showProgressBar();
					disableLoginButton();

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
					})
					.finally(() => {
						hideProgressBar();
						enableLoginButton();
					})
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
						twofaField.innerHTML = `
							<label for="twofa_code" style="position: relative; display: block;">
								2FA Code
								<?php echo $tooltip_html; ?>
							</label>
							<input type="text" required name="twofa_code" id="twofa_code" class="input" value="" maxlength="6" minlength="6">
							`;
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
					loginError.textContent = message;
					loginError.style.display = 'block';
				}

				function proceedWithLogin() {
					loginForm.removeEventListener('submit', handleSubmit);
					loginForm.submit();
				}

				function showProgressBar() {
					progressBar.classList.add('show');
				}

				function hideProgressBar() {
					progressBar.classList.remove('show');
				}

				function disableLoginButton() {
					loginButton.disabled = true;
					loginButton.value = 'Verifying...';
				}

				function enableLoginButton() {
					loginButton.disabled = false;
					loginButton.value = 'Log In';
				}
			});
			</script>
<?php
	}
}
endif;