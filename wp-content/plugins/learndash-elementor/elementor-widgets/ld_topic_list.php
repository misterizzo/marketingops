<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for ld_topic_list shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Topic_List extends LearnDash_Elementor_Widget_Base {

	/**
	 * Widget base constructor.
	 *
	 * Initializing the widget base class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @throws \Exception If arguments are missing when initializing a full widget
	 *                   instance.
	 *
	 * @param array      $data Widget data. Default is an empty array.
	 * @param array|null $args Optional. Widget default arguments. Default is null.
	 */
	public function __construct( $data = array(), $args = null ) {
		$this->widget_slug  = 'ld-topic-list';
		$this->widget_title = sprintf(
			// translators: placeholder: Topic.
			esc_html_x( '%s List', 'placeholder: Topic', 'learndash-elementor' ),
			\LearnDash_Custom_Label::get_label( 'topic' )
		);
		$this->widget_icon = 'eicon-bullet-list';

		$this->shortcode_slug   = 'ld_topic_list';
		$this->shortcode_params = array(
			'course_id'                 => 'course_id',
			'lesson_id'                 => 'lesson_id',
			'per_page'                  => 'num',
			'order'                     => 'order',
			'orderby'                   => 'orderby',

			'course_grid'               => 'course_grid',
			// 'progress_bar'              => 'progress_bar',
			'col'                       => 'col',

			'ld_topic_cat_id'           => 'topic_cat',
			'ld_topic_tag_id'           => 'topic_tag_id',
			'ld_topic_categoryselector' => 'topic_categoryselector',

			'wp_category_id'            => 'cat',
			'wp_tag_id'                 => 'tag_id',
			'wp_categoryselector'       => 'categoryselector',
		);

		parent::__construct( $data, $args );
	}

	/** Documented in Elementor /includes/base/controls-stack.php */
	protected function register_controls() {
		$this->start_controls_section(
			'ld_topic_list_settings',
			array(
				'label' => __( 'Settings', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'course_id',
			array(
				'label'        => sprintf(
					// translators: placeholder: Course.
					esc_html_x( '%s', 'placeholder: Course', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'course' )
				),
				'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'options'      => array(),
				'default'      => 0,
				'label_block'  => true,
				'autocomplete' => array(
					'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'  => array(
						'post_type' => array( learndash_get_post_type_slug( 'course' ) ),
					),
				),
			)
		);

		$this->add_control(
			'lesson_id',
			array(
				'label'        => sprintf(
					// translators: placeholder: Lesson.
					esc_html_x( '%s', 'placeholder: Lesson', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'lesson' )
				),
				'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'options'      => array(),
				'default'      => '0',
				'label_block'  => true,
				'autocomplete' => array(
					'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'  => array(
						'post_type' => array( learndash_get_post_type_slug( 'lesson' ) ),
					),
				),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'       => esc_html__( 'Order by', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'ID'         => __( 'ID - Order by post id. (default)', 'learndash-elementor' ),
					'title'      => __( 'Title - Order by post title', 'learndash-elementor' ),
					'date'       => __( 'Date - Order by post date', 'learndash-elementor' ),
					'menu_order' => __( 'Menu - Order by Page Order Value', 'learndash-elementor' ),
				),
				'default'     => 'ID',
			)
		);

		$this->add_control(
			'order',
			array(
				'label'       => esc_html__( 'Order', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'DESC' => __( 'DESC - highest to lowest values (default)', 'learndash-elementor' ),
					'ASC'  => __( 'ASC - lowest to highest values', 'learndash-elementor' ),
				),
				'default'     => 'DESC',
			)
		);

		$this->add_control(
			'per_page',
			array(
				'label'       => sprintf(
					// translators: placeholder: Topics.
					esc_html_x( '%s per page', 'placeholder: Topics', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'topics' )
				),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'description' => sprintf(
					// translators: placeholder: default per page.
					esc_html_x( 'Leave empty for default (%d) or 0 to show all items.', 'placeholder: default per page', 'learndash-elementor' ),
					\LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_General_Per_Page', 'per_page' )
				),
			)
		);

		$this->end_controls_section();

		if ( ( defined( 'LEARNDASH_COURSE_GRID_FILE' ) ) && ( file_exists( LEARNDASH_COURSE_GRID_FILE ) ) ) {
			$this->start_controls_section(
				'ld_course_list_grid_settings',
				array(
					'label' => __( 'Grid Settings', 'learndash-elementor' ),
				)
			);

			$this->add_control(
				'course_grid',
				array(
					'label'   => esc_html__( 'Show Grid', 'learndash-elementor' ),
					'type'    => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				)
			);

			/*
			$this->add_control(
				'progress_bar',
				array(
					'label'   => esc_html__( 'Show Progress Bar', 'learndash-elementor' ),
					'type'    => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				)
			);
			*/

			$this->add_control(
				'col',
				array(
					'label'   => esc_html__( 'Columns', 'learndash-elementor' ),
					'type'    => \Elementor\Controls_Manager::NUMBER,
					'default' => 3,
				)
			);

			$this->end_controls_section();
		}

		if ( ( 'yes' === \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Topics_Taxonomies', 'ld_topic_category' ) ) || ( 'yes' === \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Topics_Taxonomies', 'ld_topic_tag' ) ) ) {

			$this->start_controls_section(
				'ld_topic_list_ld_taxonomies',
				array(
					'label' => sprintf(
						// translators: placeholder: Topic.
						esc_html_x( '%s Taxonomies', 'placeholder: Topic', 'learndash-elementor' ),
						\LearnDash_Custom_Label::get_label( 'topic' )
					),
				)
			);

			if ( 'yes' === \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Topics_Taxonomies', 'ld_topic_category' ) ) {
				$this->add_control(
					'ld_topic_categoryselector',
					array(
						'label'   => sprintf(
							// translators: placeholder: Topic.
							esc_html_x( 'Show %s Category Selector', 'placeholder: Topic', 'learndash-elementor' ),
							\LearnDash_Custom_Label::get_label( 'topic' )
						),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
					)
				);

				$this->add_control(
					'ld_topic_cat_id',
					array(
						'label'        => sprintf(
								// translators: placeholder: Topic.
							esc_html_x( '%s Category', 'placeholder: Topic', 'learndash-elementor' ),
							\LearnDash_Custom_Label::get_label( 'topic' )
						),
						'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
						'description'  => sprintf(
							// translators: placeholder: Topics.
							esc_html_x( 'shows %s with mentioned LearnDash category.', 'placeholder: Topics', 'learndash-elementor' ),
							\LearnDash_Custom_Label::get_label( 'topics' )
						),
						'options'      => array(),
						'label_block'  => true,
						'autocomplete' => array(
							'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
							'query'  => array(
								'post_type' => array( learndash_get_post_type_slug( 'topic' ) ),
								'taxonomy'  => array( 'ld_topic_category' ),
							),
						),
					)
				);
			}

			if ( 'yes' === \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Topics_Taxonomies', 'ld_topic_tag' ) ) {
				$this->add_control(
					'ld_topic_tag_id',
					array(
						'label'        => sprintf(
								// translators: placeholder: Topic.
							esc_html_x( '%s Tag', 'placeholder: Topic', 'learndash-elementor' ),
							\LearnDash_Custom_Label::get_label( 'topic' )
						),
						'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
						'description'  => sprintf(
							// translators: placeholder: Topics.
							esc_html_x( 'shows %s with mentioned LearnDash tag.', 'placeholder: Topics', 'learndash-elementor' ),
							\LearnDash_Custom_Label::get_label( 'topics' )
						),
						'options'      => array(),
						'label_block'  => true,
						'autocomplete' => array(
							'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
							'query'  => array(
								'post_type' => array( learndash_get_post_type_slug( 'topic' ) ),
								'taxonomy'  => array( 'ld_topic_tag' ),
							),
						),
					)
				);
			}

			$this->end_controls_section();
		}

		if ( ( 'yes' === \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Topics_Taxonomies', 'wp_post_category' ) ) || ( 'yes' === \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Topics_Taxonomies', 'wp_post_tag' ) ) ) {
			$this->start_controls_section(
				'ld_topic_list_wp_taxonomies',
				array(
					'label' => esc_html__( 'WordPress Taxonomies', 'learndash-elementor' ),
				)
			);

			if ( 'yes' === \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Topics_Taxonomies', 'wp_post_category' ) ) {
				$this->add_control(
					'wp_course_categoryselector',
					array(
						'label'   => esc_html__( 'Show WordPress Category Selector', 'learndash-elementor' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
					)
				);

				$this->add_control(
					'wp_category_id',
					array(
						'label'        => esc_html__( 'Category', 'learndash-elementor' ),
						'description'  => sprintf(
							// translators: placeholder: Topics.
							esc_html_x( 'shows %s with mentioned WordPress category.', 'placeholder: Topics', 'learndash-elementor' ),
							\LearnDash_Custom_Label::get_label( 'topics' )
						),
						'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
						'options'      => array(),
						'label_block'  => true,
						'autocomplete' => array(
							'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
							'query'  => array(
								'post_type' => array( learndash_get_post_type_slug( 'topic' ) ),
								'taxonomy'  => array( 'category' ),
							),
						),
					)
				);
			}

			if ( 'yes' === \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Topics_Taxonomies', 'wp_post_tag' ) ) {
				$this->add_control(
					'wp_tag_id',
					array(
						'label'        => esc_html__( 'Tag', 'learndash-elementor' ),
						'description'  => sprintf(
							// translators: placeholder: Topics.
							esc_html_x( 'shows %s with mentioned WordPress tag.', 'placeholder: Topics', 'learndash-elementor' ),
							\LearnDash_Custom_Label::get_label( 'topics' )
						),
						'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
						'options'      => array(),
						'label_block'  => true,
						'autocomplete' => array(
							'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
							'query'  => array(
								'post_type' => array( learndash_get_post_type_slug( 'topic' ) ),
								'taxonomy'  => array( 'post_tag' ),
							),
						),
					)
				);
			}

			$this->end_controls_section();

			/**
			 * Start of Style tab.
			 */
			$this->start_controls_section(
				'section_topic_list_row_item',
				array(
					'label' => __( 'Row Item', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'control_topic_list_row_item_title_color',
				array(
					'label'     => __( 'Title Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-item-list .ld-item-list-item a.ld-item-name' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'control_topic_list_row_item_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-item-list .ld-item-list-item' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_section();

		}
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$shortcode_pairs = array();

		foreach ( $this->shortcode_params as $key_ex => $key_in ) {
			if ( isset( $settings[ $key_ex ] ) ) {
				$shortcode_pairs[ $key_in ] = $settings[ $key_ex ];
			}
		}

		$shortcode_params_str = '';
		foreach ( $shortcode_pairs as $key => $val ) {
			$skip_param = false;
			switch ( $key ) {
				case 'topic_categoryselector':
				case 'categoryselector':
				case 'course_grid':
					// case 'progress_bar':
					if ( 'yes' === $val ) {
						$val = 'true';
					} else {
						$val = '';
					}
					break;

				default:
					if ( '' === $val ) {
						$skip_param = true;
					} else {
						$val = esc_attr( $val );
					}
					break;
			}

			if ( true !== $skip_param ) {
				$shortcode_params_str .= ' ' . $key . '="' . $val . '"';
			}
		}
		if ( ! empty( $shortcode_params_str ) ) {
			if ( ( isset( $shortcode_pairs['course_grid'] ) ) && ( ! empty( $shortcode_pairs['course_grid'] ) ) ) {
				if ( ( defined( 'LEARNDASH_COURSE_GRID_FILE' ) ) && ( file_exists( LEARNDASH_COURSE_GRID_FILE ) ) ) {
					learndash_enqueue_course_grid_scripts();
				}
			}

			$shortcode_params_str = '[' . $this->shortcode_slug . $shortcode_params_str . ']';
			echo do_shortcode( $shortcode_params_str );
		}
	}
}
