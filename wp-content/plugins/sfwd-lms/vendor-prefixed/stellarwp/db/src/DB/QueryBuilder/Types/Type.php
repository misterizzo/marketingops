<?php

namespace StellarWP\Learndash\StellarWP\DB\QueryBuilder\Types;

use ReflectionClass;

/**
 * @since 1.0.0
 */
abstract class Type {
	/**
	 * Get Defined Types
	 *
	 * @return array
	 */
	public static function getTypes() {
		return ( new ReflectionClass( static::class ) )->getConstants();
	}
}
