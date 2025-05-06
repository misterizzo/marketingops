<?php
/**
 * Functions file related to general stuff.
 *
 * @package LearnDash\Elementor
 */

use LearnDash\Core\App;
use LearnDash\Elementor\Step;
use LearnDash\Elementor\Widgets;

/**
 * Container for filter/action hooks functions.
 *
 * @since 1.0.3
 *
 * @deprecated 1.0.5
 *
 * @return void
 */
function learndash_elementor_hooks(): void {
	_deprecated_function( __FUNCTION__, '1.0.5' );
}

/**
 * Filter 'learndash_previous_step_completed' hook value.
 *
 * @since 1.0.3
 *
 * @deprecated 1.0.5
 *
 * @return bool
 */
function learndash_elementor_filter_learndash_previous_step_completed( $completed, $object_id, $user_id ): bool {
	_deprecated_function( __FUNCTION__, '1.0.5', Step::class . '::filter_previous_step_completed' );

	return App::get( Step::class )->filter_previous_step_completed( $completed, $object_id, $user_id );
}

/**
 * Filter 'elementor/widget/render_content' hook to check for LearnDash
 * completed step.
 *
 * @since 1.0.3
 *
 * @deprecated 1.0.5
 *
 * @param string                $content Widget content.
 * @param Elementor\Widget_Base $widget  Widget base object.
 *
 * @return string
 */
function learndash_elementor_widget_render_content( $content, $widget ): string {
	_deprecated_function( __FUNCTION__, '1.0.5', Widgets::class . '::filter_render_content' );

	return App::get( Widgets::class )->filter_render_content( $content, $widget );
}

/**
 * Check whether LearnDash previous step is completed or not.
 *
 * @since 1.0.3
 *
 * @return bool
 */
function learndash_elementor_is_previous_step_completed(): bool {
	$completed = true;

	$post = learndash_elementor_get_learndash_post();

	if ( ! $post ) {
		return $completed;
	}

	$user_id   = get_current_user_id();
	$course_id = learndash_get_course_id( $post->ID );
	$step_id   = $post->ID;

	if ( $course_id === $step_id ) {
		return $completed;
	}

	if ( learndash_can_user_bypass( $user_id ) ) {
		return $completed;
	}

	$previous_incomplete_step_id = learndash_user_progress_get_previous_incomplete_step( $user_id, $course_id, $step_id );
	$completed                   =
		learndash_is_previous_complete( $post )
		&& (
			empty( $previous_incomplete_step_id )
			|| (
				! empty( $previous_incomplete_step_id )
				&& $previous_incomplete_step_id === $step_id
			)
		);

	return $completed;
}

/**
 * Get LearnDash current WP_Post type.
 *
 * @since 1.0.3
 *
 * @return ?WP_Post
 */
function learndash_elementor_get_learndash_post(): ?WP_Post {
	$post = get_post();

	if ( is_a( $post, 'WP_Post' )
		&& in_array(
			$post->post_type,
			array(
				learndash_get_post_type_slug( 'course' ),
				learndash_get_post_type_slug( 'lesson' ),
				learndash_get_post_type_slug( 'topic' ),
				learndash_get_post_type_slug( 'quiz' ),
			)
		)
	) {
		return $post;
	}

	return null;
}

/**
 * LD Elementor wrapper function for learndash_get_template_part().
 *
 * @since 1.0.5
 *
 * @param string                   $filepath The path to the template file to include.
 * @param array<string,mixed>|null $args     Any variables to pass along to the template.
 * @param boolean                  $echo     Whether to print or return the template output. Default is false.
 *
 * @return ($echo is false ? string : null )
 */
function learndash_elementor_get_template_part( $filepath, $args = null, $echo = false ) {
	if ( is_array( $args ) ) {
		$args['source'] = 'elementor';
	} else {
		$args = [
			'source' => 'elementor',
		];
	}

	return learndash_get_template_part( $filepath, $args, $echo );
}
