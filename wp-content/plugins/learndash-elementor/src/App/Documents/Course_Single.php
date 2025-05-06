<?php
/**
 * Course Single LearnDash Elementor documents class file.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Documents;

use LDLMS_Post_Types;
use LearnDash\Core\Utilities\Cast;
use SFWD_LMS;

/**
 * Class for LearnDash_Course_Single.
 */
class Course_Single extends Base {
	/**
	 * Class constructor.
	 *
	 * @since 1.0.5
	 *
	 * @param array $data Data.
	 */
	public function __construct( array $data = array() ) {
		self::$post_type_slug = learndash_get_post_type_slug( LDLMS_Post_Types::COURSE );

		parent::__construct( $data );
	}

	/** Documented in core/base/document.php */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location']       = 'single';
		$properties['condition_type'] = learndash_get_post_type_slug( 'course' );

		return $properties;
	}

	/** Documented in core/base/document.php */
	public static function get_type() {
		return learndash_get_post_type_slug( 'course' );
	}

	/** Documented in core/base/document.php */
	public static function get_title() {
		return sprintf(
			// translators: placeholder: Course.
			esc_html_x( 'Single %s', 'placeholder: Course', 'learndash-elementor' ),
			\LearnDash_Custom_Label::get_label( 'course' )
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
		return learndash_get_post_type_slug( 'course' );
	}

	/** Documented in core/base/document.php */
	protected function register_controls() {
		$this->start_controls_section(
			'sfwd_courses_settings',
			array(
				'label' => sprintf(
					// translators: placeholder: Course.
					esc_html_x( '%s Settings', 'placeholder: Course', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'course' )
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
		if ( is_singular( learndash_get_post_type_slug( 'course' ) ) ) {
			add_filter( 'the_content', array( $this, 'learndash_elementor_the_content' ), 10, 1 );
		}

		parent::before_get_content();
	}

	/** Documented in core/base/document.php */
	public function after_get_content() {
		if ( is_singular( learndash_get_post_type_slug( 'course' ) ) ) {
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
		if ( is_singular( learndash_get_post_type_slug( 'course' ) ) ) {
			$course_id = get_the_ID();

			if ( $course_id === false ) {
				return $content;
			}

			$course_post     = get_post( $course_id );
			$course_settings = learndash_get_setting( $course_id );

			$user_id   = get_current_user_id();
			$logged_in = ! empty( $user_id );

			$content_type = learndash_get_custom_label_lower( 'course' );

			$bypass_course_limits_admin_users = learndash_elementor_bypass_course_limits( $user_id, $course_id, $course_id );

			if ( ( $logged_in ) && ( ! learndash_is_course_prerequities_completed( $course_id ) ) && ( ! $bypass_course_limits_admin_users ) ) {
				$course_pre = learndash_get_course_prerequisites( $course_id );
				if ( ! empty( $course_pre ) ) {
					foreach ( $course_pre as $c_id => $c_status ) {
						break;
					}

					$level = ob_get_level();
					ob_start();
					SFWD_LMS::get_template(
						'learndash_course_prerequisites_message',
						array(
							'current_post'           => $course_post,
							// We need to support the 'prerequisite_post' element since modified templates may use it.
							'prerequisite_post'      => get_post( $c_id ),
							'prerequisite_posts_all' => $course_pre,
							'content_type'           => $content_type,
							'course_settings'        => $course_settings,
						),
						true
					);
					$content = learndash_ob_get_clean( $level );
					return $content;
				}
			} elseif ( ( $logged_in ) && ( ! learndash_check_user_course_points_access( $course_id, $user_id ) ) && ( ! $bypass_course_limits_admin_users ) ) {
				$course_access_points = learndash_get_course_points_access( $course_id );
				$user_course_points   = learndash_get_user_course_points( $user_id );

				$level = ob_get_level();
				ob_start();
				SFWD_LMS::get_template(
					'learndash_course_points_access_message',
					array(
						'current_post'         => $course_post,
						'content_type'         => $content_type,
						'course_access_points' => $course_access_points,
						'user_course_points'   => $user_course_points,
						'course_settings'      => $course_settings,
					),
					true
				);
				$content = learndash_ob_get_clean( $level );
				return $content;
			}

			/**
			 * Show Step Material.
			 */
			$step_material_select = Cast::to_string(
				$this->get_settings( 'step_material_select' )
			);

			/**
			 * Filters the Elementor document step material setting value.
			 *
			 * @since 1.0.0
			 *
			 * @param string       $step_material_select The step material setting value.
			 * @param int|false    $course_id            The course ID.
			 * @param string|false $post_type            The course type.
			 * @param self         $document             The Elementor document object.
			 *
			 * @return string
			 */
			$step_material_select = apply_filters( 'learndash_elementor_use_content_tabs', $step_material_select, get_the_ID(), get_post_type( get_the_ID() ), $this );

			$content = learndash_elementor_add_step_material_content( $content, $step_material_select, $user_id, $course_id, $course_id );
		}

		return $content;
	}
}
