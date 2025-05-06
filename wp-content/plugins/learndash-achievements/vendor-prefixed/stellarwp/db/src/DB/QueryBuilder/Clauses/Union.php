<?php
/**
 * @license GPL-2.0
 *
 * Modified by learndash on 14-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace LearnDash\Achievements\StellarWP\DB\QueryBuilder\Clauses;

use LearnDash\Achievements\StellarWP\DB\QueryBuilder\QueryBuilder;

/**
 * @since 1.0.0
 */
class Union {
	/**
	 * @var QueryBuilder
	 */
	public $builder;

	/**
	 * @var bool
	 */
	public $all = false;

	/**
	 * @param  QueryBuilder  $builder
	 * @param  bool  $all
	 */
	public function __construct( $builder, $all = false ) {
		$this->builder = $builder;
		$this->all     = $all;
	}
}
