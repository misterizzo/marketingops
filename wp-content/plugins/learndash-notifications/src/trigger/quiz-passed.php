<?php

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

/**
 * Class Quiz_Passed
 *
 * @package LearnDash_Notification\Trigger.
 */
class Quiz_Passed extends Trigger {
	/**
	 * @var string
	 */
	protected $trigger = 'pass_quiz';

	/**
	 * @param array $quiz_data
	 * @param \WP_User $user
	 */
	public function monitor( $quiz_data, $user ) {
		if ( ! $this->is_process( $quiz_data ) ) {
			return;
		}
		$quiz_id   = $quiz_data['quiz'];
		$course_id = $quiz_data['course'];
		$lesson_id = $quiz_data['lesson'];
		$topic_id  = $quiz_data['topic'];

		if ( is_object( $quiz_id ) ) {
			$quiz_id = $quiz_id->ID;
		}
		if ( is_object( $course_id ) ) {
			$course_id = $course_id->ID;
		}
		if ( is_object( $lesson_id ) ) {
			$lesson_id = $lesson_id->ID;
		}
		if ( is_object( $topic_id ) ) {
			$topic_id = $topic_id->ID;
		}
		$quiz_id   = absint( $quiz_id );
		$course_id = absint( $course_id );
		$lesson_id = absint( $lesson_id );
		$topic_id  = absint( $topic_id );
		$models    = $this->get_notifications( $this->trigger );
		if ( empty( $models ) ) {
			return;
		}
		$this->log( '==========Job start========' );
		$this->log( sprintf( 'Process %d notifications', count( $models ) ) );
		foreach ( $models as $model ) {
			// waterfall.
			if ( 0 !== $model->course_id && $course_id !== $model->course_id ) {
				continue;
			}
			if ( 0 !== $model->lesson_id && $lesson_id !== $model->lesson_id ) {
				continue;
			}
			if ( 0 !== $model->topic_id && $topic_id !== $model->topic_id ) {
				continue;
			}
			if ( 0 !== $model->quiz_id && $quiz_id !== $model->quiz_id ) {
				// specific course and this is not the one, return.
				continue;
			}

			$emails                       = $model->gather_emails( $user->ID, $course_id );
			$ld_notifications_quiz_result = array(
				'cats'       => isset( $_POST['results']['comp']['cats'] ) ? $_POST['results']['comp']['cats'] : null,
				'pro_quizid' => $quiz_data['pro_quizid'],
			);
			$args                         = array(
				'user_id'     => $user->ID,
				'course_id'   => $course_id,
				'quiz_id'     => $quiz_id,
				'lesson_id'   => $lesson_id,
				'topic_id'    => $quiz_data['topic'],
				'quiz_result' => $ld_notifications_quiz_result,
			);
			$model->populate_shortcode_data( $args );
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
	 * User should pass the quiz for this to be working
	 *
	 * @param $quiz_data
	 *
	 * @return bool
	 */
	protected function is_process( $quiz_data ) {
		return absint( $quiz_data['pass'] ) === 1;
	}

	/**
	 * A base point for monitoring the events
	 *
	 * @return void
	 */
	function listen() {
		add_action( 'learndash_quiz_completed', array( &$this, 'monitor' ), 10, 2 );
		add_action( 'leanrdash_notifications_send_delayed_email', array( &$this, 'send_db_delayed_email' ) );
	}

	/**
	 * Check if the delayed email can be sent.
	 *
	 * @param Notification $model The notification model.
	 * @param array $args The data.
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		$quiz_id = $args['quiz_id'];
		if ( 0 !== $model->quiz_id && $quiz_id !== $model->quiz_id ) {
			// specific course and this is not the one, return.
			$this->log(
				sprintf(
					"Won't send cause the ID is different from the settings. Expected: %d - Current:%d",
					$model->quiz_id,
					$quiz_id
				)
			);

			return false;
		}

		return true;
	}
}
