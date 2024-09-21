<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html       = array(
	'a' => array(
		'href' => array(),
	),
);
$default_author_img = get_field( 'moc_user_default_image', 'option' );
$author_img_id      = ! empty( get_user_meta( $current_user->ID, 'wp_user_avatar', true ) ) ? get_user_meta( $current_user->ID, 'wp_user_avatar', true ) : '';
$author_img_url     = ( empty( $author_img_id ) || false === $author_img_id ) ? $default_author_img : wp_get_attachment_url( $author_img_id );

if ( '49.36.66.201' === $_SERVER['REMOTE_ADDR'] ) { ?>
	<div class="newdashbordmain">
		<h3>Hello Tony Ryzhkov! </h3><span class="hedertitlethree">Take a look:</span>
		<ul class="dashbordlistmain">
			<li>
				<div class="innerdashbordlist">
					<div class="innsersubdashbordlist">
						<div class="iconwithtitledashbord">
							<i> 
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 3.5C11.2031 3.5026 10.5104 4.03906 10.3099 4.8099C10.1094 5.58073 10.4557 6.38802 11.1484 6.77865L9.04428 10.9844L5.23438 7.17448C5.57032 6.63542 5.58595 5.95573 5.27866 5.40104C4.97136 4.84635 4.38543 4.5 3.75001 4.5C2.89063 4.5026 2.16147 5.13021 2.02866 5.98177C1.89584 6.83073 2.40105 7.65104 3.21876 7.91406L4.83595 16H19.1641L20.7813 7.91667C21.6016 7.65365 22.1094 6.83333 21.9792 5.98177C21.8464 5.13021 21.112 4.5 20.25 4.5C19.6146 4.5 19.0313 4.84635 18.7214 5.40104C18.4141 5.95573 18.4297 6.63542 18.7656 7.17448L14.9557 10.9844L12.8516 6.77865C13.5469 6.39062 13.8932 5.58073 13.6927 4.8099C13.4922 4.03906 12.7969 3.5 12 3.5ZM5.00001 17.5V19.25C5.00001 20.2161 5.78386 21 6.75001 21H17.25C18.2162 21 19 20.2161 19 19.25V17.5H5.00001Z" fill="#6D7B83"/></svg>
							</i>
							<i> 
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path d="M12 3.5C11.2031 3.5026 10.5104 4.03906 10.3099 4.8099C10.1094 5.58073 10.4557 6.38802 11.1484 6.77865L9.04428 10.9844L5.23438 7.17448C5.57032 6.63542 5.58595 5.95573 5.27866 5.40104C4.97136 4.84635 4.38543 4.5 3.75001 4.5C2.89063 4.5026 2.16147 5.13021 2.02866 5.98177C1.89584 6.83073 2.40105 7.65104 3.21876 7.91406L4.83595 16H19.1641L20.7813 7.91667C21.6016 7.65365 22.1094 6.83333 21.9792 5.98177C21.8464 5.13021 21.112 4.5 20.25 4.5C19.6146 4.5 19.0313 4.84635 18.7214 5.40104C18.4141 5.95573 18.4297 6.63542 18.7656 7.17448L14.9557 10.9844L12.8516 6.77865C13.5469 6.39062 13.8932 5.58073 13.6927 4.8099C13.4922 4.03906 12.7969 3.5 12 3.5ZM5.00001 17.5V19.25C5.00001 20.2161 5.78386 21 6.75001 21H17.25C18.2162 21 19 20.2161 19 19.25V17.5H5.00001Z" fill="url(#paint0_linear_0_224)"/>
									<defs>
										<linearGradient id="paint0_linear_0_224" x1="1.83093" y1="11.6415" x2="34.4163" y2="11.6415" gradientUnits="userSpaceOnUse">
											<stop stop-color="#FD4B7A"/>	
											<stop offset="1" stop-color="#4D00AE"/>	
										</linearGradient>
									</defs>
								</svg>
							</i>
							
							<h4>Premium Content</h4>
						</div>
						<div class="bottomdashbordlist">
							<ul>
								<li>
									162 items available
								</li>	
							</ul>
						</div>
					</div>
				</div>
			</li>
		</ul>
	</div>
	<p class="hello-customer">
		<!-- <?php esc_html_e( 'Hello Himanshu', 'marketing-ops-core' ); ?> 
		<img src="<?php echo esc_url( $author_img_url ); ?>">
		<strong><?php echo esc_html( $current_user->display_name ); ?></strong>  
		(<?php esc_html_e( 'not', 'marketing-ops-core' ); ?> <strong><?php echo esc_html( $current_user->display_name ); ?></strong>? 
		<a href="<?php echo esc_url( wc_logout_url() ); ?>"><?php esc_html_e( 'Log out', 'marketing-ops-core' ); ?></a>) -->
	</p>
<?php } else { ?>
	<p class="hello-customer">
		<?php esc_html_e( 'Hello', 'marketing-ops-core' ); ?> 
		<img src="<?php echo esc_url( $author_img_url ); ?>">
		<strong><?php echo esc_html( $current_user->display_name ); ?></strong>  
		(<?php esc_html_e( 'not', 'marketing-ops-core' ); ?> <strong><?php echo esc_html( $current_user->display_name ); ?></strong>? 
		<a href="<?php echo esc_url( wc_logout_url() ); ?>"><?php esc_html_e( 'Log out', 'marketing-ops-core' ); ?></a>)
	</p>
	<p class="customer-dashboard-links">
		<?php
		/* translators: 1: Orders URL 2: Address URL 3: Account URL. */
		$dashboard_desc = __( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">billing address</a>, and <a href="%3$s">edit your password and account details</a>.', 'woocommerce' );
		if ( wc_shipping_enabled() ) {
			/* translators: 1: Orders URL 2: Addresses URL 3: Account URL. */
			$dashboard_desc = __( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a>, and <a href="%3$s">edit your password and account details</a>.', 'woocommerce' );
		}
		printf(
			wp_kses( $dashboard_desc, $allowed_html ),
			esc_url( wc_get_endpoint_url( 'orders' ) ),
			esc_url( wc_get_endpoint_url( 'edit-address' ) ),
			esc_url( wc_get_endpoint_url( 'edit-account' ) )
		);
		?>
	</p>

	<?php
}
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
