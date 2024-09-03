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

		public static function safePregReplace($replace_regex, $replace_string, $element) {
			if (!is_string($replace_regex) || !is_string($replace_string) || !is_string($element)) {
				return $element;
			}
			$updated_element = preg_replace($replace_regex, $replace_string, $element);
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
	}
endif;