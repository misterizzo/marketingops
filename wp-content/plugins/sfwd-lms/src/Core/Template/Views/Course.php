<?php
/**
 * The course view class.
 *
 * @since 4.6.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Template\Views;

use InvalidArgumentException;
use LDLMS_Post_Types;
use LearnDash\Core\Models;
use LearnDash\Core\Template\Tabs;
use LearnDash_Custom_Label;
use WP_Post;

/**
 * The view class for LD course post type.
 *
 * @since 4.6.0
 */
class Course extends View {
	/**
	 * The related model.
	 *
	 * @since 4.6.0
	 *
	 * @var Models\Course
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @since 4.6.0
	 *
	 * @param WP_Post              $post    The post object.
	 * @param array<string, mixed> $context Context.
	 *
	 * @throws InvalidArgumentException If the post type is not allowed.
	 */
	public function __construct( WP_Post $post, array $context = [] ) {
		$this->context = $context;
		$this->model   = Models\Course::create_from_post( $post );

		parent::__construct(
			LDLMS_Post_Types::get_post_type_key( $post->post_type ),
			$this->build_context()
		);
	}

	/**
	 * Returns the model.
	 *
	 * @since 4.21.0
	 *
	 * @return Models\Course
	 */
	public function get_model(): Models\Course {
		return $this->model;
	}

	/**
	 * Builds context for the rendering of this view.
	 *
	 * @since 4.6.0
	 *
	 * @return array<string, mixed>
	 */
	protected function build_context(): array {
		return array_merge(
			// Parent context.
			$this->context,
			// Default context (is used across all themes).
			[
				'course'     => $this->model,
				'has_access' => $this->model->get_product()->user_has_access(),
				'login_url'  => learndash_get_login_url(),
				'product'    => $this->model->get_product(),
				'tabs'       => $this->get_tabs(),
			]
		);
	}

	/**
	 * Gets the tabs.
	 *
	 * @since 4.6.0
	 *
	 * @return Tabs\Tabs
	 */
	protected function get_tabs(): Tabs\Tabs {
		$content = $this->model->get_content();

		if (
			! empty( $content )
			&& has_post_thumbnail( $this->model->get_post() )
		) {
			$content = get_the_post_thumbnail(
				$this->model->get_post(),
				'large', // We assume this is the default 1024x1024 size.
				[
					'class' => 'ld-featured-image ld-featured-image--course',
				]
			) . $content;
		}

		$tabs_array = [
			[
				'id'      => 'content',
				'icon'    => 'course',
				'label'   => LearnDash_Custom_Label::get_label( 'course' ),
				'content' => $content,
				'order'   => 10,
			],
			[
				'id'      => 'materials',
				'icon'    => 'materials',
				'label'   => __( 'Materials', 'learndash' ),
				'content' => $this->model->get_materials(),
				'order'   => 20,
			],
		];

		/** This filter is documented in themes/ld30/templates/modules/tabs.php */
		$tabs_array = (array) apply_filters(
			'learndash_content_tabs',
			$tabs_array,
			LDLMS_Post_Types::get_post_type_key( $this->model->get_post()->post_type ),
			$this->model->get_id(),
			get_current_user_id()
		);

		/**
		 * Filters the tabs.
		 *
		 * @since 4.21.0
		 *
		 * @param array<int, array<string, array{id: string, icon: string, label: string, content: string, order?: int}>> $tabs      The tabs.
		 * @param string                                                                                                  $view_slug The view slug.
		 * @param Course                                                                                                  $view      The view object.
		 *
		 * @return array<int, array<string, array{id: string, icon: string, label: string, content: string, order?: int}>>
		 */
		$tabs_array = (array) apply_filters( 'learndash_template_views_tabs', $tabs_array, $this->view_slug, $this );

		/**
		 * Filters the course tabs.
		 *
		 * @since 4.21.0
		 *
		 * @param array<int, array<string, array{id: string, icon: string, label: string, content: string, order?: int}>> $tabs      The tabs.
		 * @param string                                                                                                  $view_slug The view slug.
		 * @param Course                                                                                                  $view      The view object.
		 * @param Models\Course                                                                                           $model     The course model.
		 *
		 * @return array<int, array<string, array{id: string, icon: string, label: string, content: string, order?: int}>>
		 */
		$tabs_array = (array) apply_filters( 'learndash_template_views_course_tabs', $tabs_array, $this->view_slug, $this, $this->model );

		$tabs = new Tabs\Tabs( $tabs_array );

		return $tabs->filter_empty_content()->sort();
	}
}
