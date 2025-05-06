<?php
/**
 * Trigger base class.
 *
 * @package LearnDash\Notifications
 */

namespace LearnDash_Notification;

use LearnDash\Notifications\Logger;
use Learndash_Logger;

/**
 * Class Trigger
 */
abstract class Trigger {
	/** The trigger slug.
	 *
	 * @var string
	 */
	protected $trigger;

	/**
	 * Notification models of a particular trigger.
	 *
	 * @since 1.6.5
	 *
	 * @var Notification[]
	 */
	protected array $models = [];

	/**
	 * Contain the courses data, use for temp caching.
	 *
	 * @var array
	 */
	protected $all_courses = [];

	/**
	 * A base point for monitoring the events
	 *
	 * @return void
	 */
	abstract public function listen();

	/**
	 * Check if set conditions are valid.
	 *
	 * @since 1.5.4
	 *
	 * @param Notification $notification
	 * @param array        $args
	 * @return boolean
	 */
	private function are_conditions_valid( Notification $notification, array $args = [] ): bool {
		$statuses = [];
		foreach ( $notification->conditions as $key => $condition ) {
			switch ( $condition['condition_type'] ) {
				case 'enroll_group':
					if ( is_array( $condition['group_id'] ) && ! empty( $args['user_id'] ) && is_numeric( $args['user_id'] ) ) {
						if ( in_array( 'all', $condition['group_id'], true ) ) {
							$enrolled_groups = learndash_get_users_group_ids( $args['user_id'] );

							if ( ! empty( $enrolled_groups ) ) {
								$statuses[ $key ] = true;
							} else {
								$statuses[ $key ] = false;
							}
						} else {
							foreach ( $condition['group_id'] as $group_id ) {
								$valid = learndash_is_user_in_group( $args['user_id'], $group_id );

								if ( $valid ) {
									$statuses[ $key ] = true;
									break;
								}

								$statuses[ $key ] = false;
							}
						}
					}
					break;

				case 'enroll_course':
					if ( is_array( $condition['course_id'] ) && ! empty( $args['user_id'] ) && is_numeric( $args['user_id'] ) ) {
						if ( in_array( 'all', $condition['course_id'], true ) ) {
							$enrolled_courses = ld_get_mycourses( $args['user_id'] );

							if ( ! empty( $enrolled_courses ) ) {
								$statuses[ $key ] = true;
							} else {
								$statuses[ $key ] = false;
							}
						} else {
							foreach ( $condition['course_id'] as $course_id ) {
								$valid = sfwd_lms_has_access( $course_id, $args['user_id'] );

								if ( $valid ) {
									$statuses[ $key ] = true;
									break 2;
								}

								$statuses[ $key ] = false;
							}
						}
					}
					break;

				case 'complete_course':
					if ( is_array( $condition['course_id'] ) && ! empty( $args['user_id'] ) && is_numeric( $args['user_id'] ) ) {
						if ( in_array( 'all', $condition['course_id'], true ) ) {
							$enrolled_courses = ld_get_mycourses( $args['user_id'] );

							foreach ( $enrolled_courses as $course_id ) {
								$completed = learndash_course_completed( $args['user_id'], $course_id );

								if ( $completed ) {
									$statuses[ $key ] = true;
									break;
								}

								$statuses[ $key ] = false;
							}
						} else {
							foreach ( $condition['course_id'] as $course_id ) {
								$valid = learndash_course_completed( $args['user_id'], $course_id );

								if ( $valid ) {
									$statuses[ $key ] = true;
									break;
								}

								$statuses[ $key ] = false;
							}
						}
					}
					break;

				case 'complete_lesson':
					if ( is_array( $condition['course_id'] ) && is_array( $condition['lesson_id'] ) && ! empty( $args['user_id'] ) && is_numeric( $args['user_id'] ) ) {
						if ( in_array( 'all', $condition['course_id'], true ) ) {
							$course_ids = ld_get_mycourses( $args['user_id'] );
						} else {
							$course_ids = $condition['course_id'] ?? [];
						}

						foreach ( $course_ids as $course_id ) {
							if ( in_array( 'all', $condition['lesson_id'], true ) ) {
								$course_lesson_ids = learndash_get_course_steps( $course_id, [ 'sfwd-lessons' ] );
							} else {
								$course_lesson_ids = $condition['lesson_id'];
							}

							foreach ( $course_lesson_ids as $lesson_id ) {
								$valid = learndash_is_lesson_complete( $args['user_id'], $lesson_id, $course_id );

								if ( $valid ) {
									$statuses[ $key ] = true;
									break 3;
								}
							}
						}

						$statuses[ $key ] = false;
					}
					break;

				case 'complete_topic':
					if ( is_array( $condition['course_id'] ) && is_array( $condition['topic_id'] ) && ! empty( $args['user_id'] ) && is_numeric( $args['user_id'] ) ) {
						if ( in_array( 'all', $condition['course_id'], true ) ) {
							$course_ids = ld_get_mycourses( $args['user_id'] );
						} else {
							$course_ids = $condition['course_id'] ?? [];
						}

						foreach ( $course_ids as $course_id ) {
							if ( in_array( 'all', $condition['topic_id'], true ) ) {
								$course_topic_ids = learndash_get_course_steps( $course_id, [ 'sfwd-topic' ] );
							} else {
								$course_topic_ids = $condition['topic_id'];
							}

							foreach ( $course_topic_ids as $topic_id ) {
								$valid = learndash_is_topic_complete( $args['user_id'], $topic_id, $course_id );

								if ( $valid ) {
									$statuses[ $key ] = true;
									break 3;
								}
							}
						}

						$statuses[ $key ] = false;
					}
					break;

				case 'submit_quiz':
				case 'complete_quiz':
				case 'incomplete_quiz':
					if ( is_array( $condition['course_id'] ) && is_array( $condition['topic_id'] ) && ! empty( $args['user_id'] ) && is_numeric( $args['user_id'] ) ) {
						if ( in_array( 'all', $condition['course_id'], true ) ) {
							$course_ids = ld_get_mycourses( $args['user_id'] );
						} else {
							$course_ids = $condition['course_id'] ?? [];
						}

						foreach ( $course_ids as $course_id ) {
							if ( in_array( 'all', $condition['quiz_id'], true ) ) {
								$course_quiz_ids = learndash_get_course_steps( $course_id, [ 'sfwd-quiz' ] );
							} else {
								$course_quiz_ids = $condition['quiz_id'] ?? [];
							}

							foreach ( $course_quiz_ids as $quiz_id ) {
								if ( $condition['condition_type'] === 'complete_quiz' ) {
									$valid = learndash_is_quiz_complete( $args['user_id'], $quiz_id, $course_id );
								} elseif ( $condition['condition_type'] === 'incomplete_quiz' ) {
									$valid = ! learndash_is_quiz_complete( $args['user_id'], $quiz_id, $course_id );
								} elseif ( $condition['condition_type'] === 'submit_quiz' ) {
									$valid = ! empty( learndash_get_user_quiz_attempt( $args['user_id'], [ 'course' => $course_id ] ) );
								}

								if ( $valid ) {
									$statuses[ $key ] = true;
									break 3;
								}
							}
						}

						$statuses[ $key ] = false;
					}
					break;

				case 'upload_assignment':
				case 'approve_assignment':
					if ( is_array( $condition['course_id'] ) && ! empty( $args['user_id'] ) && is_numeric( $args['user_id'] ) ) {
						if ( in_array( 'all', $condition['course_id'], true ) ) {
							$course_ids = ld_get_mycourses( $args['user_id'] );
						} else {
							$course_ids = $condition['course_id'] ?? [];
						}

						foreach ( $course_ids as $course_id ) {
							if ( ! empty( $condition['topic_id'] ) ) {
								if ( is_array( $condition['topic_id'] ) && in_array( 'all', $condition['topic_id'] ) ) {
									$object_ids = learndash_course_get_steps_by_type( $course_id, 'sfwd-topic' );
								} elseif ( is_array( $condition['topic_id'] ) ) {
									$object_ids = $condition['topic_id'];
								}
							} elseif ( ! empty( $condition['lesson_id'] ) ) {
								if ( is_array( $condition['lesson_id'] ) && in_array( 'all', $condition['lesson_id'] ) ) {
									$object_ids = learndash_course_get_steps_by_type( $course_id, 'sfwd-lessons' );
								} elseif ( is_array( $condition['lesson_id'] ) ) {
									$object_ids = $condition['lesson_id'];
								}
							}

							if ( isset( $object_ids ) && is_array( $object_ids ) ) {
								foreach ( $object_ids as $object_id ) {
									$assignments = learndash_get_user_assignments( $object_id, $args['user_id'], $course_id );

									if ( $condition['condition_type'] === 'upload_assignment' ) {
										$valid = ! empty( $assignments );

										if ( $valid ) {
											$statuses[ $key ] = true;
											break 2;
										}
									} elseif ( $condition['condition_type'] === 'approve_assignment' ) {
										foreach ( $assignments as $assignment ) {
											$valid = learndash_is_assignment_approved( $assignment->ID );

											if ( $valid ) {
												$statuses[ $key ] = true;
												break 3;
											}
										}
									}
								}
							}

							$statuses[ $key ] = false;
						}
					}
					break;
			}
		}

		if ( ! empty( $notification->conditions ) && in_array( false, $statuses, true ) ) {
			$valid = false;
		} else {
			$valid = true;
		}

		return apply_filters( 'learndash_notifications_are_conditions_valid', $valid, $this->trigger, $notification, $args );
	}

	/**
	 * Check whether a trigger is valid and can be sent.
	 *
	 * @since 1.5.4
	 *
	 * @param Notification $notification
	 * @param array        $args
	 *
	 * @return boolean
	 */
	public function is_valid( Notification $notification, array $args ): bool {
		/**
		 * Filter whether a trigger is valid or not.
		 *
		 * @since 1.5.4
		 *
		 * @param bool          $valid
		 * @param string        $this->trigger
		 * @param Notification  $notification
		 * @param array         $args
		 *
		 * @return bool
		 */
		return apply_filters(
			'learndash_notifications_trigger_valid',
			$this->are_conditions_valid( $notification, $args )
				&& $this->are_triggering_objects_valid( $notification, $args ),
			$this->trigger,
			$notification,
			$args
		);
	}

	/**
	 * Check if triggering object of a notification is valid.
	 *
	 * @since 1.5.4
	 *
	 * @param Notification $notification
	 * @param array        $args
	 *
	 * @return bool
	 */
	private function are_triggering_objects_valid( Notification $notification, array $args ): bool {
		$valid = [];

		// Waterfall.
		if ( ! empty( $args['group_id'] ) && ! empty( $notification->group_id ) ) {
			$valid[] = $this->is_value_valid( $args['group_id'], $notification->group_id );
		}

		if ( ! empty( $args['course_id'] ) && ! empty( $notification->course_id ) ) {
			$valid[] = $this->is_value_valid( $args['course_id'], $notification->course_id );
		}

		if ( ! empty( $args['lesson_id'] ) && ! empty( $notification->lesson_id ) ) {
			$valid[] = $this->is_value_valid( $args['lesson_id'], $notification->lesson_id );
		}

		if ( ! empty( $args['topic_id'] ) && ! empty( $notification->topic_id ) ) {
			$valid[] = $this->is_value_valid( $args['topic_id'], $notification->topic_id );
		}

		if ( ! empty( $args['quiz_id'] ) && ! empty( $notification->quiz_id ) ) {
			$valid[] = $this->is_value_valid( $args['quiz_id'], $notification->quiz_id );
		}

		return apply_filters( 'learndash_notifications_are_triggering_objects_valid', ! in_array( false, $valid, true ), $this->trigger, $notification, $args );
	}

	/**
	 * Check if a value is valid against a source.
	 *
	 * @param mixed $value
	 * @param array $source
	 * @return boolean
	 */
	protected function is_value_valid( $value, array $source ): bool {
		if ( is_array( $source ) && in_array( 'all', $source ) || in_array( '0', $source ) || in_array( $value, $source ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Send the email to recipients.
	 *
	 * @param array        $emails Contain the recipients emails.
	 * @param Notification $model  The notification instance.
	 * @param array        $args   The data passed for email content.
	 */
	public function send( array $emails, Notification $model, array $args ) {
		$model->populate_shortcode_data( $args );

		// TODO: Move away from global variable to store shortcode data.
		global $ld_notifications_shortcode_data;

		/**
		 * This filter is documented in includes/notification.php.
		 */
		if ( ! apply_filters( 'learndash_notifications_send_notification', true, $ld_notifications_shortcode_data ) ) {
			return;
		}

		$subject = apply_filters(
			'learndash_notifications_email_subject',
			do_shortcode( $model->post->post_title ),
			$model->post->ID
		);

		$content = do_shortcode( $model->post->post_content );

		if ( apply_filters( 'learndash_notifications_html_email', true ) ) {
			$content = wpautop( $content );
		}

		$content = trim( $content );

		if ( apply_filters( 'learndash_notifications_email_rtl', false ) ) {
			$content = '<div dir="rtl" >' . $content . '</div>';
		}

		$content = apply_filters( 'learndash_notifications_email_content', $content, $model->post->ID );

		if ( ! empty( $emails ) ) {
			$this->log( sprintf( 'About to send email to %s', implode( ',', $emails ) ) );
		}

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
				add_action( 'wp_mail_failed', [ &$this, 'debug_email_fail' ] );
				$ret = learndash_emails_send(
					$email,
					[
						'subject'      => $subject,
						'message'      => $content,
						'content_type' => 'text/html',
					]
				);

				if ( $ret ) {
					do_action( 'learndash_notifications_email_sent', $email, $model, $args );
				} else {
					do_action( 'learndash_notifications_email_failed', $email, $model, $args );
				}

				$this->log( sprintf( 'Send to %s. Status: %s', $email, true === $ret ? 'sent' : 'fail' ) );
				remove_action( 'wp_mail_failed', [ &$this, 'debug_email_fail' ] );
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
		$query_args = [
			'post_type'      => learndash_get_post_type_slug( 'course' ),
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		];

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
		$query_args = [
			'post_type'      => learndash_get_post_type_slug( 'lesson' ),
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		];

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
			wp_schedule_single_event( $queue->sent_on, 'leanrdash_notifications_send_delayed_email' ); // cSpell:ignore leanrdash -- Typo.

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

		if ( ! $this->is_valid( $model, $args ) ) {
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
				wp_schedule_single_event( $next->sent_on, 'leanrdash_notifications_send_delayed_email' ); // cSpell:ignore leanrdash -- Typo.
			}

			return;
		}

		if ( ! $this->can_send_delayed_email( $model, $args ) ) {
			// the email is not valid anymore.
			$this->log( 'Condition not met' );
			$this->delete_queue( $queue->id );
			$next = $this->get_next_queue();
			if ( is_object( $next ) ) {
				wp_schedule_single_event( $next->sent_on, 'leanrdash_notifications_send_delayed_email' ); // cSpell:ignore leanrdash -- Typo.
			}

			return;
		}
		$emails = maybe_unserialize( $queue->recipient );
		$bcc    = maybe_unserialize( $queue->bcc );
		if ( ! is_array( $emails ) ) {
			$emails = [];
		}
		if ( ! is_array( $bcc ) ) {
			$bcc = [];
		}
		$emails = array_merge( $emails, $bcc );
		$emails = array_filter( $emails );
		$emails = array_unique( $emails );
		$this->send( $emails, $model, $args );
		$this->delete_queue( $queue->id );
		$next = $this->get_next_queue();
		if ( is_object( $next ) ) {
			wp_schedule_single_event( $next->sent_on, 'leanrdash_notifications_send_delayed_email' ); // cSpell:ignore leanrdash -- Typo.
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
		$status               = get_option( 'learndash_notifications_status', [] );
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
	 * @param array        $args  Args.
	 *
	 * @return bool
	 */
	abstract protected function can_send_delayed_email( Notification $model, $args );

	/**
	 * Pre save data in db, for processing late in cronjob
	 *
	 * @param array        $emails  The emails to queue.
	 * @param Notification $model   The notification model.
	 * @param array        $args    Mix args.
	 * @param null         $sent_on The time it should send.
	 *
	 * @return int
	 */
	public function queue_use_db( array $emails, Notification $model, array $args = [], $sent_on = null ) {
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
			[
				'title'          => $model->post->post_title,
				'message'        => $model->post->post_content,
				'recipient'      => maybe_serialize( $emails ),
				'shortcode_data' => maybe_serialize( $args ),
				'sent_on'        => $sent_on,
				// We don't need this anymore.
				'bcc'            => '',
			]
		);
		if ( $ret ) {
			// kick start.
			$queue = $this->get_next_queue();
			if ( wp_next_scheduled( 'leanrdash_notifications_send_delayed_email' ) ) { // cSpell:ignore leanrdash -- Typo.
				// if this is already queue, that mean the user has been out and in again, then we need to queue the later time.
				wp_clear_scheduled_hook( 'leanrdash_notifications_send_delayed_email' ); // cSpell:ignore leanrdash -- Typo.
			}
			$this->log(
				sprintf(
					'Queued to be sent out at %s - Unix timestamp: %s, recipients: %s',
					$this->get_current_time_from( $sent_on ),
					$sent_on,
					implode( ',', $emails )
				)
			);
			wp_schedule_single_event( $queue->sent_on, 'leanrdash_notifications_send_delayed_email' ); // cSpell:ignore leanrdash -- Typo.

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
			[
				'id' => $id,
			]
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
		$args = [
			'meta_key'       => '_ld_notifications_trigger',
			'meta_value'     => $type,
			'post_type'      => 'ld-notification',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
		];

		$posts  = get_posts( $args );
		$models = [];
		foreach ( $posts as $post ) {
			$model    = new Notification( $post );
			$models[] = $model;
		}

		return $models;
	}

	/**
	 * A logging function.
	 *
	 * @since 1.6.5 Removed unused second param.
	 *
	 * @param string      $message    The log message.
	 * @param string|null $deprecated This field is deprecated. Don't use.
	 */
	public function log( string $message, string $deprecated = null ) {
		if ( ! empty( $deprecated ) ) {
			_deprecated_argument( __METHOD__, '1.6.5' );
		}

		/**
		 * List of event trigger labels.
		 *
		 * @since 1.6.5
		 *
		 * @var array{ string: string } $event_labels The list of labels.
		 */
		$event_labels = learndash_notifications_get_triggers();
		$category     = $event_labels[ $this->trigger ] ?? __( 'Other actions', 'learndash-notifications' );

		Learndash_Logger::get_instance( 'notifications' )->info( "$category: $message" ); // @phpstan-ignore-line -- Can rely on this unless something goes horribly wrong.
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

	/**
	 * Get a course lesson ids.
	 *
	 * @param int $course_id Course post ID.
	 * @return array
	 */
	protected function get_course_lesson_ids( int $course_id ): array {
		return learndash_course_get_steps_by_type( $course_id, 'sfwd-lessons' );
	}
}
