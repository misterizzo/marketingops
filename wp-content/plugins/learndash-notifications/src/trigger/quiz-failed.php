<?php
/**
 * Quiz failed trigger.
 *
 * @package LearnDash\Notifications
 */

namespace LearnDash_Notification\Trigger;

class Quiz_Failed extends Quiz_Passed {
	protected $trigger = 'fail_quiz';

	/**
	 * Only continue when the quiz is fail
	 *
	 * @param $quiz_data
	 *
	 * @return bool
	 */
	protected function is_process( $quiz_data ) {
		return absint( $quiz_data['pass'] ) !== 1;
	}
}
