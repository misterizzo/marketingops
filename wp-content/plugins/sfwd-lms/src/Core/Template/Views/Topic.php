<?php
/**
 * The topic view class.
 *
 * @since 4.6.0
 *
 * @package LearnDash\Core
 */

/** NOTICE: This code is currently under development and may not be stable.
 *  Its functionality, behavior, and interfaces may change at any time without notice.
 *  Please refrain from using it in production or other critical systems.
 *  By using this code, you assume all risks and liabilities associated with its use.
 *  Thank you for your understanding and cooperation.
 **/

namespace LearnDash\Core\Template\Views;

use InvalidArgumentException;
use LDLMS_Post_Types;
use LearnDash\Core\Models;
use LearnDash\Core\Mappers;
use LearnDash\Core\Template\Views\Traits\Has_Steps;
use LearnDash_Custom_Label;
use WP_Post;
use LearnDash\Core\Template\Tabs;
use LearnDash\Core\Template\Breadcrumbs;

/**
 * The view class for LD topic post type.
 *
 * @since 4.6.0
 */
class Topic extends View implements Interfaces\Has_Steps {
	use Has_Steps;

	/**
	 * The related model.
	 *
	 * @var Models\Topic
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @since 4.6.0
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @throws InvalidArgumentException If the post type is not allowed.
	 */
	public function __construct( WP_Post $post ) {
		$this->model = Models\Topic::create_from_post( $post );

		parent::__construct(
			LDLMS_Post_Types::get_post_type_key( $post->post_type ),
			$this->build_context()
		);
	}

	/**
	 * Returns the total number of steps.
	 *
	 * TODO: Here it will contain the wrong number if some of the lessons or quizzes are not published. So we need to decide how we want to handle this case.
	 *
	 * @since 4.6.0
	 *
	 * @return int
	 */
	public function get_total_steps(): int {
		$steps_mapper = new Mappers\Steps\Topic( $this->model );

		return $steps_mapper->total();
	}

	/**
	 * Returns the steps page size.
	 *
	 * @since 4.6.0
	 *
	 * @return int
	 */
	public function get_steps_page_size(): int {
		$course = $this->model->get_course();

		return learndash_get_course_lessons_per_page(
			$course ? $course->get_id() : 0
		);
	}

	/**
	 * Builds context for the rendering of this view.
	 *
	 * @since 4.6.0
	 *
	 * @return array<string, mixed>
	 */
	protected function build_context(): array {
		$course = $this->model->get_course();
		$user   = wp_get_current_user();

		return [
			'topic'              => $this->model,
			'course'             => $course,
			'title'              => $this->model->get_title(),
			'content_is_visible' => true, // TODO: Not sure what controls it.
			'is_enrolled'        => $course && $course->get_product()->user_has_access( $user ), // TODO: Not sure if it's correct.
			'tabs'               => $this->get_tabs(),
		];
	}

	/**
	 * Gets the tabs.
	 *
	 * @since 4.6.0
	 *
	 * @return Tabs\Tabs
	 */
	protected function get_tabs(): Tabs\Tabs {
		$tabs_array = [
			[
				'id'      => 'content',
				'icon'    => 'lesson',
				'label'   => LearnDash_Custom_Label::get_label( 'topic' ),
				'content' => $this->model->get_content() . $this->map_steps_content(),
				'order'   => 1,
			],
			[
				'id'      => 'materials',
				'icon'    => 'materials',
				'label'   => __( 'Materials', 'learndash' ),
				'content' => $this->model->get_materials(),
				'order'   => 2,
			],
		];

		/** This filter is documented in src/Core/Template/Views/Course.php */
		$tabs_array = (array) apply_filters( 'learndash_template_views_tabs', $tabs_array, $this->view_slug, $this );

		/**
		 * Filters the topic tabs.
		 *
		 * @since 4.21.0
		 *
		 * @param array<int, array<string, array{id: string, icon: string, label: string, content: string, order?: int}>> $tabs      The tabs.
		 * @param string                                                                                                  $view_slug The view slug.
		 * @param Topic                                                                                                   $view      The view object.
		 *
		 * @ignore
		 */
		$tabs_array = (array) apply_filters( 'learndash_template_views_topic_tabs', $tabs_array, $this->view_slug, $this );

		$tabs = new Tabs\Tabs( $tabs_array );

		return $tabs->filter_empty_content()->sort();
	}

	/**
	 * Maps the steps content.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	protected function map_steps_content(): string {
		$steps_mapper = new Mappers\Steps\Topic( $this->model );

		$steps = $steps_mapper->paginated( $this->get_current_steps_page(), $this->get_steps_page_size() );

		return $this->get_steps_content( $steps );
	}
}
