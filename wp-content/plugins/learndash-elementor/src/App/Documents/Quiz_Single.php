<?php
/**
 * Quiz Single LearnDash Elementor documents class file.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Documents;

use LDLMS_Post_Types;

/**
 * Quiz single LearnDash Elementor documents class.
 *
 * @since 1.0.5
 */
class Quiz_Single extends Base {
	/**
	 * Class constructor.
	 *
	 * @since 1.0.5
	 *
	 * @param array $data Data.
	 */
	public function __construct( array $data = array() ) {
		self::$post_type_slug = learndash_get_post_type_slug( LDLMS_Post_Types::QUIZ );

		parent::__construct( $data );
	}

	/** Documented in core/base/document.php */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location']       = 'single';
		$properties['condition_type'] = learndash_get_post_type_slug( LDLMS_Post_Types::QUIZ );

		return $properties;
	}

	/** Documented in core/base/document.php */
	public static function get_type() {
		return learndash_get_post_type_slug( LDLMS_Post_Types::QUIZ );
	}

	/** Documented in core/base/document.php */
	public static function get_title() {
		return sprintf(
			// translators: placeholder: Quiz.
			esc_html_x( 'Single %s', 'placeholder: Quiz', 'learndash-elementor' ),
			\LearnDash_Custom_Label::get_label( 'quiz' )
		);
	}

	/** Documented in core/base/document.php */
	public static function get_plural_title() {
		return sprintf(
			// translators: placeholder: Course.
			esc_html_x( 'Single %s', 'placeholder: Courses', 'learndash-elementor' ),
			\LearnDash_Custom_Label::get_label( 'courses' )
		);
	}

	/** Documented in core/base/document.php */
	public function get_name() {
		return learndash_get_post_type_slug( LDLMS_Post_Types::QUIZ );
	}

	/** Documented in core/base/document.php */
	protected function register_controls() {
		$this->start_controls_section(
			'sfwd_quiz_settings',
			array(
				'label' => sprintf(
					// translators: placeholder: Quiz.
					esc_html_x( '%s Settings', 'placeholder: Quiz', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'quiz' )
				),
				'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
			)
		);

		$this->add_control(
			'step_material_select',
			array(
				'label'       => esc_html__( 'Materials Display', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'description' => esc_html__( 'How to handle the Materials content display.', 'learndash-elementor' ),
				'default'     => 'tabs',
				'options'     => array(
					'tabs'   => esc_html__( 'Tabs', 'learndash-elementor' ),
					'append' => esc_html__( 'Append to bottom', 'learndash-elementor' ),
					'none'   => esc_html__( 'Not displayed', 'learndash-elementor' ),
				),
			)
		);

		$this->end_controls_section();

		// Make sure to include the rest of the controls.
		parent::register_controls();
	}

	/** Documented in core/base/document.php */
	public function before_get_content() {
		if ( is_singular( learndash_get_post_type_slug( 'quiz' ) ) ) {
			add_filter( 'the_content', array( $this, 'learndash_elementor_the_content' ), 10, 1 );
		}
		parent::before_get_content();
	}

	/** Documented in core/base/document.php */
	public function after_get_content() {
		if ( is_singular( learndash_get_post_type_slug( 'quiz' ) ) ) {
			remove_filter( 'the_content', array( $this, 'learndash_elementor_the_content' ), 10, 1 );
		}
		parent::after_get_content();
	}

	/**
	 * Filter the post content and add in the LearnDash Materials tabs.
	 *
	 * @since 1.0.5
	 *
	 * @param string $content The post content.
	 *
	 * @return string
	 */
	public function learndash_elementor_the_content( $content = '' ): string {
		if ( is_singular( learndash_get_post_type_slug( 'quiz' ) ) ) {
			$step_id   = get_the_ID();
			$course_id = learndash_get_course_id( $step_id );
			$user_id   = get_current_user_id();

			$show_content = learndash_elementor_user_step_access_state( 'show_content', $user_id, $step_id, $course_id );
			if ( $show_content ) {
				/**
				 * Show Step Material.
				 */
				$step_material_select = $this->get_settings( 'step_material_select' );
				$step_material_select = apply_filters( 'learndash_elementor_use_content_tabs', $step_material_select, $step_id, get_post_type( $step_id ), $this );
				if ( ! empty( $step_material_select ) ) {
					$content = learndash_elementor_add_step_material_content( $content, $step_material_select, $user_id, $step_id, $course_id );
				}
			} else {
				// Follow the LearnDash logic and clear out the post content if the user does not have access.
				$content = '';
			}
		}

		return $content;
	}
}
