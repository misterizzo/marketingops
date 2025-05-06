<?php
/**
 * Payment gateway for Achievements.
 *
 * @since 2.0.1
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Modules\Payments;

use LDLMS_Post_Types;
use LearnDash\Core\Models\Course;
use LearnDash\Core\Models\Transaction;
use Learndash_DTO_Validation_Exception;
use Learndash_Pricing_DTO;
use Learndash_Transaction_Meta_DTO;
use LearnDash\Achievements\Database;
use LearnDash\Core\Models\Product;
use LearnDash\Core\Utilities\Cast;
use WP_Post;
use WP_Screen;
use WP_User;

/**
 * Payment gateway for Achievements.
 *
 * @since 2.0.1
 */
class Gateway extends \Learndash_Payment_Gateway {
	/**
	 * Gateway name.
	 *
	 * @var string
	 */
	private static string $gateway_name = 'achievements-points';

	/**
	 * Course or group post object.
	 *
	 * @var WP_Post|null
	 */
	private $post;

	/**
	 * Constructor.
	 *
	 * @since 2.0.1
	 * @since 2.0.2 Deprecate the post parameter.
	 *
	 * @param ?WP_Post $post Course or group post object.
	 */
	public function __construct( ?WP_Post $post = null ) {
		if ( ! empty( $post ) ) {
			_deprecated_argument( __METHOD__, '2.0.2', 'The post parameter is deprecated.' );
		}

		parent::__construct();
	}

	/**
	 * Returns the gateway name.
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return self::$gateway_name;
	}

	/**
	 * Returns the gateway label.
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public static function get_label(): string {
		return __( 'Achievement Points', 'learndash-achievements' );
	}


	/**
	 * Adds hooks from gateway classes.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function add_extra_hooks(): void {
		add_action( 'wp_loaded', [ $this, 'process_payment' ] );
		add_action(
			'learndash_template_after_include:modern/course/enrollment/pricing',
			[ $this, 'show_price_on_modern_course_page' ]
		);
		add_action( 'learndash-course-infobar-price-cell-after', [ $this, 'show_price' ], 10, 2 );
		add_filter( 'learndash_model_transaction_gateway_label', [ $this, 'add_orders_payment_details' ], 10, 2 );
		add_filter( 'learndash_model_transaction_formatted_price', [ $this, 'format_price' ], 10, 3 );
	}

	/**
	 * Enqueues scripts.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
	}

	/**
	 * Creates a session/order/subscription or prepares payment options on backend.
	 *
	 * @since 2.0.1
	 *
	 * @return void Json response.
	 */
	public function setup_payment(): void {
	}

	/**
	 * Configures gateway.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	protected function configure(): void {
	}

	/**
	 * Returns true if everything is configured and payment gateway can be used, otherwise false.
	 *
	 * @since 2.0.1
	 *
	 * @return bool
	 */
	public function is_ready(): bool {
		return true;
	}

	/**
	 * Returns true it's a test mode, otherwise false.
	 *
	 * @since 2.0.1
	 *
	 * @return bool
	 */
	protected function is_test_mode(): bool {
		return false;
	}

	/**
	 * Returns payment button HTML markup.
	 *
	 * @since 2.0.1
	 *
	 * @param array<mixed> $params Payment params.
	 * @param WP_Post      $post   Post being processing.
	 *
	 * @return string Payment button HTML markup.
	 */
	protected function map_payment_button_markup( array $params, WP_Post $post ): string {
		if (
			! $this->is_enabled( $post->ID )
			|| ! $this->is_points_enough( $post->ID )
		) {
			return '';
		}

		$this->post = $post;

		$button_text = $this->get_checkout_label();

		ob_start();
		?>
		<form method="post">
			<?php
			wp_nonce_field(
				'achievements_redeem_' . $post->ID,
				'_achievements_nonce'
			);
			?>
			<input type="hidden" name="course_id" value="<?php echo esc_attr( (string) $post->ID ); ?>"/>
			<input type="submit" value="<?php echo esc_attr( $button_text ); ?>" class="btn-join ld--ignore-inline-css"/>
		</form>
		<?php
		$button = ob_get_clean();

		return (string) $button;
	}

	/**
	 * Maps transaction meta.
	 *
	 * @since 2.0.1
	 *
	 * @param array   $data    Data.
	 * @param Product $product Product.
	 *
	 * @phpstan-param array{
	 *     price: int,
	 * } $data
	 *
	 * @throws \Learndash_DTO_Validation_Exception Transaction data validation exception.
	 *
	 * @return Learndash_Transaction_Meta_DTO
	 */
	protected function map_transaction_meta( $data, Product $product ): Learndash_Transaction_Meta_DTO {
		return Learndash_Transaction_Meta_DTO::create(
			[
				'ld_payment_processor' => self::get_name(),
				'is_test_mode'         => $this->is_test_mode(),
				'price_type'           => $product->get_pricing_type(),
				'pricing_info'         => Learndash_Pricing_DTO::create(
					[
						'price' => $data['price'],
					]
				),
				'gateway_transaction'  => null,
				'has_trial'            => false,
				'has_free_trial'       => false,
			]
		);
	}

	/**
	 * Handles the webhook.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function process_webhook(): void {
	}

	/**
	 * Returns the gateway label for checkout activities.
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public function get_checkout_label(): string {
		if ( empty( $this->post ) ) {
			return __( 'Pay with points', 'learndash-achievements' );
		}

		$price = Cast::to_int(
			learndash_get_setting( $this->post->ID, 'achievements_buy_course_course_price' )
		);

		// can show up the button here.
		$label = sprintf(
			// translators: 1$: point price.
			esc_attr_x(
				'Pay with %1$s',
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
				Cast::to_string( $price )
			)
		);

		return $label;
	}

	/**
	 * Returns the gateway meta HTML that appears near the payment selector.
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public function get_checkout_meta_html(): string {
		$current_points = Database::get_user_points( get_current_user_id() );

		$html = '<span>' . sprintf(
			// translators: singular point plural points.
			_n(
				'%d Point Available',
				'%d Points Available',
				absint( $current_points ),
				'learndash-achievements'
			),
			$current_points
		) . '</span>';

		return $html;
	}

	/**
	 * Returns the gateway info text for checkout activities.
	 *
	 * @since 2.0.1
	 *
	 * @param string $product_type Type of product being purchased.
	 *
	 * @return string
	 */
	public function get_checkout_info_text( string $product_type ): string {
		return '';
	}

	/**
	 * Processes the payment.
	 *
	 * @since 2.0.2
	 *
	 * @return void
	 */
	public function process_payment(): void {
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

		$product = Product::find( $post_id );

		if ( ! $product instanceof Product ) {
			return;
		}

		if ( ! $this->is_enabled( $post_id ) ) {
			return;
		}

		if ( ! $this->is_points_enough( $post_id ) ) {
			return;
		}

		$user = wp_get_current_user();

		if ( ! $user instanceof WP_User ) {
			return;
		}

		$used_points = Cast::to_int(
			get_user_meta( $user->ID, 'achievements_points_used', true )
		);

		$price        = Cast::to_int(
			learndash_get_setting( $post_id, 'achievements_buy_course_course_price' )
		);
		$price        = absint( $price );
		$used_points += $price;
		update_user_meta( $user->ID, 'achievements_points_used', $used_points );

		$this->add_access_to_products( [ $product ], $user );

		// Record transactions.

		$order_data = [
			'price' => $price,
		];

		try {
			$this->record_transaction(
				$this->map_transaction_meta( $order_data, $product )->to_array(),
				$product->get_post(),
				$user
			);

			$this->log_info( 'Recorded transaction for product ID: ' . $product->get_id() );
		} catch ( Learndash_DTO_Validation_Exception $e ) {
			$this->log_error( 'Error recording transaction: ' . $e->getMessage() );
		}

		wp_safe_redirect( (string) get_permalink( $post_id ) );
		exit();
	}

	/**
	 * Displays the price on the modern course page.
	 *
	 * @since 2.0.3
	 *
	 * @param array<string, mixed> $template_args Template arguments.
	 *
	 * @return void
	 */
	public function show_price_on_modern_course_page( $template_args ): void {
		if (
			! isset( $template_args['course'] )
			|| ! $template_args['course'] instanceof Course
		) {
			return;
		}

		$this->show_price( $template_args['course']->get_post_type(), $template_args['course']->get_id() );
	}

	/**
	 * Show price in infobar.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type Custom post type value.
	 * @param int    $course_id ID of the current course.
	 *
	 * @return void
	 */
	public function show_price( $post_type, $course_id ): void {
		if ( learndash_get_post_type_slug( 'course' ) !== $post_type ) {
			return;
		}

		if ( ! $this->is_enabled( $course_id ) ) {
			return;
		}

		$price = Cast::to_int(
			learndash_get_setting( $course_id, 'achievements_buy_course_course_price' )
		);
		?>
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
	 * Add orders payment details on order edit page.
	 *
	 * @since 2.0.2
	 *
	 * @param string      $gateway_label Gateway label.
	 * @param Transaction $transaction   Transaction model.
	 *
	 * @return string
	 */
	public function add_orders_payment_details( $gateway_label, Transaction $transaction ) {
		global $current_screen;

		// Ensure we're editing an Order.
		if (
			$transaction->get_gateway_name() !== self::get_name()
			|| ! is_admin()
			|| ! $current_screen instanceof WP_Screen
			|| $current_screen->id !== LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TRANSACTION )
		) {
			return $gateway_label;
		}

		$customer = $transaction->get_user();
		$price    = Cast::to_string(
			$transaction->get_pricing()->price
		);

		$price = ! empty( $price )
			? sprintf(
				// translators: %d: points amount.
				esc_html__( '%d Points', 'learndash-achievements' ),
				$price
			)
			: __( 'Unknown number of points', 'learndash-achievements' );

		return sprintf(
			'<a href="%s" target="_blank">%s</a><br />%s',
			get_edit_user_link( $customer->ID ) . '#learndash-achievement-points',
			$gateway_label,
			$price
		);
	}

	/**
	 * Filters transaction formatted price.
	 *
	 * @since 2.0.2
	 *
	 * @param string      $formatted_price Formatted price.
	 * @param float       $price           Price (amount).
	 * @param Transaction $transaction     Transaction model.
	 *
	 * @return string
	 */
	public function format_price( $formatted_price, float $price, Transaction $transaction ) {
		if ( $transaction->get_gateway_name() !== self::get_name() ) {
			return $formatted_price;
		}

		return sprintf(
			// translators: %s: points amount.
			__( '%s Points', 'learndash-achievements' ),
			! empty( $price )
				? Cast::to_string( $price )
				: __( 'Unknown', 'learndash-achievements' )
		);
	}

	/**
	 * Checks if the user has enough earned points.
	 *
	 * @since 2.0.2
	 *
	 * @param int $post_id ID of the current course.
	 *
	 * @return bool
	 */
	private function is_points_enough( $post_id ): bool {
		$price = Cast::to_int(
			learndash_get_setting( $post_id, 'achievements_buy_course_course_price' )
		);

		// Gets the user points.
		$current_points = Database::get_user_points( get_current_user_id() );
		$current_points = absint( $current_points );

		return $current_points >= $price;
	}

	/**
	 * Checks if points purchasing is enabled for course.
	 *
	 * @since 2.0.2
	 *
	 * @param int $post_id ID of the current course.
	 *
	 * @return bool
	 */
	private function is_enabled( $post_id ): bool {
		$course_pricing = learndash_get_course_price( $post_id );

		if ( 'paynow' !== $course_pricing['type'] ) {
			return false;
		}

		$is_enabled = Cast::to_string(
			learndash_get_setting( $post_id, 'achievements_buy_course' )
		);

		return '1' === $is_enabled;
	}
}
