<?php
/**
 * @license GPL-2.0
 *
 * Modified by learndash on 14-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace LearnDash\Achievements\StellarWP\DB\QueryBuilder\Clauses;

use LearnDash\Achievements\StellarWP\DB\DB;

/**
 * @since 1.0.0
 */
class RawSQL {
	/**
	 * @var string
	 */
	public $sql;

	/**
	 * @param  string  $sql
	 * @param  array<int,mixed>|string|null  $args
	 */
	public function __construct( $sql, $args = null ) {
		$this->sql = $args ? DB::prepare( $sql, $args ) : $sql;
	}
}
