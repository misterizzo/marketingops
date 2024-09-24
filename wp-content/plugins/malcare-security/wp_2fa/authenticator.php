<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('MCWP2FAAuthenticator')) :

class MCWP2FAAuthenticator
{
	private static $code_length = 6;

	const BASE32_LOOKUP_TABLE = array(
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
		'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
		'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
		'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
		'=',  // padding char
	);

	private static function getCode($secret, $time_slice = null) {
		if ($time_slice === null) {
			$time_slice = floor(time() / 30);
		}

		$secret_key = self::_base32Decode($secret);
		$time = chr(0).chr(0).chr(0).chr(0).pack('N*', $time_slice);

		$hm = hash_hmac('SHA1', $time, $secret_key, true);

		$offset = ord(substr($hm, -1)) & 0x0F;
		$hashpart = substr($hm, $offset, 4);

		$value = unpack('N', $hashpart);
		$value = $value[1];
		$value = $value & 0x7FFFFFFF;

		$modulo = pow(10, self::$code_length);

		return str_pad($value % $modulo, self::$code_length, '0', STR_PAD_LEFT);
	}

	public static function verifyCode($secret, $code, $discrepancy = 1, $current_time_slice = null)	{
		if ($current_time_slice === null) {
			$current_time_slice = floor(time() / 30);
		}

		if (strlen($code) != 6) {
			return false;
		}

		for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
			$calculated_code = self::getCode($secret, $current_time_slice + $i);
			if (self::timingSafeEquals($calculated_code, $code)) {
				return true;
			}
		}

		return false;
	}

	private static function _base32Decode($secret) {
		$base32_chars = MCWP2FAAuthenticator::BASE32_LOOKUP_TABLE;
		$base32_chars_flipped = array_flip($base32_chars);

		$padding_char_count = substr_count($secret, $base32_chars[32]);
		$allowed_values = array(6, 4, 3, 1, 0);
		if (!in_array($padding_char_count, $allowed_values)) {
			return false;
		}
		for ($i = 0; $i < 4; ++$i) {
			if ($padding_char_count == $allowed_values[$i] &&
				substr($secret, -($allowed_values[$i])) != str_repeat($base32_chars[32], $allowed_values[$i])) {
				return false;
			}
		}
		$secret = str_replace('=', '', $secret);

		$secret = str_split($secret);
		$binary_string = '';
		for ($i = 0; $i < count($secret); $i = $i + 8) {
			$x = '';
			if (!in_array($secret[$i], $base32_chars)) {
				return false;
			}
			for ($j = 0; $j < 8; ++$j) {
				$x .= str_pad(base_convert(@$base32_chars_flipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
			}
			$eight_bits = str_split($x, 8);
			for ($z = 0; $z < count($eight_bits); ++$z) {
				$binary_string .= (($y = chr(base_convert($eight_bits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
			}
		}

		return $binary_string;
	}

	private static function timingSafeEquals($safe_string, $user_string) {
		if (function_exists('hash_equals')) {
			return hash_equals($safe_string, $user_string);
		}
		$safe_len = strlen($safe_string);
		$user_len = strlen($user_string);

		if ($user_len != $safe_len) {
			return false;
		}

		$result = 0;

		for ($i = 0; $i < $user_len; ++$i) {
			$result |= (ord($safe_string[$i]) ^ ord($user_string[$i]));
		}

		return $result === 0;
	}
}
endif;