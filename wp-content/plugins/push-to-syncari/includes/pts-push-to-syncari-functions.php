<?php
/**
 * This file is used for writing all the re-usable custom functions.
 *
 * @since   1.0.0
 * @package Sync_Grants
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'pts_get_users' ) ) {
	/**
	 * Get the users.
	 *
	 * @param int    $paged Paged value.
	 * @param int    $number Users per request.
	 * @return object
	 * @since 1.0.0
	 */
	function pts_get_users( $paged = 1, $number = -1 ) {
		// Prepare the arguments array.
		$args = array(
			'paged'  => $paged,
			'number' => $number,
			'fields' => 'ids',
		);

		return new WP_User_Query( $args );
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'pts_push_user' ) ) {
	/**
	 * Push the user data to the syncari table.
	 *
	 * @param int $user_id User ID.
	 * @since 1.0.0
	 */
	function pts_push_user( $user_id ) {
		global $wpdb;
		$syncari_table_name  = $wpdb->prefix . 'syncari_data';
		$users_table_name    = $wpdb->prefix . 'users';
		$user_info           = get_user_meta( $user_id, 'user_all_info', true );
		$show_in_front       = get_user_meta( $user_id, 'moc_show_in_frontend', true );
		$show_in_front       = ( ! empty( $show_in_front ) && 'yes' === $show_in_front ) ? 'yes' : 'no';
		$profile_completed   = get_user_meta( $user_id, 'profile-setup-completed', true );
		$profile_completed   = ( ! empty( $profile_completed ) && 'yes' === $profile_completed ) ? 'yes' : 'no';
		$industry_experience = get_user_meta( $user_id, 'industry_experience', true );
		$industry_experience = ( ! empty( $industry_experience[0] ) ) ? $industry_experience[0] : '';
		$user_avatar         = get_user_meta( $user_id, 'wp_user_avatar', true );
		$user_avatar         = ( ! empty( $user_avatar ) ) ? wp_get_attachment_url( $user_avatar ) : '';
		$user_email          = $wpdb->get_row( "SELECT `user_email` FROM `{$users_table_name}` WHERE `ID` = {$user_id}", ARRAY_A ); // Get user email.
		$user_email          = ( ! empty( $user_email['user_email'] ) ) ? $user_email['user_email'] : '';

		// Prepare the array to push into the syncari table.
		$syncari_table_keys = array(
			'first_name'              => get_user_meta( $user_id, 'first_name', true ),
			'last_name'               => get_user_meta( $user_id, 'last_name', true ),
			'job_seeker_status'       => get_user_meta( $user_id, 'job_seeker_details', true ),
			'email_address'           => $user_email,
			'show_in_frontend'        => $show_in_front,
			'company_video'           => get_user_meta( $user_id, '_company_video', true ),
			'company_twitter'         => get_user_meta( $user_id, '_company_twitter', true ),
			'company_website'         => ( ! empty( $user_info['user_basic_info']['user_website'] ) ) ? $user_info['user_basic_info']['user_website'] : '',
			'company_tagline'         => get_user_meta( $user_id, '_company_tagline', true ),
			'company_name'            => get_user_meta( $user_id, '_company_name', true ),
			'company_logo'            => get_user_meta( $user_id, '_company_logo', true ),
			'your_primary_map'        => get_user_meta( $user_id, 'what_is_your_primary_map', true ),
			'professional_title'      => get_user_meta( $user_id, 'profetional_title', true ),
			'community_badges'        => maybe_serialize( get_user_meta( $user_id, 'moc_community_badges', true ) ),
			'job_type'                => get_user_meta( $user_id, 'job_type', true ),
			'reference'               => get_user_meta( $user_id, 'who_referred_you', true ),
			'user_info'               => $user_info,
			'industry_experience'     => $industry_experience,
			'profile_setup_completed' => $profile_completed,
			'experience_years'        => get_user_meta( $user_id, 'experience_years', true ),
			'experience'              => get_user_meta( $user_id, 'experience', true ),
			'github'                  => pts_get_user_social_handle( $user_id, 'github' ),
			'instagram'               => pts_get_user_social_handle( $user_id, 'insta' ),
			'youtube'                 => pts_get_user_social_handle( $user_id, 'youtube' ),
			'vk'                      => pts_get_user_social_handle( $user_id, 'vk' ),
			'linkedin'                => pts_get_user_social_handle( $user_id, 'linkedin' ),
			'twitter'                 => pts_get_user_social_handle( $user_id, 'twitter' ),
			'facebook'                => pts_get_user_social_handle( $user_id, 'facebook' ),
			'user_avatar'             => $user_avatar,
			'active_membership'       => pts_get_user_membership( $user_id ),
			'selected_certifications' => maybe_serialize( pts_get_selected_certifications( $user_id ) ),
			'last_update_timestamp'   => gmdate( 'Y-m-d H:i:s' ),
		);

		/**
		 * Check if the data already is present in the syncari table.
		 * Prepare the query.
		 */
		$syncari_existing_data_query = "SELECT * FROM `{$syncari_table_name}` WHERE `user_ID` = {$user_id}";
		$syncari_existing_data       = $wpdb->get_results( $syncari_existing_data_query, ARRAY_A );

		// If the existing data is empty, the user data isn't available.
		if ( empty( $syncari_existing_data ) ) {
			// Create the user entry.
			$syncari_table_keys['user_ID'] = $user_id;
			$wpdb->insert( $syncari_table_name, $syncari_table_keys );
		} else {
			// Update the user entry.
			$wpdb->update(
				$syncari_table_name,
				$syncari_table_keys,
				array(
					'user_ID' => $user_id,
				)
			);
		}
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'pts_get_user_membership' ) ) {
	/**
	 * Get the user membership.
	 *
	 * @param int $user_id User ID.
	 * @since 1.0.0
	 */
	function pts_get_user_membership( $user_id ) {
		$wc_active_memberships        = wc_memberships_get_user_active_memberships( $user_id ); // Get the user memberships.
		$user_active_membership_slugs = array();
		$membership_status            = 'INACTIVE';

		// If there are any memberships assigned.
		if ( ! empty( $wc_active_memberships ) && is_array( $wc_active_memberships ) ) {
			// Loop through the user assigned memberships.
			foreach ( $wc_active_memberships as $active_membership ) {

				// Collect the plans, if the membership is active.
				if ( ! empty( $active_membership->status ) && 'wcm-active' === $active_membership->status ) {
					$user_active_membership_slugs[] = $active_membership->plan->slug;
				}
			}
		}

		// Filter away the unwanted values from the array.
		$user_active_membership_slugs = ( ! empty( $user_active_membership_slugs ) && is_array( $user_active_membership_slugs ) ) ? array_unique( array_filter( $user_active_membership_slugs ) ) : $user_active_membership_slugs;
		$membership_status            = ( ( ! empty( $user_active_membership_slugs ) ) && in_array( 'free-membership', $user_active_membership_slugs, true ) && 1 == count( $user_active_membership_slugs ) ) ? 'FREE' : 'PRO';

		return $membership_status;
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'pts_get_user_social_handle' ) ) {
	/**
	 * Get the user membership.
	 *
	 * @param int $user_id User ID.
	 * @since 1.0.0
	 */
	function pts_get_user_social_handle( $user_id, $social_media ) {
		$user_info = get_user_meta( $user_id, 'user_all_info', true );

		// Return, if the user info is not set.
		if ( empty( $user_info ) || ! is_array( $user_info ) ) {
			return;
		}

		// Return, if the social media handles aren't set.
		if ( empty( $user_info['user_basic_info']['social_media_arr'] ) || ! is_array( $user_info['user_basic_info']['social_media_arr'] ) ) {
			return;
		}

		// Find the correct social media handle.
		$social_media_handle_cols   = array_column( $user_info['user_basic_info']['social_media_arr'], 'tag' );
		$requested_social_media_key = array_search( $social_media, $social_media_handle_cols, true );

		// Return, if the requested handle data is not available.
		if ( false === $requested_social_media_key ) {
			return;
		}

		return $user_info['user_basic_info']['social_media_arr'][ $requested_social_media_key ]['val'];
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'pts_get_selected_certifications' ) ) {
	/**
	 * Get the user membership.
	 *
	 * @param int $user_id User ID.
	 * @since 1.0.0
	 */
	function pts_get_selected_certifications( $user_id ) {
		$user_info = get_user_meta( $user_id, 'user_all_info', true );

		// Return, if the user info is not set.
		if ( empty( $user_info ) || ! is_array( $user_info ) ) {
			return;
		}

		// Return, if there are no selected certifications.
		if ( empty( $user_info['moc_certificates'] ) || ! is_array( $user_info['moc_certificates'] ) ) {
			return;
		}

		$user_certifications = array();

		// Find the certification attachment ID and loop through them.
		foreach ( $user_info['moc_certificates'] as $certificate_id ) {
			// $image = wp_get_attachment_image_src( get_post_thumbnail_id( $certificate_id ), 'single-post-thumbnail' );
			$user_certifications[] = get_the_title( $certificate_id );
		}

		return $user_certifications;
	}
}
