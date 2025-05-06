<?php
/**
 * User has completed X courses/groups trigger class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Triggers;

use LDLMS_Post_Types;
use LearnDash\Achievements\Achievement;
use LearnDash\Achievements\StellarWP\DB\DB;
use LearnDash\Core\App;
use LearnDash\Core\Utilities\Cast;
use WP_Post;
use WP_User;

/**
 * User has completed X courses/groups trigger class.
 *
 * @since 2.0.0
 */
class Complete_Courses_Groups_Count extends Trigger {
	use Traits\LearnDash;

	/**
	 * Gets trigger key.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_key(): string {
		return 'complete_courses_groups_count';
	}

	/**
	 * Gets trigger label.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label(): string {
		return sprintf(
			// Translators: %1$s is replaced with "courses" and %2$s is replaced with "groups".
			__( 'User has completed X %1$s or %2$s', 'learndash-achievements' ),
			learndash_get_custom_label_lower( 'courses' ),
			learndash_get_custom_label_lower( 'groups' )
		);
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
			'learndash_course_completed',
			App::container()->callback(
				__CLASS__,
				'handle_on_object_completed'
			),
			100,
			1
		);

		add_action(
			'learndash_group_completed',
			App::container()->callback(
				__CLASS__,
				'handle_on_object_completed'
			),
			100,
			1
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
	 * Handles achievement awarding logic when a course or group is completed.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Course or group completion data.
	 *
	 * @phpstan-param array{
	 *    'user': WP_User,
	 *    'course'?: WP_Post,
	 *    'group'?: WP_Post,
	 *    'progress': array<string, mixed>,
	 *    'course_completed'?: int,
	 *    'group_completed'?: int
	 *  } $data Course or group completion data.
	 *
	 * @return void
	 */
	public function handle_on_object_completed( $data ): void {
		// Course parameter only exists if a course is completed and group parameter only exists if a group is completed.
		$post_id = isset( $data['course'] )
			? $data['course']->ID
			: (
				isset( $data['group'] )
					? $data['group']->ID
					: null
			);

		if ( ! $post_id ) {
			return;
		}

		Achievement::create_new(
			$this->get_key(),
			$data['user']->ID,
			$post_id
		);
	}

	/**
	 * Validates trigger action.
	 *
	 * @since 2.0.0
	 *
	 * @param bool      $is_valid        Whether trigger action is valid.
	 * @param string    $trigger         Trigger.
	 * @param int       $user_id         User ID.
	 * @param int|false $trigger_post_id The trigger post ID.
	 * @param ?int      $group_id        The group ID.
	 * @param ?int      $course_id       The course ID.
	 * @param ?int      $lesson_id       The lesson ID.
	 * @param ?int      $topic_id        The topic ID.
	 * @param ?int      $quiz_id         The quiz ID.
	 * @param WP_Post   $template        The trigger template WP_Post.
	 *
	 * @return bool
	 */
	public function validate_trigger_action( $is_valid, $trigger, $user_id, $trigger_post_id, $group_id, $course_id, $lesson_id, $topic_id, $quiz_id, $template ) {
		if (
			$trigger !== $this->get_key()
			|| ! $trigger_post_id
		) {
			return $is_valid;
		}

		$post_type = get_post_type( $trigger_post_id );

		// Allow users to disable the setting by leaving the field empty.
		$courses_count_setting = get_post_meta( $template->ID, 'courses_count', true );
		$courses_count_setting = $courses_count_setting !== '' ? Cast::to_int( $courses_count_setting ) : false;

		if (
			$post_type === learndash_get_post_type_slug( LDLMS_Post_Types::COURSE )
			&& $courses_count_setting !== false
		) {
			$completed_courses_count = DB::table( 'learndash_user_activity' )
										->where( 'user_id', $user_id )
										->where( 'activity_type', 'course' )
										->where( 'activity_status', 1 )
										->count();

			return $completed_courses_count === $courses_count_setting;
		}

		// Allow users to disable the setting by leaving the field empty.
		$groups_count_setting = get_post_meta( $template->ID, 'groups_count', true );
		$groups_count_setting = $groups_count_setting !== '' ? Cast::to_int( $groups_count_setting ) : false;

		if (
			$post_type === learndash_get_post_type_slug( LDLMS_Post_Types::GROUP )
			&& $groups_count_setting !== false
		) {
			$completed_groups_count = DB::table( 'learndash_user_activity' )
										->where( 'user_id', $user_id )
										->where( 'activity_type', 'group_progress' )
										->where( 'activity_status', 1 )
										->count();

			return $completed_groups_count === $groups_count_setting;
		}

		return false;
	}
}
