<?php

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

class Enroll_Course extends Trigger {
	protected $trigger = 'enroll_course';

	protected $models = [];

	/**
	 * Monitor the course access, depend on the $remove, the access can be enroll or un-roll
	 *
	 * @param $user_id
	 * @param $course_id
	 * @param $access_list
	 * @param $remove
	 *
	 * @return array
	 */
	public function monitor_course_access( $user_id, $course_id, $access_list, $remove ): array {
		$this->models = $this->get_notifications( 'enroll_course' );
		if ( empty( $this->models ) ) {
			return array();
		}
		// parse the variable to the right type.
		$user_id   = absint( $user_id );
		$course_id = absint( $course_id );
		$remove    = filter_var( $remove, FILTER_VALIDATE_BOOLEAN );
		$result    = array();
		$this->log( sprintf( 'Process %d notifications', count( $this->models ) ) );
		foreach ( $this->models as $model ) {
			$this->log( sprintf( '- Process notification %s', $model->post->post_title ) );
			if ( true === $remove ) {
				//then we will need to delete the mark sent
				$model->mark_unsent( $user_id, $this->trigger, $model->post->ID, $course_id );
				$this->log( sprintf( 'Clear sent status for user #%d in course #%d', $user_id, $course_id ) );
				$result[ $model->id ] = 'removed';
			} else {
				$this->send_enroll_notification( $model, $user_id, $course_id );
				$result[ $model->id ] = 'sent';
			}
		}
		$this->log( '=========Job End==================' );

		return $result;
	}

	/**
	 * Send the email to the recipients instantly.
	 *
	 * @param Notification $model
	 * @param int $user_id
	 * @param int $course_id
	 *
	 * @return bool
	 */
	public function send_enroll_notification( Notification $model, int $user_id, int $course_id ) {
		if ( $model->is_sent( $user_id, $this->trigger, $model->post->ID, $course_id ) ) {
			$this->log( sprintf( 'An email already sent to the user #%d.', $user_id ) );

			return false;
		}

		if ( $model->course_id !== 0 && $model->course_id !== $course_id ) {
			//specific course and this is not the one, return
			$this->cli_log( 'Not the one, return' );

			return false;
		}
		$emails = $model->gather_emails( $user_id, $course_id );
		$args   = [
			'user_id'   => $user_id,
			'course_id' => $course_id
		];
		if ( absint( $model->delay ) ) {
			$this->queue_use_db( $emails, $model, $args );
		} else {
			$this->send( $emails, $model, $args );
			$model->mark_sent( $user_id, $this->trigger, $model->post->ID, $course_id );
		}

		$this->cli_log( 'Reach bottom' );

		return true;
	}

	/**
	 * User can enroll via automate enroll, so listen to this trigger too
	 *
	 * @param $user_id
	 * @param $group_id
	 */
	public function monitor_auto_enroll_via_group( $user_id, $group_id ) {
		$course_ids = learndash_group_enrolled_courses( $group_id );
		if ( count( $course_ids ) ) {
			$this->log( '==========Group Access ========' );
			foreach ( $course_ids as $course_id ) {
				$this->monitor_course_access( $user_id, $course_id, [], false );
			}
		}
	}

	/**
	 * @param $course_id
	 * @param $group_id
	 */
	public function monitor_enroll_via_course_group( $course_id, $group_id ) {
		$this->log( '==========Group Access========' );
		$user_ids = learndash_get_groups_user_ids( $group_id );
		foreach ( $user_ids as $user_id ) {
			$this->monitor_course_access( $user_id, $course_id, [], false );
		}
	}

	/**
	 * A base point for monitoring the events
	 * @return void
	 */
	function listen() {
		add_action( 'learndash_update_course_access', [ &$this, 'monitor_course_access' ], 10, 4 );
		add_action( 'leanrdash_notifications_send_delayed_email', [ &$this, 'send_db_delayed_email' ] );
		add_action( 'ld_added_group_access', [ &$this, 'monitor_auto_enroll_via_group' ], 10, 2 );
		add_action( 'ld_added_course_group_access', [ $this, 'monitor_enroll_via_course_group' ], 10, 2 );
	}

	/**
	 * @param Notification $model
	 * @param $args
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		$user_id   = $args['user_id'];
		$course_id = $args['course_id'];

		if ( ! ld_course_check_user_access( $course_id, $user_id ) ) {
			//this user not in this course anymore
			return false;
		}
		if ( $model->course_id !== 0 && $model->course_id !== $course_id ) {
			//specific course and this is not the one, return
			$this->log( sprintf( "Won't send cause the ID is different from the settings. Expected: %d - Current:%d",
			                     $model->course_id,
			                     $course_id ) );

			return false;
		}

		return true;
	}

	/**
	 * @param Notification $model
	 * @param array $args
	 */
	protected function after_email_sent( Notification $model, array $args ) {
		$user_id   = $args['user_id'];
		$course_id = $args['course_id'];
		$model->mark_sent( $user_id, $this->trigger, $model->post->ID, $course_id );
	}
}