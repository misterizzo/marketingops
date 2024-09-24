<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('MCWP2FAUtils')) :
	class MCWP2FAUtils {
		public static function getSecretInfo($info) {
			$default_info = array('secret' => null, 'is_encrypted' => null);

			if (empty($info) || !array_key_exists('secret', $info) || empty($info['secret']) ||
					!array_key_exists('is_encrypted', $info) || !is_bool($info['is_encrypted'])) {
				return $default_info;
			}

			$secret = base64_decode($info['secret']);
			if ($secret === false) {
				return $default_info;
			}

			return array('secret' => $secret, 'is_encrypted' => $info['is_encrypted']);
		}
	}
endif;