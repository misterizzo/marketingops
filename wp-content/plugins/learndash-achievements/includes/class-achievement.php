<?php
/**
 * Achievement class file.
 *
 * @since 1.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements;

use LearnDash\Achievements\Database;
use LearnDash\Achievements\Settings;
use LearnDash\Achievements\Utilities\Assets;
use LearnDash\Core\Utilities\Cast;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Achievement class.
 *
 * @since 1.0
 */
class Achievement {
	/**
	 * Settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, mixed>
	 */
	public static $settings;

	/**
	 * Temp data.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, mixed>
	 */
	public static $temp_data;

	/**
	 * Init the class.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function init() {
		/**
		 * Achievements popup settings.
		 *
		 * @var array<string, mixed>
		 */
		$popup_settings = get_option( 'learndash_achievements_settings_popup', Settings::get_default_value() );
		self::$settings = $popup_settings;

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );

		add_action( 'admin_head', array( __CLASS__, 'custom_css' ) );
		add_action( 'wp_head', array( __CLASS__, 'custom_css' ) );

		// AJAX.
		add_action( 'wp_ajax_ld_achievement_delete_queue', array( __CLASS__, 'ajax_delete_notification_queue' ) );
		add_action(
			'wp_ajax_nopriv_ld_achievement_delete_queue',
			array( __CLASS__, 'ajax_delete_notification_queue' )
		);

		add_action( 'wp_ajax_learndash_achievements_remove_user_badge', array( __CLASS__, 'remove_badge_from_user' ) );
		add_action(
			'wp_ajax_learndash_achievements_get_user_achievements',
			array(
				__CLASS__,
				'get_user_achievements',
			)
		);
		add_action( 'delete_post', array( __CLASS__, 'clear_badge_when_achievement_deleted' ), 10, 2 );
	}

	/**
	 * Gets user achievements by user ID.
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array<mixed>
	 */
	public static function get_by_user_id( int $user_id ): array {
		return Database::get_user_achievements( $user_id );
	}

	/**
	 * Get user raw achievements by user ID.
	 *
	 * Raw achievements are achievements that are not filtered by any kind of filters.
	 *
	 * @since 2.0.0
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $args    Query arguments.
	 *
	 * @return array<object{
	 *  id: int,
	 *  user_id: int,
	 *  post_id: int,
	 *  trigger: string,
	 *  points: int,
	 *  created_at: string
	 * }>
	 */
	public static function get_raw_by_user_id( int $user_id, array $args ): array {
		$args = wp_parse_args(
			$args,
			[
				'limit' => 100,
				'page'  => 1,
			]
		);

		$offset = ( $args['page'] - 1 ) * $args['limit'];

		return Database::get_raw_user_achievements(
			$user_id,
			[
				'limit'  => $args['limit'],
				'offset' => $offset,
			]
		);
	}

	/**
	 * Delete achievements from database.
	 *
	 * @since 2.0.0
	 *
	 * @param array<int> $ids Achievement IDs or objects.
	 *
	 * @return void
	 */
	public static function delete( array $ids ): void {
		/**
		 * Action filter hook before deleting achievements.
		 *
		 * @since 2.0.0
		 *
		 * @param array<int> $ids Achievement IDs.
		 */
		do_action( 'learndash_achievements_before_delete_achievements', $ids );

		Database::delete_badges( $ids );

		/**
		 * Action filter hook after deleting achievements.
		 *
		 * @since 2.0.0
		 *
		 * @param array<int> $ids Achievement IDs.
		 */
		do_action( 'learndash_achievements_after_delete_achievements', $ids );
	}

	/**
	 * And endpoint for fetching achievements.
	 */
	public static function get_user_achievements() {
		if ( ! is_user_logged_in() ) {
			// should not be here, but just in case.
			wp_send_json_error( array() );
		}

		$notifications = get_user_meta( get_current_user_id(), 'ld_achievements_notifications', true );
		$notifications = ! is_array( $notifications ) ? array() : $notifications;
		wp_send_json_success( $notifications );
	}

	/**
	 * When an achievement deleted, need to wipe the badges relate.
	 *
	 * @param int      $post_id The achievement id.
	 * @param \WP_Post $post    The Post instance.
	 */
	public static function clear_badge_when_achievement_deleted( $post_id, $post ) {
		if ( 'ld-achievement' === $post->post_type ) {
			Database::delete_badges_by_achievement_id( $post_id );
		}
	}

	/**
	 * Ajax endpoint for remove badge.
	 */
	public static function remove_badge_from_user() {
		$_POST = wp_unslash( $_POST );
		$ids   = isset( $_POST['id'] ) ? $_POST['id'] : '';
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : null;
		if ( ! wp_verify_nonce( $nonce, 'learndash_achievements_remove_user_badge_' . $ids ) ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) ) {
			$ids = explode( ',', $ids );
			foreach ( $ids as $id ) {
				$id = absint( $id );
				// remove the achievement id.
				Database::delete_badge( $id );
			}
			die;
		}
	}

	/**
	 * Load the assets
	 */
	public static function load_scripts() {
		wp_enqueue_script(
			'noty-script',
			LEARNDASH_ACHIEVEMENTS_PLUGIN_URL . 'lib/noty/noty.min.js',
			array(),
			LEARNDASH_ACHIEVEMENTS_VERSION,
			true
		);
		wp_enqueue_style(
			'noty-style',
			LEARNDASH_ACHIEVEMENTS_PLUGIN_URL . 'lib/noty/noty.css',
			array(),
			LEARNDASH_ACHIEVEMENTS_VERSION,
			'screen'
		);

		wp_enqueue_style(
			'ld-achievements-style',
			LEARNDASH_ACHIEVEMENTS_PLUGIN_URL . 'dist/css/styles' . learndash_min_asset() . '.css',
			array(),
			LEARNDASH_ACHIEVEMENTS_VERSION,
			'screen'
		);
		wp_enqueue_script(
			'ld-achievements-script',
			LEARNDASH_ACHIEVEMENTS_PLUGIN_URL . 'dist/js/scripts' . learndash_min_asset() . '.js',
			array( 'jquery' ),
			LEARNDASH_ACHIEVEMENTS_VERSION,
			true
		);

		if ( is_user_logged_in() ) {
			$user_id       = get_current_user_id();
			$notifications = get_user_meta( $user_id, 'ld_achievements_notifications', true );
			$notifications = empty( $notifications ) ? array() : $notifications;
			$notifications = json_encode( $notifications );
		} else {
			$notifications = json_encode( array() );
		}

		$user_id = get_current_user_id();

		wp_localize_script(
			'ld-achievements-script',
			'LD_Achievements_Data',
			array(
				'notifications' => $notifications,
				'settings'      => self::$settings,
				'user_id'       => $user_id,
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Outputs custom CSS.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function custom_css() { ?>

		<style type="text/css" media="screen">
			.noty_theme__learndash.noty_type__success {
			<?php
			if ( ! empty( self::$settings['background_color'] ) ) :
				?>
				background-color: <?php echo esc_attr( Cast::to_string( self::$settings['background_color'] ) ); ?>;
				border-bottom: 1px solid<?php echo esc_attr( Cast::to_string( self::$settings['background_color'] ) ); ?>;
			<?php endif; ?> <?php
			if ( ! empty( self::$settings['text_color'] ) ) :
				?>
				 color: <?php echo esc_attr( Cast::to_string( self::$settings['text_color'] ) ); ?>;
			<?php endif; ?>
			}
		</style>

		<?php
	}

	/**
	 * Creates a new achievement.
	 *
	 * @since 1.1.0
	 *
	 * @param string    $trigger         The trigger key.
	 * @param int       $user_id         The user ID.
	 * @param int|false $trigger_post_id The trigger post ID.
	 * @param ?int      $group_id        The group ID.
	 * @param ?int      $course_id       The course ID.
	 * @param ?int      $lesson_id       The lesson ID.
	 * @param ?int      $topic_id        The topic ID.
	 * @param ?int      $quiz_id         The quiz ID.
	 *
	 * @return void
	 */
	public static function create_new(
			$trigger,
			$user_id,
			$trigger_post_id = false,
			$group_id = null,
			$course_id = null,
			$lesson_id = null,
			$topic_id = null,
			$quiz_id = null
	) {
		// Get achievement templates.
		$templates     = self::get_templates_by_trigger( $trigger );
		$notifications = array();

		foreach ( $templates as $template ) {
			/**
			 * Filters to check whether current trigger action is valid or not.
			 *
			 * @since 2.0.0
			 *
			 * @param bool      $is_valid        Whether current trigger action is valid or not. Default true.
			 * @param string    $trigger         The trigger.
			 * @param int       $user_id         The user ID.
			 * @param int|false $trigger_post_id The trigger post ID.
			 * @param ?int      $group_id        The group ID.
			 * @param ?int      $course_id       The course ID.
			 * @param ?int      $lesson_id       The lesson ID.
			 * @param ?int      $topic_id        The topic ID.
			 * @param ?int      $quiz_id         The quiz ID.
			 * @param WP_Post   $template        The achievement trigger template post object.
			 *
			 * @return bool Whether current trigger action is valid or not.
			 */
			$is_valid = apply_filters(
				'learndash_achievements_trigger_action_is_valid',
				true,
				$trigger,
				$user_id,
				$trigger_post_id,
				$group_id,
				$course_id,
				$lesson_id,
				$topic_id,
				$quiz_id,
				$template
			);

			if ( ! $is_valid ) {
				continue;
			}

			do_action(
				'ld_' . $trigger . '_achievement',
				$trigger,
				$user_id,
				$trigger_post_id,
				$group_id,
				$course_id,
				$lesson_id,
				$topic_id,
				$quiz_id
			);
			// Check trigger post ID.
			$temp_trigger_post_id = get_post_meta( $template->ID, 'trigger_post_id', true );
			// For backward compatibility for addon < v1.1
			// LD < v2.5 doesn't support nested courses.
			if ( ! empty( $temp_trigger_post_id ) ) {
				if ( $temp_trigger_post_id !== $trigger_post_id && false !== $trigger_post_id && 'all' != $temp_trigger_post_id ) {
					continue;
				}
			} else { // For LD >= v2.5 and addon >= v1.1
				// Exit if group ID setting doesn't match.
				$t_group_id = get_post_meta( $template->ID, 'group_id', true );
				if ( isset( $group_id ) && $group_id != $t_group_id && $t_group_id != 'all' && ! empty( $t_group_id ) ) {
					continue;
				}
				// Exit if course ID setting doesn't match.
				$t_course_id = get_post_meta( $template->ID, 'course_id', true );
				if ( isset( $course_id ) && $course_id != $t_course_id && $t_course_id != 'all' && ! empty( $t_course_id ) ) {
					continue;
				}
				// Exit if lesson ID setting doesn't match.
				$t_lesson_id = get_post_meta( $template->ID, 'lesson_id', true );
				if ( isset( $lesson_id ) && $lesson_id != $t_lesson_id && $t_lesson_id != 'all' && ! empty( $t_lesson_id ) ) {
					continue;
				}
				// Exit if topic ID setting doesn't match.
				$t_topic_id = get_post_meta( $template->ID, 'topic_id', true );
				if ( isset( $topic_id ) && $topic_id != $t_topic_id && $t_topic_id != 'all' && ! empty( $t_topic_id ) ) {
					continue;
				}
				// Exit if quiz ID setting doesn't match.
				$t_quiz_id = get_post_meta( $template->ID, 'quiz_id', true );
				if ( isset( $quiz_id ) && $quiz_id != $t_quiz_id && $t_quiz_id != 'all' && ! empty( $t_quiz_id ) ) {
					continue;
				}
			}

			if ( 'quiz_score_above' === $trigger ) {
				$percent = Cast::to_string(
					get_post_meta( $template->ID, 'percentage', true )
				);
				$percent = floatval( $percent );

				if ( ! is_array( self::$temp_data ) || ! isset( self::$temp_data['percentage'] ) ) {
					continue;
				}
				$passed_percentage = floatval(
					Cast::to_string( self::$temp_data['percentage'] )
				);
				if ( $passed_percentage < $percent ) {
					// do nothing.
					continue;
				}
			}

			$user_group_check = get_post_meta( $template->ID, 'user_group', true );
			if ( $user_group_check && ! learndash_is_user_in_group( $user_id, $user_group_check ) ) {
				// this rule is have user group check but the user not in that group.
				continue;
			}

			// Check number of occurrences.
			$occurrences = absint( get_post_meta( $template->ID, 'occurrences', true ) );

			// $current_occurrences = absint( get_post_meta( $template->ID, 'current_occurrences', true ) );
			$current_occurrences = absint( self::get_occurrences( $template->ID, $user_id ) );

			if ( $current_occurrences >= $occurrences && 0 != $occurrences ) {
				continue;
			}

			// Store in DB.
			self::store( $template, $user_id );

			do_action(
				'ld_' . $trigger . '_achievement_after_save',
				$trigger,
				$user_id,
				$trigger_post_id,
				$group_id,
				$course_id,
				$lesson_id,
				$topic_id,
				$quiz_id
			);

			/**
			 * Fires after an achievement is created and recorded.
			 *
			 * @since 2.0.0
			 *
			 * @param string    $trigger         The trigger key.
			 * @param int       $user_id         The user ID.
			 * @param int|false $trigger_post_id The trigger post ID.
			 * @param ?int      $group_id        The group ID.
			 * @param ?int      $course_id       The course ID.
			 * @param ?int      $lesson_id       The lesson ID.
			 * @param ?int      $topic_id        The topic ID.
			 * @param ?int      $quiz_id         The quiz ID.
			 * @param WP_Post   $template        The achievement trigger template post object.
			 */
			do_action(
				'learndash_achievements_after_create_achievement',
				$trigger,
				$user_id,
				$trigger_post_id,
				$group_id,
				$course_id,
				$lesson_id,
				$topic_id,
				$quiz_id,
				$template
			);

			$shortcode_params = array(
				'post_id' => $template->ID,
				'user_id' => $user_id,
			);

			$title   = self::parse_shortcode_params( $template->post_title, $shortcode_params );
			$message = self::parse_shortcode_params(
				get_post_meta( $template->ID, 'achievement_message', true ),
				$shortcode_params
			);

			// Notifications to be displayed.
			$display_content = apply_filters(
				'ld_' . $trigger . '_achievement_display_content',
				array(
					'title'   => do_shortcode( $title ),
					'message' => do_shortcode( $message ),
					'image'   => Assets::achievement_icon_url( $template->ID ),
				),
				$trigger,
				$user_id,
				$trigger_post_id,
				$group_id,
				$course_id,
				$lesson_id,
				$topic_id,
				$quiz_id
			);

			$notifications[] = $display_content;
		}

		$existing      = get_user_meta( $user_id, 'ld_achievements_notifications', true );
		$existing      = empty( $existing ) ? array() : $existing;
		$notifications = array_merge( $existing, $notifications );

		update_user_meta( $user_id, 'ld_achievements_notifications', $notifications );

		// $notifications = json_encode( $notifications );

		// set_transient( 'learndash_achievements_notifications', $notifications, $seconds = 20 );
	}

	public static function store( $template, $user_id ) {
		global $wpdb;

		$insert = $wpdb->insert(
			Database::$table_name,
			array(
				'user_id'    => $user_id,
				'post_id'    => $template->ID,
				'trigger'    => get_post_meta( $template->ID, 'trigger', true ),
				'points'     => get_post_meta( $template->ID, 'points', true ),
				'created_at' => date( 'Y-m-d H:i:s' ),
			),
			array(
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
			)
		);

		if ( $insert ) {
			return $wpdb->insert_id;
		} else {
			return false;
		}
	}

	public static function ajax_delete_notification_queue() {
		$user_id = sanitize_text_field( $_POST['user_id'] );
		delete_user_meta( $user_id, 'ld_achievements_notifications' );
	}

	public static function get_occurrences( $template_id, $user_id = null ) {
		global $wpdb;

		$table_name = Database::$table_name;
		if ( is_null( $user_id ) ) {
			$occurrences = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d",
					$template_id
				)
			);
		} else {
			$occurrences = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d AND user_id = %d",
					$template_id,
					$user_id
				)
			);
		}

		return $occurrences;
	}

	public static function get_templates_by_trigger( $trigger = '' ) {
		$key       = 'achievement_templates_' . $trigger;
		$group     = 'learndash_achievements';
		$templates = wp_cache_get( $key, $group );

		if ( false === $templates ) {
			$args = array(
				'post_type'      => 'ld-achievement',
				'meta_key'       => 'trigger',
				'meta_value'     => $trigger,
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			);

			$templates = get_posts( $args );

			wp_cache_set( $key, $templates, $group );
		}

		return $templates;
	}

	/**
	 * Gets icon URLs.
	 *
	 * @since 1.0
	 *
	 * @return array<string>
	 */
	public static function get_icons() {
		$icons = wp_cache_get( 'achievement_icons', 'learndash_achievements' );

		if ( $icons === false ) {
			$icons = glob( LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'dist/img/icons/*.png' );

			$icons_url = [];
			foreach ( $icons as $icon ) {
				$icon_url    = preg_replace(
					'/(.*)\/(dist\/.*\.png)/i',
					LEARNDASH_ACHIEVEMENTS_PLUGIN_URL . '${2}',
					$icon
				);
				$icons_url[] = $icon_url;
			}

			wp_cache_set( 'achievement_icons', $icons_url, 'learndash_achievements' );
		} else {
			$icons_url = $icons;
		}

		return apply_filters( 'learndash_achievements_icons', $icons_url );
	}

	/**
	 * Registers triggers.
	 *
	 * @since 1.0
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function get_triggers() {
		$triggers = [
			'WordPress' => [
				'register'     => __( 'User registration', 'learndash-achievements' ),
				'log_in'       => __( 'User logs in', 'learndash-achievements' ),
				'add_post'     => __( 'User adds a post', 'learndash-achievements' ),
				'add_comment'  => __( 'User adds a comment', 'learndash-achievements' ),
				'visit_post'   => __( 'User visits a post', 'learndash-achievements' ),
				'post_visited' => __( 'User\'s post gets visited', 'learndash-achievements' ),
			],
			'LearnDash' => [
				'enroll_group'        => __( 'User enrolls into a group', 'learndash-achievements' ),
				'enroll_course'       => __( 'User enrolls into a course', 'learndash-achievements' ),
				'complete_course'     => __( 'User completes a course', 'learndash-achievements' ),
				'complete_lesson'     => __( 'User completes a lesson', 'learndash-achievements' ),
				'complete_topic'      => __( 'User completes a topic', 'learndash-achievements' ),
				'pass_quiz'           => __( 'User passes a quiz', 'learndash-achievements' ),
				'fail_quiz'           => __( 'User fails a quiz', 'learndash-achievements' ),
				'complete_quiz'       => __( 'User completes a quiz', 'learndash-achievements' ),
				'quiz_score_above'    => __( 'Quiz score above %', 'learndash-achievements' ),
				'upload_assignment'   => __( 'User uploads assignment', 'learndash-achievements' ),
				'assignment_approved' => __( 'User\'s assignment is approved', 'learndash-achievements' ),
				'essay_graded'        => __( 'User\'s essay question has been graded', 'learndash-achievements' ),
			],
		];

		/**
		 * Filters the list of available triggers.
		 *
		 * @since 1.0
		 *
		 * @param array<string, array<string, string>> $triggers List of triggers.
		 *
		 * @return array<string, array<string, string>>
		 */
		return apply_filters( 'learndash_achievements_triggers', $triggers );
	}

	public static function parse_shortcode_params( $content, $params = array() ) {
		$content = preg_replace( '/(post_id|user_id)=".*?"/', '', $content );
		$content = preg_replace(
			'/\[ld_achievements/',
			'[ld_achievements post_id="' . $params['post_id'] . '" user_id="' . $params['user_id'] . '"',
			$content
		);

		return $content;
	}
}

Achievement::init();
