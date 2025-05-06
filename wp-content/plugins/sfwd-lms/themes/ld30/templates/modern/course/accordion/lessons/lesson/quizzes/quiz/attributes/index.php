<?php
/**
 * View: Course Accordion Lesson Quiz - Attributes.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Quiz     $quiz   Quiz model object.
 * @var Template $this Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Quiz;
use LearnDash\Core\Template\Template;

if (
	! $quiz->is_virtual()
	&& ! $quiz->is_in_person()
	&& $quiz->get_available_on_date() === null
) {
	return;
}

?>
<div class="ld-accordion__item-attributes ld-accordion__item-attributes--lesson-quiz">
	<?php $this->template( 'modern/course/accordion/lessons/lesson/quizzes/quiz/attributes/virtual' ); ?>

	<?php $this->template( 'modern/course/accordion/lessons/lesson/quizzes/quiz/attributes/in-person' ); ?>

	<?php $this->template( 'modern/course/accordion/lessons/lesson/quizzes/quiz/attributes/available-on' ); ?>
</div>
