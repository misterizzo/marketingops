<?php
/**
 * This file is used for templating the customer's earned certificates.
 *
 * @since 1.0.0
 * @package Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/public/partials/templates/woocommerce/myaccount
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Get the customer courses.
$user_id      = get_current_user_id();
$courses      = get_user_meta( $user_id, '_sfwd-course_progress', true );
$bg_img_index = 1;
$certificates = array();

// Loop through the courses to collect the associated certificate.
if ( ! empty( $courses ) && is_array( $courses ) ) {
	foreach ( $courses as $course_id => $course_data ) {
		// Get the attached certificate ID.
		$certificate_id  = get_post_meta( $course_id, '_ld_certificate', true );
		$certficate_link = learndash_get_course_certificate_link( $course_id, $user_id );

		// If the certificate link is available, put it in an array.
		if ( ! empty( $certficate_link ) ) {
			$certificates[] = array(
				'course'           => $course_id,
				'certificate'      => $certificate_id,
				'certificate_link' => $certficate_link,
			);
		}
	}
}
?>
<div class="box_about_content box_content certification_section moc_purchased_courses_section">
	<?php if ( ! empty( $certificates ) && is_array( $certificates ) ) { ?>
		<div class="sub_title_with_content moc_profile_purchased_courses">
			<div class="content_boxes selected_certi">
				<div class="training_page_new">
					<div class="training_contnet_box">
						<div class="training_content">
							<?php foreach ( $certificates as $certificate_data ) {
								$bg_img_index = ( 8 === $bg_img_index ) ? 0 : $bg_img_index; // Reset the background image index.
								?>
								<div class="training_content_boxed moc_profile_training_index_0">
									<div class="boxed_bg" style="background-image: url('/wp-content/uploads/2022/08/<?php echo esc_attr( $bg_img_index ); ?>.jpeg');">
										<div class="training_workshop_name">
											<a target="_blank" href="<?php echo esc_url( $certificate_data['certificate_link'] ); ?>" class="workshop_title" title="<?php echo wp_kses_post( get_the_title( $certificate_data['certificate'] ) ); ?>"><?php echo wp_kses_post( get_the_title( $certificate_data['course'] ) ); ?></a>
										</div>
									</div>
								</div>
								<?php $bg_img_index++; // Increase the background color index. ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php } else { ?>
		<div class="main-mops-certificates">
			<div class="mops-no-certificates">
				<div class="mops-inner-circle">
					<img src="/wp-content/uploads/2023/09/Purchase-Order-1.svg">
					<p><?php esc_html_e( 'You don’t have any certificates yet!', 'marketing-ops-core' ); ?></p>
					<p><?php esc_html_e( 'if you\'ve enrolled to any course and unable to see the expected certificate, please confirm if the course is completed.', 'marketing-ops-core' ); ?></p>
				</div>	
			</div>
		</div>
	<?php } ?>
</div>