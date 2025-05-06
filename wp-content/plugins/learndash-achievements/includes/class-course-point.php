<?php
/**
 * Hook into LD content for output a button, allow user to redeem the course if needed.
 *
 * @since 1.1.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements;

use WP_Post;
use WP_Screen;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Achievements Course_Point class.
 *
 * @since 1.1.0
 */
class Course_Point {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'learndash_header_data', [ $this, 'add_achievement_setting_tab' ], 11, 3 );
	}

	/**
	 * Show price in infobar.
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.2
	 *
	 * @param string $post_type Custom post type value.
	 * @param int    $course_id ID of the current course.
	 * @param int    $user_id   ID of the current user.
	 *
	 * @return void
	 */
	public function show_price( $post_type, $course_id, $user_id ): void {
		_deprecated_function( __METHOD__, '2.0.2' );

		if ( learndash_get_post_type_slug( 'course' ) !== $post_type ) {
			return;
		}

		if ( ! $this->is_enabled( $course_id ) ) {
			return;
		}

		$price            = learndash_get_setting( $course_id, 'achievements_buy_course_course_price' );
		$use_inline_style = apply_filters( 'learndash_achievements_buy_course_inline_style', true );
		?>
		<?php if ( $use_inline_style ) : ?>
			<style>
				.achievement-course-price-points {
					position: relative;
					top: -10px;
					font-size: 1rem;
					line-height: 1rem;
				}
			</style>
		<?php endif; ?>
		<span class="achievement-course-price-points">
		<?php
		if ( $this->is_points_enough( $course_id ) ) {
			echo esc_html(
				sprintf(
					// translators: placeholders: Or use $d point, Or use %d points.
					_n(
						'Or use %d point',
						'Or use %d points',
						absint( $price ),
						'learndash-achievements'
					),
					absint( $price )
				)
			);
		}
		?>
	</span>
		<?php
	}

	/**
	 * Process the request.
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.2
	 *
	 * @return void
	 */
	public function process(): void {
		_deprecated_function( __METHOD__, '2.0.2' );

		if (
			! isset( $_POST['_achievements_nonce'] )
			|| ! isset( $_POST['course_id'] )
		) {
			return;
		}

		$nonce   = sanitize_text_field( wp_unslash( $_POST['_achievements_nonce'] ) );
		$post_id = absint( wp_unslash( $_POST['course_id'] ) );

		if ( ! wp_verify_nonce( $nonce, 'achievements_redeem_' . $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! is_object( $post ) ) {
			return;
		}

		if ( ! $this->is_enabled( $post_id ) ) {
			return;
		}

		if ( ! $this->is_points_enough( $post_id ) ) {
			return;
		}

		ld_update_course_access( get_current_user_id(), $post_id );
		$used_points = get_user_meta( get_current_user_id(), 'achievements_points_used', true );

		if ( empty( $used_points ) ) {
			$used_points = 0;
		}

		$price        = learndash_get_setting( $post_id, 'achievements_buy_course_course_price' );
		$price        = absint( $price );
		$used_points  = absint( $used_points );
		$used_points += $price;
		update_user_meta( get_current_user_id(), 'achievements_points_used', $used_points );

		// Record transactions.

		$post_id = wp_insert_post(
			[
				'post_title'  => get_the_title( $post_id ),
				'post_type'   => 'sfwd-transactions',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
			]
		);

		$arr = [
			'course_id' => $post_id,
			'user_id'   => get_current_user_id(),
		];

		wp_safe_redirect( get_permalink( $post ) );
		exit();
	}

	/**
	 * Need to support for Paypal and Stripe if they already activated.
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.2
	 *
	 * @param array<string, string>          $buttons Payment buttons.
	 * @param WP_Post                        $post    Current course post object.
	 * @param array{type: string, price:int} $params  Default payment params.
	 *
	 * @return array<string, string> Returned payment buttons.
	 */
	public function add_redeem_button( $buttons, WP_Post $post, array $params ) {
		_deprecated_function( __METHOD__, '2.0.2' );

		if (
			! $this->is_enabled( $post->ID )
			|| ! $this->is_points_enough( $post->ID )
		) {
			return $buttons;
		}

		/**
		 * Filter the registration variation.
		 *
		 * This is a reverse filter that learndash core hooks to in order to avoid the need for verifying if a function and class exists.
		 *
		 * @since 2.0.1
		 *
		 * @param string $variation
		 */
		$registration_variation = apply_filters( 'learndash_registration_variation', 'classic' );

		if ( 'classic' !== $registration_variation ) {
			$gateway = new Modules\Payments\Gateway( $post );

			add_filter(
				'learndash_payment_option_active_gateways',
				static function ( $active_gateways ) use ( $gateway ) {
					$active_gateways['achievements-points'] = $gateway;

					return $active_gateways;
				}
			);

			return $gateway->add_payment_button( $buttons, $post, $params );
		}

		// get the user points.
		$current_points = Database::get_user_points( get_current_user_id() );
		$price          = learndash_get_setting( $post->ID, 'achievements_buy_course_course_price' );
		// can show up the button here.
		$button_text = sprintf(
			// translators: 1$: point price, 2$: available points.
			esc_attr_x(
				'Use %1$s (Your points: %2$d)',
				'payment_button',
				'learndash-achievements'
			),
			sprintf(
				// translators: singular point plural points.
				_n(
					'%d point',
					'%d points',
					absint( $price ),
					'learndash-achievements'
				),
				$price
			),
			$current_points
		);
		ob_start();
		?>
		<div class="learndash_checkout_button learndash_achievements_points_button">
			<form method="post">
				<?php
				wp_nonce_field(
					'achievements_redeem_' . $post->ID,
					'_achievements_nonce'
				);
				?>
				<input type="hidden" name="course_id" value="<?php echo esc_attr( (string) $post->ID ); ?>"/>
				<input type="submit" value="<?php echo esc_attr( $button_text ); ?>" class="btn-join"/>
			</form>
		</div>
		<?php
		$strings                        = (string) ob_get_clean();
		$buttons['achievements-points'] = $strings;

		return $buttons;
	}

	/**
	 * Check if the user has enough earned points.
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.2
	 *
	 * @param int $post_id ID of the current course.
	 *
	 * @return bool
	 */
	protected function is_points_enough( $post_id ) {
		_deprecated_function( __METHOD__, '2.0.2' );

		$price = learndash_get_setting( $post_id, 'achievements_buy_course_course_price' );
		$price = absint( $price );
		// get the user points.
		$current_points = Database::get_user_points( get_current_user_id() );
		$current_points = absint( $current_points );

		return $current_points >= $price;
	}

	/**
	 * Check if points purchasing is enabled for course.
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.2
	 *
	 * @param int $post_id ID of the current course.
	 *
	 * @return bool
	 */
	protected function is_enabled( $post_id ) {
		_deprecated_function( __METHOD__, '2.0.2' );

		$course_pricing = learndash_get_course_price( $post_id );

		if ( 'paynow' !== $course_pricing['type'] ) {
			return false;
		}

		$is_enabled = learndash_get_setting( $post_id, 'achievements_buy_course' );

		return '1' === $is_enabled;
	}

	/**
	 * Add a settings tab into course settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, array<int, array<string, mixed>>> $header_data  Header data.
	 * @param string                                          $menu_tab_key Page URL.
	 * @param array<int, array<string, string>>               $course_data Course information.
	 *
	 * @return array<string, array<int, array<string, mixed>>> Header data.
	 */
	public function add_achievement_setting_tab( $header_data, string $menu_tab_key, array $course_data ) {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return $header_data;
		}

		if (
			$screen->id !== 'sfwd-courses'
			|| $screen->base !== 'post'
			|| 'edit.php?post_type=sfwd-courses' !== $menu_tab_key
		) {
			return $header_data;
		}

		$header_data['tabs'][] = [
			'id'        => 'learndash_course_achievements',
			'name'      => __( 'Achievements', 'learndash-achievements' ),
			'metaboxes' => [
				'learndash-course-achievements-settings',
			],
		];

		return $header_data;
	}
}

new Course_Point();
