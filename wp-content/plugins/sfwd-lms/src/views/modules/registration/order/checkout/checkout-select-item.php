<?php
/**
 * Registration - Checkout button select item.
 *
 * @since 4.16.0
 * @version 4.16.0
 *
 * @var array<string, Learndash_Payment_Gateway> $active_gateways        Active gateways.
 * @var string                                   $button_key             Button key.
 * @var array<string, string>                    $buttons                Checkout buttons.
 * @var array<string, string|int|float>          $default_payment_params Default payment params for checkout.
 * @var string                                   $product_type           Product type.
 * @var string                                   $selected_payment       Selected payment.
 * @var Template                                 $this                   The Template object.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Template\Template;
use LearnDash\Core\Utilities\Sanitize;

if ( ! isset( $active_gateways[ $button_key ] ) ) {
	return;
}

$gateway      = $active_gateways[ $button_key ];
$is_selected  = $button_key === $selected_payment;
$item_classes = [
	'ld-form__field-radio-container',
	'ld-form__field-svgradio-container',
	'ld-registration-order__checkout-select-item',
	'ld-registration-order__checkout-select-item-' . $button_key,
];

if ( $is_selected ) {
	$item_classes[] = 'ld--selected';
}

$item_info = $gateway->get_checkout_info_text( $product_type );
?>
<div
	class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>"
>
	<div class="ld-registration-order__checkout-select-item-main">
		<?php
		$this->template(
			'components/forms/fields/radio',
			[
				'field_id'    => 'ld-payment_type__' . $button_key,
				'field_label' => $gateway->get_checkout_label(),
				'field_name'  => 'payment_type',
				'field_value' => $button_key,
				'is_selected' => $is_selected,
				'extra_attrs' => [
					'data-id' => $button_key,
				],
				'is_required' => true,
			]
		);
		?>
		<div class="ld-registration-order__checkout-select-item-meta">
			<?php echo wp_kses( $gateway->get_checkout_meta_html(), Sanitize::extended_kses() ); ?>
		</div>
	</div>
	<?php if ( $item_info ) : ?>
		<div class="ld-registration-order__checkout-select-item-info" aria-live="polite" role="note">
			<?php echo esc_html( $item_info ); ?>
		</div>
	<?php endif; ?>
</div>
