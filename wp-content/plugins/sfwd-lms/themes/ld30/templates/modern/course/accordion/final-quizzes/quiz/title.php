<?php
/**
 * View: Course Accordion Final Quiz - Title.
 *
 * @since 4.21.0
 * @version 4.21.3
 *
 * @var bool     $has_access Whether the user has access to the course or not.
 * @var Quiz     $quiz Quiz model object.
 * @var Template $this Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Quiz;
use LearnDash\Core\Template\Template;
?>
<div
	class="ld-accordion__item-title-wrapper ld-tooltip ld-tooltip--modern"
>
	<a
		<?php if ( ! $has_access && ! $quiz->is_sample() ) : ?>
			aria-describedby="ld-accordion__tooltip--final-quiz-<?php echo esc_attr( (string) $quiz->get_id() ); ?>"
		<?php endif; ?>
		class="ld-accordion__item-title ld-accordion__item-title--final-quiz"
		href="<?php echo esc_url( $quiz->get_permalink() ); ?>"
	>
		<?php echo wp_kses_post( $quiz->get_title() ); ?>
	</a>
	<?php if ( ! $has_access && ! $quiz->is_sample() ) : ?>
		<div
			class="ld-tooltip__text"
			id="ld-accordion__tooltip--final-quiz-<?php echo esc_attr( (string) $quiz->get_id() ); ?>"
			role="tooltip"
		>
			<?php esc_html_e( "You don't currently have access to this content", 'learndash' ); ?>
		</div>
	<?php endif; ?>
</div>
