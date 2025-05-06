<?php
/**
 * @license GPL-2.0
 *
 * Modified by learndash on 14-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace LearnDash\Achievements\StellarWP\DB\QueryBuilder;

use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\Aggregate;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\CRUD;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\FromClause;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\GroupByStatement;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\HavingClause;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\JoinClause;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\LimitStatement;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\MetaQuery;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\OffsetStatement;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\OrderByStatement;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\SelectStatement;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\TablePrefix;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\UnionOperator;
use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\WhereClause;

/**
 * @since 1.0.0
 */
class QueryBuilder {
	use Aggregate;
	use CRUD;
	use FromClause;
	use GroupByStatement;
	use HavingClause;
	use JoinClause;
	use LimitStatement;
	use MetaQuery;
	use OffsetStatement;
	use OrderByStatement;
	use SelectStatement;
	use TablePrefix;
	use UnionOperator;
	use WhereClause;

	/**
	 * @return string
	 */
	public function getSQL() {
		$sql = array_merge(
			$this->getSelectSQL(),
			$this->getFromSQL(),
			$this->getJoinSQL(),
			$this->getWhereSQL(),
			$this->getGroupBySQL(),
			$this->getHavingSQL(),
			$this->getOrderBySQL(),
			$this->getLimitSQL(),
			$this->getOffsetSQL(),
			$this->getUnionSQL()
		);

		// Trim double spaces added by DB::prepare
		return str_replace(
			[ '   ', '  ' ],
			' ',
			implode( ' ', $sql )
		);
	}
}
