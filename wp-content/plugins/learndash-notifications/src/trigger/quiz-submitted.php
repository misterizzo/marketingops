<?php

namespace LearnDash_Notification\Trigger;

/**
 * Class Quiz_Submitted
 *
 * @package LearnDash_Notification\Trigger
 */
class Quiz_Submitted extends Quiz_Passed {
	/**
	 * The notification slug.
	 *
	 * @var string
	 */
	protected $trigger = 'submit_quiz';

	/**
	 * Listen for events.
	 */
	public function listen() {
		add_action( 'learndash_quiz_submitted', array( &$this, 'monitor' ), 10, 2 );
		add_action( 'leanrdash_notifications_send_delayed_email', array( &$this, 'send_db_delayed_email' ) );
	}

	/**
	 * It should always run
	 *
	 * @param array $quiz_data The quiz data.
	 *
	 * @return bool
	 */
	protected function is_process( $quiz_data ) {
		return true;
	}
}
