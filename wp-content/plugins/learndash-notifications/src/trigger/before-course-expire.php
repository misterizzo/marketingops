<?php

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

class Before_Course_Expire extends Trigger {

	protected $trigger = 'course_expires';

	public function maybe_send_reminder() {
		foreach ( $this->get_notifications( $this->trigger ) as $model ) {
			if ( $model->before_course_expiry <= 0 ) {
				continue;
			}
			$course_ids = [];
			if ( $model->course_id !== 0 ) {
				//okay we have the course_id
				$course_ids[] = $model->course_id;
			} else {
				//all ids
				$course_ids = $this->get_all_course();
			}

			foreach ( $course_ids as $course_id ) {
				if ( learndash_get_setting( $course_id, 'expire_access' ) !== 'on' || absint( learndash_get_setting( $course_id, 'expire_access_days' ) ) <= 0 ) {
					continue;
				}
				if ( $model->course_id !== 0 && $model->course_id !== absint( $course_id ) ) {
					continue;
				}
				$user_ids = $this->get_users_from_a_course( $course_id );

				foreach ( $user_ids as $user_id ) {
					if ( $model->is_sent( $user_id, $this->trigger, $model->post->ID, $course_id ) ) {
						continue;
					}
					if ( learndash_course_completed( $user_id, $course_id ) || ld_course_access_expired( $course_id, $user_id ) ) {
						//the user has done this
						continue;
					}
					$timestamp = ld_course_access_expires_on( $course_id, $user_id );
					if ( $timestamp === 0 ) {
						continue;
					}
					$this->log( sprintf( esc_html__( 'The course will be expired at %s', 'learndash-notifications' ), $this->get_current_time_from( $timestamp ) ) );
					$init_time = get_option( 'ld_notifications_init' );
					if ( $init_time && $init_time > $timestamp ) {
						//prevent duplicate email
						continue;
					}
					if ( strtotime( '+ ' . $model->before_course_expiry . ' days', $this->get_timestamp() ) >= $timestamp ) {
						//send emails
						$emails = $model->gather_emails( $user_id, $course_id );
						$args   = array(
							'user_id'   => $user_id,
							'course_id' => $course_id
						);
						$this->send( $emails, $model, $args );
						$model->mark_sent( $user_id, $this->trigger, $model->post->ID, $course_id );
					}
				}
			}
		}
	}

	/**
	 * @param $id
	 *
	 * @return array
	 */
	protected function get_users_from_a_course( $id ) {
		$query = learndash_get_users_for_course( $id );
		if ( ! $query instanceof \WP_User_Query ) {
			//something was wrong
			return [];
		}

		return $query->get_results();
	}

	/**
	 * A base point for monitoring the events
	 * @return void
	 */
	function listen() {
		add_action( 'learndash_notifications_cron', [ &$this, 'maybe_send_reminder' ] );
	}

	/**
	 * @param Notification $model
	 * @param $args
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		//should never here
		return false;
	}
}