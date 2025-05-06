<?php
/**
 * Integration file for Elementor frontend-related stuff.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

use LDLMS_Post_Types;
use LearnDash\Elementor\Settings\Section;
use LearnDash\Elementor\Utilities\Post;
use WP_Post;

/**
 * Frontend integration class.
 *
 * @since 1.0.6
 */
class Frontend {
	/**
	 * Get checked post types for frontend stuff.
	 *
	 * @since 1.0.6
	 *
	 * @return array<string>
	 */
	private function get_checked_post_types(): array {
		return [
			learndash_get_post_type_slug( LDLMS_Post_Types::COURSE ),
			learndash_get_post_type_slug( LDLMS_Post_Types::LESSON ),
			learndash_get_post_type_slug( LDLMS_Post_Types::TOPIC ),
		];
	}

	/**
	 * Filter builder content data if existing data from LD post types is empty to allow automatic infobar and content widgets insertion.
	 *
	 * @since 1.0.6
	 *
	 * @param array<array<string, mixed>> $data    Existing builder content data.
	 * @param int                         $post_id Current post ID.
	 *
	 * @return array<array<string, mixed>> Returned builder content data.
	 */
	public function filter_builder_content_data( $data, $post_id ): array {
		if ( ! empty( $data ) ) {
			return $data;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return $data;
		}

		if ( ! in_array(
			$post->post_type,
			$this->get_checked_post_types(),
			true
		) ) {
			return $data;
		}

		// Return minimum data.

		return [
			[
				'id'       => substr( md5( (string) time() ), 0, 7 ), // Match the pattern with normal Elementor element ID, 7 characters random string.
				'elType'   => 'section',
				'settings' => [],
				'elements' => [],
				'isInner'  => '',
			],
		];
	}

	/**
	 * Filter frontend content.
	 *
	 * @since 1.0.6
	 *
	 * @param string $content Original content.
	 *
	 * @return string New content.
	 */
	public function filter_content( $content ): string {
		$post = get_post();

		if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
			return $content;
		}

		$disabled_setting = Section::get_setting( 'disable_widget_auto_insertion' );

		if ( $disabled_setting === 'on' ) {
			return $content;
		}

		if ( ! in_array(
			$post->post_type,
			$this->get_checked_post_types(),
			true
		) ) {
			return $content;
		}

		// Bail if location document used is a custom Elementor global template.

		/**
		 * Theme builder module.
		 *
		 * @var \ElementorPro\Modules\ThemeBuilder\Module $theme_builder_module Theme builder module object.
		 */
		$theme_builder_module = \ElementorPro\Modules\ThemeBuilder\Module::instance();
		$location             = $theme_builder_module->get_locations_manager()->get_current_location();
		$location_documents   = $theme_builder_module->get_conditions_manager()->get_documents_for_location( $location );

		$document_types = [];
		foreach ( $location_documents as $document_id => $document ) {
			$document_types[] = $document::get_type();
		}

		if ( ! empty( $document_types ) ) {
			return $content;
		}

		$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( $post->ID );

		if ( ! $document instanceof \Elementor\Core\Base\Document ) {
			return $content;
		}

		$data          = $document->get_elements_data();
		$elements      = Post::extract_elements( $data );
		$el_types      = array_column( $elements, 'widgetType' );
		$elements_json = wp_json_encode( $elements );

		if ( $elements_json === false ) {
			return $content;
		}

		$context   = learndash_get_post_type_key( $post->post_type );
		$course_id = learndash_get_course_id( $post->ID );

		if ( ! is_int( $course_id ) ) {
			return $content;
		}

		$user_id       = get_current_user_id();
		$has_access    = sfwd_lms_has_access( $post->ID, $user_id );
		$course_status = learndash_course_status( $course_id, $user_id );

		if ( $course_id === $post->ID ) {
			$course_status = learndash_course_status( $course_id, $user_id );
		} elseif ( ! empty( $course_id ) && ! empty( $post->ID ) ) {
			$course_status = learndash_is_item_complete( $post->ID, $user_id, $course_id );

			if ( $course_status ) {
				$course_status = 'complete';
			} else {
				$course_status = 'incomplete';
			}
		}

		$args = [
			'context'       => $context,
			'course_id'     => $course_id,
			'user_id'       => $user_id,
			'step_id'       => $post->ID,
			'has_access'    => $has_access,
			'course_status' => $course_status,
			'post'          => $post,
		];

		// Insert certificate widget.

		$certificate_widget = '';

		$course_certificate_link = learndash_get_course_certificate_link( $course_id, $user_id );

		if (
			! in_array( 'ld-course-certificate', $el_types, true )
			&& ! has_shortcode( $elements_json, 'ld_certificate' )
			&& ! empty( $course_certificate_link )
		) {
			$certificate = learndash_get_template_part(
				'modules/alert.php',
				array(
					'type'    => 'success ld-alert-certificate',
					'icon'    => 'certificate',
					'message' => __( 'You\'ve earned a certificate!', 'learndash-elementor' ),
					'button'  => array(
						'url'    => $course_certificate_link,
						'icon'   => 'download',
						'label'  => __( 'Download Certificate', 'learndash-elementor' ),
						'target' => '_new',
					),
				),
				false
			);

			$certificate_widget = '<div class="' . esc_attr( learndash_get_wrapper_class( $post ) ) . '">' . $certificate . '</div>';
		}

		// Insert infobar widget.

		$infobar_widget = '';

		if (
			! in_array( 'ld-course-infobar', $el_types, true )
			&& ! has_shortcode( $elements_json, 'ld_infobar' )
		) {
			$infobar_html = learndash_elementor_get_template_part(
				'modules/infobar.php',
				$args,
				false
			);

			$infobar_widget = '<div class="' . esc_attr( learndash_get_wrapper_class( $post ) ) . '">' . $infobar_html . '</div>';
		}

		// Insert content widget.

		$content_widget = '';

		if (
			! in_array( 'ld-course-content', $el_types, true )
			&& ! has_shortcode( $elements_json, 'course_content' )
		) {
			ob_start();

			if ( 'course' === $context ) {
				$logged_in = ! empty( $args['user_id'] );

				$bypass_course_limits_admin_users = learndash_elementor_bypass_course_limits( $args['user_id'], $args['course_id'], $args['course_id'] );

				if (
					$logged_in
					&& ! learndash_is_course_prerequities_completed( $args['course_id'] )
					&& ! $bypass_course_limits_admin_users
				) {
					return $content;
				}

				if (
					$logged_in
					&& ! learndash_check_user_course_points_access( $args['course_id'], $args['user_id'] )
					&& ! $bypass_course_limits_admin_users
				) {
					return $content;
				}

				learndash_elementor_show_course_content_listing( $args );
			} elseif ( 'lesson' === $context ) {
				learndash_elementor_show_lesson_content_listing( $args );
			} elseif ( 'topic' === $context ) {
				learndash_elementor_show_topic_content_listing( $args );
			}

			$content_widget = ob_get_clean();
		}

		return $certificate_widget . $infobar_widget . $content . $content_widget;
	}
}
