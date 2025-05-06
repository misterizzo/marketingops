<?php
/**
 * Deprecated LearnDash_Elementor class file.
 *
 * @deprecated 1.0.5
 *
 * @package LearnDash\Elementor\Deprecated
 *
 * cspell:disable -- Disable cspell for the whole file since this is a deprecated file.
 */

use LearnDash\Elementor\Utilities\Dependency_Checker;

_deprecated_file( __FILE__, '1.0.5' );

/**
 * Deprecated LearnDash_Elementor class.
 *
 * @deprecated 1.0.5
 */
class LearnDash_Elementor {
	/**
	 * Static instance variable to ensure
	 * only one instance of class is used.
	 *
	 * @since 1.0.0
	 *
	 * @var object $instance.
	 */
	protected static $instance = null;

	/**
	 * Get or create instance object of class.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Public constructor for class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'i18nize' ) );

		add_action( 'elementor/init', array( $this, 'elementor_init' ) );
	}

	/**
	 * Loads the plugin's translated strings
	 *
	 * @since 1.0.0
	 */
	public function i18nize() {
		if (
			defined( 'LD_LANG_DIR' )
			&& LD_LANG_DIR
		) {
			load_plugin_textdomain( 'learndash-elementor', false, LD_LANG_DIR );
		} else {
			load_plugin_textdomain( 'learndash-elementor', false, dirname( plugin_basename( __DIR__ ) ) . '/languages' );
		}
	}

	/**
	 * Elementor Init
	 *
	 * @since 1.0.2
	 */
	public function elementor_init() {
		add_filter( 'elementor_pro/utils/get_public_post_types', array( $this, 'get_public_post_types' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'elements_categories_registered' ), 1, 1 );
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'registered_widgets' ), 99, 1 );
		add_action( 'elementor/documents/register', array( $this, 'register_documents' ) );
	}

	/**
	 * Register LearnDash Elementor editor Widgets category.
	 *
	 * @since 1.0.0
	 *
	 * @param object $elements_manager Instance of ElementorElements_Manager class.
	 */
	public function elements_categories_registered( $elements_manager ) {
		if ( Dependency_Checker::get_instance()->check_dependency_results() ) {
			$elements_manager->add_category(
				'learndash-elements',
				array(
					'title'  => __( 'LearnDash Elements', 'learndash-elementor' ),
					'active' => false,
				)
			);
		}
	}

	/**
	 * Handler function when Elementor registers editor widgets.
	 *
	 * @since 1.0.0
	 *
	 * @param object $widgets_manager Instance of Widgets_Manager class.
	 */
	public function registered_widgets( $widgets_manager ) {
		if ( Dependency_Checker::get_instance()->check_dependency_results() ) {
			if ( ( function_exists( 'learndash_is_active_theme' ) ) && ( learndash_is_active_theme( 'ld30' ) ) ) {
				$template_type = learndash_elementor_get_template_type();

				// Load our base Widgets class.
				require_once 'learndash-elementor-widget-base.php';
				if ( ( ! $template_type ) || ( in_array( $template_type, learndash_get_post_types( 'course' ), true ) ) ) {
					if ( learndash_get_post_type_slug( 'quiz' ) !== $template_type ) {
						require_once 'elementor-widgets/ld_course_content.php';
						if ( class_exists( 'LearnDash_Elementor_Widget_Course_Content' ) ) {
							$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Course_Content() );
						}
					} elseif ( learndash_get_post_type_slug( 'quiz' ) === $template_type ) {
						require_once 'elementor-widgets/ld_quiz.php';
						if ( class_exists( 'LearnDash_Elementor_Widget_Quiz' ) ) {
							$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Quiz() );
						}
					}

					require_once 'elementor-widgets/ld_course_infobar.php';
					if ( class_exists( 'LearnDash_Elementor_Widget_Course_Infobar' ) ) {
						$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Course_Infobar() );
					}

					if ( ( ! $template_type ) || ( learndash_get_post_type_slug( 'course' ) === $template_type ) ) {
						require_once 'elementor-widgets/ld_course_certificate.php';
						if ( class_exists( 'LearnDash_Elementor_Widget_Course_Certificate' ) ) {
							$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Course_Certificate() );
						}
					}
				}

				require_once 'elementor-widgets/ld_login.php';
				if ( class_exists( 'LearnDash_Elementor_Widget_Login' ) ) {
					$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Login() );
				}

				require_once 'elementor-widgets/ld_profile.php';
				if ( class_exists( 'LearnDash_Elementor_Widget_Profile' ) ) {
					$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Profile() );
				}

				require_once 'elementor-widgets/ld_course_list.php';
				if ( class_exists( 'LearnDash_Elementor_Widget_Course_List' ) ) {
					$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Course_List() );
				}

				require_once 'elementor-widgets/ld_lesson_list.php';
				if ( class_exists( 'LearnDash_Elementor_Widget_Lesson_List' ) ) {
					$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Lesson_List() );
				}

				require_once 'elementor-widgets/ld_topic_list.php';
				if ( class_exists( 'LearnDash_Elementor_Widget_Topic_List' ) ) {
					$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Topic_List() );
				}

				require_once 'elementor-widgets/ld_quiz_list.php';
				if ( class_exists( 'LearnDash_Elementor_Widget_Quiz_List' ) ) {
					$widgets_manager->register_widget_type( new LearnDash_Elementor_Widget_Quiz_List() );
				}
			}
		}
	}

	/**
	 * Hook into the Elementor Document Manager to register our custom templates.
	 *
	 * @param object $documents_manager Instance of Documents_Manager.
	 */
	public function register_documents( $documents_manager ) {
		if ( Dependency_Checker::get_instance()->check_dependency_results() ) {
			if ( ( function_exists( 'learndash_is_active_theme' ) ) && ( learndash_is_active_theme( 'ld30' ) ) ) {

				// Load our base Widgets class.
				require_once 'learndash-elementor-document-base.php';

				require_once 'elementor-documents/course-single.php';
				if ( class_exists( 'LearnDash_Course_Single' ) ) {
					$documents_manager->register_document_type( learndash_get_post_type_slug( 'course' ), LearnDash_Course_Single::get_class_full_name() );
				}

				require_once 'elementor-documents/lesson-single.php';
				if ( class_exists( 'LearnDash_Lesson_Single' ) ) {
					$documents_manager->register_document_type( learndash_get_post_type_slug( 'lesson' ), LearnDash_Lesson_Single::get_class_full_name() );
				}

				require_once 'elementor-documents/topic-single.php';
				if ( class_exists( 'LearnDash_Topic_Single' ) ) {
					$documents_manager->register_document_type( learndash_get_post_type_slug( 'topic' ), LearnDash_Topic_Single::get_class_full_name() );
				}

				require_once 'elementor-documents/quiz-single.php';
				if ( class_exists( 'LearnDash_Quiz_Single' ) ) {
					$documents_manager->register_document_type( learndash_get_post_type_slug( 'quiz' ), LearnDash_Quiz_Single::get_class_full_name() );
				}
			}
		}
	}

	/**
	 * Include the LearnDash Post Types to show when adding new Elementor Templates.
	 *
	 * Required Elementor Pro 2.3.0 or higher.
	 *
	 * @since 1.0.0
	 * @param array $post_types array of post type slugs and labels to show.
	 * @return array of post types.
	 */
	public function get_public_post_types( $post_types ) {
		if ( Dependency_Checker::get_instance()->check_dependency_results() ) {
			if ( ( function_exists( 'learndash_is_active_theme' ) ) && ( learndash_is_active_theme( 'ld30' ) ) ) {
				if ( function_exists( 'learndash_get_post_type_slug' ) ) {
					$ld_post_types = array(
						learndash_get_post_type_slug( 'course' ) => LearnDash_Custom_Label::get_label( 'courses' ),
						learndash_get_post_type_slug( 'lesson' ) => LearnDash_Custom_Label::get_label( 'lessons' ),
						learndash_get_post_type_slug( 'topic' ) => LearnDash_Custom_Label::get_label( 'topics' ),
						learndash_get_post_type_slug( 'quiz' ) => LearnDash_Custom_Label::get_label( 'quizzes' ),
					);

					foreach ( $ld_post_types as $ld_post_type_slug => $ld_post_type_label ) {
						if ( ! isset( $post_types[ $ld_post_type_slug ] ) ) {
							$post_types[ $ld_post_type_slug ] = $ld_post_type_label;
						}
					}
				}
			}
		}

		return $post_types;
	}

	// End of functions.
}
