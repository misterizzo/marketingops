<?php
/**
 * User More details
 *
 * This template can be overridden by copying it to yourtheme/marketing-ops-core/users/profile-extra-information.php
 *
 * @see         https://wordpress-784994-2704071.cloudwaysapps.com/
 * @author      Adarsh Verma
 * @package     Marketing_Ops_Core
 * @category    Template
 * @since       1.0.0
 * @version     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
get_header();

if ( is_user_logged_in() ) {
	wp_enqueue_editor(); // Enqueue the editor JS.
	wp_enqueue_media();
	global $wpdb;
	$user_id        = get_current_user_id();
	$is_ambassador  = mops_is_user_ambassador( $user_id );
	$source         = filter_input( INPUT_GET, 'source', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	if ( ! $is_ambassador ) {
		echo '<script type="text/javascript">';
      	echo 'window.location.href="'. home_url( 'profile' ) .'";';
      	echo '</script>';
      	echo '<noscript>';
      	echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
      	echo '</noscript>'; exit;
	}

	$all_user_meta             = get_user_meta( $user_id );
	$firstname                 = ! empty( $all_user_meta['first_name'] ) ? $all_user_meta['first_name'][0] : '';
	$lastname                  = ! empty( $all_user_meta['last_name'] ) ? $all_user_meta['last_name'][0] : '';
	$user_display_name         = ! empty( $firstname ) ? $firstname . ' ' . $lastname : $all_user_meta['nickname'][0];
	$user_nice_name            = get_the_author_meta( 'user_nicename', $user_id );
	$sidebar_title             = get_field( 'sidebar_title', 'option' );
	$pro_member_badge          = get_field( 'pro_member_badge', 'option' );
	$author_info               = get_userdata( $user_id );
	$profile_url               = site_url(). '/profile/'.$author_info->data->user_nicename;
	// For Normal blog posts.
	$total_blogs_query     = moc_posts_query_by_author( 'post', 1, -1, $user_id, array('publish', 'pending', 'draft' ) );
	$total_blogs           = $total_blogs_query->posts;
	$total_blogs_count     = count( $total_blogs );

	// For podcasts.
	$total_podcast_query   = moc_posts_query_by_author( 'podcast', 1, -1, $user_id, array('publish', 'pending', 'draft' ) );
	$total_podcasts        = $total_podcast_query->posts;
	$total_podcasts_count  = count( $total_podcasts );

	// For workshops.
	// $total_workshop_query  = moc_posts_query_by_author( 'workshop', 1, -1, $user_id, array('publish', 'pending', 'draft' ) );
	// $total_workshops       = $total_workshop_query->posts;
	// $total_workshops_count = count( $total_workshops );

	// For courses.
	// $total_course_query    = moc_posts_query_by_author( 'sfwd-courses', 1, -1, $user_id, array('publish', 'pending', 'draft' ) );
	// $total_courses         = $total_course_query->posts;
	// $total_courses_count   = count( $total_courses );
	?>
	<section class="user_profile_blog elementor-section elementor-top-section elementor-element elementor-section-boxed elementor-section-height-default elementor-section-height-default">
		<div class="elementor-container elementor-column-gap-default">
			<?php
			if ( ! is_null( $source ) && 'customer-dashboard' === $source ) {
				?><a class="back-to-customer-dashboard" title="<?php esc_html_e( 'Back to your e-commerce dashboard', 'marketingops' ); ?>" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Back to dashboard', 'marketingops' ); ?></a><?php
			}
			?>
			<!-- Left Sidebar -->
			<div class="user_profile_left_side blog_box">
				<div class="blog_box_container elementor-widget-wrap elementor-element-populated">
					<div class= "moc_not_changable_container">
						<div class="loader_bg">
							<div class="loader"></div>  
						</div>
						<div class="box_image_title">
							<h4><?php echo esc_html( $user_display_name ); ?></h4>
							<div class="image_box">
								<?php echo moc_user_avtar_image( get_current_user_id() ); ?>
							</div>
							<div class="profile_status">
								<p><?php echo sprintf( __( 'Profile completeness: %1$s %2$s %3$s', 'marketingops' ), '<span>', '67%', '</span>' ) ?></p>
							</div>
						</div>
					</div>
					<div class="profile_details moc_profile_details">
						<div class="loader_bg">
							<div class="loader"></div>  
						</div>
						<div class="details_box moc_post_count_details">
							<!-- Forloop Here -->
						</div>
					</div>
				</div>
			</div>
			<!-- Right Sidebar -->
			<div class="user_profile_right_side blog_box">
				<div class="blog_box_container elementor-widget-wrap elementor-element-populated">
					<div class="right_side_box_tabbing_content tabbing_content">
						<div class="tabbing_content_container">
							<div class="link_box column_box">
								<a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>"><?php esc_html_e( 'Profile', 'marketingops' ); ?></a>
							</div>
							<div class="tab_box column_box active_tab">
								<a href="javascript:;" class="tab_link moc_tab_link" data-post = "post" data-tab="tab_1"><?php esc_html_e( 'Articles', 'marketingops' ); ?></a>
							</div>
							<div class="tab_box column_box">
								<a href="javascript:;" class="tab_link moc_tab_link" data-post = "podcast" data-tab="tab_2"><?php esc_html_e( 'Podcasts', 'marketingops' ); ?></a>
							</div>
							<!-- <div class="tab_box column_box">
								<a href="javascript:;" class="tab_link moc_tab_link" data-post = "workshop" data-tab="tab_3"><?php esc_html_e( 'Workshops & Webinars', 'marketingops' ); ?></a>
							</div>
							<div class="tab_box column_box">
								<a href="javascript:;" class="tab_link moc_tab_link" data-post = "sfwd-courses" data-tab="tab_4"><?php esc_html_e( 'Training Courses', 'marketingops' ); ?></a>
							</div> -->
						</div>
					</div>
					<div class="right_side_box_tab_contnet tabbing_boxed">
						<div class="tabbing_boxed_container">
							<div class="moc_write_a_post_content_section">
								<div class="loader_bg">
									<div class="loader"></div>  
								</div>
							</div>
							<!-- Tabbing Content Details -->
							<?php
							for( $i = 1; $i <= 4; $i++ ) {
								?>
								<div id="tab_<?php echo esc_attr( $i ); ?>" class="tabbing_content_details active_tab">
									<div class="tabbing_row">
										<div class="moc_data_to_show" id="moc_data_to_show">
											<!-- Articles Contnet Forloop Here -->
											<?php /* echo moc_html_for_listing_post_data( $total_blogs ); */ ?>
											<!-- Last Forloop Here -->
										</div>
									</div>
								</div>
								<?php
							}
							?>
							<div class="row_column row_btn_column">
								<div class="column_container">
									<div class="column_box column_btn">
										<a href="javascript:;" class="moc_write_post elementor-button-link elementor-button elementor-size-lg" role="button">
											<span class="elementor-button-content-wrapper">
												<span class="elementor-button-icon elementor-align-icon-right">
													<svg xmlns="http://www.w3.org/2000/svg" width="20" height="11" viewBox="0 0 20 11" fill="none">
														<path d="M14.7859 0.74192C14.4643 0.729551 14.1659 0.913544 14.0345 1.20731C13.9015 1.50109 13.9587 1.84433 14.1814 2.07935L16.6135 4.70782H1.30494C1.0189 4.70318 0.754506 4.85316 0.610713 5.10055C0.465374 5.34639 0.465374 5.65253 0.610713 5.89837C0.754506 6.14575 1.0189 6.29573 1.30494 6.29109H16.6135L14.1814 8.91957C13.9835 9.12675 13.9139 9.42361 13.9974 9.69728C14.0809 9.97096 14.3051 10.1781 14.5834 10.24C14.8632 10.3018 15.1539 10.2075 15.3441 9.99569L19.5017 5.49946L15.3441 1.00322C15.2018 0.845513 14.9993 0.749651 14.7859 0.74192Z" fill="white"></path>
													</svg> 
												</span>
												<span class="elementor-button-text"><?php esc_html_e( 'Write new post', 'marketingops' ); ?></span>
											</span>
										</a>
									</div>
								</div>
							</div>
							<!-- Tabbing Content Details -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php } else {
	echo '<script type="text/javascript">';
	echo 'window.location.href="'. home_url( 'log-in' ) .'";';
	echo '</script>';
	echo '<noscript>';
	echo '<meta http-equiv="refresh" content="0;url='. home_url( 'log-in' ) .'" />';
	echo '</noscript>'; exit;
}

get_footer();
