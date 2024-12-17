<?php
/**
 * This file is used for templating the agency profile registration for loggedin members.
 *
 * @since 1.0.0
 * @package Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/public/partials/templates/woocommerce/myaccount
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

global $current_user, $wpdb;

$is_agency_member = mops_is_user_agency_partner( $current_user->ID );
$agency_id        = new WP_Query(
	array(
		'post_type'      => 'agency',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'post_status'    => array( 'publish', 'draft' ),
		'meta_query'     => array(
			array(
				'key'     => 'agency_owner',
				'value'   => $current_user->ID,
				'compare' => '=',
			)
		),
	)
);
$agency_id        = ( ! empty( $agency_id->posts[0] ) ) ? $agency_id->posts[0] : false;

// If the linked agency is not available, show the registration page.
if ( false === $agency_id || false === $is_agency_member ) {
	?><div class="bluredpage">
		<h5>Coming soon</h5>
		<div class="bluredpageinner">
		<?php echo do_shortcode( '[elementor-template id="231177"]' ); ?>
</div>
	</div><?	
} else {
	$agency_title       = get_the_title( $agency_id );
	$agency_image       = wp_get_attachment_image_src( get_post_thumbnail_id( $agency_id ), 'full-image' );
	$agency_description = get_post_field( 'post_content', $agency_id );
	?>
	<!-- <form name="agency-signup-form" method="GET" enctype="multipart/form-data"> -->
		<div name="agency-signup-form">
		<section class="agencyformone">
			<h1><?php esc_html_e( 'General', 'marketingops' ); ?></h1>
			<div class="agencyformgroup">
				<label><?php esc_html_e( 'Agency name', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></label>
				<input type="text" class="agancyinputbox" id="agencyname" name="agencyname" value="<?php echo wp_kses_post( $agency_title ); ?>">
				<small><?php esc_html_e( 'This will be how your name will be displayed in the account section', 'marketingops' ); ?></small>
			</div>
			<div class="agencyformgroup logoupload">
				<label><?php esc_html_e( 'Logo', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></label>
				<div class="upload-btn-wrapper image-upload-container">
					<button class="btn"><?php esc_html_e( 'Select an image', 'marketingops' ); ?></button>
					<p><?php esc_html_e( 'For the best results, upload horizontal version, 560 x 240px max', 'marketingops' ); ?></p>
					<input type="file" class="imageInput" name="myfile" onchange="readURL(this)" accept="image/*" />
						<div id="previewContainer" class="preview-container" style="<?php echo ( empty( $agency_image[0] ) ? 'display: none;' : '' ); ?>">
    						<img class="preview-image" src="<?php echo ( ! empty( $agency_image[0] ) ? $agency_image[0] : '' ); ?>" alt="<?php esc_html_e( 'Image Preview', 'marketingops' ); ?>" />
    						<button class="removePreview remove-preview-btn" id="removePreview"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="16" cy="16" r="15.35" stroke="white" stroke-width="1.3"></circle><path d="M11 11L16 16L11 21" stroke="white" stroke-width="1.3"></path><path d="M21 11L16 16L21 21" stroke="white" stroke-width="1.3"></path></svg></button>
  						</div>
				</div>
			</div>
			<div class="agencyformgroup">
				<label><?php esc_html_e( 'Description', 'marketingops' ); ?></label>
				<textarea id="description" name="description" rows="4" cols="50"><?php echo wp_kses_post( $agency_description ); ?></textarea>
				<small class="agency-description-characters-count"><?php echo sprintf( __( '%1$d of 400 characters', 'marketingops' ), strlen( $agency_description ) ); ?></small>
			</div>

			<h2><?php esc_html_e( 'Contact', 'marketingops' ); ?></h2>
			<?php
			$agency_user_name    = get_field( 'agency_user_name', $agency_id );
			$agency_user_email   = get_field( 'agency_user_email', $agency_id );
			$agency_user_website = get_field( 'agency_user_website', $agency_id );
			?>
			<div class="agencyformgroups">
				<div class="agencyfirstblock">
					<label><?php esc_html_e( 'Name', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></label>
					<input type="text" class="agancyinputbox" id="name" name="name" value="<?php echo wp_kses_post( $agency_user_name ); ?>">
				</div> 
				<div class="agencyfirstblock">
					<label><?php esc_html_e( 'E-mail', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></label>
					<input type="email" class="agancyinputbox" id="email" name="email" value="<?php echo esc_html( $agency_user_email ); ?>">
				</div>    
			</div>
			<div class="agencyformgroup">
				<label><?php esc_html_e( 'Agency Website', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></label>
				<input type="text" class="agancyinputbox" id="agencywebsite" name="agencywebsite" value="<?php echo esc_url( $agency_user_website ); ?>">
			</div>
		</section>

		<?php
		$agency_types      = wp_get_object_terms( $agency_id, 'agency_type', array( 'fields' => 'ids' ) );
		$agency_type_terms = get_terms(
			array(
				'taxonomy'   => 'agency_type',
				'hide_empty' => false,
			)
		);
		$year_founded      = (int) get_field( 'agency_year_founded', $agency_id );
		$employees_list    = array( '1-10', '10-25', '25-50', '50-100', '100-250', '250-500', '500-1000', '1000-2000', 'More than 2000' );
		$agency_employees  = get_field( 'agency_employees', $agency_id );
		?>
		<section class="agencyformone detailsblock">
			<h3><?php esc_html_e( 'Details', 'marketingops' ); ?></h3>
			<h5><?php esc_html_e( 'Agency type', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></h5>
			<?php if ( ! empty( $agency_type_terms ) && is_array( $agency_type_terms ) ) { ?>
				<?php foreach ( $agency_type_terms as $agency_type_term ) { ?>
					<p>
						<input type="radio" id="affiliate" name="radio-group" <?php echo esc_attr( ( in_array( $agency_type_term->term_id, $agency_types, true ) ) ? 'checked' : '' ); ?>>
						<label for="affiliate"><?php echo wp_kses_post( $agency_type_term->name ); ?>
							<small><?php echo wp_kses_post( $agency_type_term->description ); ?></small>
						</label>
					</p>
				<?php } ?>
			<?php } ?>

			<div class="agencyformgroups">
				<div class="agencyfirstblock">
					<label><?php esc_html_e( 'Year founded', 'marketingops' ); ?></label>
					<select class="marketingops-selectbox">
						<?php for ( $i = 1900; $i <= gmdate( 'Y' ); $i++ ) { ?>
							<option value="<?php echo esc_attr( $i ); ?>" <?php echo esc_attr( ( ! empty( $year_founded ) && $year_founded === $i ) ? 'selected' : '' ); ?>><?php echo esc_attr( $i ); ?></option>
						<?php } ?>
					</select>
				</div> 
				<div class="agencyfirstblock">
					<label><?php esc_html_e( 'Employees', 'marketingops' ); ?></label>
					<select class="marketingops-selectbox">
						<?php foreach ( $employees_list as $employees_list_item ) { ?>
							<option value="<?php echo esc_html( $employees_list_item ); ?>" <?php echo esc_attr( ( ! empty( $agency_employees ) && $agency_employees === $employees_list_item ) ? 'selected' : '' ); ?>><?php echo esc_html( $employees_list_item ); ?></option>
						<?php } ?>
					</select>
				</div>    
			</div>

			<?php
			$agency_regions      = wp_get_object_terms( $agency_id, 'agency_region', array( 'fields' => 'ids' ) );
			$agency_region_terms = get_terms(
				array(
					'taxonomy'   => 'agency_region',
					'hide_empty' => false,
				)
			);
			?>
			<h5><?php esc_html_e( 'Which regions do you have full time employees in?', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></h5>
			<?php if ( ! empty( $agency_region_terms ) && is_array( $agency_region_terms ) ) { ?>
				<?php foreach ( $agency_region_terms as $agency_region_term ) { ?>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="region-<?php echo esc_html( sanitize_title( $agency_region_term->name ) ); ?>" value="<?php echo esc_html( $agency_region_term->name ); ?>" <?php echo esc_attr( ( ! empty( $agency_regions ) && in_array( $agency_region_term->term_id, $agency_regions, true ) ) ? 'checked' : '' ); ?>>
						<label for="region-<?php echo esc_html( sanitize_title( $agency_region_term->name ) ); ?>"><?php echo esc_html( $agency_region_term->name ); ?></label>
					</div>
				<?php } ?>
			<?php } ?>
			<div class="agencyformgroup form-group" id="regions-container"></div>
			<button style="display: none;" class="addregion add-new-taxonomy-term" data-taxonomy="agency_region" data-type="region"><?php esc_html_e( 'Add another region', 'marketingops' ); ?></button>

			<?php
			$agency_primary_verticals      = wp_get_object_terms( $agency_id, 'agency_primary_vertical', array( 'fields' => 'ids' ) );
			$agency_primary_vertical_terms = get_terms(
				array(
					'taxonomy'   => 'agency_primary_vertical',
					'hide_empty' => false,
				)
			);
			?>
			<div class="agencyformgroups">
				<div class="agencyfirstblock">
					<label><?php esc_html_e( 'Primary Verticals', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></label>
					<?php if ( ! empty( $agency_primary_vertical_terms ) && is_array( $agency_primary_vertical_terms ) ) { ?>
						<?php foreach ( $agency_primary_vertical_terms as $agency_primary_vertical_term ) { ?>
							<div class="agencyformgroup form-group">
								<input type="checkbox" id="region-<?php echo esc_html( sanitize_title( $agency_primary_vertical_term->name ) ); ?>" value="<?php echo esc_html( $agency_primary_vertical_term->name ); ?>" <?php echo esc_attr( ( ! empty( $agency_primary_verticals ) && in_array( $agency_primary_vertical_term->term_id, $agency_primary_verticals, true ) ) ? 'checked' : '' ); ?>>
								<label for="region-<?php echo esc_html( sanitize_title( $agency_primary_vertical_term->name ) ); ?>"><?php echo esc_html( $agency_primary_vertical_term->name ); ?></label>
							</div>
						<?php } ?>
					<?php } ?>
					<button class="addregion" style="display: none;">Add new vertical</button>
				</div>

				<?php
				$agency_services      = wp_get_object_terms( $agency_id, 'agency_service', array( 'fields' => 'ids' ) );
				$agency_service_terms = get_terms(
					array(
						'taxonomy'   => 'agency_service',
						'hide_empty' => false,
					)
				);
				?>
				<div class="agencyfirstblock">
				<label><?php esc_html_e( 'What services do you provide?', 'marketingops' ); ?><?php echo mops_get_required_asterisk(); ?></label>
					<?php if ( ! empty( $agency_service_terms ) && is_array( $agency_service_terms ) ) { ?>
						<?php foreach ( $agency_service_terms as $agency_service_term ) { ?>
							<div class="agencyformgroup form-group">
								<input type="checkbox" id="region-<?php echo esc_html( sanitize_title( $agency_service_term->name ) ); ?>" value="<?php echo esc_html( $agency_service_term->name ); ?>" <?php echo esc_attr( ( ! empty( $agency_services ) && in_array( $agency_service_term->term_id, $agency_services, true ) ) ? 'checked' : '' ); ?>>
								<label for="region-<?php echo esc_html( sanitize_title( $agency_service_term->name ) ); ?>"><?php echo esc_html( $agency_service_term->name ); ?></label>
							</div>
						<?php } ?>
					<?php } ?>
					<button class="addregion" style="display: none;">Add new service</button>
				</div>
			</div>
		</section>

		<section class="agencyformone spotlit">
			<h3 class="spotlit">In the spotlight</h3>
			<div class="agency-main-people-container">
				<div class="person-data">
					<h4>Person 1</h4>
					<div class="agencyformgroups">
						<div class="agencyfirstblock">
							<label>Full Name</label>
							<input type="text" class="agancyinputbox" id="fullnmame" name="fullnmame">
						</div> 
						<div class="agencyfirstblock">
							<label>Position</label>
							<input type="text" class="agancyinputbox" id="position" name="position">
						</div>    
					</div>
					<div class="agencyformgroup bottomtext">
						<label>LinkedIn URL </label>
						<input type="text" class="agancyinputbox" id="linkedin" name="linkedin">
					</div>

					<div class="agencyformgroup">
						<label>Image </label>
						<div class="upload-btn-wrapper image-upload-container">
							<button class="btn">Select an image</button>
							<p>For the best results, crop your photo to 640 x 380px before uploading.</p>
							<input type="file" class="imageInput" onchange="readURL(this)" accept="image/*"/>
							<div class="preview-container" style="display: none;">
									<img class="preview-image" src="#" alt="Image Preview" />
									<button class="removePreview remove-preview-btn" id="removePreview"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="16" cy="16" r="15.35" stroke="white" stroke-width="1.3"></circle><path d="M11 11L16 16L11 21" stroke="white" stroke-width="1.3"></path><path d="M21 11L16 16L21 21" stroke="white" stroke-width="1.3"></path></svg></button>
								</div>
						</div>
					</div>
				</div>
				<div class="person-data">
					<h4>Person 2</h4>
					<div class="agencyformgroups">
						<div class="agencyfirstblock">
							<label>Full Name</label>
							<input type="text" class="agancyinputbox" id="fullnmame" name="fullnmame">
						</div> 
						<div class="agencyfirstblock">
							<label>Position</label>
							<input type="text" class="agancyinputbox" id="position" name="position">
						</div>    
					</div>
					<div class="agencyformgroup bottomtext">
						<label>LinkedIn URL </label>
						<input type="text" class="agancyinputbox" id="linkedin" name="linkedin">
					</div>

					<div class="agencyformgroup">
						<label>Image </label>
						<div class="upload-btn-wrapper image-upload-container">
							<button class="btn">Select an image</button>
							<p>For the best results, crop your photo to 640 x 380px before uploading.</p>
							<input type="file" class="imageInput" onchange="readURL(this)" accept="image/*"/>
							<div class="preview-container" style="display: none;">
									<img class="preview-image" src="#" alt="Image Preview" />
									<button class="removePreview remove-preview-btn" id="removePreview"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="16" cy="16" r="15.35" stroke="white" stroke-width="1.3"></circle><path d="M11 11L16 16L11 21" stroke="white" stroke-width="1.3"></path><path d="M21 11L16 16L21 21" stroke="white" stroke-width="1.3"></path></svg></button>
								</div>
						</div>
					</div>
				</div>
			</div>

			<div id="dynamicContent"></div>
			<button class="addpersone">Add Person</button>
			
		</section>

		<section class="agencyformone mentions">
			<h6 class="special">Special mentions</h6>
			<div class="agencyformgroup videogroup articals" style="margin-top:0;">
			<h5>Testimonial</h5>
				<label>Text </label>
				<textarea id="Text" name="Text" rows="4" cols="50"></textarea>
				<small>0 of 400 max character</small>    
			</div>

			<div class="agencyformgroup person">
				<label>Name of the person quoted </label>
				<input type="text" class="agancyinputbox" id="person" name="person">
			</div>

			<div class="agencyformgroup videogroup articals">
			<h5>Clients</h5>
				<label>Text </label>
				<textarea id="Text" name="Text" rows="4" cols="50"></textarea>
				<small> list as many as you want</small>    
			</div>

			<div class="agencyformgroup videogroup articals">
			<h5>Certifications</h5>
				<label>Text </label>
				<textarea id="Text" name="Text" rows="4" cols="50"></textarea>
				<small> list as many as you want</small>    
			</div>

			<div class="agencyformgroup videogroup articals">
			<h5>Awards</h5>
				<label>Text </label>
				<textarea id="Text" name="Text" rows="4" cols="50"></textarea>
				<small> Please list one award per line to create a list</small>    
			</div>

			<div class="agencyformgroup videogroup articals">
				<h6 class="jbtitle"><?php esc_html_e( 'Articles & Press Releases', 'marketingops' ); ?></h6>
				<div class="fromgops">
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="articles">
						<label for="articles"><?php esc_html_e( 'Publish articles & press releases posted by me', 'marketingops' ); ?></label>
					</div>
					<button id="toggleButton">
					<?php esc_html_e( 'Select', 'marketingops' ); ?> <i><svg xmlns="http://www.w3.org/2000/svg" width="15" height="11" viewBox="0 0 15 11" fill="none"><g clip-path="url(#clip0_102_2793)"><path d="M10.5262 3.99457C10.2892 3.98546 10.0693 4.12103 9.97249 4.3375C9.87452 4.55396 9.91667 4.80688 10.0807 4.98005L11.8728 6.91682H0.592831C0.382065 6.9134 0.187248 7.02391 0.0812957 7.20619C-0.0257965 7.38734 -0.0257965 7.61292 0.0812957 7.79406C0.187248 7.97634 0.382065 8.08685 0.592831 8.08344H11.8728L10.0807 10.0202C9.9349 10.1729 9.88363 10.3916 9.94515 10.5933C10.0067 10.7949 10.1719 10.9476 10.3769 10.9931C10.5831 11.0387 10.7973 10.9692 10.9375 10.8131L14.001 7.50013L10.9375 4.18711C10.8326 4.0709 10.6834 4.00027 10.5262 3.99457Z" fill="white"/></g><defs><clipPath id="clip0_102_2793"><rect width="15" height="11" fill="white"/></clipPath></defs></svg></i>
					</button>
				</div>
				<div id="dynamicContainer"></div>
			</div>  
			
			<?php $agency_video = get_field( 'agency_video', $agency_id ); ?>
			<div class="agencyformgroup videogroup">
			<h6 class="jbtitle"><?php esc_html_e( 'Video', 'marketingops' ); ?></h6>
				<label><?php esc_html_e( 'Youtube / Vimeo link', 'marketingops' ); ?></label>
				<input type="text" class="agancyinputbox" id="agency-video" name="Video" value="<?php echo esc_url( $agency_video ); ?>">
				<div id="videoPreview"></div>
			</div>

			<?php $include_jobs = get_field( 'agency_include_jobs', $agency_id ); ?>
			<h6 class="jbtitle"><?php esc_html_e( 'Jobs', 'marketingops' ); ?></h6>
			<div class="agencyformgroup form-group">
				<input type="checkbox" id="include-jobs" <?php echo esc_attr( ( ! empty( $include_jobs ) && true === $include_jobs ) ? 'checked' : '' ); ?>>
				<label for="include-jobs"><?php esc_html_e( 'Include jobs posted by me to this page', 'marketingops' ); ?></label>
			</div>
		</section> 

		<section class="agencyformone">
			<ul>
				<li><a href="javascript:void(0);" class="savedratbtn"><?php esc_html_e( 'Save Draft', 'marketingops' ); ?></a></li>
				<li><a href="javascript:void(0);" class="profilebtn"><?php esc_html_e( 'Create Profile', 'marketingops' ); ?></a></li>
			</ul>
		</section>
	</div>
	<?php
}
