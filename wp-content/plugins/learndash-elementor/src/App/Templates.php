<?php
/**
 * LearnDash Elementor template-related integration class.
 *
 * @since 1.0.5
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

use LearnDash\Elementor\Utilities\Post;

/**
 * Template-related integration class.
 *
 * @since 1.0.5
 */
class Templates {
	/**
	 * Check whether we need to import default templates or not.
	 *
	 * @since 1.0.5
	 *
	 * @return void
	 */
	public function check_import_templates(): void {
		$changes                  = false;
		$learndash_elementor_data = get_option( 'learndash_elementor_data', [] );

		if (
			! is_array( $learndash_elementor_data )
			|| empty( $learndash_elementor_data )
		) {
			$changes                  = true;
			$learndash_elementor_data = [];
		}

		if ( ! isset( $learndash_elementor_data['version'] ) ) {
			$changes                             = true;
			$learndash_elementor_data['version'] = LEARNDASH_ELEMENTOR_VERSION;
		}

		if ( ! isset( $learndash_elementor_data['templates_imported'] ) ) {
			$changes            = true;
			$templates_imported = $this->import_templates();

			if ( $templates_imported ) {
				$learndash_elementor_data['templates_imported'] = $templates_imported;
			}
		}

		if ( true === $changes ) {
			update_option( 'learndash_elementor_data', $learndash_elementor_data );
		}
	}

	/**
	 * Import default Course, Lesson, Topic and Quiz templates.
	 *
	 * Called from the admin_footer action hook.
	 *
	 * @since 1.0.5
	 * @since 1.0.6   Return bool value.
	 *
	 * @return bool True if import is successful, false otherwise.
	 */
	private function import_templates(): bool {
		$exports_dir = LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/data/templates';

		if (
			! file_exists( $exports_dir )
			|| ! function_exists( 'learndash_scandir_recursive' )
		) {
			return false;
		}

		$import_files = learndash_scandir_recursive( $exports_dir );

		if ( empty( $import_files ) ) {
			return false;
		}

		$source = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' );

		if ( ! $source instanceof \Elementor\TemplateLibrary\Source_Local ) {
			return false;
		}

		foreach ( $import_files as $import_file ) {
			if (
				'.' !== $import_file[0]
				&& '.json' === substr( $import_file, -1 * strlen( '.json' ), strlen( '.json' ) )
			) {
				$source->import_template( basename( $import_file ), $import_file );
			}
		}

		return true;
	}

	/**
	 * Filter LearnDash templates.
	 *
	 * @since 1.0.5
	 *
	 * @param string     $filepath         Template file path.
	 * @param string     $name             Template name.
	 * @param array|null $args             Template data.
	 * @param bool|null  $echo             Whether to echo the template output or not.
	 * @param bool       $return_file_path Whether to return file or path or not.
	 *
	 * @return string
	 */
	public function filter_learndash_template( $filepath, $name, $args, $echo, $return_file_path ): string {
		if ( ! Post::is_elementor() ) {
			return $filepath;
		}

		switch ( $name ) {
			case 'course':
				$filepath = LEARNDASH_ELEMENTOR_VIEWS_DIR . 'themes/ld30/course/index.php';
				break;

			case 'lesson':
				$filepath = LEARNDASH_ELEMENTOR_VIEWS_DIR . 'themes/ld30/lesson/index.php';
				break;

			case 'topic':
				$filepath = LEARNDASH_ELEMENTOR_VIEWS_DIR . 'themes/ld30/topic/index.php';
				break;

			case 'quiz':
				$filepath = LEARNDASH_ELEMENTOR_VIEWS_DIR . 'themes/ld30/quiz/index.php';
				break;

			case 'course/listing.php':
				if (
					(
						! isset( $args['source'] )
						|| $args['source'] !== 'elementor'
					) && (
						! isset( $args['context'] )
						|| ! strpos( $args['context'], 'shortcode' )
					)
				) {
					$filepath = '';
				}
				break;
		}

		return $filepath;
	}
}
