<?php
/**
 * Assignment approved trigger.
 *
 * @package LearnDash\Notifications
 */

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

class Assignment_Approved extends Trigger {
	protected $trigger = 'approve_assignment';

	public function monitor( $assignment_id ) {
		$user_id   = get_post_meta( $assignment_id, 'user_id', true );
		$course_id = get_post_meta( $assignment_id, 'course_id', true );
		$lesson_id = get_post_meta( $assignment_id, 'lesson_id', true );
		$post_type = get_post_type( $lesson_id );
		$topic_id  = null;
		$models    = $this->get_notifications( $this->trigger );
		if ( empty( $models ) ) {
			return;
		}
		$this->log( '==========Job start========' );
		$this->log( sprintf( 'Process %d notifications', count( $models ) ) );
		if ( 'sfwd-topic' === $post_type ) {
			$topic_id  = $lesson_id;
			$lesson_id = null;
		}
		foreach ( $models as $model ) {
			if ( ! $this->is_valid(
				$model,
				[
					'user_id'   => $user_id,
					'course_id' => $course_id,
					'lesson_id' => $lesson_id,
					'topic_id'  => $topic_id,
				]
			) ) {
				continue;
			}

			$emails = $model->gather_emails( $user_id, $course_id );
			$args   = [
				'user_id'       => $user_id,
				'course_id'     => $course_id,
				'topic_id'      => $topic_id,
				'lesson_id'     => $lesson_id,
				'assignment_id' => $assignment_id,
			];
			if ( absint( $model->delay ) ) {
				$this->queue_use_db( $emails, $model, $args );
			} else {
				$this->send( $emails, $model, $args );
				$model->mark_sent( $user_id, $this->trigger, $model->post->ID, $course_id );
				$this->log( 'Done, moving next if any' );
			}
		}
	}

	/**
	 * A base point for monitoring the events
	 *
	 * @return void
	 */
	function listen() {
		add_action( 'learndash_assignment_approved', [ &$this, 'monitor' ] );
		add_action( 'leanrdash_notifications_send_delayed_email', [ &$this, 'send_db_delayed_email' ] );
	}

	/**
	 * @param Notification $model
	 * @param $args
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		$user_id   = $args['user_id'] ?? null;
		$course_id = $args['course_id'] ?? null;
		$lesson_id = $args['lesson_id'] ?? null;
		$topic_id  = $args['topic_id'] ?? null;

		if ( ! $this->is_valid(
			$model,
			[
				'user_id'   => $user_id,
				'course_id' => $course_id,
				'lesson_id' => $lesson_id,
				'topic_id'  => $topic_id,
			]
		) ) {
			return false;
		}

		return true;
	}
}
