<?php
/**
 * @license GPL-2.0
 *
 * Modified by learndash on 14-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace LearnDash\Achievements\StellarWP\DB\QueryBuilder\Clauses;

/**
 * @since 1.0.0
 */
class Select {
	/**
	 * @var string
	 */
	public $column;

	/**
	 * @var string
	 */
	public $alias;

	/**
	 * @param  string  $column
	 * @param  string|null  $alias
	 */
	public function __construct( $column, $alias = '' ) {
		$this->column = trim( $column );
		$this->alias  = is_scalar( $alias ) ? trim( (string) $alias ) : '';
	}
}
