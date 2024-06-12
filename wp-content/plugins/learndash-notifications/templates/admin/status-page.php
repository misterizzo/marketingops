<div id="learndash-settings-status" class="learndash-status">
	<h2><?php _e( 'Status', 'learndash-notifications' ); ?></h2>

	<table cellspacing="0" class="learndash-support-settings">
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Server Cron Setup', 'learndash-notifications' )  ?></th>
				<td>
					<?php echo isset( $values['cron_setup'] ) && $values['cron_setup'] == 'true' ? __( 'Yes', 'learndash-notifications' ) : '<span style="color:red;">' . __( 'Not yet detected', 'learndash-notifications' ) . '</span>' ; ?>
					<?php echo ! isset( $values['cron_setup'] ) || $values['cron_setup'] == 'false' ? sprintf( __( ', <a href="%s" target="_blank" rel="noreferrer">click here</a> for cron setup instruction (it may take some times for this value to be updated)', 'learndash-notifications' ), 'https://www.learndash.com/support/docs/faqs/email-notifications-send-time/' ) : ''; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Queued Emails in DB', 'learndash-notifications' ) ?></th>
				<td>
					<?php $emails_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ld_notifications_delayed_emails" ); ?>
					<?php $emails_count = $emails_count > 0 ? $emails_count : 0; ?>
					<?php echo $emails_count; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Last Run', 'learndash-notifications' ) ?></th>
				<td>
					<?php $last_run = ! empty( $values['last_run'] ) ? date( 'Y-m-d H:i:s', $values['last_run'] ) : __( 'Not yet detected', 'learndash-notifications' ); ?>
					<?php echo $last_run; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<div id="learndash-settings-tools" class="learndash-status">
	<h2><?php _e( 'Tools', 'learndash-notifications' ); ?></h2>

	<table cellspacing="0" class="learndash-support-settings">
		<thead>
			<tr>
				<th scope="col" class="learndash-support-settings-left"></th>
				<th scope="col" class="learndash-support-settings-right"></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Empty DB Table', 'learndash-notifications' )  ?></th>
				<td>
					<?php $url = add_query_arg(
						array(
							'page' => 'ld-notifications-status',
							'tool' => 'empty-table',
							'nonce' => wp_create_nonce( 'ld_notifications_empty_db_table' ),
						),
						admin_url( '/admin.php' )
					); ?>
					<a href="<?php echo esc_url( $url ); ?>" class="empty-db-table button button-secondary"><?php _e( 'Run', 'learndash-notifications' ); ?></a>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Fix Scheduled Notification Recipients', 'learndash-notifications' )  ?></th>
				<td>
					<a href="#" id="ld-fix-recipient-button" class="button button-secondary"><?php _e( 'Run', 'learndash-notifications' ); ?></a>
				</td>
			</tr>
		</tbody>
	</table>
</div>