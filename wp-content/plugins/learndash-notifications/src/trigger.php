<?php

namespace LearnDash_Notification;

/**
 * Class Trigger
 *
 * @package LearnDash_Notification
 */
abstract class Trigger {

	/** The trigger slug.
	 *
	 * @var string
	 */
	protected $trigger;

	/**
	 * Contain the courses data, use for temp caching.
	 *
	 * @var array
	 */
	protected $all_courses = array();

	/**
	 * A base point for monitoring the events
	 *
	 * @return void
	 */
	abstract public function listen();

	/**
	 * Send the email to recipients.
	 *
	 * @param array        $emails Contain the recipients emails.
	 * @param Notification $model The notification instance.
	 * @param array        $args The data passed for email content.
	 */
	public function send( array $emails, Notification $model, array $args ) {
		$model->populate_shortcode_data( $args );
		$subject = apply_filters(
			'learndash_notifications_email_subject',
			do_shortcode( $model->post->post_title ),
			$model->post->ID
		);
		$content = do_shortcode( $model->post->post_content );
		if ( ! strstr( $content, '<!DOCTYPE' ) && ! strstr( $content, '<p' ) && ! strstr( $content, '<div' ) ) {
			$content = wpautop( $content );
		}
		$content = trim( $content );
		$content = apply_filters( 'learndash_notifications_email_content', $content, $model->post->ID );
		if ( apply_filters( 'learndash_notifications_email_rtl', false ) ) {
			$content = '<div dir="rtl" >' . $content . '</div>';
		}
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);
		$this->log( sprintf( 'About the send email to %s', implode( ',', $emails ) ) );
		foreach ( $emails as $email ) {
			$user    = get_user_by( 'email', $email );
			$is_send = true;
			if ( is_object( $user ) ) {
				// check the subscription.
				$list = get_user_meta( $user->ID, 'learndash_notifications_subscription', true );
				if ( isset( $list[ $this->trigger ] ) && absint( $list[ $this->trigger ] ) === 0 ) {
					$this->log( sprintf( 'Email %s excluded', $email ) );
					$is_send = false;
				}
			}
			if ( $is_send ) {
				add_action( 'wp_mail_failed', array( &$this, 'debug_email_fail' ) );
				$ret = wp_mail( $email, $subject, $content, $headers );
				$this->log( sprintf( 'Send to %s. Status: %s', $email, true === $ret ? 'sent' : 'fail' ) );
				remove_action( 'wp_mail_failed', array( &$this, 'debug_email_fail' ) );
			}
		}
	}

	/**
	 * Debug email fail if system error
	 *
	 * @param \WP_Error $error The WP_Error object.
	 */
	public function debug_email_fail( \WP_Error $error ) {
		$this->log( sprintf( 'Email error status: %s', $error->get_error_message() ) );
	}

	/**
	 * Get all Course IDS
	 *
	 * @return array
	 */
	protected function get_all_course(): array {
		if ( ! empty( $this->all_courses ) ) {
			return $this->all_courses;
		}
		$query_args = array(
			'post_type'      => learndash_get_post_type_slug( 'course' ),
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		$query = new \WP_Query( $query_args );

		$this->all_courses = $query->get_posts();

		return $this->all_courses;
	}

	/**
	 * Get all Course IDS
	 *
	 * @return int[]
	 */
	protected function get_all_lessons() {
		$query_args = array(
			'post_type'      => learndash_get_post_type_slug( 'lesson' ),
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		$query = new \WP_Query( $query_args );

		return $query->get_posts();
	}

	/**
	 * Send the email that scheduled..
	 */
	public function send_db_delayed_email() {
		$queue = $this->get_next_queue();
		if ( ! is_object( $queue ) ) {
			return;
		}

		if ( $this->get_timestamp() < $queue->sent_on ) {
			// pull back, and reschedule.
			wp_schedule_single_event( $queue->sent_on, 'leanrdash_notifications_send_delayed_email' );

			return;
		}

		$args = $queue->shortcode_data;
		$args = maybe_unserialize( $args );
		if ( ! is_array( $args ) ) {
			// invalid data, this one should not happen.
			return;
		}
		$n_id = $args['notification_id'];
		$post = get_post( $n_id );
		if ( ! is_object( $post ) ) {
			return;
		}

		$model = new Notification( $post );
		if ( $model->trigger !== $this->trigger ) {
			return;
		}

		$this->log( '====Cron Start====' );
		$this->log(
			sprintf(
				'Planed time: %s - Unix timestamp: %s',
				$this->get_current_time_from( $queue->sent_on ),
				$queue->sent_on
			)
		);

		// now we check if the model is no delay anymore, then just quit and delete this.
		if ( absint( $model->delay ) === 0 ) {
			$this->log( 'The notification settings has changed from delayed to instantly' );
			$this->delete_queue( $queue->id );
			$next = $this->get_next_queue();
			if ( is_object( $next ) ) {
				wp_schedule_single_event( $next->sent_on, 'leanrdash_notifications_send_delayed_email' );
			}

			return;
		}

		if ( ! $this->can_send_delayed_email( $model, $args ) ) {
			// the email is not valid anymore.
			$this->log( 'Condition not met' );
			$this->delete_queue( $queue->id );
			$next = $this->get_next_queue();
			if ( is_object( $next ) ) {
				wp_schedule_single_event( $next->sent_on, 'leanrdash_notifications_send_delayed_email' );
			}

			return;
		}
		$emails = maybe_unserialize( $queue->recipient );
		$bcc    = maybe_unserialize( $queue->bcc );
		if ( ! is_array( $emails ) ) {
			$emails = array();
		}
		if ( ! is_array( $bcc ) ) {
			$bcc = array();
		}
		$emails = array_merge( $emails, $bcc );
		$emails = array_filter( $emails );
		$emails = array_unique( $emails );
		$this->send( $emails, $model, $args );
		$this->delete_queue( $queue->id );
		$next = $this->get_next_queue();
		if ( is_object( $next ) ) {
			wp_schedule_single_event( $next->sent_on, 'leanrdash_notifications_send_delayed_email' );
			$this->log( sprintf( 'Another check will be on %s', $this->get_current_time_from( $next->sent_on ) ) );
		}
		$this->update_cron_status();
		$this->log( '====Cron End====' );
		$this->after_email_sent( $model, $args );
	}

	/**
	 * Update the status in Status screen
	 */
	protected function update_cron_status() {
		$status               = get_option( 'learndash_notifications_status', array() );
		$status['cron_setup'] = 'true';
		$status['last_run']   = time();
		update_option( 'learndash_notifications_status', $status );
	}

	/**
	 * For child to implement
	 *
	 * @param Notification $model The notification model.
	 * @param array        $args  Array of data.
	 */
	protected function after_email_sent( Notification $model, array $args ) {
		// for child to implement.
	}

	/**
	 * The last check before send out delayed email.
	 *
	 * @param Notification $model The notification model.
	 * @param array        $args Args.
	 *
	 * @return bool
	 */
	abstract protected function can_send_delayed_email( Notification $model, $args );

	/**
	 * Pre save data in db, for processing late in cronjob
	 *
	 * @param array        $emails The emails to queue.
	 * @param Notification $model The notification model.
	 * @param array        $args Mix args.
	 * @param null         $sent_on The time it should send.
	 *
	 * @return int
	 */
	public function queue_use_db( array $emails, Notification $model, array $args = array(), $sent_on = null ) {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'ld_notifications_delayed_emails';

		$unit     = $model->delay_unit;
		$interval = absint( $model->delay );
		if ( null === $sent_on ) {
			$sent_on = strtotime( "+$interval $unit" );
		}
		$args['notification_id'] = $model->post->ID;
		$ret                     = $wpdb->insert(
			$table_name,
			array(
				'title'          => $model->post->post_title,
				'message'        => $model->post->post_content,
				'recipient'      => maybe_serialize( $emails ),
				'shortcode_data' => maybe_serialize( $args ),
				'sent_on'        => $sent_on,
				// doesnt need this anymore.
				'bcc'            => '',
			)
		);
		if ( $ret ) {
			// kick start.
			$queue = $this->get_next_queue();
			if ( wp_next_scheduled( 'leanrdash_notifications_send_delayed_email' ) ) {
				// if this is already queue, that mean the user has been out and in again, then we need to queue the later time.
				wp_clear_scheduled_hook( 'leanrdash_notifications_send_delayed_email' );
			}
			$this->log(
				sprintf(
					'Queued to be sent out at %s - Unix timestamp: %s, recipients: %s',
					$this->get_current_time_from( $sent_on ),
					$sent_on,
					implode( ',', $emails )
				)
			);
			wp_schedule_single_event( $queue->sent_on, 'leanrdash_notifications_send_delayed_email' );

			return $wpdb->insert_id;
		}
	}

	/**
	 * Get the next queue should be send
	 *
	 * @return array|object|void|null
	 */
	protected function get_next_queue() {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'ld_notifications_delayed_emails';
		$sql        = "SELECT * FROM $table_name  ORDER BY sent_on ASC LIMIT 1";
		//phpcs:ignore
		return $wpdb->get_row( $sql );
	}

	/**
	 * Delete the queue.
	 *
	 * @param int $id Queue ID.
	 */
	protected function delete_queue( int $id ) {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'ld_notifications_delayed_emails';
		$ret        = $wpdb->delete(
			$table_name,
			array(
				'id' => $id,
			)
		);
		$this->log( sprintf( 'Remove queue from database. Status %s', $ret ) );
	}

	/**
	 * Get all notifications models.
	 *
	 * @param string $type The notification slug.
	 *
	 * @return Notification[]
	 */
	public function get_notifications( string $type ): array {
		$args = array(
			'meta_key'       => '_ld_notifications_trigger',
			'meta_value'     => $type,
			'post_type'      => 'ld-notification',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
		);

		$posts  = get_posts( $args );
		$models = array();
		foreach ( $posts as $post ) {
			$model    = new Notification( $post );
			$models[] = $model;
		}

		return $models;
	}

	/**
	 * A logging function.
	 *
	 * @param string      $message The log message.
	 * @param string|null $category The log filename.
	 */
	public function log( string $message, string $category = null ) {
		if ( is_null( $category ) ) {
			$category = $this->trigger;
		}
		$log_dir = wp_upload_dir( null, true );
		$log_dir = $log_dir['basedir'] . DIRECTORY_SEPARATOR . 'learndash-notifications' . DIRECTORY_SEPARATOR;
		if ( ! is_dir( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}

		// put an index.html, index.php there.
		if ( ! file_exists( $log_dir . 'index.html' ) ) {
			//phpcs:ignore
			file_put_contents( $log_dir . 'index.html', '' );
		}

		if ( ! file_exists( $log_dir . 'index.php' ) ) {
			//phpcs:ignore
			file_put_contents( $log_dir . 'index.php', '<?php' );
		}
		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		//phpcs:ignore
		$message   = sprintf( date( $format, current_time( 'timestamp' ) ) . ': %s', $message );
		$file_name = hash( 'sha256', sanitize_file_name( $category ) . AUTH_SALT );
		//phpcs:ignore
		file_put_contents( $log_dir . $file_name, $message . PHP_EOL, FILE_APPEND );
	}

	/**
	 * The logging for cli mode only.
	 *
	 * @param string $message The message.
	 */
	public function cli_log( string $message ) {
		if ( 'cli' === php_sapi_name() ) {
			// if this is cli, then we output direct to screen.
			//phpcs:ignore
			fwrite( STDERR, $message . PHP_EOL );

			return;
		}
	}

	/**
	 * A simple function for getting the current time, we separate the function so we can mock it in test.
	 *
	 * @return int
	 */
	public function get_timestamp(): int {
		return time();
	}

	/**
	 * Convert a timestamp into human friendly time.
	 *
	 * @param int $timestamp The unix timestamp.
	 *
	 * @return string
	 */
	protected function get_current_time_from( int $timestamp ) {
		$date_time = new \DateTime();
		$date_time->setTimestamp( $timestamp );
		$date_time->setTimezone( wp_timezone() );

		return $date_time->format( 'Y-m-d H:i:s' );
	}
}
