<?php
/**
 * Earn badges or points count trigger class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Triggers;

use LearnDash\Achievements\Achievement;
use LearnDash\Achievements\Database;
use LearnDash\Core\App;
use LearnDash\Core\Utilities\Cast;
use WP_Post;

/**
 * Earn badges or points count trigger class.
 *
 * @since 2.0.0
 */
class Earn_Badges_Points_Count extends Trigger {
	use Traits\LearnDash;

	/**
	 * Gets trigger key.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_key(): string {
		return 'earn_badges_points_count';
	}

	/**
	 * Gets trigger label.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'User has earned X badges or points', 'learndash-achievements' );
	}

	/**
	 * Registers hooks.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action(
			'learndash_achievements_after_create_achievement',
			App::container()->callback(
				__CLASS__,
				'handle_on_achievement_award'
			),
			10,
			9
		);

		add_filter(
			'learndash_achievements_trigger_action_is_valid',
			App::container()->callback(
				__CLASS__,
				'validate_trigger_action'
			),
			10,
			10
		);
	}

	/**
	 * Handles user achievements logic when user logs in.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $trigger         Trigger.
	 * @param int     $user_id         User ID.
	 * @param int     $trigger_post_id Trigger post ID.
	 * @param int     $group_id        Group ID.
	 * @param int     $course_id       Course ID.
	 * @param int     $lesson_id       Lesson ID.
	 * @param int     $topic_id        Topic ID.
	 * @param int     $quiz_id         Quiz ID.
	 * @param WP_Post $template        Template.
	 *
	 * @return void
	 */
	public function handle_on_achievement_award( $trigger, $user_id, $trigger_post_id, $group_id, $course_id, $lesson_id, $topic_id, $quiz_id, $template ): void {
		// Don't award achievement if it's triggered from this own trigger.
		if ( $trigger === $this->get_key() ) {
			return;
		}

		Achievement::create_new( $this->get_key(), $user_id );
	}

	/**
	 * Validates trigger action.
	 *
	 * @since 2.0.0
	 *
	 * @param bool    $is_valid        Whether trigger action is valid.
	 * @param string  $trigger         Trigger.
	 * @param int     $user_id         User ID.
	 * @param int     $trigger_post_id Trigger post ID.
	 * @param int     $group_id        Group ID.
	 * @param int     $course_id       Course ID.
	 * @param int     $lesson_id       Lesson ID.
	 * @param int     $topic_id        Topic ID.
	 * @param int     $quiz_id         Quiz ID.
	 * @param WP_Post $template        Template.
	 *
	 * @return bool
	 */
	public function validate_trigger_action( $is_valid, $trigger, $user_id, $trigger_post_id, $group_id, $course_id, $lesson_id, $topic_id, $quiz_id, $template ) {
		if ( $trigger !== $this->get_key() ) {
			return $is_valid;
		}

		// Badges count can have empty string or number.
		$badges_number_required = get_post_meta( $template->ID, 'trigger_badges_count', true );
		$badges_number_required = $badges_number_required !== '' ? Cast::to_int( $badges_number_required ) : false;

		if ( $badges_number_required !== false ) {
			return count( Database::get_user_achievements( $user_id ) ) >= $badges_number_required;
		}

		// Points count can have empty string or number.
		$points_number_required = get_post_meta( $template->ID, 'trigger_points_count', true );
		$points_number_required = $points_number_required !== '' ? Cast::to_int( $points_number_required ) : false;

		if ( $points_number_required !== false ) {
			return Database::get_user_points( $user_id ) >= $points_number_required;
		}

		return $is_valid;
	}
}
