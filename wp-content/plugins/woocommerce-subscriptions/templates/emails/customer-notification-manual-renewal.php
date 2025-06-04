<?php
/**
 * Customer Notification: Manual renewal needed.
 *
 * @package WooCommerce_Subscriptions/Templates/Emails
 * @version 7.3.0 - Updated for WC core email improvements.
 */

defined( 'ABSPATH' ) || exit;

$email_improvements_enabled = wcs_is_wc_feature_enabled( 'email_improvements' );

/**
 * @hooked WC_Emails::email_header() Output the email header.
 *
 * @since 6.9.0
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>
		<?php
		echo esc_html(
			sprintf(
					/* translators: %s: Customer first name */
				__( 'Hi %s.', 'woocommerce-subscriptions' ),
				$subscription->get_billing_first_name()
			)
		);
		?>
	</p>


	<p>
		<?php
		echo wp_kses(
			sprintf(
				// translators: %1$s: human readable time difference (eg 3 days, 1 day), %2$s: date in local format.
				__(
					'Your subscription is up for renewal in %1$s — that’s <strong>%2$s</strong>.',
					'woocommerce-subscriptions'
				),
				$subscription_time_til_event,
				$subscription_event_date
			),
			[ 'strong' => [] ]
		);
		?>
	</p>

	<p>
		<strong>
		<?php
			esc_html_e( 'This subscription will not renew automatically.', 'woocommerce-subscriptions' );
		?>
		</strong>
		<?php
		if ( $can_renew_early ) {
			echo wp_kses(
				__( 'You can <strong>renew it manually</strong> in a few short steps via the <em>Subscriptions</em> tab in your account dashboard.', 'woocommerce-subscriptions' ),
				[
					'strong' => [],
					'em'     => [],
				]
			);
		}

		?>
	</p>


	<table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
		<tr>
			<td>
			<?php
			if ( $can_renew_early ) {
				$link_text = __( 'Renew Subscription', 'woocommerce-subscriptions' );
			} else {
				$link_text = __( 'Manage Subscription', 'woocommerce-subscriptions' );
			}
				echo wp_kses(
					'<a href="' . esc_url( $url_for_renewal ) . '">' . esc_html( $link_text ) . '</a>',
					[ 'a' => [ 'href' => true ] ]
				);
				?>

			</td>
		</tr>
	</table>

	<br>
<?php

// Show subscription details.
\WC_Subscriptions_Email::subscription_details( $subscription, $order, $sent_to_admin, $plain_text );

/** This action is documented in templates/emails/customer-notification-auto-renewal.php */
do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td class="email-additional-content">' : '';
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	echo $email_improvements_enabled ? '</td></tr></table>' : '';
}

/**
 * @hooked WC_Emails::email_footer() Output the email footer.
 *
 * @since 6.9.0
 */
do_action( 'woocommerce_email_footer', $email );
