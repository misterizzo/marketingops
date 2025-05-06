<?php
/**
 * This class provides the easy way to operate a group.
 *
 * @since 4.6.0
 *
 * @package LearnDash\Core
 */

/** NOTICE: This code is currently under development and may not be stable.
 *  Its functionality, behavior, and interfaces may change at any time without notice.
 *  Please refrain from using it in production or other critical systems.
 *  By using this code, you assume all risks and liabilities associated with its use.
 *  Thank you for your understanding and cooperation.
 **/

namespace LearnDash\Core\Models;

use LDLMS_Post_Types;
use LearnDash\Core\Models\Traits\Has_Materials;

/**
 * Group model class.
 *
 * @since 4.6.0
 */
class Group extends Post {
	use Has_Materials;

	/**
	 * Returns allowed post types.
	 *
	 * @since 4.6.0
	 *
	 * @return string[]
	 */
	public static function get_allowed_post_types(): array {
		return array(
			LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::GROUP ),
		);
	}

	/**
	 * Returns a product model based on the group.
	 *
	 * @since 4.6.0
	 *
	 * @return Product
	 */
	public function get_product(): Product {
		/**
		 * Filters a group product.
		 *
		 * @since 4.6.0
		 *
		 * @param Product $product Product model.
		 * @param Group   $group   Group model.
		 *
		 * @ignore
		 */
		return apply_filters(
			'learndash_model_group_product',
			Product::create_from_post( $this->get_post() ),
			$this
		);
	}

	/**
	 * Returns related courses models.
	 *
	 * @since 4.6.0
	 *
	 * @param int $limit  Optional. Limit. Default is 0 which will be changed with LD settings.
	 * @param int $offset Optional. Offset. Default 0.
	 *
	 * @return Course[]
	 */
	public function get_courses( int $limit = 0, int $offset = 0 ): array {
		$query_args = [
			'offset' => $offset,
		];

		if ( $limit !== 0 ) {
			$query_args['per_page'] = $limit;
		}

		/**
		 * Filters group courses.
		 *
		 * @since 4.6.0
		 *
		 * @param Course[] $courses Courses.
		 * @param Group    $group   Group model.
		 *
		 * @ignore
		 */
		return apply_filters(
			'learndash_model_group_courses',
			Course::find_many(
				learndash_get_group_courses_list( $this->get_id(), $query_args )
			),
			$this
		);
	}

	/**
	 * Returns the total number of related courses.
	 *
	 * @since 4.6.0
	 *
	 * @return int
	 */
	public function get_courses_number(): int {
		/**
		 * Filters group courses number.
		 *
		 * @since 4.6.0
		 *
		 * @param int   $number Number of courses.
		 * @param Group $group  Group model.
		 *
		 * @ignore
		 */
		return apply_filters(
			'learndash_model_group_courses_number',
			count(
				learndash_group_enrolled_courses( $this->get_id() )
			),
			$this
		);
	}
}
