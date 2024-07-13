<?php
/**
 * Conference vault details page template.
 *
 * This template can be overridden by copying it to activetheme/marketing-ops-core/conference-vault/conference-vault-single.php
 *
 * @see         https://marketingops.com/
 * @author      Adarsh Verma
 * @package     Marketing_Ops_Core
 * @category    Template
 * @since       1.0.0
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get the header first.
get_header();

$session_title      = get_the_title();
$session_id         = get_the_ID();
$session_by         = get_field( 'session_author', $session_id );
$session_link       = get_field( 'vimeo_video_url', $session_id );
$conference_term    = wp_get_object_terms( $session_id, 'conference' );
$conference         = ( ! empty( $conference_term[0]->name ) ) ? $conference_term[0]->name : '';
$back_link          = ( ! empty( $conference_term[0]->term_id ) ) ? get_term_link( $conference_term[0]->term_id ) : '';
$session_content    = get_the_content();
$session_chatscript = get_field( 'session_chatscript', $session_id );

// Get more sessions from the same conference.
if ( ! empty( $conference_term[0]->taxonomy ) ) {
	$same_conference_videos_query = moc_get_conference_videos(
		'conference_vault',
		1,
		3,
		array(
			'taxonomy' => $conference_term[0]->taxonomy,
			'field'    => 'term_id',
			'terms'    => array( $conference_term[0]->term_id ),
		),
		''
	);
	$same_conference_video_ids    = ( ! empty( $same_conference_videos_query->posts ) ) ? $same_conference_videos_query->posts : '';
}

// Check if the other conferences have videos.
$other_conferences_args            = array(
	'taxonomy'   => 'conference',
	'hide_empty' => true,
);
$other_conferences_args['exclude'] = ( ! empty( $conference_term[0]->term_id ) ) ? array( $conference_term[0]->term_id ) : array();
$other_conferences                 = array_values( get_terms( $other_conferences_args ) );

// Get sessions from other conference.
if ( ! empty( $other_conferences[0]->taxonomy ) ) {
	$other_conference_videos_query = moc_get_conference_videos(
		'conference_vault',
		1,
		3,
		array(
			'taxonomy' => $other_conferences[0]->taxonomy,
			'field'    => 'term_id',
			'terms'    => array( $other_conferences[0]->term_id ),
		),
		''
	);
	$other_conference_video_ids    = ( ! empty( $other_conference_videos_query->posts ) ) ? $other_conference_videos_query->posts : '';
}

// Restrict the modal based on user active membership.
$user_memberships = moc_get_membership_plan_slug();

if ( false === $user_memberships ) {
	$conference_vault_container_class = 'is-unregistered-member';
} elseif ( ! empty( $user_memberships ) && is_array( $user_memberships ) ) {
	if ( 1 === count( $user_memberships ) && in_array( 'free-membership', $user_memberships, true ) ) {
		$conference_vault_container_class = 'is-free-member';
	} elseif ( in_array( 'pro-plus-membership', $user_memberships, true ) ) {
		$conference_vault_container_class = 'is-pro-plus-member';
	} else {
		$conference_vault_container_class = 'is-other-membership-member';
	}
}
?>
<section class="marketingopstemplatesconfernace conferencevaultevent conferencevaulteventdetails presentation elementor-section elementor-section-boxed" data-usersubscriptionclass="<?php echo esc_attr( $conference_vault_container_class ); ?>">
	<div class="margktingimgss"></div>
	<div class="elementor-container elementor-column-gap-default">
		<div class="conferencevaultinner">
			<h1><span><?php echo wp_kses_post( $session_title ); ?></span></h1>
			<ul>
				<?php if ( ! empty( $back_link ) ) { ?>
					<li>
						<a href="<?php echo esc_url( $back_link ); ?>">
							<i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="11" viewBox="0 0 16 11" fill="none"><g clip-path="url(#clip0_26_136)"><path d="M4.97479 2.50043C5.21176 2.49132 5.43164 2.62689 5.52848 2.84335C5.62646 3.05982 5.58431 3.31274 5.42025 3.48591L3.62817 5.42268H14.9081C15.1189 5.41926 15.3137 5.52977 15.4197 5.71205C15.5268 5.8932 15.5268 6.11877 15.4197 6.29992C15.3137 6.4822 15.1189 6.59271 14.9081 6.5893H3.62817L5.42025 8.52607C5.56608 8.67873 5.61735 8.89747 5.55583 9.09912C5.4943 9.30077 5.32911 9.45344 5.12404 9.49901C4.91783 9.54458 4.70365 9.47508 4.56352 9.319L1.5 6.00599L4.56352 2.69297C4.66833 2.57676 4.81757 2.50613 4.97479 2.50043Z" fill="url(#paint0_linear_26_136)"/></g><defs><linearGradient id="paint0_linear_26_136" x1="15.8302" y1="6.02333" x2="-6.76765" y2="6.02333" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient><clipPath id="clip0_26_136"><rect width="15" height="10" fill="white" transform="matrix(-1 0 0 1 15.5 0.5)"/></clipPath></defs></svg></i>
							<?php esc_html_e( 'Back', 'marketing-ops-core' ); ?>
						</a>
					</li>
				<?php } ?>
				<li><?php echo esc_html( sprintf( __( 'by %1$s, %2$s conference', 'marketing-ops-core' ), $session_by, $conference ) ); ?></li>
				
				<?php if ( ! empty( $back_link ) ) { ?>
					<li style="visibility:hidden;">
						<a href="<?php echo esc_url( $back_link ); ?>">
							<i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="11" viewBox="0 0 16 11" fill="none"><g clip-path="url(#clip0_26_136)"><path d="M4.97479 2.50043C5.21176 2.49132 5.43164 2.62689 5.52848 2.84335C5.62646 3.05982 5.58431 3.31274 5.42025 3.48591L3.62817 5.42268H14.9081C15.1189 5.41926 15.3137 5.52977 15.4197 5.71205C15.5268 5.8932 15.5268 6.11877 15.4197 6.29992C15.3137 6.4822 15.1189 6.59271 14.9081 6.5893H3.62817L5.42025 8.52607C5.56608 8.67873 5.61735 8.89747 5.55583 9.09912C5.4943 9.30077 5.32911 9.45344 5.12404 9.49901C4.91783 9.54458 4.70365 9.47508 4.56352 9.319L1.5 6.00599L4.56352 2.69297C4.66833 2.57676 4.81757 2.50613 4.97479 2.50043Z" fill="url(#paint0_linear_26_136)"/></g><defs><linearGradient id="paint0_linear_26_136" x1="15.8302" y1="6.02333" x2="-6.76765" y2="6.02333" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient><clipPath id="clip0_26_136"><rect width="15" height="10" fill="white" transform="matrix(-1 0 0 1 15.5 0.5)"/></clipPath></defs></svg></i>
							<?php esc_html_e( 'Prev', 'marketing-ops-core' ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>

			<div class="conference-session-details">
				<?php echo wp_kses_post( $session_content ); ?>
			</div>

			<!-- SESSION VIDEO -->
			<?php if ( ! empty( $session_link ) ) { ?>
				<div class="elementor-wrapper elementor-open-inline">
					<iframe class="elementor-video-iframe" allowfullscreen="" allow="clipboard-write" title="vimeo Video Player" src="<?php echo esc_url( $session_link ); ?>?color&amp;autopause=0&amp;loop=0&amp;muted=0&amp;title=1&amp;portrait=1&amp;byline=1&amp;h=427b06ed1e#t="></iframe> 
				</div>
			<?php } ?>

			<!-- CHAT SCRIPT -->
			<?php if ( ! empty( $session_chatscript ) ) { ?>
				<div class="conference-session-transcript">
					<?php echo wp_kses_post( $session_chatscript ); ?>
				</div>
			<?php } ?>

			<?php if ( ! empty( $same_conference_video_ids ) && is_array( $same_conference_video_ids ) ) { ?>
				<div class="conferencevaultinner_inner sessions-from-same-conference">
					<div class="conferencevaultinner_innerright">
						<div class="conferencevaultinner_innerright_inner">
							<h3><?php echo esc_html( sprintf( __( 'More from %1$s', 'marketing-ops-core' ), $conference ) ); ?></h3>
							<ul>
								<?php
								foreach ( $same_conference_video_ids as $video_id ) {
									echo moc_conference_vault_video_box_html( $video_id ); // Print the conference video post.
								} ?>
							</ul>
						</div>
					</div>
				</div>
			<?php } ?>

			<?php if ( ! empty( $other_conference_video_ids ) && is_array( $other_conference_video_ids ) ) { ?>
				<div class="conferencevaultinner_inner sessions-from-other-conference">
					<div class="conferencevaultinner_innerright">
						<div class="conferencevaultinner_innerright_inner">
							<h3><?php echo esc_html( sprintf( __( 'Sessions from %1$s', 'marketing-ops-core' ), $other_conferences[0]->name ) ); ?></h3>
							<ul>
								<?php
								foreach ( $other_conference_video_ids as $video_id ) {
									echo moc_conference_vault_video_box_html( $video_id ); // Print the conference video post.
								} ?>
							</ul>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
<?php
get_footer();
