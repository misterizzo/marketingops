<?php
/**
 * View: Course Accordion - Final Quizzes.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Course   $course        Course model object.
 * @var Quiz[]   $final_quizzes Array of quiz model objects.
 * @var Template $this          Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Template\Template;
use LearnDash\Core\Models\Course;
use LearnDash\Core\Models\Quiz;

if ( count( $final_quizzes ) <= 0 ) {
	return;
}

?>
<div class="ld-accordion__section ld-accordion__section--final-quizzes">
	<?php $this->template( 'modern/course/accordion/final-quizzes/heading' ); ?>

	<div class="ld-accordion__items ld-accordion__items--final-quizzes">
		<?php foreach ( $final_quizzes as $quiz ) : ?>
			<?php $this->template( 'modern/course/accordion/final-quizzes/quiz', [ 'quiz' => $quiz ] ); ?>
		<?php endforeach; ?>
	</div>
</div>

