<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.cmsminds.com/
 * @since      1.0.0
 *
 * @package    Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/admin
 * @author     cmsMinds <info@cmsminds.com>
 */
class Marketing_Ops_Core_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Reservation - Custom product type.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $custom_product_type Training - Custom product type.
	 */
	private $custom_product_type;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of this plugin.
	 * @param    string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->custom_product_type = moc_get_custom_product_type_slug();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Marketing_Ops_Core_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Marketing_Ops_Core_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		wp_enqueue_style(
			$this->plugin_name . '-moc-admin-core',
			plugin_dir_url( __FILE__ ) . 'css/marketing-ops-core-admin.css',
			array(),
			filemtime( MOC_PLUGIN_PATH . 'admin/css/marketing-ops-core-admin.css' ),
			'all'
		);
		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/marketing-ops-core-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/marketing-ops-core-admin.js',
			array( 'jquery' ),
			// filemtime( MOC_PLUGIN_PATH . 'public/js/marketing-ops-core-admin.js' ),
			time(),
			true
		);

		// Localize public script.
		wp_localize_script(
			$this->plugin_name,
			'Moc_Admin_JS_Obj',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

	}
	/**
	 * Function to return Unset company details from job edit metabox.
	 *
	 * @since    1.0.0
	 * @param      array $fields This variable holds the fields of jobs.
	 */
	public function moc_get_job_custome_meta_fields( $fields ) {

		return $fields;
	}

	/**
	 * Function to return call function on admin init
	 *
	 * @since    1.0.0
	 */
	public function moc_function_run_on_admin_init_callbak() {
		moc_add_option_page();
	}

	/**
	 * Function to return set default image of company
	 *
	 * @since    1.0.0
	 * @param      array $url This variable holds the url of default image of company.
	 */
	public function moc_company_placeholder_image_callback( $url ) {
		if ( ! empty( get_field( 'company_placeholder_image', 'option' ) ) ) {
			$image_array            = get_field( 'company_placeholder_image', 'option' );
			$place_holder_image_url = $image_array['sizes']['medium'];
			$url                    = $place_holder_image_url;
		}

		return $url;
	}

	/**
	 * Function to return to display custom template in dropdown of page attributes.
	 *
	 * @since    1.0.0
	 * @param    array $templates This variable holds the all the templates array.
	 */
	public function moc_add_page_template( $templates ) {
		$templates['moc-user-edit.php']            = __( 'User Edit', 'marketing-ops-core' );
		$templates['add-a-blog.php']               = __( 'Add Blog Template', 'marketing-ops-core' );
		$templates['blog-listing-tempate.php']     = __( 'Blog Listing', 'marketing-ops-core' );
		$templates['podcast-listings-tempate.php'] = __( 'Podcast Listing', 'marketing-ops-core' );

		return $templates;
	}

	/**
	 * Function to return change gravavtar for default.
	 * @since    1.0.0
	 * @param    string $avatar_defaults This variable holds src of default image.
	 */
	public function moc_admin_set_default_gravatar ( $avatar_defaults ) {
		$default_image                     = get_option( 'moc_user_default_image', 'option' );
		$avatar_defaults[ $default_image ] = 'Default Gravatar';

		return $avatar_defaults;
	}

	/**
	 * Function to return change gravavtar for default.
	 * @since    1.0.0
	 * @param    string $avatar_defaults This variable holds src of default image.
	 */
	public function moc_user_add_extra_field_callback ( $user ) {
		$user_all_info     = ! empty( get_user_meta( $user->ID, 'user_all_info', true ) ) ? get_user_meta( $user->ID, 'user_all_info', true ) : array();
		$user_basic_info   = ! empty( $user_all_info['user_basic_info'] ) ? $user_all_info['user_basic_info'] : array();
		$moc_certificates  = ! empty( $user_all_info['moc_certificates'] ) ? $user_all_info['moc_certificates'] : array();
		$moc_work_data     = ! empty( $user_all_info['moc_work_data'] ) ? $user_all_info['moc_work_data'] : array();
		$moc_cl_skill_info = ! empty( $user_all_info['moc_cl_skill_info'] ) ? $user_all_info['moc_cl_skill_info'] : array();
		$moc_martech_info  = ! empty( $user_all_info['moc_martech_info'] ) ? $user_all_info['moc_martech_info'] : array();
		$all_user_meta     = get_user_meta( $user->ID );

		foreach( $moc_certificates as $moc_certificate ) {
			$update_value_arr[] = ( int )$moc_certificate;
		}
		?>
		<div class="tab">
			<button class="tablinks active" data-src="tab_1"><?php esc_html_e( 'Social Media Links', 'marketing-ops-core' ); ?></button>
			<button class="tablinks" data-src="tab_2"><?php esc_html_e( 'Martech tools experience', 'marketing-ops-core' ); ?></button>
			<button class="tablinks" data-src="tab_3"><?php esc_html_e( 'Skills', 'marketing-ops-core' ); ?></button>
			<button class="tablinks" data-src="tab_4"><?php esc_html_e( 'Work History', 'marketing-ops-core' ); ?></button>
			<button class="tablinks" data-src="tab_5"><?php esc_html_e( 'Selected Certificates', 'marketing-ops-core' ); ?></button>
		</div>

		<div id="tab_1" class="tabcontent active">
			<table class="form-table">
				<h3><?php esc_html_e( 'Social Media Links', 'marketing-ops-core' ); ?></h3>
				<?php foreach ( $user_basic_info[ 'social_media_arr' ] as $social_links ) { ?>
					<tr>
						<th><?php echo esc_html( $social_links['tag'] ); ?></th>
						<td>
							<input type="text" name="moc_social_links[]" id="<?php echo esc_html( $social_links['tag'] ); ?>" value="<?php echo esc_html( $social_links['val'] ); ?>" class="regular-text">
							<input type="hidden" name="moc_social_tags[]" value="<?php echo esc_html( $social_links['tag'] ); ?>">
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
		<div id="tab_2" class="tabcontent">
			<table class="form-table">
				<h3><?php esc_html_e( 'Martech tools experience', 'marketing-ops-core' ); ?></h3>
				<?php
				foreach ( $moc_martech_info as $moc_martech_row ) {
					$main_platform_name = $moc_martech_row['platform'];
					$year_experience    = (float) $moc_martech_row['experience'];
					$experience_string  = ( 1 >= $year_experience ) ? $year_experience . ' Year' : $year_experience . ' Years';
					$skill_level        = (int)$moc_martech_row['skill_level'];

					if ( 1 === $skill_level ) {
						$skill_level_txt = esc_html__( 'BASIC', 'marketing-ops-core' );
						$skill_class     = 'yellow_btn';
						$skill_html      = '<a id="' . $skill_class . '" class="expert_btn btn ' . esc_attr( $skill_class ) . '">' .esc_html( $skill_level_txt ) .'</a>';
					} elseif ( 2 === $skill_level ) {
						$skill_level_txt = esc_html__( 'INTERMEDIATE', 'marketing-ops-core' );
						$skill_class     = 'gradient_btn';
						$skill_html      = '<a id="' . $skill_class . '" class="expert_btn btn ' . esc_attr( $skill_class ) . '"><span>' .esc_html( $skill_level_txt ) .'</span></a>';
					} elseif ( 3 === $skill_level ) {
						$skill_level_txt = esc_html__( 'ADVANCED', 'marketing-ops-core' );
						$skill_class     = 'pink_btn';
						$skill_html      = '<a id="' . $skill_class . '" class="expert_btn btn ' . esc_attr( $skill_class ) . '">' .esc_html( $skill_level_txt ) .'</a>';
					} else {
						$skill_level_txt = esc_html__( 'EXPERT', 'marketing-ops-core' );
						$skill_class     = 'blue_btn';
						$skill_html      = '<a id="' . $skill_class . '" class="expert_btn btn ' . esc_attr( $skill_class ) . '">' .esc_html( $skill_level_txt ) .'</a>';
					}
					$excperience_description = $moc_martech_row['exp_descp'];
					?>
					<tr>
						<th><?php esc_html_e( 'Main platform', 'marketing-ops-core' ); ?></th>
						<td><input type="text" name="main_platform[]" id="main_platform" value="<?php echo esc_html( $main_platform_name ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Experience', 'marketing-ops-core' ); ?></th>
						<td><input type="text" name="moc_experience[]" id="moc_experience" value="<?php echo esc_html( $year_experience ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Skill level', 'marketing-ops-core' ); ?></th>
						<td>
							<input class="range_slider_input rangeslider" type="range" name="moc_skill_level[]" min="1" max="4" step="1" value="<?php echo esc_html( $skill_level ); ?>">
							<span class="moc_skill_span"><?php echo esc_html( $skill_level_txt ); ?></span>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Description', 'marketing-ops-core' ); ?></th>
						<td><textarea name="moc_exp_description[]" id="moc_exp_description" rows="10" cols="30" class=""><?php echo esc_html( $excperience_description ); ?></textarea></td>
					</tr>
				<?php } ?>
			</table>
		</div>
		<div id="tab_3" class="tabcontent">
			<table class="form-table">
				<h3><?php esc_html_e( 'Skills', 'marketing-ops-core' ); ?></h3>
				<?php 
				foreach ( $moc_cl_skill_info as $moc_cl_skill_row ) {
					$main_platform_name      = $moc_cl_skill_row['cl_platform'];
					$year_experience         = ( float )$moc_cl_skill_row['cl_experience'];
					$experience_string       = ( 1 >= $year_experience ) ? $year_experience . ' Year' : $year_experience . ' Years';
					$skill_level             = (int)$moc_cl_skill_row['cl_skill_level'];

					if ( 1 === $skill_level ) {
						$skill_level_txt = esc_html__( 'BASIC', 'marketing-ops-core' );
						$skill_class     = 'yellow_btn';
						$skill_html      = '<a id="' . $skill_class . '" class="expert_btn btn ' . esc_attr( $skill_class ) . '">' .esc_html( $skill_level_txt ) .'</a>';
					} elseif ( 2 === $skill_level ) {
						$skill_level_txt = esc_html__( 'INTERMEDIATE', 'marketing-ops-core' );
						$skill_class     = 'gradient_btn';
						$skill_html      = '<a id="' . $skill_class . '" class="expert_btn btn ' . esc_attr( $skill_class ) . '"><span>' .esc_html( $skill_level_txt ) .'</span></a>';
					} elseif ( 3 === $skill_level ) {
						$skill_level_txt = esc_html__( 'EXPERT', 'marketing-ops-core' );
						$skill_class     = 'blue_btn';
						$skill_html      = '<a id="' . $skill_class . '" class="expert_btn btn ' . esc_attr( $skill_class ) . '">' .esc_html( $skill_level_txt ) .'</a>';
					} else {
						$skill_level_txt = esc_html__( 'ADVANCED', 'marketing-ops-core' );
						$skill_class     = 'pink_btn';
						$skill_html      = '<a id="' . $skill_class . '" class="expert_btn btn ' . esc_attr( $skill_class ) . '">' .esc_html( $skill_level_txt ) .'</a>';
					}
					?>
					<tr>
						<th><?php esc_html_e( 'Coding language', 'marketing-ops-core' ); ?></th>
						<td><input type="text" name="moc_coding_language[]" id="moc_coding_language" value="<?php echo esc_html( $main_platform_name ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Experience', 'marketing-ops-core' ); ?></th>
						<td><input type="text" name="moc_cl_experience[]" id="moc_cl_experience" value="<?php echo esc_html( $year_experience ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Skill level', 'marketing-ops-core' ); ?></th>
						<td>
							<input class="range_slider_input rangeslider" type="range" name="moc_cl_skill_level[]" min="1" max="4" step="1" value="<?php echo esc_html( $skill_level ); ?>">
							<span class="moc_skill_span"><?php echo esc_html( $skill_level_txt ); ?></span>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
		<div id="tab_4" class="tabcontent"> 
			<table class="form-table">
				<h3><?php esc_html_e( 'Work History', 'marketing-ops-core' ); ?></h3>
				<?php 
				foreach ( $moc_work_data as $key=>$moc_work_info ) {
					$company_name            = ! empty( $moc_work_info['work_company'] ) ? $moc_work_info['work_company'] : '';
					$position                = ! empty ( $moc_work_info['work_position'] ) ? $moc_work_info['work_position'] : '';
					$work_moc_start_mm       = ! empty( $moc_work_info['work_moc_start_mm'] ) ? $moc_work_info['work_moc_start_mm'] : '';
					$work_moc_start_mm       = ( int )$work_moc_start_mm;
					$work_moc_start_yyyy     = ! empty( $moc_work_info['work_moc_start_yyyy'] ) ? $moc_work_info['work_moc_start_yyyy'] : '';
					$work_moc_start_yyyy     = ( int )$work_moc_start_yyyy;
					$work_moc_end_mm         = ! empty( $moc_work_info['work_moc_end_mm'] ) ? $moc_work_info['work_moc_end_mm'] : '';
					$work_moc_end_mm         = ( int )$work_moc_end_mm;
					$work_moc_end_yyyy       = ! empty( $moc_work_info['work_moc_end_yyyy'] ) ? $moc_work_info['work_moc_end_yyyy'] : '';
					$work_moc_end_yyyy       = ( int )$work_moc_end_yyyy;
					$website                 = ! empty( $moc_work_info['work_website'] ) ? $moc_work_info['work_website'] : '';
					$moc_at_present_val      = ! empty( $moc_work_info['moc_at_present_val'] ) ? $moc_work_info['moc_at_present_val'] : '';
					?>
					<tr>
						<th><?php esc_html_e( 'Company', 'marketing-ops-core' ); ?></th>
						<td><input type="text" name="moc_work_company[]" id="moc_work_company" value="<?php echo esc_html( $company_name ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Position', 'marketing-ops-core' ); ?></th>
						<td><input type="text" name="moc_work_position[]" id="moc_work_position" value="<?php echo esc_html( $position ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Start Year', 'marketing-ops-core' ); ?></th>
						<td>
							<select name="moc_start_month[]">
								<option value=""><?php esc_html_e( 'MM', 'marketing-ops-core' )?></option>
								<?php 
								$month_array = moc_months_array();
								foreach( $month_array as $key=>$month  ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $work_moc_start_mm ) ? 'selected' : ''; ?> ><?php echo esc_attr( $month ); ?></option>
								<?php } ?>
							</select>
							<select name="moc_start_year[]">
								<option value=""><?php esc_html_e( 'YYYY', 'marketing-ops-core' )?></option>
								<?php
								$get_current_year = date("Y");
								for( $i = 1970; $i <= $get_current_year; $i++ ) { ?>
									<option value="<?php echo esc_attr( $i ); ?>" <?php echo ( $i === $work_moc_start_yyyy ) ? 'selected' : ''; ?> ><?php echo esc_attr( $i ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'End Year', 'marketing-ops-core' ); ?></th>
						<td>
							<select name="moc_end_month[]">
								<option value=""><?php esc_html_e( 'MM', 'marketing-ops-core' )?></option>
								<?php 
								$month_array = moc_months_array();
								foreach( $month_array as $key=>$month  ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $work_moc_end_mm ) ? 'selected' : ''; ?> ><?php echo esc_attr( $month ); ?></option>
								<?php } ?>
							</select>
							<select name="moc_end_year[]">
								<option value=""><?php esc_html_e( 'YYYY', 'marketing-ops-core' )?></option>
								<?php
								$get_current_year = date("Y");
								for( $i = 1970; $i <= $get_current_year; $i++ ) { ?>
									<option value="<?php echo esc_attr( $i ); ?>" <?php echo ( $i === $work_moc_end_yyyy ) ? 'selected' : ''; ?> ><?php echo esc_attr( $i ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Present', 'marketing-ops-core' ); ?></th>
						<td><input id="moc_work_position" type="checkbox" name="moc_at_present_val[]" value="<?php echo esc_html( $moc_at_present_val ); ?>" <?php echo ( 'yes'  === $moc_at_present_val ) ? 'checked' : ''; ?> ></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Website', 'marketing-ops-core' ); ?></th>
						<td><input type="text" name="moc_work_website[]" id="moc_work_position" value="<?php echo esc_html( $website ); ?>" class="regular-text"></td>
					</tr>
				<?php }	?>
			</table>
		</div>
		<div id="tab_5" class="tabcontent">
			<table class="form-table">
				<?php
				$certificates_query = moc_posts_query( 'certificate', 1, -1 );
				$certificates_ids   = $certificates_query->posts;
				?>
				<tr>
					<th><?php esc_html_e( 'Selected Certificates', 'marketing-ops-core' ); ?></th>
					<td>
						<select name="moc_certificate[]" id="moc_certificate" multiple>
							<?php foreach ( $certificates_ids as $certificates_id ) { ?>
								<option value="<?php echo esc_html( $certificates_id ); ?>" <?php echo ( in_array( $certificates_id, $update_value_arr, true  ) ) ? 'selected' : ''; ?> ><?php echo esc_html( get_the_title( $certificates_id ) ); ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Function to return change gravavtar for default.
	 * @since    1.0.0
	 * @param    string $avatar_defaults This variable holds src of default image.
	 */
	public function moc_user_add_extra_field_update_callback ( $user_id ) {
		$user_all_info       = ( ! empty( get_user_meta( $user_id, 'user_all_info', true ) ) ) ? get_user_meta( $user_id, 'user_all_info', true ) : array();
		$user_basic_info     = ( ! empty( $user_all_info['user_basic_info'] ) ) ? $user_all_info['user_basic_info'] : array();
		$posted_array        = filter_input_array( INPUT_POST );
		$moc_social_links    = ( ! empty( $posted_array['moc_social_links'] ) && is_array( $posted_array['moc_social_links'] ) ) ? $posted_array['moc_social_links'] : array();
		$moc_social_tags     = ( ! empty( $posted_array['moc_social_tags'] ) && is_array( $posted_array['moc_social_tags'] ) ) ? $posted_array['moc_social_tags'] : array();
		$user_website        = ( ! empty( $posted_array['url'] ) ) ? $posted_array['url'] : '';
		$user_bio            = ( ! empty( $posted_array['description'] ) ) ? $posted_array['description'] : '';
		$cheked_industries   = ( ! empty( $posted_array['industry_experience'] ) ) ? $posted_array['industry_experience'] : array();

		// Social Links
		foreach ( $moc_social_tags as $index => $moc_social_tag ) {
			if ( empty( $moc_social_links[ $index ] ) ) continue;

			$moc_updated_social_arr[] = array(
				'tag' => $moc_social_tag,
				'val' => $moc_social_links[ $index ],
			);
		}

		// Martech_info
		$main_platforms      = ( ! empty( $posted_array['main_platform'] ) && is_array( $posted_array['main_platform'] ) ) ? $posted_array['main_platform'] : array();
		$moc_experience      = ( ! empty( $posted_array['moc_experience'] ) && is_array( $posted_array['moc_experience'] ) ) ? $posted_array['moc_experience'] : array();
		$moc_skill_level     = ( ! empty( $posted_array['moc_skill_level'] ) && is_array( $posted_array['moc_skill_level'] ) ) ? $posted_array['moc_skill_level'] : array();
		$moc_exp_description = ( ! empty( $posted_array['moc_exp_description'] ) && is_array( $posted_array['moc_exp_description'] ) ) ? $posted_array['moc_exp_description'] : array();

		foreach ( $main_platforms as $index => $main_platform ) {
			$moc_update_martech_arr[] = array(
				'platform'    => $main_platform,
				'experience'  => $moc_experience[ $index ],
				'skill_level' => $moc_skill_level[ $index ],
				'exp_descp'   => $moc_exp_description[ $index ],
			);
		}

		// Skill_info
		$moc_coding_languages = $posted_array['moc_coding_language'];
		$moc_cl_experience    = $posted_array['moc_cl_experience'];
		$moc_cl_skill_level   = $posted_array['moc_cl_skill_level'];

		foreach ( $moc_coding_languages as $index => $moc_coding_language ) {
			$moc_update_skill_arr[] = array(
				'cl_platform'    => $moc_coding_language,
				'cl_experience'  => $moc_cl_experience[ $index ],
				'cl_skill_level' => $moc_cl_skill_level[ $index ],
			);
		}

		// Work_info
		$moc_work_companies = $posted_array['moc_work_company'];
		$moc_work_position  = $posted_array['moc_work_position'];
		$moc_start_month    = $posted_array['moc_start_month'];
		$moc_start_year     = $posted_array['moc_start_year'];
		$moc_end_month      = $posted_array['moc_end_month'];
		$moc_end_year       = $posted_array['moc_end_year'];
		$moc_work_website   = $posted_array['moc_work_website'];
		$moc_at_present_val = $posted_array['moc_at_present_val'];

		foreach ( $moc_work_companies as $index => $moc_work_company ) {
			$moc_at_present_value = empty( $moc_at_present_val[ $index ] ) ? 'no' : 'yes';
			$moc_update_work_history_arr[] = array(
				'work_company'        => $moc_work_company,
				'work_position'       => $moc_work_position[ $index ],
				'work_moc_start_mm'   => $moc_start_month[ $index ],
				'work_moc_start_yyyy' => $moc_start_year[ $index ],
				'work_moc_end_mm'     => $moc_end_month[ $index ],
				'work_moc_end_yyyy'   => $moc_end_year[ $index ],
				'work_website'        => $moc_work_website[ $index ],
				'moc_at_present_val'  => $moc_at_present_value,
			);
		}

		// Certificates
		$moc_certificate           = $posted_array['moc_certificate'];

		$updated_user_array        = array(
			'user_basic_info' => array(
				'user_bio'          => $user_bio,
				'user_website'      => $user_website,
				'social_media_arr'  => $moc_updated_social_arr,
				'cheked_industries' => $cheked_industries,
			),
		);
		$updated_martech_array     = array( 'moc_martech_info' => $moc_update_martech_arr );
		$updated_skill_array       = array( 'moc_cl_skill_info' => $moc_update_skill_arr );
		$updated_work_array        = array( 'moc_work_data' => $moc_update_work_history_arr );
		$updated_certificate_array = array( 'moc_certificates' => $moc_certificate );
		$final_array_update        = array_merge( $updated_user_array, $updated_martech_array, $updated_skill_array, $updated_work_array, $updated_certificate_array );

		update_user_meta( $user_id, 'user_all_info', $final_array_update );
	}

	/**
	 * Function to return set column show on listing category.
	 * @since    1.0.0
	 * @param    string $columns This variable holds the column of category.
	 */
	public function moc_add_show_in_frontend_column_columns( $columns ) {
		$show_all_frontend = array();
		$all_terms         = get_terms(
			array(
				'taxonomy' => 'category',
				'hide_empty' => false,
			)
		);

		foreach ( $all_terms as $all_term ) {
			$term_id             = $all_term->term_id;
			$get_term_meta       = get_term_meta( $term_id, 'moc_show_category_in_frontend', true );
			$get_term_meta       = $get_term_meta[0];
			$show_all_frontend[] = ( 'yes' === $get_term_meta ) ? 'yes' : 'no';
		}

		$checked_value               = ( ! in_array( 'no', $show_all_frontend, true ) ) ? 'checked' : '';
		$columns['show_in_frontend'] = '<input type="checkbox" name="moc_all_show_on_frontend[]" '. $checked_value .' >  Show In Front';

		return $columns;
	}

	/**
	 * Function to return set column content on listing category.
	 * @since    1.0.0
	 * @param    string  $content This variable holds the content of category.
	 * @param    string  $column_name This variable holds the column name of category.
	 * @param    integer $term_id This variable holds the term ID of category.
	 */
	public function moc_add_show_in_frontend_column_content( $content, $column_name, $term_id ) {
		$get_term_meta = get_term_meta( $term_id, 'moc_show_category_in_frontend', true );
		$get_term_meta = $get_term_meta[0];
		$checked_value = ( 'yes' === $get_term_meta ) ? 'checked' : '';

		switch ( $column_name ) {
			case 'show_in_frontend':
				$content = '<input type="checkbox" name="moc_show_on_frontend[]" data-termid="' . $term_id . '" value="' . $get_term_meta . '" ' . $checked_value . ' >';
				break;
			default:
				break;
		}

		return $content;
	}

	/**
	 * Function to return set column show on listing category.
	 * @since    1.0.0
	 * @param    string $columns This variable holds the column of category.
	 */
	public function moc_add_show_in_frontend_podcast_category_column_columns( $columns ) {
		$show_all_frontend = array();
		$all_terms         = get_terms(
			array(
				'taxonomy' => 'podcast_category',
				'hide_empty' => false,
			)
		);

		foreach ( $all_terms as $all_term ) {
			$term_id = $all_term->term_id;
			$get_term_meta = get_term_meta( $term_id, 'moc_show_category_in_frontend', true );
			$get_term_meta = $get_term_meta[0];
			$show_all_frontend[] = ( 'yes' === $get_term_meta ) ? 'yes' : 'no';

		}

		$checked_value               = ( ! in_array( 'no', $show_all_frontend, true ) ) ? 'checked' : '';
		$columns['show_in_frontend'] = '<input type="checkbox" name="moc_all_show_on_frontend[]" '. $checked_value .' >  Show In Frontend';

		return $columns;
	}

	/**
	 * Function to return set column content on listing category.
	 * @since    1.0.0
	 * @param    string  $content This variable holds the content of category.
	 * @param    string  $column_name This variable holds the column name of category.
	 * @param    integer $term_id This variable holds the term ID of category.
	 */
	public function moc_add_show_in_frontend_podcast_category_column_content( $content, $column_name,$term_id ) {
		$get_term_meta = get_term_meta( $term_id, 'moc_show_category_in_frontend', true );
		$get_term_meta = $get_term_meta[0];
		$checked_value = ( 'yes' === $get_term_meta ) ? 'checked' : '';

		switch ( $column_name ) {
			case 'show_in_frontend':
				$content = '<input type="checkbox" name="moc_show_on_frontend[]" data-termid="' . $term_id . '" value="' . $get_term_meta . '" ' . $checked_value . ' >';
				break;
			default:
				break;
		}

		return $content;
	}

	/**
	 * Function to return set column show on listing category.
	 * @since    1.0.0
	 * @param    string $columns This variable holds the column of category.
	 */
	public function moc_add_show_in_frontend_nobsdemo_category_column_columns( $columns ) {
		$show_all_frontend = array();
		$all_terms         = get_terms(
			array(
				'taxonomy'   => 'no_bs_demo_category',
				'hide_empty' => false,
			)
		);

		foreach ( $all_terms as $all_term ) {
			$term_id = $all_term->term_id;
			$get_term_meta = get_term_meta( $term_id, 'moc_show_category_in_frontend', true );
			$get_term_meta = $get_term_meta[0];
			$show_all_frontend[] = ( 'yes' === $get_term_meta ) ? 'yes' : 'no';
		}

		$checked_value               = ( ! in_array( 'no', $show_all_frontend, true ) ) ? 'checked' : '';
		$columns['show_in_frontend'] = '<input type="checkbox" name="moc_all_show_on_frontend[]" '. $checked_value .' >  Show In Frontend';

		return $columns;
	}

	/**
	 * Function to return set column content on listing category.
	 * @since    1.0.0
	 * @param    string  $content This variable holds the content of category.
	 * @param    string  $column_name This variable holds the column name of category.
	 * @param    integer $term_id This variable holds the term ID of category.
	 */
	public function moc_add_show_in_frontend_nobsdemo_category_column_content( $content, $column_name,$term_id ) {
		$get_term_meta = get_term_meta( $term_id, 'moc_show_category_in_frontend', true );
		$get_term_meta = $get_term_meta[0];
		$checked_value = ( 'yes' === $get_term_meta ) ? 'checked' : '';

		switch ( $column_name ) {
			case 'show_in_frontend':
				$content = '<input type="checkbox" name="moc_show_on_frontend[]" data-termid="' . $term_id . '" value="' . $get_term_meta . '" ' . $checked_value . ' >';
				break;
			default:
				break;
		}

		return $content;
	}

	/**
	 * Function to return ajax callback for saving column custom data.
	 * @since    1.0.0
	 */
	public function moc_make_enable_disable_show_in_frontend_callback() {
		$term_id       = (int) filter_input( INPUT_POST, 'term_id', FILTER_SANITIZE_NUMBER_INT );
		$checkbox_val  = filter_input( INPUT_POST, 'checkbox_val', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$checkbox_val  = ( 'yes' === $checkbox_val ) ? array( 'yes' ) : array( 'no' );
		$field         = acf_get_field( 'moc_show_category_in_frontend' );
		$field_key     = $field['key'];

		update_field( $field_key, $checkbox_val, 'category_'.$term_id );

		wp_send_json_success( array( 'code' => 'marketinops-update-taxonomy' ) );
		wp_die();
	}

	/**
	 * Function to return ajax callback for saving column custom data.
	 * @since    1.0.0
	 */
	public function moc_make_enable_disable_show_in_frontend_for_all_callback() {
		$posted_array  = filter_input_array( INPUT_POST );
		$checkbox_arr  = $posted_array[ 'checkbox_arr' ];
		$field         = acf_get_field( 'moc_show_category_in_frontend' );
		$field_key     = $field['key'];
		foreach ( $checkbox_arr as $checkbox_val ) {
			$term_id      = $checkbox_val[ 'term_id' ];
			$checkbox_val = $checkbox_val[ 'checkbox_val' ];
			$checkbox_val = ( 'yes' === $checkbox_val ) ? array( 'yes' ) : array( 'no' );
			update_field( $field_key, $checkbox_val, 'category_'.$term_id);
		}

		wp_send_json_success( array( 'code' => 'marketinops-update-all-taxonomy' ) );
		wp_die();
	}

	/**
	 * Register product setting tabs in WooCommerce Products.
	 *
	 * @param array $tabs Holds the list of registered product settings tabs.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function moc_woocommerce_product_data_tabs_callback( $tabs ) {
		// Select Professor for courses.
		$professor_tab_title = __( 'Professor', 'marketing-ops-core' );

		/**
		 * This hook fires in admin panel on the item settings page.
		 *
		 * This filter will help in modifying the product type tab title - blockout dates.
		 *
		 * @param string $professor_tab_title Holds the product type tab title.
		 *
		 * @return string
		 */
		$professor_tab_title = apply_filters( 'moc_product_professor_tab_label', $professor_tab_title );

		// Add the new tab - reservation blockout dates.
		$tabs['moc_product_professor'] = array(
			'label'    => $professor_tab_title,
			'target'   => 'moc_product_professor_options',
			'class'    => array(
				'moc_moc_product_professor',
				"show_if_{$this->custom_product_type}",
				'hide_if_simple',
				'hide_if_grouped',
				'hide_if_external',
				'hide_if_variable',
			),
			'priority' => 68,
		);
		// Hide the general tab.
		if ( ! empty( $tabs['general'] ) ) {
			$tabs['general']['class'][] = "show_if_{$this->custom_product_type}";
		}

		// Hide the inventory tab.
		if ( ! empty( $tabs['inventory'] ) ) {
			$tabs['inventory']['class'][] = "show_if_{$this->custom_product_type}";
		}

		// Hide the shipping tab.
		if ( ! empty( $tabs['shipping'] ) ) {
			$tabs['shipping']['class'][] = "show_if_{$this->custom_product_type}";
		}

		// Hide the linked products tab.
		if ( ! empty( $tabs['linked_product'] ) ) {
			$tabs['linked_product']['class'][] = "show_if_{$this->custom_product_type}";
		}

		// Hide the attributes tab.
		if ( ! empty( $tabs['attribute'] ) ) {
			$tabs['attribute']['class'][] = "hide_if_{$this->custom_product_type}";
		}

		// Hide the variations tab.
		if ( ! empty( $tabs['variations'] ) ) {
			$tabs['variations']['class'][] = "hide_if_{$this->custom_product_type}";
		}
		return $tabs;
	}

	/**
	 * Create the settings template for the reservation type.
	 *
	 * @since 1.0.0
	 */
	public function moc_woocommerce_product_data_panels_callback() {
		global $post;

		if ( empty( $post->ID ) ) {
			return;
		}

		// Reservation details.
		require_once MOC_PLUGIN_PATH . 'admin/templates/product-tab/professor.php';
	}

	/**
	 * Update product custom meta details.
	 *
	 * @param int $post_id Holds the product ID.
	 *
	 * @since 1.0.0
	 */
	public function moc_woocommerce_process_product_meta_callback( $post_id ) {
		$professor_id = filter_input( INPUT_POST, 'moc_selected_professors', FILTER_SANITIZE_NUMBER_INT );

		if ( ! empty( $professor_id ) ) {
			update_post_meta( $post_id, '_moc_selected_professors', $professor_id );
		} else {
			delete_post_meta( $post_id, '_moc_selected_professors' );
		}
	}

	/**
	 * Register a new product type in WooCommerce Products.
	 *
	 * @param array $product_types Holds the list of registered product types.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function moc_product_type_selector_callback( $product_types ) {
		$product_type_label = moc_get_custom_product_type_label();

		// Check if the reservation product type already exists. Return, if it already exists.
		if ( in_array( $this->custom_product_type, $product_types, true ) ) {
			return $product_types;
		}

		// Add the new product type.
		$product_types[ $this->custom_product_type ] = $product_type_label;

		return $product_types;
	}

	public function moc_mime_types( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';

		return $mimes;
	}

	public function moc_change_author_base_url( $link, $user_id ) {
		$link_base = trailingslashit( get_option('home') );
		$link      = preg_replace("|^{$link_base}author/|", 'profile/', $link);

		return $link_base . $link;
	}

	/**
	 * Add new columns to the users listing on admin end.
	 *
	 * @since 1.0.0
	 * @param $defaults array Users table default columns.
	 * @return array
	 */
	public function moc_manage_users_columns_callback( $defaults ) {
		$defaults['show-in-frontend'] = __( 'Public Visiblity', 'marketing-ops-core' );
		$defaults['memeber_since']    = __( 'Member Since', 'marketing-ops-core' );
		
		return $defaults;
	}
	
	/**
	 * Content of the custom column on users table.
	 *
	 * @since 1.0.0
	 * @param $defaults array Users table default columns.
	 * @param $defaults array Users table default columns.
	 * @return array
	 */
	public function moc_manage_users_custom_column_callback( $column_content, $column_name, $user_id ) {
		if ( 'show-in-frontend' == $column_name ) {
			$show_in_frontend = get_user_meta( $user_id, 'moc_show_in_frontend', true );
			ob_start();
			?><input type="checkbox" class="toggle-show-in-frontend" value="1" <?php echo esc_attr( ( ! empty( $show_in_frontend ) && 'yes' === $show_in_frontend ) ? 'checked' : '' ); ?> /><?php
			
			$column_content = ob_get_clean();
		} elseif ( 'memeber_since' === $column_name ) {
			$user_info      = get_userdata( $user_id );
			$column_content = $user_info->user_registered;
		}

		return $column_content;
	}

	/**
	 * Add new columns to the users listing on admin end.
	 *
	 * @since 1.0.0
	 * @param $columns array Users table default columns.
	 * @return array
	 */
	public function moc_manage_users_sortable_columns_callback( $columns ) {
		$columns["memeber_since"] = __( 'Member Since', 'marketing-ops-core' );

		return $columns;
	}

	/**
	 * AJAX for toggling featured partyguru.
	 *
	 * @since 1.0.0
	 */
	public function moc_toggle_user_visiblity_callback() {
		// Posted data.
		$user_id     = (int) filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
		$show_in_frontend = filter_input( INPUT_POST, 'show_in_frontend', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		update_user_meta( $user_id, 'moc_show_in_frontend', $show_in_frontend ); // Update the usermeta.

		// Send the AJAX response now.
		wp_send_json_success(
			array(
				'code'          => 'toggled-show-in-frontend-user',
				'toast_message' => ( 'yes' === $show_in_frontend ) ? __( 'User visiblity enabled.', 'marketing-ops-core' ) : __( 'User visiblity disabled.', 'marketing-ops-core' ),
			)
		);
		wp_die();
	}

	public function moc_wc_membership_plan_data_tabs_callback( $fields ) {
		$fields['membership_restrict_popup'] = array(
			'label'  => __( 'Membership restrct popup content', 'marketing-ops-core' ),
			'target' => 'membership-restrict-popup-content',
			'class'  => '',
		);

		return $fields;
	}

	public function moc_wc_membership_plan_data_panels_callback() {
		global $post;

		// Start preparing the HTML.
		ob_start();
		?>
		<div id="membership-restrict-popup-content" class="panel woocommerce_options_panel">
			<div class="options_group">
				<?php
				woocommerce_wp_text_input( 
					array( 
						'id'          => 'membership_restrict_popup_title',
						'name'        => 'membership_restrict_popup_title',
						'label'       => __( 'Title', 'marketing-ops-core' ),
						'placeholder' => __( 'Free Membership', 'marketing-ops-core' ),
						'desc_tip'    => 'true',
						'description' => __( 'Title of restriction popup', 'marketing-ops-core' ),
						'value'       => get_post_meta( $post->ID, 'membership_restrict_popup_title', true ),
					)
				);
				woocommerce_wp_textarea_input(
					array(
						'id'                => 'membership_restrict_popup_description',
						'name'              => 'membership_restrict_popup_description',
						'label'             => __( 'Title', '' ),
						'placeholder'       => __( 'Description', 'marketing-ops-core' ),
						'desc_tip'          => 'true',
						'description'       => __( 'Description of popup', '' ),
						'value'             => get_post_meta( $post->ID, 'membership_restrict_popup_description', true ),
						'custom_attributes' => array(
							'rows' => 2,
						),
					)
				);
				woocommerce_wp_text_input( 
					array( 
						'id'          => 'membership_restrict_popup_btn_title',
						'name'        => 'membership_restrict_popup_btn_title',
						'label'       => __( 'Button Title', 'marketing-ops-core' ),
						'placeholder' => __( 'Create a free account', 'marketing-ops-core' ),
						'desc_tip'    => 'true',
						'description' => __( 'Button Title', 'marketing-ops-core' ),
						'value'       => get_post_meta( $post->ID, 'membership_restrict_popup_btn_title', true ),
					)
				);
				woocommerce_wp_text_input( 
					array( 
						'id'          => 'membership_restrict_popup_btn_link',
						'name'        => 'membership_restrict_popup_btn_link',
						'label'       => __( 'Button Link', 'marketing-ops-core' ),
						'placeholder' => __( 'https://example.com', 'marketing-ops-core' ),
						'desc_tip'    => 'true',
						'description' => __( 'Button Link', 'marketing-ops-core' ),
						'value'       => get_post_meta( $post->ID, 'membership_restrict_popup_btn_link', true ),
					)
				);
				?>
			</div>
		</div>
		<?php

		echo ob_get_clean();
	}

	/**
	 * Function to return save data on save post.
	 *
	 * @param integer $post_id This variable holds the post id.
	 * @since 1.0.0
	 */
	public function moc_save_data_on_save_callback( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
		
		if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;

		$post = get_post( $post_id );

		/* save data only for membership plan custom post type */
		if ( 'wc_membership_plan' === $post->post_type ) {
			/* Update custom data to membership plan */
			update_post_meta( $post_id, 'membership_restrict_popup_title', filter_input( INPUT_POST, 'membership_restrict_popup_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
			update_post_meta( $post_id, 'membership_restrict_popup_description', filter_input( INPUT_POST, 'membership_restrict_popup_description', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
			update_post_meta( $post_id, 'membership_restrict_popup_btn_title', filter_input( INPUT_POST, 'membership_restrict_popup_btn_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
			update_post_meta( $post_id, 'membership_restrict_popup_btn_link', filter_input( INPUT_POST, 'membership_restrict_popup_btn_link', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		}

		return $post_id;
	}

	/**
	 * Function to add more triggers on achivements
	 *
	 * @param array $triggers This variable holds the triggers action of achivements learndash.
	 * @since 1.0.0
	 */
	public function moc_add_more_triggers_in_ld_achivements( $triggers ) {
		$triggers['LearnDash']['course_completed_1']  = __( 'User completes a course', 'learndash-achievements' );
		$triggers['LearnDash']['course_completed_5']  = __( 'User Completes five courses', 'learndash-achievements' );
		$triggers['LearnDash']['course_completed_10'] = __( 'User completes ten courses', 'learndash-achievements' );
		$triggers['LearnDash']['course_completed_15'] = __( 'User completes fifteen courses', 'learndash-achievements' );
		$triggers['LearnDash']['course_completed_20'] = __( 'User completes twenty courses', 'learndash-achievements' );

		unset( $triggers[ 'LearnDash' ]['complete_course'] );

		return $triggers;
	}

	/**
	 * Function to return call on admin init functions.
	 *
	 * @since 1.0.0
	 */
	public function moc_admin_init_callback() {
		add_post_type_support( 'sfwd-courses', 'author' );
	}

	/**
	 * Customize the row actions for the courses.
	 *
	 * @param array   $actions Post row actions.
	 * @param WP_Post $post WordPress post object.
	 *
	 * @return array
	 */
	public function moc_post_row_actions_callback( $actions, $post ) {
		// Return, if the post object is unavailable.
		if ( empty( $post ) ) {
			return $actions;
		}

		// Return, if the current user is not administrator.
		if ( ! current_user_can( 'manage_options' ) ) {
			return $actions;
		}

		// For the learndash courses.
		if ( 'sfwd-courses' === $post->post_type ) {
			// Remove the "Clone" action.
			unset( $actions['learndash_cloning_action_course'] );
		}

		return $actions;
	}

	/**
	 * Customize the row actions for the users.
	 *
	 * @param array   $actions Post row actions.
	 * @param WP_User $user WordPress user object.
	 *
	 * @return array
	 */
	public function moc_user_row_actions_callback( $actions, $user_object ) {
		// Return, if the user object is unavailable.
		if ( empty( $user_object ) ) {
			return $actions;
		}

		// Return, if the current user is not administrator.
		if ( ! current_user_can( 'manage_options' ) ) {
			return $actions;
		}

		// Remove the unwanted actions.
		unset( $actions['create-author'] );
		unset( $actions['resetpassword'] );
		unset( $actions['capabilities'] );

		return $actions;
	}

	/**
	 * Disable gutenberg editor for certain post types.
	 *
	 * @param boolean $current_status Current status of the gutenberg editor.
	 * @param string  $post_type Post type.
	 *
	 * @return boolean
	 */
	public function moc_use_block_editor_for_post_type_callback( $current_status, $post_type ) {
		// Return, if the post type is unavailable.
		if ( empty( $post_type ) ) {
			return $current_status;
		}

		// If the post type is strategists.
		if ( 'strategists' === $post_type ) {
			return false;
		} elseif ( 'template' === $post_type ) { // If the post type is project templates.
			return false;
		} elseif ( 'conference_vault' === $post_type ) { // If the post type is conference vault videos.
			return false;
		}

		return $current_status;
	}

	/**
	 * Add custom metaboxes.
	 *
	 * @since 1.0.0
	 */
	public function moc_add_meta_boxes_callback() {
		// Add the metabox for showing the download counter for the project templates.
		add_meta_box(
			'prj_template_download_counter_info',
			__( 'Download Counter', 'marketing-ops-core' ),
			array( $this, 'moc_prj_template_download_counter_callback' ),
			'template',
			'side',
			'high'
		);

		// Add the metabox for showing the favourite counter and details for the project templates.
		add_meta_box(
			'prj_template_favourite_counter_info',
			__( 'Favourite Counter', 'marketing-ops-core' ),
			array( $this, 'moc_prj_template_favourite_counter_callback' ),
			'template',
			'side',
			'high'
		);
	}

	/**
	 * Tempplate for the download counter information.
	 *
	 * @since 1.0.0
	 */
	public function moc_prj_template_download_counter_callback() {
		$post_id          = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$download_counter = ( ! is_null( $post_id ) ) ? (int) get_post_meta( $post_id, 'template_download', true ) : 0;

		echo wp_kses_post( sprintf( __( '%2$sThis template has been downloaded %4$s%1$d%5$s times.%3$s', 'marketing-ops-core' ), $download_counter, '<p>', '</p>', '<strong>', '</strong>' ) );
	}

	/**
	 * Tempplate for the favourite counter information.
	 *
	 * @since 1.0.0
	 */
	public function moc_prj_template_favourite_counter_callback() {
		$post_id        = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$template_likes = ( ! is_null( $post_id ) ) ? get_post_meta( $post_id, 'template_likes', true ) : array();
		$template_likes = ( ! empty( $template_likes ) && is_array( $template_likes ) ) ? $template_likes : array();

		echo wp_kses_post( sprintf( __( '%2$sThis template has been marked favourite %4$s%1$d%5$s times.%3$s', 'marketing-ops-core' ), count( $template_likes ), '<p>', '</p>', '<strong>', '</strong>' ) );

		// If the fovourite counter is greater than 0.
		if ( 0 < count( $template_likes ) ) {
			echo wp_kses_post( sprintf( __( '%1$sPeople who marked it favourite are listed below:%2$s', 'marketing-ops-core' ), '<p>', '</p>' ) );

			echo '<ol class="mops-template-likes-user-list">';
			foreach ( $template_likes as $user_id ) {
				echo '<li>' . get_user_meta( $user_id, 'first_name', true ) . ' - ' . get_user_meta( $user_id, 'last_name', true ) . '</li>';
			}
			echo '</ol>';
		}
	}

	/**
	 * Add custom columns to the 'project template' posts.
	 *
	 * @param array $default_cols Columns array.
	 * @return array
	 * @since 1.0.0
	 */
	public function cf_manage_edit_template_columns_callback( $default_cols ) {
		// If the array key doesn't exist for download counter.
		if ( ! array_key_exists( 'downloads', $default_cols ) ) {
			$default_cols['downloads'] = __( 'Downloads', 'marketing-ops-core' );
		}

		// If the array key doesn't exist for favourites counter.
		if ( ! array_key_exists( 'favourites', $default_cols ) ) {
			$default_cols['favourites'] = __( 'Favourites', 'marketing-ops-core' );
		}

		// If the array key doesn't exist for template file.
		if ( ! array_key_exists( 'template_file', $default_cols ) ) {
			$default_cols['template_file'] = __( 'File', 'marketing-ops-core' );
		}

		return $default_cols;
	}

	/**
	 * Add custom column data to the 'project template' posts.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id Post ID.
	 * @since 1.0.0
	 */
	public function cf_manage_template_posts_custom_column_callback( $column_name, $post_id ) {
		// Print the content for "download counter" column name.
		if ( 'downloads' === $column_name ) {
			echo ( ! is_null( $post_id ) ) ? (int) get_post_meta( $post_id, 'template_download', true ) : 0;
		}

		// Print the content for "favourite counter" column name.
		if ( 'favourites' === $column_name ) {
			$template_likes = ( ! is_null( $post_id ) ) ? get_post_meta( $post_id, 'template_likes', true ) : array();
			$template_likes = ( ! empty( $template_likes ) && is_array( $template_likes ) ) ? $template_likes : array();
			echo ( ! is_null( $post_id ) ) ? count( $template_likes ) : 0;
		}

		// Print the content for "template file" column name.
		if ( 'template_file' === $column_name ) {
			$file = get_field( 'template_file', $post_id );

			// If the file is available.
			if ( ! empty( $file ) ) {
				$file_url = wp_get_attachment_url( $file );
				ob_start();
				?>
				<a target="_blank" title="<?php echo wp_kses_post( basename( $file_url ) ); ?>" href="<?php echo esc_url( get_edit_post_link( $file ) ); ?>"><?php echo wp_kses_post( basename( $file_url ) ); ?></a>
				<?php

				echo ob_get_clean();
			}
		}
	}

	/**
	 * Add custom columns to the 'podcast' posts.
	 *
	 * @param array $default_cols Columns array.
	 * @return array
	 * @since 1.0.0
	 */
	public function cf_manage_edit_podcast_columns_callback( $default_cols ) {
		// If the array key doesn't exist for podcast guests.
		if ( ! array_key_exists( 'podcast_guest', $default_cols ) ) {
			$default_cols['podcast_guest'] = __( 'Guest(s)', 'marketing-ops-core' );
		}

		return $default_cols;
	}

	/**
	 * Add custom column data to the 'podcast' posts.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id Post ID.
	 * @since 1.0.0
	 */
	public function cf_manage_podcast_posts_custom_column_callback( $column_name, $post_id ) {
		// Print the content for "template file" column name.
		if ( 'podcast_guest' === $column_name ) {
			$guests = get_field( 'podcast_guest', $post_id );

			// If the guests are available.
			if ( ! empty( $guests ) && is_array( $guests ) ) {
				// Loop through the guests array.
				foreach ( $guests as $guest_id ) {
					$first_name     = get_user_meta( $guest_id, 'first_name', true );
					$last_name      = get_user_meta( $guest_id, 'last_name', true );
					$guest_edit     = get_edit_user_link( $guest_id );
					$print_guests[] = '<a href="' . $guest_edit . '" title="' . $first_name . ' ' . $last_name . '">' . $first_name . ' ' . $last_name . '</a>';
				}

				// Print the guests.
				echo wp_kses_post( implode( ', ', $print_guests ) );
			} else {
				echo wp_kses_post( sprintf( __( '%1$sNo guests added.%2$s', 'marketing-ops-core' ), '<p>', '</p>' ) );
			}
		}
	}

	/**
	 * Filter hooked to redirect automated emails to my email abotu any technical issues on the website.
	 *
	 * @param array  $email {
	 * 		Used to build a call to wp_mail().
	 *
	 * 		@type string|array $to          Array or comma-separated list of email addresses to send message.
	 * 		@type string       $subject     Email subject
	 * 		@type string       $message     Message contents
	 * 		@type string|array $headers     Optional. Additional headers.
	 * 		@type string|array $attachments Optional. Files to attach.
	 * }
	 * @param string $url   URL to enter recovery mode.
	 * @return string
	 * @since 1.0.0
	 */
	public function moc_recovery_mode_email_callback( $email, $url ) {
		$email['to'] = array( 'adarsh.srmcem@gmail.com', 'mike@marketingops.com' );

		return $email;
	}

	/**
	 * Add custom columns to the 'conference_vault' posts.
	 *
	 * @param array $default_cols Columns array.
	 * @return array
	 * @since 1.0.0
	 */
	public function cf_manage_edit_conference_vault_columns_callback( $default_cols ) {
		// If the array key doesn't exist for session video.
		if ( ! array_key_exists( 'session_video', $default_cols ) ) {
			$default_cols['session_video'] = __( 'Video', 'marketing-ops-core' );
		}

		// If the array key doesn't exist for session speakers.
		if ( ! array_key_exists( 'session_speaker', $default_cols ) ) {
			$default_cols['session_speaker'] = __( 'Speaker(s)', 'marketing-ops-core' );
		}

		return $default_cols;
	}

	/**
	 * Add custom column data to the 'conference_vault' posts.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id Post ID.
	 * @since 1.0.0
	 */
	public function cf_manage_conference_vault_posts_custom_column_callback( $column_name, $post_id ) {
		// Print the content for "session video" column name.
		if ( 'session_video' === $column_name ) {
			$video_url = get_field( 'vimeo_video_url', $post_id );
			echo '<iframe src="' . $video_url . '?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;color=ffffff" width="640" height="360" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>';
		}

		if ( 'session_speaker' === $column_name ) {
			$video_speaker = get_field( 'session_author', $post_id );
			echo $video_speaker;
		}
	}
}
