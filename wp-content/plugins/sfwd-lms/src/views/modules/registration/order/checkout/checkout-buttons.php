<?php
/**
 * Registration - Checkout buttons section.
 *
 * @since 4.16.0
 * @version 4.16.0
 *
 * @var array<string, string> $buttons Checkout buttons.
 * @var Template              $this    The Template object.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Template\Template;

?>
<div class="ld-registration-order__checkout-buttons">
	<?php foreach ( $buttons as $button_key => $button ) : ?>
		<?php
		$this->template(
			'modules/registration/order/checkout/checkout-button',
			[
				'button_html'            => $button,
				'button_key'             => $button_key,
			]
		);
		?>
	<?php endforeach; ?>
</div>
