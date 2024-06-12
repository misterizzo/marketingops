<div class="logs-page">
    <form method="post">
		<?php wp_nonce_field( 'ld_notifications_clear_logs' ) ?>
        <button type="submit" class="clear-all-logs button">
			<?php _e( 'Clear all logs', 'learndash-notifications' ) ?>
        </button>
    </form>
    <div class="clearfix"></div>
	<?php if ( ! empty( $logs ) ): ?>
        <div id="logs-tab">
            <ul>
				<?php
				foreach ( $logs as $key => $trigger ):?>
                    <li><a href="#<?php echo $key ?>"><?php echo $trigger['name'] ?></a></li>
				<?php endforeach; ?>
            </ul>
			<?php foreach ( $logs as $key => $trigger ): ?>
                <div id="<?php echo $key ?>">
                    <pre><?php echo $trigger['log'] ?></pre>
                    <form method="post">
						<?php wp_nonce_field( 'ld_notifications_clear_logs' ) ?>
                        <input type="hidden" name="trigger" value="<?php echo $key ?>"/>
                        <button type="submit" class="button">
							<?php _e( 'Clear log', 'learndash-notifications' ) ?>
                        </button>
                    </form>
                </div>
			<?php endforeach; ?>
        </div>
	<?php else: ?>
        <p><?php _e( 'The logs are empty.', 'learndash-notifications' ) ?></p>
	<?php endif; ?>
</div>
<script>
    jQuery(function ($) {
        $("#logs-tab").tabs();
    });
</script>
