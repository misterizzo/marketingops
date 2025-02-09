<?php
/**
 * Payment methods
 *
 * Shows customer payment methods on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/payment-methods.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$saved_methods = wc_get_customer_saved_methods_list( get_current_user_id() );
$has_methods   = (bool) $saved_methods;
$types         = wc_get_account_payment_methods_types();

do_action( 'woocommerce_before_account_payment_methods', $has_methods );
?>
<?php if ( $has_methods ) : ?>
	<div class="main-payment-method">
		<?php foreach ( $saved_methods as $type => $methods ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
			<?php foreach ( $methods as $method ) : ?>
				<!-- EXISTING PAYMENT METHOD -->
				<div class="inner-payment-sec">
					<div class="payment-box">
						<div class="boxes">
							<?php if ( ! empty( $method['method']['brand'] ) ) { ?>
								<?php if ( 'Visa' === $method['method']['brand'] ) { ?>
									<svg viewBox="0 0 70 60" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 .06h24.66a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H3a3 3 0 0 1-3-3v-14a3 3 0 0 1 3-3zm0 1a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h24.66a2 2 0 0 0 2-2v-14a2 2 0 0 0-2-2H3z" fill="#DDD"/><path d="M13.82 13.323h-1.737l1.087-6.3h1.737l-1.086 6.3zm6.3-6.147a4.534 4.534 0 0 0-1.559-.268c-1.716 0-2.924.858-2.932 2.085-.014.904.866 1.407 1.523 1.709.673.308.901.51.901.784-.007.422-.543.617-1.043.617-.694 0-1.066-.1-1.63-.335l-.23-.101-.243 1.414c.408.174 1.159.329 1.938.336 1.824 0 3.01-.845 3.025-2.152.006-.717-.458-1.267-1.459-1.716-.608-.288-.98-.482-.98-.777.007-.269.315-.543 1-.543a3.12 3.12 0 0 1 1.295.241l.157.067.237-1.36z" fill="#1A1F71"/>	<path fill-rule="evenodd" clip-rule="evenodd" d="M23.23 7.022h1.345l1.402 6.3h-1.61s-.157-.723-.207-.944h-2.23l-.366.945h-1.823l2.581-5.778c.18-.409.494-.523.909-.523zm-.107 2.306-.693 1.763h1.444c-.072-.315-.4-1.823-.4-1.823l-.122-.543c-.051.14-.125.332-.175.461l-.054.142zM4.554 7.022H7.35c.379.014.686.128.786.53l.608 2.895.186.872 1.702-4.297h1.837l-2.731 6.294H7.9L6.352 7.842a7.359 7.359 0 0 0-1.827-.692l.029-.128z" fill="#1A1F71"/><defs><linearGradient id="a" x1="42.207" y1="18.605" x2="36.063" y2="39.418" gradientUnits="userSpaceOnUse"><stop stop-color="#222E72"/><stop offset=".592" stop-color="#40CBFF"/><stop offset="1" stop-color="#3CB792"/></linearGradient></defs></svg>
								<?php } elseif ( 'Amex' === $method['method']['brand'] ) { ?>
									<svg viewBox="0 0 70 60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 25a2 2 0 0 1 2-2h24.66a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V25z" fill="#2557D6"/><path fill-rule="evenodd" clip-rule="evenodd" d="M3 22h24.66a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H3a3 3 0 0 1-3-3V25a3 3 0 0 1 3-3zm0 1a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h24.66a2 2 0 0 0 2-2V25a2 2 0 0 0-2-2H3z" fill="#DDD"/><path fill-rule="evenodd" clip-rule="evenodd" d="M1 31.453h1.377l.31-.745h.695l.31.745H6.4v-.57l.241.572H8.05l.242-.58v.578h6.731l-.003-1.224h.13c.092.003.118.012.118.162v1.062h3.482v-.285c.28.15.718.285 1.292.285h1.465l.314-.745h.695l.306.745h2.823v-.708l.427.708h2.263v-4.682h-2.24v.553l-.313-.553h-2.297v.553l-.287-.553h-3.104c-.519 0-.975.072-1.344.273v-.273h-2.141v.273c-.235-.207-.555-.273-.91-.273H7.876l-.526 1.21-.538-1.21H4.348v.553l-.27-.553H1.975L1 28.997v2.456zm8.69-.675h-.826l-.003-2.63-1.168 2.63h-.708l-1.17-2.632v2.631h-1.64l-.31-.748H2.188l-.313.749H1l1.443-3.357H3.64l1.37 3.178v-3.178h1.315l1.055 2.277.969-2.277H9.69v3.357zm-6.1-1.445-.551-1.338-.549 1.337h1.1zm9.388 1.445h-2.692V27.42h2.692v.7h-1.886v.604h1.84v.688h-1.84v.67h1.886v.695zm3.793-2.453a.946.946 0 0 1-.568.895.929.929 0 0 1 .399.283c.114.167.133.316.133.615v.66h-.813l-.003-.423c0-.202.02-.493-.127-.654-.118-.118-.297-.144-.587-.144h-.865v1.22h-.806v-3.356h1.854c.411 0 .715.011.975.16.255.151.408.37.408.744zm-1.019.499c-.11.067-.241.069-.398.069h-.979v-.746h.992c.14 0 .287.007.382.06.105.05.17.154.17.298 0 .146-.062.264-.167.319zm2.311 1.954h-.822V27.42h.822v3.357zm9.545 0h-1.142l-1.527-2.52v2.52h-1.642l-.313-.749H21.31l-.304.75h-.943c-.392 0-.888-.087-1.168-.372-.284-.285-.431-.671-.431-1.282 0-.498.088-.953.434-1.312.261-.268.669-.392 1.224-.392h.78v.72h-.763c-.294 0-.46.043-.62.198-.138.142-.232.41-.232.76 0 .36.072.62.222.79.124.132.349.172.561.172h.362l1.136-2.64h1.207l1.364 3.175v-3.175h1.227l1.416 2.338v-2.338h.826v3.357zm-4.898-1.445-.558-1.338-.554 1.337h1.112zm6.95 6.805c-.195.285-.576.43-1.093.43h-1.556v-.72h1.55c.154 0 .261-.021.326-.084a.296.296 0 0 0 .096-.219.276.276 0 0 0-.099-.222c-.058-.051-.144-.075-.284-.075-.757-.025-1.7.024-1.7-1.04 0-.486.31-.998 1.156-.998h1.605v-.668h-1.492c-.45 0-.777.107-1.008.274v-.274h-2.206c-.352 0-.766.087-.962.274v-.274h-3.939v.274c-.313-.226-.843-.274-1.086-.274h-2.599v.274c-.248-.24-.8-.274-1.135-.274h-2.908l-.665.716-.623-.716H6.694v4.685h4.262l.685-.729.646.729 2.627.002v-1.102h.258c.349.005.76-.009 1.123-.165v1.264h2.166v-1.22h.105c.134 0 .147.005.147.137v1.083h6.581c.418 0 .855-.107 1.097-.3v.3h2.088c.434 0 .859-.061 1.181-.216v-.873zm-13.05-1.803c0 .933-.7 1.126-1.403 1.126H14.2v1.126h-1.566l-.992-1.112-1.031 1.112H7.42V33.23h3.24l.992 1.101 1.025-1.1h2.575c.64 0 1.358.176 1.358 1.105zm-6.406 1.546H8.223v-.668h1.769v-.685h-1.77v-.61h2.02l.882.978-.92.985zm3.191.384-1.236-1.368 1.236-1.324v2.692zm1.83-1.493h-1.041v-.855h1.05c.291 0 .493.118.493.412 0 .29-.192.443-.502.443zm5.455-1.542h2.69v.694h-1.887v.61h1.84v.685h-1.84v.668l1.887.004v.696h-2.69V33.23zm-1.033 1.797a.877.877 0 0 1 .394.281c.114.165.13.318.134.613v.666h-.81v-.42c0-.202.02-.5-.13-.657-.117-.12-.296-.15-.59-.15h-.862v1.227h-.81V33.23h1.86c.409 0 .706.018.97.159.255.153.415.363.415.745 0 .536-.36.81-.571.894zm-.456-.425c-.108.064-.24.07-.398.07h-.979v-.755h.993c.143 0 .286.003.384.06.104.055.167.16.167.303a.36.36 0 0 1-.167.322zm7.274.214c.157.162.241.366.241.711 0 .723-.453 1.06-1.266 1.06h-1.57v-.72h1.564c.153 0 .261-.02.33-.083a.299.299 0 0 0-.004-.441c-.061-.051-.147-.075-.287-.075-.753-.025-1.697.023-1.697-1.04 0-.486.307-.998 1.152-.998h1.615v.714h-1.478c-.146 0-.242.005-.323.06-.088.055-.12.136-.12.242a.257.257 0 0 0 .175.25.93.93 0 0 0 .314.039l.434.011c.437.01.738.086.92.27zm3.197-.899h-1.468c-.147 0-.244.006-.326.06-.085.055-.118.136-.118.243a.253.253 0 0 0 .176.25.931.931 0 0 0 .31.038l.437.011c.441.011.735.087.915.27.032.026.052.055.074.084v-.956z" fill="#fff"/><defs><linearGradient id="a" x1="42.207" y1="18.605" x2="36.063" y2="39.418" gradientUnits="userSpaceOnUse"><stop stop-color="#222E72"/><stop offset=".592" stop-color="#40CBFF"/><stop offset="1" stop-color="#3CB792"/></linearGradient></defs></svg>
								<?php } elseif ( 'Mastercard' === $method['method']['brand'] ) { ?>
									<svg viewBox="0 0 70 60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M36 3a2 2 0 0 1 2-2h24.66a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H38a2 2 0 0 1-2-2V3z" fill="#fff"/><path fill-rule="evenodd" clip-rule="evenodd" d="M38 0h24.66a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H38a3 3 0 0 1-3-3V3a3 3 0 0 1 3-3zm0 1a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h24.66a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2H38z" fill="#DDD"/><path fill-rule="evenodd" clip-rule="evenodd" d="M60.055 13.048v-.211h-.054l-.064.145-.063-.145h-.055v.21h.039v-.158l.059.137h.04l.06-.138v.16h.039zm-.348 0v-.175h.07v-.036h-.18v.036h.072v.175h.038zm.36-3.137c0 3.265-2.64 5.912-5.897 5.912-3.256 0-5.896-2.647-5.896-5.912C48.274 6.647 50.914 4 54.17 4c3.257 0 5.897 2.647 5.897 5.911z" fill="#F79F1A"/><path fill-rule="evenodd" clip-rule="evenodd" d="M52.793 9.911c0 3.265-2.64 5.912-5.897 5.912C43.64 15.823 41 13.176 41 9.91 41 6.647 43.64 4 46.897 4c3.256 0 5.896 2.647 5.896 5.911z" fill="#EA001B"/><path fill-rule="evenodd" clip-rule="evenodd" d="M50.533 5.258a5.905 5.905 0 0 0-2.258 4.653c0 1.889.883 3.572 2.258 4.654a5.907 5.907 0 0 0 2.26-4.654 5.905 5.905 0 0 0-2.26-4.653z" fill="#FF5F01"/><defs><linearGradient id="a" x1="42.207" y1="18.605" x2="36.063" y2="39.418" gradientUnits="userSpaceOnUse"><stop stop-color="#222E72"/><stop offset=".592" stop-color="#40CBFF"/><stop offset="1" stop-color="#3CB792"/></linearGradient></defs></svg>
								<?php } elseif ( 'Mastercard' === $method['method']['brand'] ) { ?>
									<svg viewBox="0 0 70 60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M36 25a2 2 0 0 1 2-2h24.66a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H38a2 2 0 0 1-2-2V25z" fill="url(#a)"/><path fill-rule="evenodd" clip-rule="evenodd" d="M38 22h24.66a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H38a3 3 0 0 1-3-3V25a3 3 0 0 1 3-3zm0 1a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h24.66a2 2 0 0 0 2-2V25a2 2 0 0 0-2-2H38z" fill="#DDD"/><path fill-rule="evenodd" clip-rule="evenodd" d="M45.73 31.863h4.795c-.043-1.037-.284-2.105-1.017-2.792-.873-.818-2.401-1.121-3.767-1.121-1.421 0-2.993.333-3.87 1.21-.757.756-.92 1.972-.92 3.065 0 1.144.32 2.476 1.14 3.242.873.816 2.286 1.033 3.65 1.033 1.325 0 2.76-.245 3.627-1.02.866-.773 1.165-2.076 1.165-3.255v-.006h-4.804v-.356zm5.117.356v4.09h6.672v-.006c.976-.053 1.753-.933 1.753-2.014 0-1.081-.777-2.018-1.753-2.071v.001h-6.672zm6.596-4.098c.951 0 1.706.826 1.706 1.867 0 .987-.693 1.792-1.575 1.875h-6.727v-3.748h6.385a.7.7 0 0 1 .13.002c.028.002.055.004.08.004z" fill="#FEFEFE"/><defs><linearGradient id="a" x1="42.207" y1="18.605" x2="36.063" y2="39.418" gradientUnits="userSpaceOnUse"><stop stop-color="#222E72"/><stop offset=".592" stop-color="#40CBFF"/><stop offset="1" stop-color="#3CB792"/></linearGradient></defs></svg>
								<?php } ?>
							<?php } ?>
						</div>
						<div class="boxes">
							<p><b><?php esc_html_e( 'Method', 'marketingops' ); ?></b></p>
							<p>
								<?php
								if ( ! empty( $method['method']['last4'] ) ) {
									/* translators: 1: credit card type 2: last 4 digits */
									echo sprintf( esc_html__( '%1$s ending in %2$s', 'woocommerce' ), esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) ), esc_html( $method['method']['last4'] ) );
								} else {
									echo esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) );
								}
								?>
							</p>
						</div>
						<div class="boxes">
							<p><b><?php esc_html_e( 'Expires', 'marketingops' ); ?></b></p>
							<p><?php echo esc_html( $method['expires'] ); ?></p>
						</div>
						<div class="boxes">
							<?php foreach ( $method['actions'] as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>&nbsp;';
							} ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endforeach; ?>

		<!-- ADD NEW PAYMENT METHOD -->
		<div class="inner-payment-sec">
			<div class="payment-box">
				<div class="boxes"></div>
				<div class="boxes"></div>
				<div class="boxes"></div>
				<div class="boxes">
					<?php if ( WC()->payment_gateways->get_available_payment_gateways() ) : ?>
						<a class="button" href="<?php echo esc_url( wc_get_endpoint_url( 'add-payment-method' ) ); ?>"><?php esc_html_e( 'Add new', 'woocommerce' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

<?php else : ?>
	<div class="no-payment-sec">
		<div class="inner-no-payment">
			<div class="c-img">
				<img src="/wp-content/uploads/2023/10/Subscription.svg">
				<p>You don't have any saved payment methods yet.</p>
				<div class="n-p-btn">
					<?php if ( WC()->payment_gateways->get_available_payment_gateways() ) : ?>
						<a class="button" href="<?php echo esc_url( wc_get_endpoint_url( 'add-payment-method' ) ); ?>"><?php esc_html_e( 'Add new', 'woocommerce' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_payment_methods', $has_methods ); ?>
