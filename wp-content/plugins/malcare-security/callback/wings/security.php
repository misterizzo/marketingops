<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('BVSecurityCallback')) :
	class BVSecurityCallback extends BVCallbackBase {
		private $settings;

		public function __construct() {
			$this->settings = new MCWPSettings();
		}

		function getCrontab() {
			$resp = array();

			if (function_exists('exec')) {
				$output = array();
				$retval = -1;
				$execRes = exec('crontab -l', $output, $retval);
				if ($execRes !== false && $execRes !== null) {
					$resp["content"] = implode("\n", $output);
					$resp["status"] = "success";
					$resp["code"] = $retval;
				}
			}
			if (empty($resp) && function_exists('popen')) {
				$handle = popen('crontab -l', 'rb');
				if ($handle) {
					$output = '';
					while (!feof($handle)) {
						$output .= fread($handle, 8192);
					}
					$resp["content"] = $output;
					$resp["status"] = "success";
					pclose($handle);
				} else {
					$resp["status"] = "failed";
				}
			}

			return $resp;
		}

		public function setupWP2FA($secrets_by_uids, $to_encrypt, $cipher_algo, $enabled) {
			if (!is_array($secrets_by_uids)) {
				return array("status" => false, "message" => "secrets_by_uids is not an array.");
			}

			$result = array();
			foreach ($secrets_by_uids as $user_id => $secret) {
				if (empty($user_id) || !is_string($secret)) {
					continue;
				}

				if ($to_encrypt === true) {
					if (empty($cipher_algo)) {
						$cipher_algo = MCWP2FA::$cipher_algo;
					}

					if (defined('SECURE_AUTH_KEY')) {
						$encryption_result = MCHelper::opensslEncrypt($secret, $cipher_algo, SECURE_AUTH_KEY);
						if ($encryption_result[0] === false) {
							return array("status" => false, "message" => $encryption_result[1]);
						}
						$secret = $encryption_result[1];
					} else {
						return array("status" => false, "message" => "Encryption key not found.");
					}
				}

				$secret_info = array(
					"secret" => base64_encode($secret),
					"is_encrypted" => $to_encrypt
				);

				$result[$user_id][MCWP2FA::SECRET_META_KEY] = update_user_meta($user_id, MCWP2FA::SECRET_META_KEY, $secret_info);
				$result[$user_id][MCWP2FA::FLAG_META_KEY] = update_user_meta($user_id, MCWP2FA::FLAG_META_KEY, true);
			}

			if (is_bool($enabled)) {
				$config = array("enabled" => $enabled);
				$result[MCWP2FA::$wp_2fa_option] = $this->settings->updateOption(MCWP2FA::$wp_2fa_option, $config);
			}

			return array("status" => true, "result" => $result);
		}

		public function verifyWP2FACode($user_id, $code, $cipher_algo = null) {
			$encoded_secret_info = get_user_meta($user_id, MCWP2FA::SECRET_META_KEY, true);

			$secret_info = MCWP2FAUtils::getSecretInfo($encoded_secret_info);
			$secret = $secret_info['secret'];
			$is_secret_encrypted = $secret_info['is_encrypted'];

			if (is_null($secret) || is_null($is_secret_encrypted)) {
				return array("status" => false, "message" => "Secret and encryption status not found.");
			}

			if ($is_secret_encrypted === true) {
				if (empty($cipher_algo)) {
					$cipher_algo = MCWP2FA::$cipher_algo;
				}

				if (defined('SECURE_AUTH_KEY')) {
					$decryption_result = MCHelper::opensslDecrypt($secret, $cipher_algo, SECURE_AUTH_KEY);
					if ($decryption_result[0] === false) {
						return array("status" => false, "message" => $decryption_result[1]);
					}
					$secret = $decryption_result[1];
				} else {
					return array("status" => false, "message" => "Decryption key not found.");
				}
			}

			return array("status" => MCWP2FAAuthenticator::verifyCode($secret, $code, 2));
		}

		public function readWP2FAKeys($user_id) {
			$secret = get_user_meta($user_id, MCWP2FA::SECRET_META_KEY, true);
			$enabled = get_user_meta($user_id, MCWP2FA::FLAG_META_KEY, true);
			return array(
				"secret" => $secret,
				"enabled" => $enabled
			);
		}

		public function deleteWP2FAKeys($user_ids, $is_disable = false) {
			$result = array();

			foreach ($user_ids as $user_id) {
				$secret_deleted = delete_user_meta($user_id, MCWP2FA::SECRET_META_KEY);
				$flag_deleted = delete_user_meta($user_id, MCWP2FA::FLAG_META_KEY);
				$result[$user_id] = array(
					MCWP2FA::SECRET_META_KEY => $secret_deleted,
					MCWP2FA::FLAG_META_KEY => $flag_deleted
				);
			}

			if ($is_disable === true) {
				$result[MCWP2FA::$wp_2fa_option] = $this->settings->deleteOption(MCWP2FA::$wp_2fa_option);
			}

			return array("status" => true, "result" => $result);
		}

		public function process($request) {
			$params = $request->params;

			switch ($request->method) {
			case "gtcrntb":
				$resp = $this->getCrontab();
				break;
			case "stupwp2fa":
				$enable_wp_2fa = null;
				if (array_key_exists('enable_wp_2fa', $request->params)) {
					$enable_wp_2fa = $request->params['enable_wp_2fa'];
				}

				$resp = $this->setupWP2FA($params['secrets_by_uids'], $params['to_encrypt'], $params['cipher_algo'], $enable_wp_2fa);
				break;
			case "vrfywp2fa":
				$resp = $this->verifyWP2FACode($params['user_id'], $params['code'], $params['cipher_algo']);
				break;
			case "rdwp2fa":
				$resp = $this->readWP2FAKeys($params['user_id']);
				break;
			case "dltewp2fa":
				$resp = $this->deleteWP2FAKeys($params['user_ids'], $params['is_disable']);
				break;
			default:
				$resp = false;
			}

			return $resp;
		}
	}
endif;