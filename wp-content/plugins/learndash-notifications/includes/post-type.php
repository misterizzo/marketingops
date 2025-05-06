<?php
/**
 * Post type functions.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Notifications
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use LearnDash_Notification\Notification;

/**
 * Registers a new post type
 *
 * @uses $wp_post_types Inserts new post type object into the list
 *
 * @param string  Post type key, must not exceed 20 characters
 * @param array|string  See optional args description above.
 * @return object|WP_Error the registered post type object, or an error object
 */
function learndash_notifications_register_post_type() {
	$labels = [
		'name'               => __( 'Notifications', 'learndash-notifications' ),
		'singular_name'      => __( 'Notification', 'learndash-notifications' ),
		'add_new'            => _x( 'Add New Notification', 'learndash-notifications', 'learndash-notifications' ),
		'add_new_item'       => __( 'Add New Notification', 'learndash-notifications' ),
		'edit_item'          => __( 'Edit Notification', 'learndash-notifications' ),
		'new_item'           => __( 'New Notification', 'learndash-notifications' ),
		'view_item'          => __( 'View Notification', 'learndash-notifications' ),
		'search_items'       => __( 'Search Notifications', 'learndash-notifications' ),
		'not_found'          => __( 'No Notification found', 'learndash-notifications' ),
		'not_found_in_trash' => __( 'No Notification found in Trash', 'learndash-notifications' ),
		'parent_item_colon'  => __( 'Parent Notification:', 'learndash-notifications' ),
		'menu_name'          => __( 'Notifications', 'learndash-notifications' ),
	];

	$args = [
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => __( 'Notifications created for LearnDash.', 'learndash-notifications' ),
		'taxonomies'          => [],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'show_in_admin_bar'   => false,
		'menu_icon'           => null,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite'             => true,
		'capability_type'     => 'post',
		'capabilities'        => [],
		'map_meta_cap'        => true,
		'supports'            => [
			'title',
			'editor',
		],
	];

	register_post_type( 'ld-notification', $args );
}

add_action( 'init', 'learndash_notifications_register_post_type' );

/**
 * Change notification updated messages
 *
 * @param array $messages Existing messages
 * @return array            New updated messages
 */
function learndash_notifications_post_updated_messages( $messages ) {
	$messages['ld-notification'] = [
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Notification updated.' ),
		2  => __( 'Custom field updated.' ),
		3  => __( 'Custom field deleted.' ),
		4  => __( 'Notification updated.' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Notification restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Notification published.' ),
		7  => __( 'Notification saved.' ),
		8  => __( 'Notification submitted.' ),
		9  => '',
		10 => __( 'Notification draft updated.' ),
	];

	return $messages;
}

add_filter( 'post_updated_messages', 'learndash_notifications_post_updated_messages' );

/**
 * Set or unset columns used in the post type
 *
 * @param array $columns Array of available columns
 * @return array          Array of new modified columns
 */
function learndash_notifications_post_type_columns( $columns ) {
	unset( $columns['date'] );
	$columns = array_merge(
		$columns,
		[
			'course'  => LearnDash_Custom_Label::get_label( 'course' ),
			'lesson'  => LearnDash_Custom_Label::get_label( 'lesson' ),
			'topic'   => LearnDash_Custom_Label::get_label( 'topic' ),
			'quiz'    => LearnDash_Custom_Label::get_label( 'quiz' ),
			'trigger' => __( 'Trigger', 'learndash-notifications' ),
		]
	);
	return $columns;
}

add_filter( 'manage_ld-notification_posts_columns', 'learndash_notifications_post_type_columns' );

/**
 * Add custom columns to notifications post screen
 *
 * @param string $column  Column name
 * @param int    $post_id Id of a post
 */
function learndash_notifications_post_type_custom_columns( $column, $post_id ) {
	$course_label = LearnDash_Custom_Label::get_label( 'course' );
	$lesson_label = LearnDash_Custom_Label::get_label( 'lesson' );
	$topic_label  = LearnDash_Custom_Label::get_label( 'topic' );
	$quiz_label   = LearnDash_Custom_Label::get_label( 'quiz' );

	$post           = get_post( $post_id );
	$model          = new Notification( $post );
	$printed_object = '';

	switch ( $column ) {
		case 'course':
			if ( in_array( $model->trigger, [ 'enroll_group', 'essay_graded' ] ) ) {
				$printed_object = '-';
				break;
			}

			if ( in_array( 'all', $model->course_id ) ) {
				$printed_object = sprintf( _x( 'Any %s', 'Courses label', 'learndash-notifications' ), $course_label );
			} else {
				$printed_object = [];
				foreach ( $model->course_id as $course_id ) {
					$course = get_post( $course_id );

					if ( is_object( $course ) ) {
						$printed_object[] = $course->post_title . ' (ID: ' . $course->ID . ')';
					}
				}
			}
			break;

		case 'lesson':
			if ( in_array( $model->trigger, [ 'enroll_group', 'enroll_course', 'essay_graded', 'not_logged_in', 'course_expires' ] ) ) {
				$printed_object = '-';
				break;
			}

			if ( in_array( 'all', $model->lesson_id ) ) {
				printf( _x( 'Any %s', 'Lessons label', 'learndash-notifications' ), $lesson_label );
			} else {
				$printed_object = [];
				foreach ( $model->lesson_id as $lesson_id ) {
					$lesson = get_post( $lesson_id );

					if ( is_object( $lesson ) ) {
						$printed_object[] = $lesson->post_title . ' (ID: ' . $lesson->ID . ')';
					}
				}
			}
			break;

		case 'topic':
			if ( in_array( $model->trigger, [ 'enroll_group', 'enroll_course', 'essay_graded', 'not_logged_in', 'complete_lesson', 'lesson_available', 'course_expires' ] ) ) {
				echo '-';
				break;
			}

			if ( in_array( 'all', $model->topic_id ) ) {
				printf( _x( 'Any %s', 'Lessons label', 'learndash-notifications' ), $topic_label );
			} else {
				$printed_object = [];
				foreach ( $model->topic_id as $topic_id ) {
					$topic = get_post( $topic_id );

					if ( is_object( $topic ) ) {
						$printed_object[] = $topic->post_title . ' (ID: ' . $topic->ID . ')';
					}
				}
			}
			break;

		case 'quiz':
			if ( in_array( $model->trigger, [ 'enroll_group', 'enroll_course', 'upload_assignment', 'approve_assignment', 'essay_graded', 'not_logged_in', 'complete_lesson', 'complete_topic', 'lesson_available', 'course_expires' ] ) ) {
				echo '-';
				break;
			}

			if ( in_array( 'all', $model->quiz_id ) ) {
				printf( _x( 'Any %s', 'Lessons label', 'learndash-notifications' ), $quiz_label );
			} else {
				$printed_object = [];
				foreach ( $model->quiz_id as $quiz_id ) {
					$quiz = get_post( $quiz_id );

					if ( is_object( $quiz ) ) {
						$printed_object[] = $quiz->post_title . ' (ID: ' . $quiz->ID . ')';
					}
				}
			}
			break;

		case 'trigger':
			$settings       = learndash_notifications_get_meta_box_settings();
			$printed_object = $settings['trigger']['value'][ $model->trigger ];
			break;
	}

	if ( is_string( $printed_object ) ) {
		echo esc_html( $printed_object );
	} elseif ( is_array( $printed_object ) ) {
		$total         = count( $printed_object );
		$max_displayed = 3;
		$not_displayed = $total - $max_displayed;

		$output = '';
		if ( ! empty( $printed_object ) ) {
			$printed_object = array_slice( $printed_object, 0, 3, true );

			$output .= '<ul class="learndash-object-list">';
			foreach ( $printed_object as $object_string ) {
				$output .= '<li>' . $object_string . '</li>';
			}
			$output .= '</ul>';
		}

		if ( $not_displayed > 0 ) {
			printf( _nx( '%1$s %2$sand %3$d other%4$s', '%1$s %2$s and %3$d others%4$s', $not_displayed, 'After mentioning object one by one', 'learndash-notifications' ), $output, '<p class="learndash-other-description">', $not_displayed, '</p>' );
		} else {
			echo $output;
		}
	}
}

add_filter( 'manage_ld-notification_posts_custom_column', 'learndash_notifications_post_type_custom_columns', 10, 2 );

function learndash_notifications_hide_months_dropdown() {
	global $typenow;
	if ( $typenow != 'ld-notification' ) {
		return;
	}

	add_filter( 'months_dropdown_results', '__return_empty_array' );
}

add_action( 'admin_init', 'learndash_notifications_hide_months_dropdown' );

/**
 * Output filter dropdown on admin page
 *
 * @param string $post_type Current post type name
 */
function learndash_notifications_admin_posts_filter( $post_type ) {
	if ( $post_type != 'ld-notification' ) {
		return;
	}

	$filters            = [];
	$filters['courses'] = [];
	$filters['lessons'] = [];
	$filters['topics']  = [];
	$filters['quizzes'] = [];

	if ( isset( $_GET['course_id'] ) ) {
		$filters['courses'][] = get_post( intval( $_GET['course_id'] ) );
	}

	if ( isset( $_GET['lesson_id'] ) ) {
		$filters['lessons'][] = get_post( intval( $_GET['lesson_id'] ) );
	}

	if ( isset( $_GET['topic_id'] ) ) {
		$filters['topics'][] = get_post( intval( $_GET['topic_id'] ) );
	}

	if ( isset( $_GET['quiz_id'] ) ) {
		$filters['quizzes'][] = get_post( intval( $_GET['quiz_id'] ) );
	}

	$filters_key            = [];
	$filters_key['courses'] = 'course';
	$filters_key['lessons'] = 'lesson';
	$filters_key['topics']  = 'topic';
	$filters_key['quizzes'] = 'quiz';

	foreach ( $filters as $key => $filter ) {
		$class = '';

		if ( $key !== 'courses' ) {
			$class .= ' disabled-child';
		}

		echo '<select name="' . $filters_key[ $key ] . '_id" id="' . $filters_key[ $key ] . '_id" class="postform notifications-filter select2 ' . $class . ' ">';
		echo '<option value="">' . sprintf( __( 'Select %s', 'learndash-notifications' ), LearnDash_Custom_Label::get_label( $filters_key[ $key ] ) ) . '</option>';
		echo '<option value="all"' . ( isset( $_GET[ $filters_key[ $key ] . '_id' ] ) && $_GET[ $filters_key[ $key ] . '_id' ] == 'all' ? ' selected="selected"' : '' ) . '>' . sprintf( __( 'Any %s', 'learndash-notifications' ), LearnDash_Custom_Label::get_label( $filters_key[ $key ] ) ) . '</option>';

		foreach ( $filter as $post ) {
			if ( ! empty( $post ) ) {
				echo '<option value="' . $post->ID . '"' . ( isset( $_GET[ $filters_key[ $key ] . '_id' ] ) && $_GET[ $filters_key[ $key ] . '_id' ] == $post->ID ? ' selected="selected"' : '' ) . '>' . $post->post_title . '</option>';
			}
		}

		echo '</select>';
	}

	// Output triggers filter dropdown
	$triggers = learndash_notifications_get_meta_box_settings();
	$triggers = $triggers['trigger']['value'];
	unset( $triggers[0] );

	echo '<select name="trigger_id" id="trigger_id" class="postform notifications-filter">';
	echo '<option value="">' . __( 'Select Trigger', 'learndash-notifications' ) . '</option>';

	foreach ( $triggers as $k => $t ) {
		echo '<option value="' . $k . '"' . ( isset( $_GET['trigger_id'] ) && $_GET['trigger_id'] == $k ? ' selected="selected"' : '' ) . '>' . $t . '</option>';
	}

	echo '</select>';
}

add_action( 'restrict_manage_posts', 'learndash_notifications_admin_posts_filter', 10, 2 );

/**
 * Filter notification CPT posts
 *
 * @param object $query WP query object. Passed by reference.
 */
function learndash_notifications_admin_post_filter_query( $query ) {
	global $pagenow;
	$filters = [ 'course', 'lesson', 'topic', 'quiz' ];

	foreach ( $filters as $filter ) {
		if ( is_admin() && $pagenow === 'edit.php' && ! empty( $_GET[ $filter . '_id' ] ) && ( $query->query['post_type'] === 'ld-notification' ) ) {
			if ( $_GET[ $filter . '_id' ] === 'all' ) {
				continue;
			}

			$query->query_vars['meta_query'][] = [
				'key'     => '_ld_notifications_' . $filter . '_id',
				'value'   => sanitize_text_field( $_GET[ $filter . '_id' ] ),
				'compare' => 'LIKE',
			];
		}
	}

	if ( is_admin() && $pagenow === 'edit.php' && ! empty( $_GET['trigger_id'] ) && ( $query->query['post_type'] === 'ld-notification' ) ) {
		$query->query_vars['meta_query'][] = [
			'key'   => '_ld_notifications_trigger',
			'value' => $_GET['trigger_id'],
		];
	}
}

add_filter( 'parse_query', 'learndash_notifications_admin_post_filter_query', 100, 2 );
