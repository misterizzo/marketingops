<?php
/**
 * Printful reconnect.
 *
 * @var string $reconnect_url
 */
?>

<div class="printful-setting-group">

	<h2><?php echo esc_html__('Connection', 'printful'); ?></h2>

	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row">
				<?php echo esc_html__('Reconnect your store', 'printful'); ?>

			</th>
			<td>
				<a href="<?php echo esc_url($reconnect_url); ?>" class="button button-primary"><?php echo esc_html__('Reconnect', 'printful'); ?></a>
			</td>
		</tr>
		</tbody>
	</table>
</div>
