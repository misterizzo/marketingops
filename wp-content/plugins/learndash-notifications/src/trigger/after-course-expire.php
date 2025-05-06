<?php
/**
 * After course expire trigger.
 *
 * @package LearnDash\Notifications
 */

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

class After_Course_Expire extends Trigger {
	protected $trigger = 'course_expires_after';

	public function maybe_send_reminder() {
		foreach ( $this->get_notifications( $this->trigger ) as $model ) {
			if ( $model->after_course_expiry <= 0 ) {
				continue;
			}
			$course_ids = [];
			if ( $model->course_id !== 0 ) {
				// okay we have the course_id
				$course_ids[] = $model->course_id;
			} else {
				// all ids
				$course_ids = $this->get_all_course();
			}

			foreach ( $course_ids as $course_id ) {
				if ( learndash_get_setting( $course_id, 'expire_access' ) !== 'on' || absint( learndash_get_setting( $course_id, 'expire_access_days' ) ) <= 0 ) {
					continue;
				}

				$user_ids = $this->get_users_from_a_course( $course_id );

				foreach ( $user_ids as $user_id ) {
					if ( ! $this->is_valid(
						$model,
						[
							'user_id'   => $user_id,
							'course_id' => $course_id,
						]
					) ) {
						continue;
					}

					if ( $model->is_sent( $user_id, $this->trigger, $model->post->ID, $course_id ) ) {
						continue;
					}
					if ( ! ld_course_access_expired( $course_id, $user_id ) ) {
						$this->log( 'course has not expired' );
						continue;
					}
					$timestamp = ld_course_access_expires_on( $course_id, $user_id );
					if ( $timestamp === 0 ) {
						// something wrong
						continue;
					}
					$init_time = get_option( 'ld_notifications_init' );
					if ( $init_time && $init_time > $timestamp ) {
						// prevent duplicate email
						continue;
					}
					$this->log( sprintf( esc_html__( 'The course will be expire at %s', 'learndash-notifications' ), $this->get_current_time_from( $timestamp ) ) );
					if ( strtotime( '+ ' . $model->before_course_expiry . ' days', $timestamp ) <= $this->get_timestamp() ) {
						// send emails
						$args   = [
							'user_id'   => $user_id,
							'course_id' => $course_id,
						];
						$emails = $model->gather_emails( $user_id, $course_id );
						$this->send( $emails, $model, $args );
						$model->mark_sent( $user_id, $this->trigger, $model->post->ID, $course_id );
					}
				}
			}
		}
	}

	/**
	 * Check trigger sent status when user course access is updated
	 *
	 * @param int   $user_id
	 * @param int   $course_id
	 * @param array $course_access_list
	 * @param bool  $remove
	 * @return void
	 */
	public function monitor_sent_status( $user_id, $course_id, $course_access_list, $remove ) {
		$this->models = $this->get_notifications( $this->trigger );
		if ( empty( $this->models ) ) {
			return [];
		}

		// Parse the variable to the right type.
		$user_id   = absint( $user_id );
		$course_id = absint( $course_id );
		$remove    = filter_var( $remove, FILTER_VALIDATE_BOOLEAN );
		$result    = [];
		$this->log( sprintf( 'Process %d notifications', count( $this->models ) ) );
		foreach ( $this->models as $model ) {
			$this->log( sprintf( '- Process notification %s', $model->post->post_title ) );
			if ( $model->is_sent( $user_id, $this->trigger, $model->post->ID, $course_id ) ) {
				$model->mark_unsent( $user_id, $this->trigger, $model->post->ID, $course_id );
				$this->log( sprintf( 'Clear sent status for user #%d in course #%d', $user_id, $course_id ) );
				$result[ $model->id ] = 'removed';
			}
		}

		return $result;
	}

	/**
	 * @param $id
	 *
	 * @return array
	 */
	protected function get_users_from_a_course( $id ) {
		$query = learndash_get_users_for_course( $id );
		if ( ! $query instanceof \WP_User_Query ) {
			// something was wrong
			return [];
		}

		return $query->get_results();
	}

	/**
	 * A base point for monitoring the events
	 *
	 * @return void
	 */
	function listen() {
		add_action( 'learndash_notifications_cron', [ &$this, 'maybe_send_reminder' ] );
		add_action( 'learndash_update_course_access', [ &$this, 'monitor_sent_status' ], 10, 4 );
	}

	/**
	 * @param Notification $model
	 * @param $args
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		return false;
	}
}
