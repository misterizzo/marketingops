<?php
/**
 * LearnDash Strings helper class.
 *
 * @since 4.6.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Utilities;

/**
 * A helper class to provide string manipulation methods.
 *
 * @since 4.6.0
 */
class Str {
	/**
	 * Replaces the first occurrence of a given value in the string.
	 *
	 * @since 4.6.0
	 *
	 * @param string $search  The string to search for and replace.
	 * @param string $replace The replacement string.
	 * @param string $subject The string to do the search and replace from.
	 *
	 * @return string The string with the first occurrence of a given value replaced.
	 */
	public static function replace_first( string $search, string $replace, string $subject ): string {
		if ( '' === $search ) {
			return $subject;
		}

		$position = strpos( $subject, $search );

		if ( $position !== false ) {
			return substr_replace( $subject, $replace, $position, strlen( $search ) );
		}

		return $subject;
	}

	/**
	 * Replaces the last occurrence of a given value in the string.
	 *
	 * @since 4.6.0
	 *
	 * @param string $search  The string to search for and replace.
	 * @param string $replace The replacement string.
	 * @param string $subject The string to do the search and replace from.
	 *
	 * @return string The string with the last occurrence of a given value replaced.
	 */
	public static function replace_last( string $search, string $replace, string $subject ): string {
		if ( '' === $search ) {
			return $subject;
		}

		$position = strrpos( $subject, $search );

		if ( $position !== false ) {
			return substr_replace( $subject, $replace, $position, strlen( $search ) );
		}

		return $subject;
	}

	/**
	 * Determines if a given string starts with a given substring.
	 * Supports multiple needles.
	 *
	 * @since 4.6.0
	 *
	 * @param string          $haystack Haystack.
	 * @param string|string[] $needles  Needle can be a string or an array of strings.
	 *
	 * @return bool
	 */
	public static function starts_with( string $haystack, $needles ): bool {
		if ( ! is_array( $needles ) ) {
			$needles = array( $needles );
		}

		foreach ( $needles as $needle ) {
			if ( $needle !== '' && 0 === mb_strpos( $haystack, $needle ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if a given string contains a given substring.
	 * Supports multiple needles.
	 *
	 * @since 4.6.0
	 *
	 * @param string          $haystack    The string to search in.
	 * @param string|string[] $needles     The string to search for.
	 * @param bool            $ignore_case Whether to ignore case. Default false.
	 *
	 * @return bool
	 */
	public static function contains( string $haystack, $needles, bool $ignore_case = false ): bool {
		if ( $ignore_case ) {
			$haystack = mb_strtolower( $haystack );
		}

		if ( ! is_array( $needles ) ) {
			$needles = (array) $needles;
		}

		foreach ( $needles as $needle ) {
			if ( $ignore_case ) {
				$needle = mb_strtolower( $needle );
			}

			if ( $needle !== '' && false !== mb_strpos( $haystack, $needle ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a given string contains all needles.
	 *
	 * @since 4.6.0
	 *
	 * @param string   $haystack    The string to search in.
	 * @param string[] $needles     The string to search for.
	 * @param bool     $ignore_case Whether to ignore case. Default false.
	 *
	 * @return bool
	 */
	public static function contains_all( string $haystack, array $needles, bool $ignore_case = false ): bool {
		foreach ( $needles as $needle ) {
			if ( ! static::contains( $haystack, $needle, $ignore_case ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Masks a portion of a string with a repeated character.
	 *
	 * @since 4.6.0
	 *
	 * @param  string   $string    The input string.
	 * @param  string   $character The character to use for masking.
	 * @param  int      $index     The index of the first character to mask.
	 * @param  int|null $length    The number of characters to mask. If null, masks until the end of the string.
	 * @param  string   $encoding  The encoding of the string. Default UTF-8.
	 *
	 * @return string
	 */
	public static function mask( $string, $character, $index, $length = null, $encoding = 'UTF-8' ) {
		if ( $character === '' ) {
			return $string;
		}

		$segment = mb_substr( $string, $index, $length, $encoding );

		if ( $segment === '' ) {
			return $string;
		}

		$strlen      = mb_strlen( $string, $encoding );
		$start_index = $index;

		if ( $index < 0 ) {
			$start_index = $index < -$strlen ? 0 : $strlen + $index;
		}

		$start          = mb_substr( $string, 0, $start_index, $encoding );
		$segment_length = mb_strlen( $segment, $encoding );
		$end            = mb_substr( $string, $start_index + $segment_length );

		return $start . str_repeat( mb_substr( $character, 0, 1, $encoding ), intval( $segment_length ) ) . $end;
	}

	/**
	 * Gets the portion of a string before the first occurrence of a given value.
	 *
	 * @since 4.8.0
	 *
	 * @param string $subject Subject string.
	 * @param string $search  String to search.
	 *
	 * @return string
	 */
	public static function before( string $subject, string $search ): string {
		if ( $search === '' ) {
			return $subject;
		}

		$result = strstr( $subject, $search, true );

		return $result === false ? $subject : $result;
	}

	/**
	 * Determines if a given string exactly matches one or multiple strings.
	 *
	 * @since 4.21.5
	 *
	 * @param string          $string      The string to check.
	 * @param string|string[] $matches     The string(s) to match against.
	 * @param bool            $ignore_case Whether to ignore case. Default false.
	 *
	 * @return bool Returns true if the string exactly matches any of the provided strings.
	 */
	public static function matches( string $string, $matches, bool $ignore_case = false ): bool {
		if ( ! is_array( $matches ) ) {
			$matches = array( $matches );
		}

		foreach ( $matches as $match ) {
			if ( $ignore_case ) {
				if ( strcasecmp( $string, $match ) === 0 ) {
					return true;
				}
			} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found -- For better clarity.
				if ( $string === $match ) {
					return true;
				}
			}
		}

		return false;
	}
}
