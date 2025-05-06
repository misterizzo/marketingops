<?php
/**
 * Achievements widget class file.
 *
 * @since 1.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Widget;

use LearnDash\Achievements\Database;
use WP_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Leaderboard widget class.
 *
 * @since 1.0
 *
 * @template T of array<string, mixed>
 * @extends WP_Widget<T>
 */
class Achievements extends WP_Widget {
	/**
	 * Achievements constructor.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'ld-my-achievements',
			'description' => __( 'Display current user LearnDash Achievements.', 'learndash-achievements' ),
		);
		parent::__construct(
			'ld-my-achievements',
			__( 'LearnDash My Achievements', 'learndash-achievements' ),
			$widget_ops
		);
	}

	/**
	 * Main widget
	 *
	 * @param array $args The widget args.
	 * @param array $instance The instance.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		echo do_shortcode( '[ld_my_achievements]' );
		echo $args['after_widget'];
	}

	/**
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'My Achievements', 'learndash-achievements' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_attr( 'Title', 'learndash-achievements' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}
