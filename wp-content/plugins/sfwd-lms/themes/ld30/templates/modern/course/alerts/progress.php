<?php
/**
 * View: Course Header - Progress.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var bool     $has_access Whether the user has access to the course or not.
 * @var Course   $course     Course model.
 * @var WP_User  $user       Current user.
 * @var Template $this       Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Course;
use LearnDash\Core\Template\Template;

// Bail if user does not have access to the course. We are showing the progress only to users who have access.
if ( ! $has_access ) {
	return;
}

$this->template(
	'modules/infobar/course',
	[
		'has_access'    => $has_access,
		'user_id'       => $user->ID,
		'course_id'     => $course->get_id(),
		'course_status' => learndash_course_status( $course->get_id(), $user->ID ),
		'post'          => $course->get_post(),
	]
);
