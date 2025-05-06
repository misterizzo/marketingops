<?php
/**
 * Privacy tools class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Modules\Privacy_Tools;

use LearnDash\Achievements\Achievement;
use WP_User;

/**
 * Privacy tools class.
 *
 * @since 2.0.0
 */
class Controller {
	/**
	 * Registers data exporters.
	 *
	 * @since 2.0.0
	 *
	 * @param array<string, array<string, mixed>> $exporters Existing exporters.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function register_exporters( $exporters ): array {
		$exporters['learndash-achievements'] = [
			'exporter_friendly_name' => __( 'LearnDash LMS - Achievements', 'learndash-achievements' ),
			'callback'               => [ $this, 'export_data' ],
		];

		return $exporters;
	}

	/**
	 * Registers data erasers.
	 *
	 * @since 2.0.0
	 *
	 * @param array<string, array<string, mixed>> $erasers Existing exporters.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function register_erasers( $erasers ): array {
		$erasers['learndash-achievements'] = [
			'eraser_friendly_name' => 'LearnDash LMS - Achievements',
			'callback'             => [ $this, 'erase_data' ],
		];

		return $erasers;
	}

	/**
	 * Exports user data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $email User email address used as key when exporting data.
	 * @param int    $page  Current page of the batch processing when getting the data.
	 *
	 * @return array<string, mixed>
	 */
	public function export_data( $email, $page = 1 ): array {
		$export_items = [];

		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof WP_User ) {
			return [
				'data' => [],
				'done' => true,
			];
		}

		$achievements = Achievement::get_raw_by_user_id(
			$user->ID,
			[
				'page' => $page,
			]
		);

		foreach ( $achievements as $achievement ) {
			$export_items[] = [
				'group_id'    => 'learndash-achievements',
				'group_label' => __( 'LearnDash LMS - Achievements', 'learndash-achievements' ),
				'item_id'     => 'ld-achievements-' . $achievement->id,
				'data'        => [
					[
						'name'  => __( 'Achievement Type', 'learndash-achievements' ),
						'value' => $achievement->trigger,
					],
					[
						'name'  => __( 'Points Earned', 'learndash-achievements' ),
						'value' => $achievement->points,
					],
					[
						'name'  => __( 'Date Earned', 'learndash-achievements' ),
						'value' => $achievement->created_at,
					],
				],
			];
		}

		return [
			'data' => $export_items,
			'done' => empty( $achievements ),
		];
	}

	/**
	 * Erases user data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $email User email address used as key when exporting data.
	 * @param int    $page  Current page of the batch processing when getting the data.
	 *
	 * @return array<string, mixed>
	 */
	public function erase_data( $email, $page ): array {
		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof WP_User ) {
			return [
				'items_removed'  => false,
				'items_retained' => true,
				'messages'       => [
					__( 'Failed to erase user LearnDash LMS - Achievements data. Can\'t retrieve a user with the provided email address.', 'learndash-achievements' ),
				],
				'done'           => true,
			];
		}

		$achievements = Achievement::get_raw_by_user_id(
			$user->ID,
			[
				'page' => $page,
			]
		);

		Achievement::delete(
			wp_list_pluck( $achievements, 'id' )
		);

		return [
			'items_removed'  => true,
			'items_retained' => false,
			'messages'       => empty( $achievements )
				? [
					__( 'The user LearnDash achievements have been deleted.', 'learndash-achievements' ),
				]
				: [],
			'done'           => empty( $achievements ),
		];
	}
}
