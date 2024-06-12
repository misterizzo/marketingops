<?php

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

class Complete_Topic extends Trigger {

	/**
	 * @var string
	 */
	protected $trigger = 'complete_topic';

	public function monitor( $args ) {
		$course = isset( $args['course'] ) ? $args['course'] : null;
		$user   = isset( $args['user'] ) ? $args['user'] : null;
		$lesson = isset( $args['lesson'] ) ? $args['lesson'] : null;
		$topic  = isset( $args['topic'] ) ? $args['topic'] : null;
		if ( ! $course instanceof \WP_Post || ! is_object( $user ) || ! $topic instanceof \WP_Post ) {
			//nothing to do here
			$this->log( 'Invalid access', $this->trigger );

			return;
		}
		$models = $this->get_notifications( $this->trigger );
		if ( empty( $models ) ) {
			return;
		}

		$this->log( '==========Job start========' );
		$this->log( sprintf( 'Process %d notifications', count( $models ) ) );
		foreach ( $models as $model ) {
			if ( $model->course_id !== 0 && absint( $course->ID ) !== $model->course_id ) {
				continue;
			}

			if ( $model->lesson_id !== 0 && absint( $lesson->ID ) !== $model->lesson_id ) {
				continue;
			}

			if ( $model->topic_id !== 0 && $model->topic_id !== absint( $topic->ID ) ) {
				//this is not for me, as a lesson only belong to a course, so we don't need to check the course ID
				continue;
			}

			$emails = $model->gather_emails( $user->ID, $course->ID );
			$args   = [
				'user_id'   => $user->ID,
				'course_id' => $course->ID,
				'lesson_id' => $lesson->ID,
				'topic_id'  => $topic->ID,
			];

			if ( absint( $model->delay ) ) {
				$this->queue_use_db( $emails, $model, $args );
			} else {
				$this->send( $emails, $model, $args );
				$this->log( 'Done, moving next if any' );
			}
		}
		$this->log( '==========Job end========' );
	}

	/**
	 * A base point for monitoring the events
	 * @return void
	 */
	function listen() {
		add_action( 'learndash_topic_completed', [ &$this, 'monitor' ], 10 );
		add_action( 'leanrdash_notifications_send_delayed_email', [ &$this, 'send_db_delayed_email' ] );
	}

	/**
	 * @param Notification $model
	 * @param $args
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		$topic_id = $args['topic_id'];
		if ( $model->topic_id !== 0 && $model->topic_id !== $topic_id ) {
			//this is not for me, as a lesson only belong to a course, so we don't need to check the course ID
			$this->log( sprintf( "Won't send cause the ID is different from the settings. Expected: %d - Current:%d", $model->topic_id, $topic_id ) );

			return false;
		}

		return true;
	}
}