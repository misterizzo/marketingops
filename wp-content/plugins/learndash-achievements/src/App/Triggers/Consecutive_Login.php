<?php
/**
 * Consecutive login trigger class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Triggers;

use LearnDash\Achievements\Achievement;
use LearnDash\Core\App;
use LearnDash\Core\Utilities\Cast;
use WP_Post;
use WP_User;

/**
 * Consecutive login trigger class.
 *
 * Trigger logic description:
 * - When user logs in, check if the user logged in yesterday.
 * - If yes, increment user consecutive days login count.
 * - If no, reset user consecutive days login count to 0.
 * - If user log in again the next day, it will be considered as the day #1 of consecutive login.
 * - If user consecutive days login count is equal or less than the setting, trigger the achievement.
 *
 * @since 2.0.0
 */
class Consecutive_Login extends Trigger {
	use Traits\WordPress;

	/**
	 * User meta key for consecutive days login.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private const USER_META_KEY_CONSECUTIVE_DAYS_LOGIN = 'learndash_consecutive_days_login';

	/**
	 * Gets trigger key.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_key(): string {
		return 'consecutive_login';
	}

	/**
	 * Gets trigger label.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'User has logged in for X consecutive days', 'learndash-achievements' );
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
			'set_logged_in_cookie',
			App::container()->callback(
				__CLASS__,
				'handle_on_logged_in_cookie'
			),
			10,
			6
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
	 * Handles the achievement trigger on user logged in cookie setter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $logged_in_cookie The logged-in cookie value.
	 * @param int    $expire           The time the login grace period expires as a UNIX timestamp. Default is 12 hours past the cookie's expiration time.
	 * @param int    $expiration       The time when the logged-in authentication cookie expires as a UNIX timestamp. Default is 14 days from now.
	 * @param int    $user_id          User ID.
	 * @param string $scheme           Authentication scheme. Default 'logged_in'.
	 * @param string $token            User's session token to use for this cookie.
	 *
	 * @return void
	 */
	public function handle_on_logged_in_cookie( $logged_in_cookie, $expire, $expiration, $user_id, $scheme, $token ): void {
		$user = get_user_by( 'id', $user_id );

		if ( ! $user instanceof WP_User ) {
			return;
		}

		$this->handle_user_achievement( $user );
	}

	/**
	 * Validates trigger action.
	 *
	 * @since 2.0.0
	 *
	 * @param bool      $is_valid        Whether trigger action is valid.
	 * @param string    $trigger         Trigger.
	 * @param int       $user_id         User ID.
	 * @param int|false $trigger_post_id Trigger post ID.
	 * @param ?int      $group_id        The group ID.
	 * @param ?int      $course_id       The course ID.
	 * @param ?int      $lesson_id       The lesson ID.
	 * @param ?int      $topic_id        The topic ID.
	 * @param ?int      $quiz_id         The quiz ID.
	 * @param WP_Post   $template        The achievement trigger template post object.
	 *
	 * @return bool
	 */
	public function validate_trigger_action( $is_valid, $trigger, $user_id, $trigger_post_id, $group_id, $course_id, $lesson_id, $topic_id, $quiz_id, $template ) {
		if ( $trigger !== $this->get_key() ) {
			return $is_valid;
		}

		$consecutive_days_setting = Cast::to_int(
			get_post_meta( $template->ID, 'days', true )
		);

		if ( $consecutive_days_setting === 0 ) { // "0" means no limit.
			return true;
		}

		$consecutive_days_user = Cast::to_int(
			get_user_meta( $user_id, self::USER_META_KEY_CONSECUTIVE_DAYS_LOGIN, true )
		);

		return $consecutive_days_user <= $consecutive_days_setting;
	}

	/**
	 * Handler the achievement trigger.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $user User object.
	 *
	 * @return void
	 */
	private function handle_user_achievement( WP_User $user ): void {
		$user_last_login = Cast::to_int(
			get_user_meta( $user->ID, 'learndash-last-login', true )
		);

		// First time login.
		if ( $user_last_login === 0 ) {
			update_user_meta( $user->ID, 'learndash-last-login', time() );
		}

		$user_last_login_date = gmdate( 'Y-m-d', $user_last_login );

		if (
			$user_last_login === 0 // First time login.
			|| $user_last_login_date !== gmdate( 'Y-m-d', strtotime( 'yesterday' ) )
		) {
			// Avoid resetting user consecutive days login count to 0 if the user last login is today in case user log in multiple times in a day.
			if ( $user_last_login_date !== gmdate( 'Y-m-d' ) ) {
				update_user_meta( $user->ID, self::USER_META_KEY_CONSECUTIVE_DAYS_LOGIN, 0 );
			}

			return;
		}

		// Increment user consecutive days login count.
		$consecutive_days = Cast::to_int(
			get_user_meta( $user->ID, self::USER_META_KEY_CONSECUTIVE_DAYS_LOGIN, true )
		);
		++$consecutive_days;

		update_user_meta( $user->ID, self::USER_META_KEY_CONSECUTIVE_DAYS_LOGIN, $consecutive_days );

		Achievement::create_new( $this->get_key(), $user->ID );
	}
}
