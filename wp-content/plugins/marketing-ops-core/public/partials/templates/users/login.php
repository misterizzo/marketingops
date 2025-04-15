<?php
/**
 * User Login Template.
 *
 * This template can be overridden by copying it to yourtheme/marketing-ops-core/users/login.php.
 *
 * @see         https://marketingops.com/
 * @author      Adarsh Verma
 * @package     Marketing_Ops_Core
 * @category    Template
 * @since       1.0.0
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

get_header();

if ( is_user_logged_in() ) {
	?>
	<div class="elementor-column elementor-col-100 elementor-top-column elementor-element register_content">
		<div class="elementor-widget-wrap elementor-element-populated">
			<div class="elementor-element profileheading gradient-title elementor-widget elementor-widget-heading">
				<div class="elementor-widget-container">
					<h2 class="elementor-heading-title elementor-size-default"><?php esc_html_e( 'You are already loggedin!', 'marketingops' ); ?></h2>
				</div>
			</div>
			<div class="elementor-element profileheading elementor-widget elementor-widget-button">
				<div class="elementor-widget-container">
					<a href="<?php echo esc_url( home_url( 'profile' ) ); ?>" class="elementor-button elementor-size-default"><?php esc_html_e( 'Go to my profile', 'marketingops' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<?php
} else {
	?>
	<div class="elementor-column elementor-col-100 elementor-top-column elementor-element register_content login_content moc_login_form_section register_page login_page">
		<div class="elementor-widget-wrap elementor-element-populated moc-register-container elementor-container elementor-column-gap-default">
			<div class="loader_bg">
				<div class="loader"></div>  
			</div>
			<div class="elementor-element profileheading gradient-title elementor-widget elementor-widget-heading">
				<div class="elementor-widget-container">
					<h2 class="elementor-heading-title elementor-size-default"><?php esc_html_e( 'Welcome back!', 'marketingops' ); ?></h2>
				</div>
			</div>
			<div class="elementor-element profilesubheading elementor-widget elementor-widget-text-editor">
				<div class="elementor-widget-container">
					<p><?php esc_html_e( 'Login to manage your account!', 'marketingops' ); ?></p>
				</div>
			</div>
			<div class="elementor-element profilfrm elementor-widget elementor-widget-shortcode">
				<div class="elementor-widget-container"><?php wp_login_form(); ?></div>
			</div>
		</div>
	</div>
	<?php
}

get_footer();
