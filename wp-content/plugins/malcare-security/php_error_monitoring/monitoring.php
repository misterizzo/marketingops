<?php
if (!defined('ABSPATH') && !defined("PHP_ERR_MONIT_PATH")) exit;

if (!class_exists('MCWPPHPErrorMonitoring')) :
	class MCWPPHPErrorMonitoring {
		private static $db;
		private static $settings;
		private static $info;
		private static $initialized = false;
		private static $config = array();

		const ERROR_TABLE = 'php_error_store';

		private static $include_backtrace = false;
		private static $max_table_length = 10000;
		private static $error_level = E_ALL;
		private static $max_backtrace_frames = 10;
		private static $md5s_to_ignore = array();

		public static function init() {
			if (self::$initialized) {
				return;
			}
			include_once dirname(__FILE__) . '/../wp_settings.php';
			include_once dirname(__FILE__) . '/../wp_db.php';
			include_once dirname(__FILE__) . '/../info.php';

			self::$settings = new MCWPSettings();
			self::$db = new MCWPDb();
			self::$info = new MCInfo(self::$settings);

			if (self::$info->isServiceActive('php_error_monitoring')) {
				add_action('mc_clear_php_error_config', array('MCWPPHPErrorMonitoring', 'clearConfig'));
			}

			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			if (is_plugin_active('malcare-security/malcare.php')) {
				if (self::$info->isServiceActive('php_error_monitoring') && self::$info->hasValidDBVersion()) {
					self::updateDefaultValues();
					self::setPhpErrorHandler();
					self::$initialized = true;
				}
			}
		}

		public static function clearConfig() {
			self::$db->dropBVTable(self::ERROR_TABLE);
		}

		public static function isValidErrorLevel($error_level) {
			return is_int($error_level) && ($error_level & E_ALL) === $error_level;
		}

		public static function updateDefaultValues() {
			if (!isset(self::$info->config['php_error_monitoring']) ||
					!is_array(self::$info->config['php_error_monitoring'])) {
				return;
			}

			$config = self::$info->config['php_error_monitoring'];

			if (array_key_exists('include_backtrace', $config) && is_bool($config['include_backtrace'])) {
				self::$include_backtrace = $config['include_backtrace'];
			}

			if (array_key_exists('max_table_length', $config) && is_int($config['max_table_length'])) {
				self::$max_table_length = $config['max_table_length'];
			}

			if (array_key_exists('error_level', $config) && self::isValidErrorLevel($config['error_level'])) {
				self::$error_level = $config['error_level'];
			}

			if (array_key_exists('md5s_to_ignore', $config) && is_array($config['md5s_to_ignore'])) {
				self::$md5s_to_ignore = $config['md5s_to_ignore'];
			}

			if (array_key_exists('max_backtrace_frames', $config) && is_int($config['max_backtrace_frames'])) {
				self::$max_backtrace_frames = $config['max_backtrace_frames'];
			}
		}

		public static function setPhpErrorHandler() {
			set_error_handler(array(self::class, 'errorHandler'), self::$error_level);
			register_shutdown_function(array(self::class, 'handleShutdown'));
		}

		public static function handleShutdown() {
			$error = error_get_last();
			if (null !== $error && (($error['type'] & self::$error_level) === $error['type'])) {
				self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
			}
		}

		public static function canAddToBVTable() {
			$bv_table = self::$db->getBVTable(self::ERROR_TABLE);

			if (!self::$db->isTablePresent($bv_table)) {
				return false;
			}

			$row_count = self::$db->rowsCount($bv_table);
			return $row_count < self::$max_table_length;
		}

		public static function saveError($data) {
			if (!self::canAddToBVTable()) {
				return;
			}
			$values = array("data" => $data, "time" => time());
			self::$db->insertIntoBVTable(self::ERROR_TABLE, $values);
		}

		public static function canCaptureError($md5) {
			if (in_array($md5, self::$md5s_to_ignore, true)) {
				return false;
			}
			return true;
		}

		public static function errorHandler($code, $message, $file, $line) {
			$data = array();

			$serialized_backtrace = '';
			if (self::$include_backtrace) {
				$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, self::$max_backtrace_frames);

				$keys = array_flip(array('file', 'line', 'function'));
				foreach ($backtrace as &$frame) {
					$frame = array_intersect_key($frame, $keys);
				}

				$serialized_backtrace = maybe_serialize($backtrace);
				$data["backtrace"] = $serialized_backtrace;
			}

			$data["md5"] = md5($code . '-' . $message . '-' . $line . '-' . $file . '-' . $serialized_backtrace);

			if (!self::canCaptureError($data['md5'])) {
				return;
			}

			$data["error_code"] = $code;
			$data["error_message"] = $message;
			$data["error_line"] = $line;
			$data["error_file"] = $file;
			$uri = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '';
			$data["request_path"] = parse_url($uri, PHP_URL_PATH);
			$data["request_id"] = MCInfo::getRequestID();

			self::saveError(maybe_serialize($data));
		}
	}
endif;