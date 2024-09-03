<?php

/**
 * the_archive_title handler function.
 *
 * @param boolean $include_context Include Context Boolean.
 * @return string|void $title Return final title
 */
function seedprod_lite_get_page_title( $include_context = true, $show_home_title = 'yes', $fallback = '' ) {
	if ( is_home() && 'yes' !== $show_home_title ) {
		if ( $fallback ) {
			return wp_kses_post( $fallback );
		}
		return;
	}

	$title = '';

	if ( is_singular() ) {
		$title = get_the_title();

		if ( $include_context ) {
			$post_type_obj = get_post_type_object( get_post_type() );
			$title         = sprintf( '%s: %s', $post_type_obj->labels->singular_name, $title );
		}
	} elseif ( is_search() ) {
		/* translators: %s: Search term. */
		$title = sprintf( esc_html__( 'Search Results for: %s', 'coming-soon' ), get_search_query() );

		if ( get_query_var( 'paged' ) ) {
			/* translators: %s is the page number. */
			$title .= sprintf( esc_html__( '&nbsp;&ndash; Page %s', 'coming-soon' ), get_query_var( 'paged' ) );
		}
	} elseif ( is_category() ) {
		$title = single_cat_title( '', false );

		if ( $include_context ) {
			/* translators: Category archive title. 1: Category name */
			$title = sprintf( esc_html__( 'Category: %s', 'coming-soon' ), $title );
		}
	} elseif ( is_tag() ) {
		$title = single_tag_title( '', false );
		if ( $include_context ) {
			/* translators: Tag archive title. 1: Tag name */
			$title = sprintf( esc_html__( 'Tag: %s', 'coming-soon' ), $title );
		}
	} elseif ( is_author() ) {
		$title = '<span class="vcard">' . get_the_author() . '</span>';

		if ( $include_context ) {
			/* translators: Author archive title. 1: Author name */
			$title = sprintf( esc_html__( 'Author: %s', 'coming-soon' ), $title );
		}
	} elseif ( is_year() ) {
		$title = get_the_date( _x( 'Y', 'yearly archives date format', 'coming-soon' ) );

		if ( $include_context ) {
			/* translators: Yearly archive title. 1: Year */
			$title = sprintf( esc_html__( 'Year: %s', 'coming-soon' ), $title );
		}
	} elseif ( is_month() ) {
		$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'coming-soon' ) );

		if ( $include_context ) {
			/* translators: Monthly archive title. 1: Month name and year */
			$title = sprintf( esc_html__( 'Month: %s', 'coming-soon' ), $title );
		}
	} elseif ( is_day() ) {
		$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'coming-soon' ) );

		if ( $include_context ) {
			/* translators: Daily archive title. 1: Date */
			$title = sprintf( esc_html__( 'Day: %s', 'coming-soon' ), $title );
		}
	} elseif ( is_tax( 'post_format' ) ) {
		if ( is_tax( 'post_format', 'post-format-aside' ) ) {
			$title = _x( 'Asides', 'post format archive title', 'coming-soon' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			$title = _x( 'Galleries', 'post format archive title', 'coming-soon' );
		} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
			$title = _x( 'Images', 'post format archive title', 'coming-soon' );
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			$title = _x( 'Videos', 'post format archive title', 'coming-soon' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			$title = _x( 'Quotes', 'post format archive title', 'coming-soon' );
		} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
			$title = _x( 'Links', 'post format archive title', 'coming-soon' );
		} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
			$title = _x( 'Statuses', 'post format archive title', 'coming-soon' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			$title = _x( 'Audio', 'post format archive title', 'coming-soon' );
		} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
			$title = _x( 'Chats', 'post format archive title', 'coming-soon' );
		}
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );

		if ( $include_context ) {
			/* translators: Post type archive title. 1: Post type name */
			$title = sprintf( esc_html__( 'Archives: %s', 'coming-soon' ), $title );
		}
	} elseif ( is_tax() ) {
		$title = single_term_title( '', false );

		if ( $include_context ) {
			$tax = get_taxonomy( get_queried_object()->taxonomy );
			/* translators: Taxonomy term archive title. 1: Taxonomy singular name, 2: Current taxonomy term */
			$title = sprintf( esc_html__( '%1$s: %2$s', 'coming-soon' ), $tax->labels->singular_name, $title );
		}
	} elseif ( is_archive() ) {
		$title = esc_html__( 'Archives', 'coming-soon' );
	} elseif ( is_404() ) {
		$title = esc_html__( 'Page Not Found', 'coming-soon' );
	}

	// Fallback if value is empty.
	if ( empty( $title ) && $fallback ) {
		$title = wp_kses_post( $fallback );
	}

	return $title;
}

/**
 * Get current datetime
 *
 * @param array $settings Contains settings.
 * @return string
 */
function seedprod_lite_get_current_datetime( $settings = array(), $fallback = '') {
	if ( 'custom' === $settings['date_format'] ) {
		$format = $settings['custom_format'];
	} else {
		$date_format = $settings['date_format'];
		$time_format = $settings['time_format'];
		$format      = '';

		if ( 'default' === $date_format ) {
			$date_format = get_option( 'date_format' );
		}

		if ( 'default' === $time_format ) {
			$time_format = get_option( 'time_format' );
		}

		if ( $date_format ) {
			$format   = $date_format;
			$has_date = true;
		} else {
			$has_date = false;
		}

		if ( $time_format ) {
			if ( $has_date ) {
				$format .= ' ';
			}
			$format .= $time_format;
		}
	}

	$datetime = date_i18n( $format );

	// Fallback if value is empty.
	if ( empty( $datetime ) && $fallback ) {
		$datetime = wp_kses_post( $fallback );
	}

	return $datetime;
}

/**
 * Get archive meta
 *
 * @param string $meta_key Meta key.
 * @return string
 */
function seedprod_lite_get_archive_meta( $meta_key = '', $fallback = '' ) {
	$output = '';

	if ( ! empty( $meta_key ) && ( is_category() || is_tax() ) ) {
		$output = get_term_meta( get_queried_object_id(), $meta_key, true );
	} elseif ( ! empty( $meta_key ) && is_author() ) {
		$output = get_user_meta( get_queried_object_id(), $meta_key, true );
	}

	// Fallback if value is empty.
	if ( empty( $output ) && $fallback ) {
		$output = wp_kses_post( $fallback );
	}

	return $output;
}

/**
 * Get request paramet
 *
 * @param array $settings Settings array.
 * @return string
 */
function seedprod_lite_get_request_parameter( $settings = array(), $fallback = '' ) {
	$request_type = isset( $settings['request_type'] ) ? strtoupper( $settings['request_type'] ) : false;
	$param_name   = isset( $settings['param_name'] ) ? $settings['param_name'] : false;
	$value        = '';

	if ( ! $param_name || ! $request_type ) {
		return '';
	}

	switch ( $request_type ) {
		case 'POST':
			if ( ! isset( $_POST[ $param_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return '';
			}
			$value = sanitize_text_field( wp_unslash( $_POST[ $param_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			break;
		case 'GET':
			if ( ! isset( $_GET[ $param_name ] ) ) { // phpcs:ignore
				return '';
			}
			$value = $_GET[ $param_name ]; // phpcs:ignore
			break;
		case 'QUERY_VAR':
			$value = get_query_var( $param_name );
			break;
	}

	// Fallback if value is empty.
	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	echo htmlentities( wp_kses_post( $value ) );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Get shortcode
 *
 * @param string $shortcode Shortcode to be run.
 * @return string|void
 */
function seedprod_lite_get_shortcode( $shortcode = '', $fallback = '' ) {
	if ( empty( $shortcode ) ) {
		return;
	}

	$shortcode_string = wp_unslash( $shortcode );
	$value            = do_shortcode( $shortcode_string );

	// Fallback if value is empty.
	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	echo wp_kses_post( $value );
}

/**
 * Get user info.
 *
 * @param string $type     Type of user info.
 * @param string $meta_key Meta Key.
 * @return string|void
 */
function seedprod_lite_get_user_info( $type = '', $meta_key = '', $fallback = '') {
	$user = wp_get_current_user();
	if ( empty( $type ) || 0 === $user->ID ) {
		return wp_kses_post( $fallback );
	}

	$type = strtolower( $type );
	$meta_key = strtolower( $meta_key );

	if ( 'meta' === $type && empty( $meta_key ) ) {
		return wp_kses_post( $fallback );
	}

	if ( 'meta' === $type ) {
		// Set meta key as the type and handle below
		$type = $meta_key;
	}

	$value = '';

	// Get usermeta.
	$disallowed_meta = array(
		'user_pass',
		'pass',
		'activation_key',
		'user_activation_key',
	);

	if ( in_array( $type, $disallowed_meta, true ) ) {
		return wp_kses_post( $fallback );
	}

	switch ( $type ) {
		case 'login':
		case 'email':
		case 'url':
		case 'nicename':
			$field = 'user_' . $type;
			$value = isset( $user->$field ) ? $user->$field : '';
			break;
		case 'id':
			$value = $user->ID;
			break;
		case 'display_name':
			$value = isset( $user->$type ) ? $user->$type : '';
			break;
		case 'first_name':
		case 'last_name':
		case 'nickname':
		case 'description':
			$value = get_user_meta( $user->ID, $type, true );
			break;
		default:
			// Handle custom meta keys.
			$value = get_user_meta( $user->ID, $type, true ) ? get_user_meta( $user->ID, 'user_' . $type, true ) : '';

			if ( empty( $value ) ) {
				// Check meta key in User object.
				$field = 'user_' . $type;
				$value = isset( $user->$field ) ? $user->$field : '';
				if ( empty( $value ) ) {
					// Check meta key in User object.
					$value = isset( $user->$meta_key ) ? $user->$meta_key : '';
				}
			}
			break;
	}

	// Fallback if value is empty.
	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	echo wp_kses_post( $value );
}

/**
 * Fetch featured image post.
 *
 * @return WP_POST
 */
function seedprod_lite_fetch_attachment_post() {
	$id = get_post_thumbnail_id();

	if ( ! $id ) {
		return false;
	}

	return get_post( $id );
}

/**
 * Get featured image data.
 *
 * @param string $attachment_data Which image data to return.
 * @return void|string
 */
function seedprod_lite_get_featured_image_data( $attachment_data = '', $fallback = '' ) {
	$attachment = seedprod_lite_fetch_attachment_post();

	if ( ! $attachment ) {
		// Fallback if value is empty.
		if ( $fallback ) {
			return wp_kses_post( $fallback );
		}
		return;
	}

	$value = '';

	switch ( $attachment_data ) {
		case 'alt':
			$value = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
			break;
		case 'caption':
			$value = $attachment->post_excerpt;
			break;
		case 'description':
			$value = $attachment->post_content;
			break;
		case 'href':
			$value = get_permalink( $attachment->ID );
			break;
		case 'src':
			$value = $attachment->guid;
			break;
		case 'title':
			$value = $attachment->post_title;
			break;
	}

	// Fallback if value is empty.
	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	echo wp_kses_post( $value );
}

/**
 * Get author information.
 *
 * @param string $key Author meta to fetch.
 * @return void|string
 */
function seedprod_lite_get_author_info( $key = '', $fallback = '' ) {
	if ( empty( $key ) ) {
		return;
	}

	$value = get_the_author_meta( $key );

	// Fallback if value is empty.
	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	echo wp_kses_post( $value );
}

/**
 * Get author name.
 *
 * @return string
 */
function seedprod_lite_get_author_name( $fallback = '' ) {
	$value = get_the_author();

	// Fallback if value is empty.
	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	echo wp_kses_post( $value );
}

/**
 * Get comments number
 *
 * @param array $settings Settings array.
 * @return string
 */
function seedprod_lite_get_comments_number( $settings = array(), $fallback = '') {
	$comments_number = get_comments_number();

	if ( ! $comments_number ) {
		$count = $settings['format_no_comments'];
	} elseif ( '1' === $comments_number ) {
		$count = $settings['format_one_comments'];
	} else {
		$count = strtr(
			$settings['format_many_comments'],
			array(
				'{number}' => number_format_i18n( $comments_number ),
			)
		);
	}

	if ( 'comments_link' === $settings['link_to'] ) {
		$count = sprintf( '<a href="%s">%s</a>', get_comments_link(), $count );
	}

	// Fallback if value is empty.
	if ( empty( $count ) && $fallback ) {
		$count = $fallback;
	}

	echo wp_kses_post( $count );
}

/**
 * Fetch ACF group fields.
 *
 * @return array
 */
function seedprod_lite_get_acf_option_fields() {
	// ACF >= 5.0.0
	if ( function_exists( 'acf_get_field_groups' ) ) {
		$acf_groups = acf_get_field_groups();
	} else {
		$acf_groups = apply_filters( 'acf/get_field_groups', array() ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	}

	$groups = array();

	$options_page_groups_ids = array();

	if ( function_exists( 'acf_options_page' ) ) {
		$pages = acf_options_page()->get_pages();
		foreach ( $pages as $slug => $page ) {
			$options_page_groups = acf_get_field_groups(
				array(
					'options_page' => $slug,
				)
			);

			foreach ( $options_page_groups as $options_page_group ) {
				$options_page_groups_ids[] = $options_page_group['ID'];
			}
		}
	}

	foreach ( $acf_groups as $acf_group ) {
		// ACF >= 5.0.0
		if ( function_exists( 'acf_get_fields' ) ) {
			if ( isset( $acf_group['ID'] ) && ! empty( $acf_group['ID'] ) ) {
				$fields = acf_get_fields( $acf_group['ID'] );
			} else {
				$fields = acf_get_fields( $acf_group );
			}
		} else {
			$fields = apply_filters( 'acf/field_group/get_fields', array(), $acf_group['id'] ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		$options = array();

		if ( ! is_array( $fields ) ) {
			continue;
		}

		$has_option_page_location = in_array( $acf_group['ID'], $options_page_groups_ids, true );
		$is_only_options_page     = $has_option_page_location && 1 === count( $acf_group['location'] );

		foreach ( $fields as $field ) {
			// Use group ID for unique keys
			if ( $has_option_page_location ) {
				$key = 'options:' . $field['name'];

				$options[ $field['name'] ] = array(
					'label'  => $field['label'],
					'key'    => $key,
					'name'   => esc_html__( 'Options', 'coming-soon' ) . ':' . $field['label'],
					'prefix' => $field['prefix'],
					'type'   => $field['type'],
				);

				if ( $is_only_options_page ) {
					continue;
				}
			}

			$options[ $field['name'] ] = array(
				'label'  => $field['label'],
				'key'    => $field['key'] . ':' . $field['name'],
				'name'   => $field['name'],
				'prefix' => $field['prefix'],
				'type'   => $field['type'],
			);
		}

		if ( empty( $options ) ) {
			continue;
		}

		if ( 1 === count( $options ) ) {
			$options = array( -1 => ' -- ' ) + $options;
		}

		$groups[] = array(
			'label'   => $acf_group['title'],
			'options' => $options,
		);
	}

	return $groups;
}

/**
 * Get field data for a given ACF field key.
 *
 * @param string $key ACF unique field key.
 * @return array
 */
function seedprod_lite_get_acf_tag_value_field( $key = '' ) {
	if ( ! empty( $key ) ) {
		list( $field_key, $meta_key ) = explode( ':', $key );

		if ( 'options' === $field_key ) {
			$field = get_field_object( $meta_key, $field_key );
		} else {
			$field = get_field_object( $field_key, get_queried_object() );
		}

		return array( $field, $meta_key );
	}

	return array();
}

/**
 * Get ACF field value.
 *
 * @param array $settings ACF field array.
 * @return void
 */
function seedprod_lite_get_acf_field_value( $settings = array(), $fallback = '' ) {
	if ( empty( $settings ) ) {
		if ( $fallback ) {
			return wp_kses_post( $fallback );
		}
		return;
	}

	// Get field array value.
	$field_data = seedprod_lite_get_acf_tag_value_field( $settings['field_name'] );

	if ( empty( $field_data ) ) {
		if ( $fallback ) {
			return wp_kses_post( $fallback );
		}
		return;
	}

	$value = '';

	// Arrays to handle field types
	$text_field_types = array(
		'text',
		'textarea',
		'number',
		'email',
		'url',
		'password',
		'wysiwyg',
		'oembed',
		'google_map',
	);

	$url_field_types = array(
		'image',
		'file',
		'gallery',
		'post_object',
		'page_link',
		'relationship',
		'taxonomy',
		'user',
	);

	// Check field type.
	if ( ! empty( $settings['field_type'] ) && 'text' === $settings['field_type'] ) {
		$value = seedprod_lite_get_acf_text_data( $field_data, $fallback );
	} elseif ( ! empty( $settings['field_type'] ) && 'image' === $settings['field_type'] ) {
		$value = seedprod_lite_get_acf_image_data( $field_data, $fallback );
	} elseif ( ! empty( $settings['field_type'] ) && 'color' === $settings['field_type'] ) {
		$value = seedprod_lite_get_acf_color_data( $field_data, $fallback );
	} elseif ( ! empty( $settings['field_type'] ) && 'number' === $settings['field_type'] ) {
		$value = seedprod_lite_get_acf_number_data( $field_data, $fallback );
	} elseif ( ! empty( $settings['field_type'] ) && 'url' === $settings['field_type'] ) {
		$value = seedprod_lite_get_acf_url_data( $field_data );
	} elseif ( ! ! empty( $settings['field_type'] ) && 'gallery' === $settings['field_type'] ) {
		$value = seedprod_lite_get_acf_gallery_data( $field_data );
	}

	echo wp_kses_post( $value );
}

/**
 * Get ACF Image field data.
 *
 * @param array $field_data Array of field description data.
 * @return array $image_data Array of image data.
 */
function seedprod_lite_get_acf_image_data( $field_data = array(), $fallback = '' ) {
	$image_data = array(
		'id'  => null,
		'url' => '',
	);

	list( $field, $meta_key ) = $field_data;

	if ( $field && is_array( $field ) ) {
		$field['return_format'] = isset( $field['save_format'] ) ? $field['save_format'] : $field['return_format'];
		switch ( $field['return_format'] ) {
			case 'object':
			case 'array':
				$value = $field['value'];
				break;
			case 'url':
				$value = array(
					'id'  => 0,
					'url' => $field['value'],
				);
				break;
			case 'id':
				$src   = wp_get_attachment_image_src( $field['value'], $field['preview_size'] );
				$value = array(
					'id'  => $field['value'],
					'url' => $src[0],
				);
				break;
		}
	}

	if ( ! isset( $value ) ) {
		// Field settings has been deleted or not available.
		$value = get_field( $meta_key );
	}

	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	if ( ! empty( $value ) && is_array( $value ) ) {
		// $image_data['id']  = $value['id'];
		$image_data = $value['url'];
	}

	return $image_data;
}

/**
 * Get ACF color data.
 *
 * @param array $field_data Array of field description data.
 * @return string $value Color data string.
 */
function seedprod_lite_get_acf_color_data( $field_data = array(), $fallback = '' ) {
	list( $field, $meta_key ) = $field_data;

	if ( $field ) {
		$value = $field['value'];
	} else {
		// Field settings has been deleted or not available.
		$value = get_field( $meta_key );
	}

	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	return $value;
}

/**
 * Get ACF Text data.
 *
 * @param array $field_data Array of field description data.
 * @return string $value Text data string.
 */
function seedprod_lite_get_acf_text_data( $field_data = array(), $fallback = '' ) {
	list( $field, $meta_key ) = $field_data;

	if ( $field && ! empty( $field['type'] ) ) {
		$value = $field['value'];

		switch ( $field['type'] ) {
			case 'radio':
				if ( isset( $field['choices'][ $value ] ) ) {
					$value = $field['choices'][ $value ];
				}
				break;
			case 'select':
				// Use as array for `multiple=true` or `return_format=array`.
				$values = (array) $value;

				foreach ( $values as $key => $item ) {
					if ( isset( $field['choices'][ $item ] ) ) {
						$values[ $key ] = $field['choices'][ $item ];
					}
				}

				$value = implode( ', ', $values );

				break;
			case 'checkbox':
				$value  = (array) $value;
				$values = array();
				foreach ( $value as $item ) {
					if ( isset( $field['choices'][ $item ] ) ) {
						$values[] = $field['choices'][ $item ];
					} else {
						$values[] = $item;
					}
				}

				$value = implode( ', ', $values );

				break;
			case 'oembed':
				// Get from db without formatting.
				$value = seedprod_lite_get_queried_object_meta( $meta_key );
				break;
			case 'google_map':
				$meta  = seedprod_lite_get_queried_object_meta( $meta_key );
				$value = isset( $meta['address'] ) ? $meta['address'] : '';
				break;

			default:
				$value = get_field( $meta_key );
				break;
		} // End switch().
	} else {
		// Field settings has been deleted or not available.
		$value = get_field( $meta_key );
	} // End if().

	// Fallback if value is empty.
	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	return wp_kses_post( $value );
}

/**
 * Get queried object meta.
 *
 * @param string $meta_key Post meta key.
 * @return string $value Meta value key.
 */
function seedprod_lite_get_queried_object_meta( $meta_key = '' ) {
	$value = '';
	if ( is_singular() ) {
		$value = get_post_meta( get_the_ID(), $meta_key, true );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$value = get_term_meta( get_queried_object_id(), $meta_key, true );
	}

	return $value;
}

/**
 * Get ACF Number field data.
 *
 * @param array $field_data Array of field description data.
 * @return string $value Return string.
 */
function seedprod_lite_get_acf_number_data( $field_data = array(), $fallback = '' ) {
	list( $field, $meta_key ) = $field_data;

	if ( $field && ! empty( $field['type'] ) ) {
		$value = $field['value'];
	} else {
		// Field settings has been deleted or not available.
		$value = get_field( $meta_key );
	} // End if().

	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	return wp_kses_post( $value );
}

/**
 * Get ACF Number field data.
 *
 * @param array $settings Array of field description data.
 * @return string|void $value Return string.
 */
function seedprod_lite_get_custom_post_field_value( $settings = array(), $fallback = '' ) {
	$key = str_replace( '\'', '', trim( $settings['key'] ) );

	// Check if key is present.
	if ( empty( $key ) || '' === $key ) {
		// If not present, check if custom key is present.
		if ( $settings['custom_key'] !== '') {
			$key = str_replace( '\'', '', trim( $settings['custom_key'] ) );
		} else {
			// If not present, return.
			return;
		}
	}

	$value = get_post_meta( get_the_ID(), $key, false );

	// Check if value is array.
	$value = is_array( $value ) && isset( $value[0] ) ? $value[0] : '';

	if ( empty( $value ) && $fallback ) {
		$value = $fallback;
	}

	return wp_kses_post( $value );
}

/**
 * Get ACF URL data.
 *
 * @param array $field_data Array of field description data.
 * @return string $value Return string.
 */
function seedprod_lite_get_acf_url_data( $field_data = array() ) {
	list( $field, $meta_key ) = $field_data;

	if ( $field ) {
		$value = $field['value'];

		if ( is_array( $value ) && isset( $value[0] ) ) {
			$value = $value[0];
		}

		if ( $value ) {
			if ( ! isset( $field['return_format'] ) ) {
				$field['return_format'] = isset( $field['save_format'] ) ? $field['save_format'] : '';
			}

			switch ( $field['type'] ) {
				case 'email':
					if ( $value ) {
						$value = 'mailto:' . $value;
					}
					break;
				case 'image':
				case 'file':
					switch ( $field['return_format'] ) {
						case 'array':
						case 'object':
							$value = $value['url'];
							break;
						case 'id':
							if ( 'image' === $field['type'] ) {
								$src   = wp_get_attachment_image_src( $value, 'full' );
								$value = $src[0];
							} else {
								$value = wp_get_attachment_url( $value );
							}
							break;
					}
					break;
				case 'post_object':
				case 'relationship':
					$value = get_permalink( $value );
					break;
				case 'taxonomy':
					$value = get_term_link( $value, $field['taxonomy'] );
					break;
			} // End switch().
		}
	} else {
		// Field settings has been deleted or not available.
		$value = get_field( $meta_key );
	} // End if().

	return wp_kses_post( $value );
}

/**
 * Get ACF Gallery data.
 *
 * @param array $field_data Array of field description data.
 * @return array $images Array of gallery images.
 */
function seedprod_lite_get_acf_gallery_data( $field_data = array() ) {
	$images = array();

	list( $field, $meta_key ) = $field_data;

	if ( $field ) {
		$value = $field['value'];
	} else {
		// Field settings has been deleted or not available.
		$value = get_field( $meta_key );
	}

	$image_ids = array();

	if ( is_array( $value ) && ! empty( $value ) ) {
		foreach ( $value as $image ) {
			array_push( $image_ids, $image['ID'] );
		}
	}

	return '[gallery ids="' . implode( ',', $images ) . '"]';
}

/**
 * Get Dynamic Site Logo.
 *
 * @return string $url Site log URL.
 */
function seedprod_lite_get_dynamic_site_logo() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );

	$url = '';

	if ( $custom_logo_id ) {
		$url = wp_get_attachment_image_src( $custom_logo_id, 'full' )[0];
	}

	return $url;
}

/**
 * Get Dynamic Author Profile Picture.
 *
 * @return string $url Author Profile Picture URL.
 */
function seedprod_lite_get_dynamic_author_profile_picture() {
	return get_avatar_url( (int) get_the_author_meta( 'ID' ) );
}

/**
 * Get Dynamic User Profile Picture.
 *
 * @return string $url User Profile Picture URL.
 */
function seedprod_lite_get_dynamic_user_profile_picture() {
	return get_avatar_url( get_current_user_id() );
}

/**
 * Get post custom keys.
 *
 * @return array
 */
function seedprod_lite_get_post_custom_keys_array() {
	if ( check_ajax_referer( 'seedprod_nonce' ) ) {
		if ( ! current_user_can( apply_filters( 'seedprod_builder_preview_render_capability', 'edit_others_posts' ) ) ) {
			wp_send_json_error();
		}

		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

		$custom_keys = array();
		$custom_keys = get_post_custom_keys( $id );

		// Fetch the keys from the current post.
		// Notes: Not accurate because of how our theme builder works.

		// $args = array(
		// 	'posts_per_page' => 1,
		// 	'post_type'      => 'post',
		// );

		// $the_query   = new WP_Query( $args );
		// $custom_keys = array();

		// if ( $the_query->have_posts() ) {
		// 	while ( $the_query->have_posts() ) {
		// 		$the_query->the_post();
		// 		$custom_keys = get_post_custom_keys();
		// 	}
		// }

		/* Restore original Post Data */
		wp_reset_postdata();

		$options = array(
			'' => esc_html__( 'Select Key', 'coming-soon' ),
		);

		if ( ! empty( $custom_keys ) ) {
			foreach ( $custom_keys as $custom_key ) {
				if ( '_' !== substr( $custom_key, 0, 1 ) ) {
					$options[ $custom_key ] = $custom_key;
				}
			}
		}

		echo wp_kses( wp_json_encode( $options ), 'post' );
		exit;
	}
	exit;
}

if ( defined( 'DOING_AJAX' ) ) {
	add_action( 'wp_ajax_seedprod_lite_get_post_custom_keys_array', 'seedprod_lite_get_post_custom_keys_array' );
}
