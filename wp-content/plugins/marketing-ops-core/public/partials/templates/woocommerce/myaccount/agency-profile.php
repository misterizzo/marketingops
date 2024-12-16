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
$agency_id        = ( ! empty( $agency_id[0] ) ) ? $agency_id[0] : false; 

// If the linked agency is not available, show the registration page.
if ( false === $agency_id || false === $is_agency_member ) {
	echo do_shortcode( '[elementor-template id="231177"]' );
} else {
	$agency_title = get_the_title( $agency_id );
	?>
	<!-- <form name="agency-signup-form" method="GET" enctype="multipart/form-data"> -->
		<div name="agency-signup-form">
		<section class="agencyformone">
			<h1><?php esc_html_e( 'General', 'marketingops' ); ?></h1>
			<div class="agencyformgroup">
				<label><?php esc_html_e( 'Agency name', 'marketingops' ); ?> <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
				<input type="text" class="agancyinputbox" id="agencyname" name="agencyname" value="<?php echo wp_kses_post( $agency_title ); ?>">
				<small><?php esc_html_e( 'This will be how your name will be displayed in the account section', 'marketingops' ); ?></small>
			</div> 

			<div class="agencyformgroup logoupload">
				<label>Logo <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>

				<div class="upload-btn-wrapper image-upload-container">
					<button class="btn">Select an image</button>
					<p>For the best results, upload horizontal version, 560 x 240px max</p>
					<input type="file" class="imageInput" name="myfile" onchange="readURL(this)" accept="image/*" />
						<div id="previewContainer" class="preview-container" style="display: none;">
    						<img class="preview-image" src="#" alt="Image Preview" />
    						<button class="removePreview remove-preview-btn" id="removePreview"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="16" cy="16" r="15.35" stroke="white" stroke-width="1.3"></circle><path d="M11 11L16 16L11 21" stroke="white" stroke-width="1.3"></path><path d="M21 11L16 16L21 21" stroke="white" stroke-width="1.3"></path></svg></button>
  						</div>
				</div>
			</div>

			<div class="agencyformgroup">
				<label>Description </label>
				<textarea id="description" name="description" rows="4" cols="50"></textarea>
				<small>0 of 400 max character</small>    
			</div>

			<h2>Contact</h2>
			<div class="agencyformgroups">
				<div class="agencyfirstblock">
					<label>Name</label>
					<input type="text" class="agancyinputbox" id="name" name="name">
				</div> 
				<div class="agencyfirstblock">
					<label>E-mail</label>
					<input type="email" class="agancyinputbox" id="email" name="email">
				</div>    
			</div>
			<div class="agencyformgroup">
				<label>Agency Website <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
				<input type="text" class="agancyinputbox" id="agencywebsite" name="agencywebsite">
			</div>
		</section>

		<section class="agencyformone detailsblock">
			<h3>Details</h3>
			<h5>Agency type <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></h5>
			<p>
				<input type="radio" id="affiliate" name="radio-group" checked>
				<label for="affiliate">Affiliate/OPM
					<small>Affiliate is the primary service provided by the agency</small>
				</label>
			</p>
			<p>
				<input type="radio" id="consultant" name="radio-group">
				<label for="consultant">Consultant
					<small>Martech strategy, product selection and stack implementation</small>
				</label>
			</p>
			<p>
				<input type="radio" id="digital" name="radio-group">
				<label for="digital">Digital
					<small>Full suite of digital marketing services</small>
				</label>
			</p>
			<p>
				<input type="radio" id="holding" name="radio-group">
				<label for="holding">Holding company
					<small>Agency is part of a global collection of agencies</small>
				</label>
			</p>
			<p>
				<input type="radio" id="influencer" name="radio-group">
				<label for="influencer">Influencer / Creator agency
					<small>Influencer / creator is the primary service</small>
				</label>
			</p>
			<p>
				<input type="radio" id="PR" name="radio-group">
				<label for="PR">PR agency
					<small>PR & performance PR is the primary service provided by the agency</small>
				</label>
			</p>
			<p>
				<input type="radio" id="Reddit" name="radio-group">
				<label for="Reddit">Reddit agency
					<small>What is this?</small>
				</label>
			</p>
			<p>
				<input type="radio" id="Search" name="radio-group">
				<label for="Search">Search/social
					<small>Search & social are the primary services</small>
				</label>
			</p>
			<div class="agencyformgroups">
				<div class="agencyfirstblock">
					<label>Year founded</label>
					<select class="marketingops-selectbox">
						<option value="1">Option 1</option>
						<option value="2">Option 2</option>
						<option value="3">Option 3</option>
					</select>
				</div> 
				<div class="agencyfirstblock">
					<label>Employees</label>
					<select class="marketingops-selectbox">
						<option value="1">Option 1</option>
						<option value="2">Option 2</option>
						<option value="3">Option 3</option>
					</select>
				</div>    
			</div>

			<h5>Which regions do you have full time employees in?  <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></h5>
			<div class="agencyformgroup form-group">
				<input type="checkbox" id="APAC">
				<label for="APAC">APAC</label>
			</div>
			<div class="agencyformgroup form-group">
				<input type="checkbox" id="northamerica">
				<label for="northamerica">North America</label>
			</div>
			<div class="agencyformgroup form-group">
				<input type="checkbox" id="US">
				<label for="US">US</label>
			</div>
			<div class="agencyformgroup form-group">
				<input type="checkbox" id="EMEA">
				<label for="EMEA">EMEA</label>
			</div>
			<div class="agencyformgroup form-group">
				<input type="checkbox" id="southamerica">
				<label for="southamerica">South America</label>
			</div>
			<div class="agencyformgroup form-group"id="regions-container"></div>
			<button class="addregion">Add another region</button>

			

			<div class="agencyformgroups">
				<div class="agencyfirstblock">
					<label>Primary Verticals <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Automotive">
						<label for="Automotive">Automotive</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="ConsumerPackageGoods">
						<label for="ConsumerPackageGoods">Consumer Packaged Goods</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Education">
						<label for="Education">Education</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="FinancialServices">
						<label for="FinancialServices">Financial Services</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Healthcare">
						<label for="Healthcare">Healthcare</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Retail">
						<label for="Retail">Retail</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Subscription">
						<label for="Subscription">Subscription</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Travel">
						<label for="Travel">Travel</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="B2B">
						<label for="B2B">B2B</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="DTC">
						<label for="DTC">DTC</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Entertainment">
						<label for="Entertainment">Entertainment</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Caming">
						<label for="Caming">Caming</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Real Estate">
						<label for="Real Estate">Real Estate</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="SMB">
						<label for="SMB">SMB</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Telecom">
						<label for="Telecom">Telecom</label>
					</div>
					<button class="addregion">Add new vertical</button>
				</div>


				<div class="agencyfirstblock">
					<label>What services do you provide? <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Affiliate Marketing">
						<label for="Affiliate Marketing">Affiliate Marketing</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Database Acquisition">
						<label for="Database Acquisition">Database Acquisition</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Influencer Marketing">
						<label for="Influencer Marketing">Influencer Marketing</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Programmatic">
						<label for="Programmatic">Programmatic</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="SEO">
						<label for="SEO">SEO</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Branded Content">
						<label for="Branded Content">Branded Content</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Email Marketing">
						<label for="Email Marketing">Email Marketing</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Performance PR">
						<label for="Performance PR">Performance PR</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="SEM">
						<label for="SEM">SEM</label>
					</div>
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="Social media">
						<label for="Social media">Social media</label>
					</div>
					<button class="addregion">Add new service</button>
				</div>
			</div>
		</section>

		<section class="agencyformone spotlit">
			<h3 class="spotlit">In the spotlight</h3>
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
				<h6 class="jbtitle">Articles & Press Releses</h6>
				<div class="fromgops">
					<div class="agencyformgroup form-group">
						<input type="checkbox" id="articles">
						<label for="articles">Publish articles & press releases posted by me</label>
					</div>
					<button id="toggleButton">
						Select <i><svg xmlns="http://www.w3.org/2000/svg" width="15" height="11" viewBox="0 0 15 11" fill="none"><g clip-path="url(#clip0_102_2793)"><path d="M10.5262 3.99457C10.2892 3.98546 10.0693 4.12103 9.97249 4.3375C9.87452 4.55396 9.91667 4.80688 10.0807 4.98005L11.8728 6.91682H0.592831C0.382065 6.9134 0.187248 7.02391 0.0812957 7.20619C-0.0257965 7.38734 -0.0257965 7.61292 0.0812957 7.79406C0.187248 7.97634 0.382065 8.08685 0.592831 8.08344H11.8728L10.0807 10.0202C9.9349 10.1729 9.88363 10.3916 9.94515 10.5933C10.0067 10.7949 10.1719 10.9476 10.3769 10.9931C10.5831 11.0387 10.7973 10.9692 10.9375 10.8131L14.001 7.50013L10.9375 4.18711C10.8326 4.0709 10.6834 4.00027 10.5262 3.99457Z" fill="white"/></g><defs><clipPath id="clip0_102_2793"><rect width="15" height="11" fill="white"/></clipPath></defs></svg></i>
					</button>
				</div>
				<div id="dynamicContainer"></div>
			</div>  
			
			
			<div class="agencyformgroup videogroup">
			<h6 class="jbtitle">Video</h6>
				<label>Youtube / Vimeo link </label>
				<input type="text" class="agancyinputbox" id="Video" name="Video">
				<div id="videoPreview" style="margin-top: 20px;"></div>
			</div>

			<h6 class="jbtitle">Jobs</h6>
			<div class="agencyformgroup form-group">
				<input type="checkbox" id="jobs">
				<label for="jobs">Include jobs posted by me to this page</label>
			</div>
		</section> 

		<section class="agencyformone">
			<ul>
				<li>
					<a href="javascript:void(0);" class="savedratbtn">
						Save Draft
					</a>
				</li>
				<li>
					<a href="javascript:void(0);" class="profilebtn">
						Create Profile
					</a>
				</li>
			</ul>
		</section>
	</div>
	<?php
}
