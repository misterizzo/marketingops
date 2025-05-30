<?php
/**
 * ProQuiz view question edit.
 *
 * @package LearnDash\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProQuiz view question edit.
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName,WordPress.NamingConventions.ValidFunctionName,WordPress.NamingConventions.ValidHookName
 */
class WpProQuiz_View_QuestionEdit extends WpProQuiz_View_View {
	/**
	 * @var WpProQuiz_Model_Category
	 */
	public $categories;

	/**
	 * @var WpProQuiz_Model_Question;
	 */
	public $question;

	/**
	 * Outputs the view.
	 *
	 * @return void
	 */
	public function show() {
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );

		if ( isset( $_GET['post_id'] ) ) {
			$post_id = absint( $_GET['post_id'] );
		} else {
			$post_id = 0;
		}
		?>
<div id="wpProQuiz_nonce" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpProQuiz_nonce' ) ); ?>" style="display:none;"></div>
<div class="wrap wpProQuiz_questionEdit">
	<h2 style="margin-bottom: 10px;"><?php echo wp_kses_post( $this->header ); ?></h2>
	<form action="admin.php?page=ldAdvQuiz&module=question&action=addEdit&quiz_id=<?php echo intval( $this->quiz->getId() ); ?>&questionId=<?php echo intval( $this->question->getId() ); ?>&post_id=<?php echo intval( $post_id ); ?>" method="POST">
		<a style="float: left;" class="button-secondary" href="admin.php?page=ldAdvQuiz&module=question&action=show&quiz_id=<?php echo intval( $this->quiz->getId() ); ?>&post_id=<?php echo intval( $post_id ); ?>">
		<?php
		printf(
			// translators: Question
			esc_html_x( 'Return to %s Overview', 'placeholder: Question', 'learndash' ),
			esc_html( learndash_get_custom_label( 'question' ) )
		)
		?>
		</a>
		<div style="float: right;">
			<select name="templateLoadId">
				<?php
				foreach ( $this->templates as $template ) {
					echo '<option value="', esc_attr( $template->getTemplateId() ), '">', esc_html( $template->getName() ), '</option>';
				}
				?>
			</select>
			<input type="submit" name="templateLoad" value="<?php esc_html_e( 'load template', 'learndash' ); ?>" class="button-primary">
		</div>
		<div style="clear: both;"></div>
		<!-- <input type="hidden" value="edit" name="hidden_action">
		<input type="hidden" value="<?php echo intval( $this->question->getId() ); ?>" name="questionId">-->
		<div id="poststuff">
			<div class="postbox">
				<h3 class="hndle"><?php esc_html_e( 'Title', 'learndash' ); ?> <?php esc_html_e( '(optional)', 'learndash' ); ?></h3>
				<div class="inside">
					<p class="description">
						<?php
						// translators: placeholder: quiz.
						printf( esc_html_x( 'The title is used for overview, it is not visible in %s. If you leave the title field empty, a title will be generated.', 'placeholder: quiz', 'learndash' ), esc_html( learndash_get_custom_label_lower( 'quiz' ) ) );
						?>
					</p>
					<input name="title" class="regular-text" value="<?php echo esc_attr( $this->question->getTitle() ); ?>" type="text">
				</div>
			</div>
			<div class="postbox">
				<h3 class="hndle"><?php esc_html_e( 'Points', 'learndash' ); ?> <?php esc_html_e( '(required)', 'learndash' ); ?></h3>
				<div class="inside">
					<div id="wpProQuiz_questionPoints>
						<p class="description">
							<?php
								printf(
									// translators: placeholder: question.
									esc_html_x( 'Points for this %s (Standard is 1 point)', 'placeholder: question', 'learndash' ),
									esc_html( learndash_get_custom_label_lower( 'question' ) )
								)
							?>
						</p>
						<label>
							<input name="points" class="small-text" value="<?php echo esc_attr( (string) $this->question->getPoints() ); ?>" type="number" min="1"> <?php esc_html_e( 'Points', 'learndash' ); ?>
						</label>
						<p class="description">
							<?php
								printf(
									// translators: placeholder: question
									esc_html_x( 'These points will be rewarded, only if the user answers the %s correctly.', 'placeholder: question', 'learndash' ),
									esc_html( learndash_get_custom_label_lower( 'question' ) )
								)
							?>
						</p>
					</div>
					<div style="margin-top: 10px;" id="wpProQuiz_answerPointsActivated">
						<label>
							<input name="answerPointsActivated" type="checkbox" value="1" <?php echo $this->question->isAnswerPointsActivated() ? 'checked="checked"' : ''; ?>>
							<?php esc_html_e( 'Different points for each answer', 'learndash' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'If you enable this option, you can enter different points for every answer.', 'learndash' ); ?>
						</p>
					</div>
					<div style="margin-top: 10px; display: none;" id="wpProQuiz_showPointsBox">
						<label>
							<input name="showPointsInBox" value="1" type="checkbox" <?php echo $this->question->isShowPointsInBox() ? 'checked="checked"' : ''; ?>>
							<?php esc_html_e( 'Show reached points in the correct and incorrect message?', 'learndash' ); ?>
						</label>
					</div>
				</div>
			</div>
			<div class="postbox">
				<h3 class="hndle"><?php esc_html_e( 'Category', 'learndash' ); ?> <?php esc_html_e( '(optional)', 'learndash' ); ?></h3>
				<div class="inside">
					<p class="description">
						<?php esc_html_e( 'You can assign classify category for a question. Categories are e.g. visible in statistics function.', 'learndash' ); ?>
					</p>
					<p class="description">
						<?php esc_html_e( 'You can manage categories in global settings.', 'learndash' ); ?>
					</p>
					<div>
						<select name="category">
							<option value="-1">--- <?php esc_html_e( 'Create new category', 'learndash' ); ?> ----</option>
							<option value="0" <?php echo $this->question->getCategoryId() == 0 ? 'selected="selected"' : ''; ?>>--- <?php esc_html_e( 'No category', 'learndash' ); ?> ---</option>
							<?php
							foreach ( $this->categories as $cat ) {
								echo '<option ' . ( $this->question->getCategoryId() == $cat->getCategoryId() ? 'selected="selected"' : '' ) . ' value="' . esc_attr( $cat->getCategoryId() ) . '">' . esc_html( stripslashes( $cat->getCategoryName() ) ) . '</option>';
							}
							?>
						</select>
					</div>
					<div style="display: none;" id="categoryAddBox">
						<h4><?php esc_html_e( 'Create new category', 'learndash' ); ?></h4>
						<input type="text" name="categoryAdd" value="">
						<input type="button" class="button-secondary" name="" id="categoryAddBtn" value="<?php esc_html_e( 'Create', 'learndash' ); ?>">
					</div>
					<div id="categoryMsgBox" style="display:none; padding: 5px; border: 1px solid rgb(160, 160, 160); background-color: rgb(255, 255, 168); font-weight: bold; margin: 5px; ">
						Kategorie gespeichert
					</div>
				</div>
			</div>
			<div class="postbox">
				<h3 class="hndle"><?php esc_html_e( 'Question', 'learndash' ); ?> <?php esc_html_e( '(required)', 'learndash' ); ?></h3>
				<div class="inside">
					<?php
						wp_editor( $this->question->getQuestion(), 'question', array( 'textarea_rows' => 5 ) );
					?>
				</div>
			</div>
			<div class="postbox" style="<?php echo $this->quiz->isHideAnswerMessageBox() ? '' : 'display: none;'; ?>">
				<h3 class="hndle"><?php esc_html_e( 'Message with the correct / incorrect answer', 'learndash' ); ?></h3>
				<div class="inside">
					<?php
					// translators: placeholder: quiz.
					printf( esc_html_x( 'Deactivated in %s settings.', 'placeholder: quiz', 'learndash' ), esc_html( learndash_get_custom_label_lower( 'quiz' ) ) );
					?>
				</div>
			</div>
			<div style="<?php echo $this->quiz->isHideAnswerMessageBox() ? 'display: none;' : ''; ?>">
				<div class="postbox" id="wpProQuiz_correctMessageBox">
					<h3 class="hndle"><?php esc_html_e( 'Message with the correct answer', 'learndash' ); ?> <?php esc_html_e( '(optional)', 'learndash' ); ?></h3>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'This text will be visible if answered correctly. It can be used as explanation for complex questions. The message "Right" or "Wrong" is always displayed automatically.', 'learndash' ); ?>
						</p>
						<div style="padding-top: 10px; padding-bottom: 10px;">
							<label for="wpProQuiz_correctSameText">
								<?php esc_html_e( 'Same text for correct- and incorrect-message?', 'learndash' ); ?>
								<input type="checkbox" name="correctSameText" id="wpProQuiz_correctSameText" value="1" <?php echo $this->question->isCorrectSameText() ? 'checked="checked"' : ''; ?>>
							</label>
						</div>
						<?php
							wp_editor( $this->question->getCorrectMsg(), 'correctMsg', array( 'textarea_rows' => 3 ) );
						?>
					</div>
				</div>
				<div class="postbox" id="wpProQuiz_incorrectMassageBox">
					<h3 class="hndle"><?php esc_html_e( 'Message with the incorrect answer', 'learndash' ); ?> <?php esc_html_e( '(optional)', 'learndash' ); ?></h3>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'This text will be visible if answered incorrectly. It can be used as explanation for complex questions. The message "Right" or "Wrong" is always displayed automatically.', 'learndash' ); ?>
						</p>
						<?php
							wp_editor( $this->question->getIncorrectMsg(), 'incorrectMsg', array( 'textarea_rows' => 3 ) );
						?>
					</div>
				</div>
			</div>
			<div class="postbox">
				<h3 class="hndle"><?php esc_html_e( 'Hint', 'learndash' ); ?> <?php esc_html_e( '(optional)', 'learndash' ); ?></h3>
				<div class="inside">
					<p class="description">
						<?php esc_html_e( 'Here you can enter solution hint.', 'learndash' ); ?>
					</p>
					<div style="padding-top: 10px; padding-bottom: 10px;">
						<label for="wpProQuiz_tip">
							<?php esc_html_e( 'Activate hint for this question?', 'learndash' ); ?>
							<input type="checkbox" name="tipEnabled" id="wpProQuiz_tip" value="1" <?php echo $this->question->isTipEnabled() ? 'checked="checked"' : ''; ?>>
						</label>
					</div>
					<div id="wpProQuiz_tipBox">
						<?php
							wp_editor( $this->question->getTipMsg(), 'tipMsg', array( 'textarea_rows' => 3 ) );
						?>
					</div>
				</div>
			</div>
			<div class="postbox">
				<h3 class="hndle"><?php esc_html_e( 'Answer type', 'learndash' ); ?></h3>
				<div class="inside">
				<?php
					$type = $this->question->getAnswerType();
					$type = null === $type ? 'single' : $type;
				?>
					<label style="padding-right: 10px;">
						<input type="radio" name="answerType" value="single" <?php echo ( 'single' === $type ) ? 'checked="checked"' : ''; ?>>
						<?php esc_html_e( 'Single choice', 'learndash' ); ?>
					</label>
					<label style="padding-right: 10px;">
						<input type="radio" name="answerType" value="multiple" <?php echo ( 'multiple' === $type ) ? 'checked="checked"' : ''; ?>>
						<?php esc_html_e( 'Multiple choice', 'learndash' ); ?>
					</label>
					<label style="padding-right: 10px;">
						<input type="radio" name="answerType" value="free_answer" <?php echo ( 'free_answer' === $type ) ? 'checked="checked"' : ''; ?>>
						<?php esc_html_e( '"Free" choice', 'learndash' ); ?>
					</label>
					<label style="padding-right: 10px;">
						<input type="radio" name="answerType" value="sort_answer" <?php echo ( 'sort_answer' === $type ) ? 'checked="checked"' : ''; ?>>
						<?php esc_html_e( '"Sorting" choice', 'learndash' ); ?>
					</label>
					<label style="padding-right: 10px;">
						<input type="radio" name="answerType" value="matrix_sort_answer" <?php echo ( 'matrix_sort_answer' === $type ) ? 'checked="checked"' : ''; ?>>
						<?php esc_html_e( '"Matrix Sorting" choice', 'learndash' ); ?>
					</label>
					<label style="padding-right: 10px;">
						<input type="radio" name="answerType" value="cloze_answer" <?php echo ( 'cloze_answer' === $type ) ? 'checked="checked"' : ''; ?>>
						<?php esc_html_e( 'Fill in the blank', 'learndash' ); ?>
					</label>
					<label style="padding-right: 10px;">
						<input type="radio" name="answerType" value="assessment_answer" <?php echo ( 'assessment_answer' === $type ) ? 'checked="checked"' : ''; ?>>
						<?php esc_html_e( 'Assessment', 'learndash' ); ?>
					</label>
					<label style="padding-right: 10px;">
						<input type="radio" name="answerType" value="essay" <?php echo ( 'essay' === $type ) ? 'checked="checked"' : ''; ?>>
						<?php esc_html_e( 'Essay / Open Answer', 'learndash' ); ?>
					</label>
				</div>
			</div>
			<?php $this->singleChoiceOptions( $this->data['classic_answer'] ); ?>
			<div class="postbox" id="wpProQuiz_answers">
				<h3 class="hndle"><?php esc_html_e( 'Answers', 'learndash' ); ?> <?php esc_html_e( '(required)', 'learndash' ); ?></h3>
				<div class="inside answer_felder">
					<div class="free_answer">
						<?php $this->freeChoice( $this->data['free_answer'] ); ?>
					</div>
					<div class="sort_answer">
						<p class="description">
							<?php esc_html_e( 'Please sort the answers in right order with the "Move" - Button. The answers will be displayed randomly.', 'learndash' ); ?>
						</p>
						<ul class="answerList">
							<?php $this->sortingChoice( $this->data['sort_answer'] ); ?>
						</ul>
						<input type="button" class="button-primary addAnswer" data-default-value="<?php echo esc_attr( LEARNDASH_LMS_DEFAULT_ANSWER_POINTS ); ?>" value="<?php esc_html_e( 'Add new answer', 'learndash' ); ?>">
					</div>
					<div class="classic_answer">
						<ul class="answerList">
							<?php $this->singleMultiCoice( $this->data['classic_answer'] ); ?>
						</ul>
						<input type="button" class="button-primary addAnswer" data-default-value="<?php echo esc_attr( LEARNDASH_LMS_DEFAULT_ANSWER_POINTS ); ?>" value="<?php esc_html_e( 'Add new answer', 'learndash' ); ?>">
					</div>
					<div class="matrix_sort_answer">
						<p class="description">
							<?php esc_html_e( 'In this mode, Sort Elements must be assigned to their corresponding Criterion.', 'learndash' ); ?>
						</p>
						<p class="description">
							<?php esc_html_e( 'Each Sort Element must be unique, and only one-to-one associations are supported.', 'learndash' ); ?>
						</p>
						<br>
						<label>
							<?php esc_html_e( 'Percentage width of criteria table column:', 'learndash' ); ?>
							<?php $msacwValue = $this->question->getMatrixSortAnswerCriteriaWidth() > 0 ? $this->question->getMatrixSortAnswerCriteriaWidth() : 20; ?>
							<input type="number" min="1" max="99" step="1" name="matrixSortAnswerCriteriaWidth" value="<?php echo esc_attr( $msacwValue ); ?>">%
						</label>
						<p class="description">
							<?php esc_html_e( 'Allows adjustment of the left column\'s width, and the right column will auto-fill the rest of the available space. Increase this to allow accommodate longer criterion text. Defaults to 20%.', 'learndash' ); ?>
						</p>
						<br>
						<ul class="answerList">
							<?php $this->matrixSortingChoice( $this->data['matrix_sort_answer'] ); ?>
						</ul>
						<input type="button" class="button-primary addAnswer" data-default-value="<?php echo esc_attr( LEARNDASH_LMS_DEFAULT_ANSWER_POINTS ); ?>" value="<?php esc_html_e( 'Add new answer', 'learndash' ); ?>">
					</div>
					<div class="cloze_answer">
						<?php $this->clozeChoice( $this->data['cloze_answer'] ); ?>
					</div>
					<div class="assessment_answer">
						<?php $this->assessmentChoice( $this->data['assessment_answer'] ); ?>
					</div>
					<div class="essay">
						<?php $this->essayChoice( $this->data['essay'] ); ?>
					</div>
				</div>
			</div>

			<div style="float: left;">
				<input type="submit" name="submit" id="saveQuestion" class="button-primary" value="<?php esc_html_e( 'Save', 'learndash' ); ?>">
			</div>
			<div style="float: right;">
				<input type="text" placeholder="<?php esc_html_e( 'template name', 'learndash' ); ?>" class="regular-text" name="templateName" style="border: 1px solid rgb(255, 134, 134);">
				<select name="templateSaveList">
					<option value="0">=== <?php esc_html_e( 'Create new template', 'learndash' ); ?> === </option>
					<?php
					// @phpstan-ignore-next-line -- I don't want to touch it.
					foreach ( $this->templates as $template ) {
						echo '<option value="', esc_attr( $template->getTemplateId() ), '">', esc_html( $template->getName() ), '</option>';
					}
					?>
				</select>

				<input type="submit" name="template" class="button-primary" id="wpProQuiz_saveTemplate" value="<?php esc_html_e( 'Save as template', 'learndash' ); ?>">
			</div>
			<div style="clear: both;"></div>

		</div>
	</form>
</div>

		<?php
	}

	/**
	 * Echoes the multi choice row.
	 *
	 * @since 2.6.0
	 *
	 * @param array<WpProQuiz_Model_AnswerTypes> $data Data.
	 *
	 * @return void
	 */
	public function singleMultiCoice( $data ) {
		// cspell:disable-line.
		foreach ( $data as $d ) {
			?>

	<li style="border-bottom:1px dotted #ccc; padding-bottom: 5px; background-color: whiteSmoke;" id="TEST">
		<table style="width: 100%;border: 1px solid #9E9E9E;border-collapse: collapse; margin-bottom: 20px;">
			<thead>
				<tr>
					<th width="160px" style=" border-right: 1px solid #9E9E9E; padding: 5px; "><?php esc_html_e( 'Options', 'learndash' ); ?></th>
					<th style="padding: 5px;"><?php esc_html_e( 'Answer', 'learndash' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="border-right: 1px solid #9E9E9E; padding: 5px; vertical-align: top;">
						<div>
							<label>
								<input type="checkbox" class="wpProQuiz_classCorrect wpProQuiz_checkbox" name="answerData[][correct]" value="1" <?php $this->checked( $d->isCorrect() ); ?>>
								<?php esc_html_e( 'Correct', 'learndash' ); ?>
							</label>
						</div>
						<div style="padding-top: 5px;">
							<label>
								<input type="checkbox" class="wpProQuiz_checkbox" name="answerData[][html]" value="1" <?php $this->checked( $d->isHtml() ); ?>>
								<?php esc_html_e( 'Allow HTML', 'learndash' ); ?>
							</label>
						</div>
						<div style="padding-top: 5px;" class="wpProQuiz_answerPoints">
							<label>
								<input type="number" step="any" class="small-text wpProQuiz_points" name="answerData[][points]" value="<?php echo esc_attr( $d->getPoints() ); ?>">
								<?php esc_html_e( 'Points', 'learndash' ); ?>
							</label>
						</div>
					</td>
					<td style="padding: 5px; vertical-align: top;">
						<textarea rows="2" cols="50" class="large-text wpProQuiz_text wpProQuiz_single_text" name="answerData[][answer]" style="resize:vertical;"><?php echo esc_html( $d->getAnswer() ); ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>

		<input type="button" name="submit" class="button-primary deleteAnswer" value="<?php esc_html_e( 'Delete answer', 'learndash' ); ?>">
		<input type="button" class="button-secondary addMedia" value="<?php esc_html_e( 'Add Media', 'learndash' ); ?>">
		<a href="#" class="button-secondary wpProQuiz_move" style="cursor: move;"><?php esc_html_e( 'Move', 'learndash' ); ?></a>

	</li>

			<?php
		}
	}

	/**
	 * Outputs the matrix sorting choice.
	 *
	 * @param array<WpProQuiz_Model_AnswerTypes> $data Data.
	 *
	 * @return void
	 */
	public function matrixSortingChoice( $data ) {
		foreach ( $data as $d ) {
			?>
			<li style="border-bottom:1px dotted #ccc; padding-bottom: 5px; background-color: whiteSmoke;">
				<table style="width: 100%;border: 1px solid #9E9E9E;border-collapse: collapse; margin-bottom: 20px;">
					<thead>
						<tr>
							<th width="130px" style=" border-right: 1px solid #9E9E9E; padding: 5px; "><?php esc_html_e( 'Options', 'learndash' ); ?></th>
							<th style=" border-right: 1px solid #9E9E9E; padding: 5px; "><?php esc_html_e( 'Criterion', 'learndash' ); ?></th>
							<th style="padding: 5px;"><?php esc_html_e( 'Sort elements', 'learndash' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="border-right: 1px solid #9E9E9E; padding: 5px; vertical-align: top;">
								<label class="wpProQuiz_answerPoints">
									<input type="number" step="any" class="small-text wpProQuiz_points" name="answerData[][points]" value="<?php echo esc_attr( $d->getPoints() ); ?>">
									<?php esc_html_e( 'Points', 'learndash' ); ?>
								</label>
							</td>
							<td style="border-right: 1px solid #9E9E9E; padding: 5px; vertical-align: top;">
								<textarea rows="4" name="answerData[][answer]" class="wpProQuiz_text wpProQuiz_matrix_answer" style="width: 100%; resize:vertical;"><?php echo wp_kses_post( $d->getAnswer() ); ?></textarea>
							</td>
							<td style="padding: 5px; vertical-align: top;">
								<textarea rows="4" name="answerData[][sort_string]" class="wpProQuiz_text wpProQuiz_matrix_sort_string" style="width: 100%; resize:vertical;"><?php echo wp_kses_post( $d->getSortString() ); ?></textarea>
							</td>
						</tr>
						<tr>
							<td style="border-right: 1px solid #9E9E9E; padding: 5px; vertical-align: top;"></td>
							<td style="border-right: 1px solid #9E9E9E; padding: 5px; vertical-align: top;">
								<label>
									<input type="checkbox" class="wpProQuiz_checkbox" name="answerData[][html]" value="1" <?php $this->checked( $d->isHtml() ); ?>>
									<?php esc_html_e( 'Allow HTML', 'learndash' ); ?>
								</label>
							</td>
							<td style="padding: 5px; vertical-align: top;">
								<label>
									<input type="checkbox" class="wpProQuiz_checkbox" name="answerData[][sort_string_html]" value="1" <?php $this->checked( $d->isSortStringHtml() ); ?>>
									<?php esc_html_e( 'Allow HTML', 'learndash' ); ?>
								</label>
							</td>
						</tr>
					</tbody>
				</table>

				<input type="button" name="submit" class="button-primary deleteAnswer" value="<?php esc_html_e( 'Delete answer', 'learndash' ); ?>">
				<input type="button" class="button-secondary addMedia" value="<?php esc_html_e( 'Add Media', 'learndash' ); ?>">
				<a href="#" class="button-secondary wpProQuiz_move" style="cursor: move;"><?php esc_html_e( 'Move', 'learndash' ); ?></a>
			</li>
			<?php
		}
	}


	/**
	 * Outputs the sorting choice.
	 *
	 * @param array<WpProQuiz_Model_AnswerTypes> $data Data.
	 *
	 * @return void
	 */
	public function sortingChoice( $data ) {
		foreach ( $data as $d ) {
			?>
			<li style="border-bottom:1px dotted #ccc; padding-bottom: 5px; background-color: whiteSmoke;">
				<table style="width: 100%;border: 1px solid #9E9E9E;border-collapse: collapse;margin-bottom: 20px;">
					<thead>
						<tr>
							<th width="160px" style=" border-right: 1px solid #9E9E9E; padding: 5px; "><?php esc_html_e( 'Options', 'learndash' ); ?></th>
							<th style="padding: 5px;"><?php esc_html_e( 'Answer', 'learndash' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="border-right: 1px solid #9E9E9E; padding: 5px; vertical-align: top;">
								<div>
									<label>
										<input type="checkbox" class="wpProQuiz_checkbox" name="answerData[][html]" value="1" <?php $this->checked( $d->isHtml() ); ?>>
										<?php esc_html_e( 'Allow HTML', 'learndash' ); ?>
									</label>
								</div>
								<div style="padding-top: 5px;" class="wpProQuiz_answerPoints">
									<label>
										<input type="number" step="any" class="small-text wpProQuiz_points" name="answerData[][points]" value="<?php echo esc_attr( $d->getPoints() ); ?>">
										<?php esc_html_e( 'Points', 'learndash' ); ?>
									</label>
								</div>
							</td>
							<td style="padding: 5px; vertical-align: top;">
								<textarea rows="2" cols="100" class="large-text wpProQuiz_text wpProQuiz_sort_answer" name="answerData[][answer]" style="resize:vertical;"><?php echo wp_kses_post( $d->getAnswer() ); ?></textarea>
							</td>
						</tr>
					</tbody>
				</table>

				<input type="button" name="submit" class="button-primary deleteAnswer" value="<?php esc_html_e( 'Delete answer', 'learndash' ); ?>">
				<input type="button" class="button-secondary addMedia" value="<?php esc_html_e( 'Add Media', 'learndash' ); ?>">
				<a href="#" class="button-secondary wpProQuiz_move" style="cursor: move;"><?php esc_html_e( 'Move', 'learndash' ); ?></a>
			</li>
			<?php
		}
	}

	public function freeChoice( $data ) {
		$single = $data[0];
		?>
	<div class="answerList">
		<p class="description">
			<?php echo esc_html_x( 'Correct answers (one per line) (answers will be converted to lower case). If mode "Different points for each answer" is activated, you can assign points to each answer using "|". Example: One|15. The default point value is 1.', 'Question type description for Free Answers', 'learndash' ); ?>
		</p>
		<p style="border-bottom:1px dotted #ccc;">
			<textarea rows="6" cols="100" class="large-text wpProQuiz_text wpProQuiz_free_text" name="answerData[][answer]"><?php echo wp_kses_post( $single->getAnswer() ); ?></textarea>
		</p>
	</div>
		<?php
	}

	public function clozeChoice( $data ) {
		$single = $data[0];
		?>
		<p class="description">
			<?php echo wp_kses_post( __( 'Enclose the searched words with <code>{</code> <code>}</code> e.g. <code>I {play} soccer</code>. Capital and small letters will be ignored.', 'learndash' ) ); ?>
		</p>
		<p class="description">
			<?php echo wp_kses_post( __( 'You can specify multiple options for a search word. Enclose the word with <code>[</code> <code>]</code> e.g. <code>I {[play][love][hate]} soccer</code>. In this case answers "play", "love" OR "hate" are correct.', 'learndash' ) ); ?>
		</p>
		<p class="description" style="margin-top: 10px;">
			<?php echo wp_kses_post( __( 'If mode "Different points for each answer" is activated, you can assign points to each answer using <code>|</code>. The default point value is be 1.', 'learndash' ) ); ?>
		</p>
		<p class="description">
			<?php echo wp_kses_post( '<code>I play {[football][soccer|2][golf|5]}</code>. In this example there are three possible answer, "football" is 1 point, "soccer" is 2 points, and "golf" is 5 point.', 'learndash' ); ?>
		</p>
		<p class="description">
			<?php echo wp_kses_post( 'The total possible points will be the total of the highest points values from each answer set.', 'learndash' ); ?>
		</p>
		<?php
			wp_editor(
				$single->getAnswer(),
				'cloze',
				array(
					'textarea_rows' => 10,
					'textarea_name' => 'answerData[cloze][answer]',
				)
			);
		?>
		<?php
	}

	public function assessmentChoice( $data ) {
		$single = $data[0];
		?>
		<p class="description">
			<?php esc_html_e( 'Here you can create an assessment question.', 'learndash' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'Enclose a assesment with {}. The individual assessments are marked with [].', 'learndash' ); ?>
			<br>
			<?php esc_html_e( 'The number of options in the maximum score.', 'learndash' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Examples:', 'learndash' ); ?>
			<br>
			* <?php esc_html_e( 'less true { [1] [2] [3] [4] [5] } more true', 'learndash' ); ?>
		</p>

		<p>
			* <?php esc_html_e( 'less true { [a] [b] [c] } more true', 'learndash' ); ?>
		</p>

		<p></p>

		<?php
			wp_editor(
				$single->getAnswer(),
				'assessment',
				array(
					'textarea_rows' => 10,
					'textarea_name' => 'answerData[assessment][answer]',
				)
			);
		?>
		<?php
	}


	public function essayChoice( $data ) {
		$data = array_shift( $data );
		if ( is_a( $data, 'WpProQuiz_Model_AnswerTypes' ) ) {
			?>
				<p class="description"><?php esc_html_e( 'How should the user submit their answer?', 'learndash' ); ?></p>
				<select name="answerData[essay][type]" id="essay-type">
					<option value="text" <?php selected( $data->getGradedType(), 'text' ); ?>><?php esc_html_e( 'Text Box', 'learndash' ); ?></option>
					<option value="upload" <?php selected( $data->getGradedType(), 'upload' ); ?>><?php esc_html_e( 'Upload', 'learndash' ); ?></option>
				</select>

				<p class="description" style="margin-top: 10px">
					<?php
					// translators: placeholder: question, course.
					printf( esc_html_x( 'If you set the %1$s to be graded and the student doesn\'t have enough points to proceed, they will be blocked from continuing the %2$s until the %1$s has been graded.', 'placeholder: question, course', 'learndash' ), esc_html( learndash_get_custom_label_lower( 'question' ) ), esc_html( learndash_get_custom_label_lower( 'course' ) ) )
					?>
					<br />
					<?php esc_html_e( 'The student can progress if they have enough points to move on.', 'learndash' ); ?><br />
					<?php
					// translators: placeholder: question, quiz.
					printf( esc_html_x( 'How should the answer to this %1$s be marked and graded upon %2$s submission?', 'placeholder: question, quiz', 'learndash' ), esc_html( learndash_get_custom_label_lower( 'question' ) ), esc_html( learndash_get_custom_label_lower( 'quiz' ) ) );
					?>
					<br />
				</p>
				<select name="answerData[essay][progression]" id="essay-progression">
					<option value=""><?php esc_html_e( '-- Select --', 'learndash' ); ?></option>
					<option value="not-graded-none" <?php selected( $data->getGradingProgression(), 'not-graded-none' ); ?>><?php esc_html_e( 'Not Graded, No Points Awarded', 'learndash' ); ?></option>
					<option value="not-graded-full" <?php selected( $data->getGradingProgression(), 'not-graded-full' ); ?>><?php esc_html_e( 'Not Graded, Full Points Awarded', 'learndash' ); ?></option>
					<option value="graded-full" <?php selected( $data->getGradingProgression(), 'graded-full' ); ?>><?php esc_html_e( 'Graded, Full Points Awarded', 'learndash' ); ?></option>
				</select>
				<input type="hidden"  id="essay" name="answerData[essay][answer]">
			<?php
		}   }

	/**
	 * Outputs the single choice options.
	 *
	 * @param array<WpProQuiz_Model_AnswerTypes> $data Data.
	 *
	 * @return void
	 */
	public function singleChoiceOptions( $data ) {
		?>
	<div class="postbox" id="singleChoiceOptions">
		<h3 class="hndle"><?php esc_html_e( 'Single choice options', 'learndash' ); ?></h3>
		<div class="inside">
			<p class="description">
				<?php echo wp_kses_post( __( 'If "Different points for each answer" is activated, you can activate a special mode.<br> This changes the calculation of the points.', 'learndash' ) ); ?>
			</p>
			<label>
				<input type="checkbox" name="answerPointsDiffModusActivated" value="1" <?php $this->checked( $this->question->isAnswerPointsDiffModusActivated() ); ?>>
				<?php esc_html_e( 'Different points - modus 2 activate', 'learndash' ); ?>
			</label>
			<br><br>
			<p class="description">
				<?php
				echo esc_html(
					sprintf(
						// translators: placeholder: question.
						__(
							'Disables the distinction between correct and incorrect. If enabled, the total points for the %s are the greatest points from all answers.',
							'learndash'
						),
						learndash_get_custom_label_lower( 'question' )
					)
				);
				?>
				<br/>
			</p>
			<label>
				<input type="checkbox" name=disableCorrect value="1" <?php $this->checked( $this->question->isDisableCorrect() ); ?>>
				<?php esc_html_e( 'Disable correct and incorrect', 'learndash' ); ?>
			</label>

			<div style="padding-top: 20px;">
				<a href="#" id="clickPointDia"><?php esc_html_e( 'Explanation of points calculation', 'learndash' ); ?></a>
				<?php $this->answerPointDia(); ?>
			</div>
		</div>
	</div>

		<?php
	}

	/**
	 * Outputs the explanation of points calculation.
	 *
	 * @return void
	 */
	private function answerPointDia() {
		?>
<style>
.pointDia td {
	border: 1px solid #9E9E9E;
	padding: 8px;
}
</style>
	<table style="border-collapse: collapse; display: none; margin-top: 10px;" class="pointDia">
	<tr>
		<th>
			<?php esc_html_e( '"Different points for each answer" enabled', 'learndash' ); ?>
			<br>
			<?php esc_html_e( '"Different points - mode 2" disable', 'learndash' ); ?>
		</th>
		<th>
			<?php esc_html_e( '"Different points for each answer" enabled', 'learndash' ); ?>
			<br>
			<?php esc_html_e( '"Different points - mode 2" enabled', 'learndash' ); ?>
		</th>
	</tr>
	<tr>
		<td>
			<?php
			echo nl2br(
				'Question - Single Choice - 3 Answers - Diff points mode

			A=3 Points [correct]
			B=2 Points [incorrect]
			C=1 Point [incorrect]

			= 3 Points
			'
			);
			?>

		</td>
		<td>
			<?php
			echo nl2br(
				'Question - Single Choice - 3 Answers - Modus 2

			A=3 Points [correct]
			B=2 Points [incorrect]
			C=1 Point [incorrect]

			= 3 Points
			'
			);
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			echo nl2br(
				'~~~ User 1: ~~~

			A=checked
			B=unchecked
			C=unchecked

			Result:
			A=correct and checked (correct) = 3 Points
			B=incorrect and unchecked (correct) = 0 Points
			C=incorrect and unchecked (correct) = 0 Points

			= 3 / 3 Points 100%
			'
			);
			?>

		</td>
		<td>
			<?php
			echo nl2br(
				'~~~ User 1: ~~~

			A=checked
			B=unchecked
			C=unchecked

			Result:
			A=checked = 3 Points
			B=unchecked = 0 Points
			C=unchecked = 0 Points

			= 3 / 3 Points 100%'
			);
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			echo nl2br(
				'~~~ User 2: ~~~

			A=unchecked
			B=checked
			C=unchecked

			Result:
			A=correct and unchecked (incorrect) = 0 Points
			B=incorrect and checked (incorrect) = 0 Points
			C=incorrect and uncecked (correct) = 0 Points

			= 0 / 3 Points 0%
			'
			);
			?>

		</td>
		<td>
			<?php
			echo nl2br(
				'~~~ User 2: ~~~

			A=unchecked
			B=checked
			C=unchecked

			Result:
			A=unchecked = 0 Points
			B=checked = 2 Points
			C=uncecked = 0 Points

			= 2 / 3 Points 66,67%'
			);
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			echo nl2br(
				'~~~ User 3: ~~~

			A=unchecked
			B=unchecked
			C=checked

			Result:
			A=correct and unchecked (incorrect) = 0 Points
			B=incorrect and unchecked (correct) = 0 Points
			C=incorrect and checked (incorrect) = 0 Points

			= 0 / 6 Points 0%
			'
			);
			?>

		</td>
		<td>
			<?php
			echo nl2br(
				'~~~ User 3: ~~~

			A=unchecked
			B=unchecked
			C=checked

			Result:
			A=unchecked = 0 Points
			B=unchecked = 0 Points
			C=checked = 1 Points

			= 1 / 3 Points 33,33%'
			);
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			echo nl2br(
				'~~~ User 4: ~~~

			A=unchecked
			B=unchecked
			C=unchecked

			Result:
			A=correct and unchecked (incorrect) = 0 Points
			B=incorrect and unchecked (correct) = 0 Points
			C=incorrect and unchecked (correct) = 0 Points

			= 0 / 3 Points 50%
			'
			);
			?>

		</td>
		<td>
			<?php
			echo nl2br(
				'~~~ User 4: ~~~

			A=unchecked
			B=unchecked
			C=unchecked

			Result:
			A=unchecked = 0 Points
			B=unchecked = 0 Points
			C=unchecked = 0 Points

			= 0 / 3 Points 0%'
			);
			?>
		</td>
	</tr>
	</table>
		<?php
	}
}
