<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH') && !defined('PHP_ERR_MONIT_PATH')) exit;

if (!class_exists('MCHelper')) :
	class MCHelper {
		public static function safePregMatch($pattern, $subject, &$matches = null, $flags = 0, $offset = 0) {
			if (!is_string($pattern) || !is_string($subject)) {
				return false;
			}
			return preg_match($pattern, $subject, $matches, $flags, $offset);
		}

		# XNOTE - The below function assumes valid input
		# $array should be an array and $keys should be an array of string, or integer data
		public static function filterArray($array, $keys) {
			$filteredArray = array();
			foreach ($keys as $key) {
				if (array_key_exists($key, $array)) {
					$filteredArray[$key] = $array[$key];
				}
			}
			return $filteredArray;
		}

		# XNOTE - The below function assumes valid input
		# $array should be an array and $keys should be an array of string, or integer data
		public static function digArray($array, $keys) {
			if (empty($keys)) {
				return null;
			}
			$curr_array = $array;
			foreach ($keys as $key) {
				if (is_array($curr_array) && array_key_exists($key, $curr_array)) {
					$curr_array = $curr_array[$key];
				} else {
					return null;
				}
			}
			return $curr_array;
		}

		public static function arrayKeyFirst($array) {
			if (!function_exists('array_key_first')) {
				foreach ($array as $key => $value) {
					return $key;
				}
				return null;
			}

			return array_key_first($array);
		}

		public static function safePregReplace($replace_regex, $replace_string, $element, $limit = -1) {
			if (!is_string($replace_regex) || !is_string($replace_string) || !is_string($element) || !is_int($limit)) {
				return $element;
			}

			$updated_element = preg_replace($replace_regex, $replace_string, $element, $limit);

			if ($updated_element === null && preg_last_error() !== PREG_NO_ERROR) {
				return $element;
			}

			return $updated_element;
		}	

		public static function safeStrReplace($search, $replace, $subject) {
			if (!is_string($search) || !is_string($replace) || !is_string($subject)) {
				return $subject;
			}
			$updated_subject = str_replace($search, $replace, $subject);
			if ($updated_subject === null) {
				return $subject;
			}
			return $updated_subject;
		}

		public static function preInitWPHook($hook_name, $function_name, $priority, $accepted_args) {
			global $wp_filter;

			// Check if $wp_filter is not initialized or not an array
			if (!isset($wp_filter) || !is_array($wp_filter)) {
				$wp_filter = array();
			}

			// Check if the hook exists in $wp_filter
			if (!isset($wp_filter[$hook_name])) {
				$wp_filter[$hook_name] = array();
			}

			// Check if the priority exists for the hook
			if (!isset($wp_filter[$hook_name][$priority])) {
				$wp_filter[$hook_name][$priority] = array();
			}

			// Add the filter function information to the $wp_filter array
			$wp_filter[$hook_name][$priority][] = array(
				'function' => $function_name,
				'accepted_args' => $accepted_args,
			);
		}

		public static function removePatternFromWpConfig($pattern) {
			if (!defined('ABSPATH')) {
				return;
			}

			$wp_conf_paths = array(
				rtrim(ABSPATH, DIRECTORY_SEPARATOR) . "/wp-config.php",
				rtrim(ABSPATH, DIRECTORY_SEPARATOR) . "../wp-config.php"
			);

			if (file_exists($wp_conf_paths[0])) {
				$fname = $wp_conf_paths[0];
			} elseif (file_exists($wp_conf_paths[1])) {
				$fname = $wp_conf_paths[1];
			} else {
				return;
			}

			self::fileRemovePattern($fname, $pattern);
		}

		public static function fileRemovePattern($fname, $pattern, $is_regex = false) {
			if (!is_string($fname) || !is_string($pattern)) {
				return;
			}

			if (!MCWPFileSystem::getInstance()->exists($fname)) {
				return;
			}

			$content = MCWPFileSystem::getInstance()->getContents($fname);
			if ($content !== false) {
				if ($is_regex !== false) {
					$modified_content = preg_replace($pattern, "", $content);
				} else {
					$modified_content = str_replace($pattern, "", $content);
				}

				if (empty($modified_content)) {
					return;
				}

				if ($content !== $modified_content) {
					MCWPFileSystem::getInstance()->putContents($fname, $modified_content,
							MCWPFileSystem::getInstance()->getchmodOctal($fname));
				}
			}
		}

		public static function opensslEncrypt($plain_text, $cipher_algo, $encryption_key, $iv = null) {
			if (!function_exists('openssl_encrypt') || !function_exists('openssl_get_cipher_methods') ||
					!function_exists('openssl_random_pseudo_bytes') || !function_exists('openssl_cipher_iv_length')) {
				return array(false, "OpenSSL extension not found.");
			}

			if (empty($plain_text) || !is_string($plain_text) ||
					empty($encryption_key) || !is_string($encryption_key)) {
				return array(false, "Plain text or encryption key is not a valid string.");
			}

			if (!in_array($cipher_algo, openssl_get_cipher_methods(), true)) {
				return array(false, "Invalid cipher algorithm - " . $cipher_algo);
			}

			if ($iv === null) {
				$iv_length = openssl_cipher_iv_length($cipher_algo);
				if ($iv_length === false) {
					return array(false, "IV length not found.");
				}
				$iv = openssl_random_pseudo_bytes($iv_length);
				if ($iv === false) {
					return array(false, "IV generation failed.");
				}
			}

			if (strlen($iv) !== $iv_length) {
				return array(false, "Invalid IV length. Expected length is " . $iv_length . " bytes.");
			}

			$encrypted_data = openssl_encrypt($plain_text, $cipher_algo, $encryption_key, OPENSSL_RAW_DATA, $iv);
			if ($encrypted_data === false) {
				return array(false, "Encryption failed.");
			}

			return array(true, ($iv . $encrypted_data));
		}

		public static function opensslDecrypt($data, $cipher_algo, $encryption_key) {
			if (!function_exists('openssl_decrypt') || !function_exists('openssl_get_cipher_methods') ||
					!function_exists('openssl_cipher_iv_length')) {
				return array(false, "OpenSSL extension not found.");
			}

			if (empty($data) || !is_string($data) || empty($encryption_key) || !is_string($encryption_key)) {
				return array(false, "Encrypted secret or encryption key is not a valid string.");
			}

			if (!in_array($cipher_algo, openssl_get_cipher_methods(), true)) {
				return array(false, "Invalid cipher algorithm - " . $cipher_algo);
			}

			$iv_length = openssl_cipher_iv_length($cipher_algo);
			if ($iv_length === false) {
				return array(false, "IV length not found.");
			}

			if (strlen($data) <= $iv_length) {
				return array(false, "Data length is insufficient to contain IV.");
			}

			$iv = substr($data, 0, $iv_length);
			$encrypted_data = substr($data, $iv_length);

			if ($iv === false || $encrypted_data === false) {
				return array(false, "IV or encrypted data not found.");
			}

			$decrypted_data = openssl_decrypt($encrypted_data, $cipher_algo, $encryption_key, OPENSSL_RAW_DATA, $iv);

			if ($decrypted_data === false) {
				return array(false, "Decryption failed.");
			}

			return array(true, $decrypted_data);
		}

		public static function get_direct_filesystem() {
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			return new WP_Filesystem_Direct(new StdClass());
		}

		/**
		 * Maybe unslash a value if WordPress is loaded
		 *
		 * @param string $value The value to potentially unslash
		 * @return string The unslashed value if WP is loaded, original value otherwise
		 */
		public static function maybeUnslashValue($value) {
			if (function_exists('wp_unslash')) {
				return wp_unslash($value);
			}
			return $value;
		}

		/**
		 * Get and sanitize a string parameter from superglobal
		 *
		 * @param string $superglobal The superglobal type ('GET', 'POST', etc.)
		 * @param string $key The parameter key to retrieve
		 * @param string $context The sanitization context ('text', 'email', 'url')
		 * @return string|null Sanitized string value or null if invalid or unknown context
		 */
		public static function getStringParamSanitized($superglobal, $key, $context) {
			$raw_value = self::getRawParam($superglobal, $key);

			if (!is_string($raw_value)) {
				return null;
			}

			switch ($context) {
			case 'text':
				if (!function_exists('sanitize_text_field')) {
					return null;
				}
				return sanitize_text_field($raw_value);
			case 'email':
				if (!function_exists('sanitize_email')) {
					return null;
				}
				return sanitize_email($raw_value);
			case 'url':
				if (!function_exists('esc_url_raw')) {
					return null;
				}
				return esc_url_raw($raw_value);
			default:
				return null;
			}
		}

		/**
		 * Get and escape a string parameter from superglobal
		 *
		 * @param string $superglobal The superglobal type ('GET', 'POST', etc.)
		 * @param string $key The parameter key to retrieve
		 * @param string $context The escaping context ('attr', 'html', 'url')
		 * @return string|null Escaped string value or null if invalid or unknown context
		 */
		public static function getStringParamEscaped($superglobal, $key, $context) {
			$raw_value = self::getRawParam($superglobal, $key);

			if (!is_string($raw_value)) {
				return null;
			}

			switch ($context) {
			case 'attr':
				if (!function_exists('esc_attr')) {
					return null;
				}
				return esc_attr($raw_value);
			case 'html':
				if (!function_exists('esc_html')) {
					return null;
				}
				return esc_html($raw_value);
			case 'url':
				if (!function_exists('esc_url')) {
					return null;
				}
				return esc_url($raw_value);
			default:
				return null;
			}
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		/**
		 * Get raw parameter value from superglobal
		 *
		 * @param string $superglobal The superglobal type ('GET', 'POST', etc.)
		 * @param string $key The parameter key to retrieve
		 * @return mixed Raw parameter value or null if not found
		 */
		public static function getRawParam($superglobal, $key) {
			$value = null;

			switch (strtoupper($superglobal)) {
			case 'GET':
				$value = isset($_GET[$key]) ? $_GET[$key] : null;
				break;
			case 'POST':
				$value = isset($_POST[$key]) ? $_POST[$key] : null;
				break;
			case 'COOKIE':
				$value = isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
				break;
			case 'REQUEST':
				$value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
				break;
			case 'SERVER':
				$value = isset($_SERVER[$key]) ? $_SERVER[$key] : null;
				break;
			}

			return $value !== null ? self::maybeUnslashValue($value) : null;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
	}
endif;