<?php
/**
 * @var WC_Shipping_UPS $this
 */

if ( empty( $field_key ) || empty( $data ) ) {
	return;
}
?>
<tr id="oauth_status">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $field_key ); ?>">
			<?php echo wp_kses_post( $data['title'] ); ?>

			<?php
			// The WC_Settings_API::get_tooltip_html() method escapes the tooltip already.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->get_tooltip_html( $data );
			?>
		</label>
	</th>
	<td class="forminp">
		<div
			<?php
			// The WC_Settings_API::get_custom_attribute_html() method escapes the value attributes already.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->get_custom_attribute_html( $data );
			?>
			>

			<?php if ( $this->get_ups_oauth()->is_authenticated() ) : ?>
				<p style="color: #00a32a;"><?php esc_html_e( 'Authenticated', 'woocommerce-shipping-ups' ); ?></p>
			<?php else : ?>
				<p style="color: #d63638;"><?php esc_html_e( 'Not Authenticated.', 'woocommerce-shipping-ups' ); ?></p>
				<p><?php esc_html_e( 'Please enter your UPS Account Number, Client ID, and UPS Client Secret. Then click "Save changes".', 'woocommerce-shipping-ups' ); ?></p>
			<?php endif; ?>
		</div>
	</td>
</tr>

