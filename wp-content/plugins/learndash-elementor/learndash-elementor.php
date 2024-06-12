<?php
/**
 * Plugin Name:  LearnDash LMS - Elementor
 * Plugin URI:   https://support.learndash.com
 * Description:  LearnDash LMS add-on to add Elementor widgets and templates.
 * Author:       LearnDash
 * Author URI:   https://support.learndash.com
 * Version:      1.0.3
 * License:      GPL v2 or later
 * Text Domain: learndash-elementor
 * Doman Path: /languages/
 *
 * @package LearnDash
 */

use Elementor\TemplateLibrary\Source_Local;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'LEARNDASH_ELEMENTOR_VERSION', '1.0.3' );

if ( ! class_exists( 'LearnDash_Elementor' ) ) {

	/**
	 * Class to create the instance.
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
		protected function __construct() {
			// Hook into the WP admin_footer action.
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );
			add_action( 'plugins_loaded', array( $this, 'i18nize' ) );

			// Hooks into Elementor.
			add_action( 'elementor/init', array( $this, 'elementor_init' ) );
		}

		/**
		 * Loads the plugin's translated strings
		 *
		 * @since 1.0.0
		 */
		public function i18nize() {
			if ( ( defined( 'LD_LANG_DIR' ) ) && ( LD_LANG_DIR ) ) {
				load_plugin_textdomain( 'learndash-elementor', false, LD_LANG_DIR );
			} else {
				load_plugin_textdomain( 'learndash-elementor', false, dirname( plugin_basename( dirname( __FILE__ ) ) ) . '/languages' );
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
			if ( LearnDash_Dependency_Check_LD_Elementor::get_instance()->check_dependency_results() ) {
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
			if ( LearnDash_Dependency_Check_LD_Elementor::get_instance()->check_dependency_results() ) {
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
			if ( LearnDash_Dependency_Check_LD_Elementor::get_instance()->check_dependency_results() ) {
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
			if ( LearnDash_Dependency_Check_LD_Elementor::get_instance()->check_dependency_results() ) {
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

		/**
		 * Handler function for the admin_footer action.
		 */
		public function admin_footer() {
			if ( LearnDash_Dependency_Check_LD_Elementor::get_instance()->check_dependency_results() ) {
				$changes = false;

				$learndash_elementor_data = get_option( 'learndash_elementor_data', array() );
				if ( ( ! is_array( $learndash_elementor_data ) ) || ( empty( $learndash_elementor_data ) ) ) {
					$changes                  = true;
					$learndash_elementor_data = array();
				}

				if ( ! isset( $learndash_elementor_data['version'] ) ) {
					$changes                             = true;
					$learndash_elementor_data['version'] = LEARNDASH_ELEMENTOR_VERSION;
				}

				if ( ! isset( $learndash_elementor_data['templates_imported'] ) ) {
					$changes                                        = true;
					$learndash_elementor_data['templates_imported'] = $this->import_templates();
				}

				if ( true === $changes ) {
					update_option( 'learndash_elementor_data', $learndash_elementor_data );
				}
			}
		}

		/**
		 * Handler function to import default Course, Lesson, Topic, and Quiz templates.
		 *
		 * Called from the admin_footer handler function in this same class.
		 *
		 * @since 1.0.0
		 */
		private function import_templates() {
			$exports_dir = dirname( __FILE__ ) . '/exports';
			if ( ( file_exists( $exports_dir ) ) && ( function_exists( 'learndash_scandir_recursive' ) ) ) {
				$import_files = learndash_scandir_recursive( $exports_dir );
				if ( ! empty( $import_files ) ) {
					$source = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' );
					foreach ( $import_files as $import_file ) {
						if ( ( '.' !== $import_file[0] ) && ( '.json' === substr( $import_file, -1 * strlen( '.json' ), strlen( '.json' ) ) ) ) {
							$imported_items = $source->import_template( basename( $import_file ), $import_file );
						}
					}
				}
			}

			return true;
		}

		// End of functions.
	}
	LearnDash_Elementor::get_instance();
}
require plugin_dir_path( __FILE__ ) . 'includes/course-template-functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/lesson-template-functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/topic-template-functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-learndash-elementor-shortcodes-tinymce.php';

require plugin_dir_path( __FILE__ ) . 'includes/class-ld-dependency-check.php';
LearnDash_Dependency_Check_LD_Elementor::get_instance()->set_dependencies(
	array(
		'sfwd-lms/sfwd_lms.php'           => array(
			'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
			'class'       => 'SFWD_LMS',
			'min_version' => '3.1.6',
		),
		'elementor/elementor.php'         => array(
			'label'       => '<a href="https://elementor.com">Elementor</a>',
			'min_version' => '2.9.8',
		),
		'elementor-pro/elementor-pro.php' => array(
			'label'       => '<a href="https://elementor.com">Elementor Pro</a>',
			'min_version' => '2.9.3',
		),
	)
);
LearnDash_Dependency_Check_LD_Elementor::get_instance()->set_message(
	esc_html__( 'LearnDash LMS Elementor Add-on requires the following plugin(s) be active:', 'learndash-elementor' )
);
