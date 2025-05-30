<?php
/**
 * ProQuiz question model mapper.
 *
 * @package LearnDash\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use LearnDash\Core\Utilities\Cast;

/**
 * ProQuiz question model mapper.
 *
 * @since 2.6.0
 */
class WpProQuiz_Model_QuestionMapper extends WpProQuiz_Model_Mapper {
	private $_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->_table = $this->_tableQuestion;
	}

	public function delete( $id ) {
		$this->_wpdb->delete( $this->_table, array( 'id' => $id ), '%d' );
	}

	public function deleteByQuizId( $id ) {
		$this->_wpdb->delete( $this->_table, array( 'quiz_id' => $id ), '%d' );
	}

	public function getSort( $questionId ) {
		return $this->_wpdb->get_var( $this->_wpdb->prepare( "SELECT sort FROM {$this->_tableQuestion} WHERE id = %d", $questionId ) );
	}

	public function updateSort( $id, $sort ) {
		$this->_wpdb->update(
			$this->_table,
			array(
				'sort' => $sort,
			),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);

		if ( true === learndash_is_data_upgrade_quiz_questions_updated() ) {
			$question_post_id = learndash_get_question_post_by_pro_id( $id );
			if ( ! empty( $question_post_id ) ) {
				$update_post = array(
					'ID'         => $question_post_id,
					'menu_order' => absint( $sort ),
				);
				wp_update_post( $update_post );
				learndash_set_question_quizzes_dirty( $question_post_id );
			}
		}
	}

	public function setOnlineOff( $questionId ) {
		return $this->_wpdb->update( $this->_tableQuestion, array( 'online' => 0 ), array( 'id' => $questionId ), null, array( '%d' ) );
	}

	/**
	 * Set Question Previous ID.
	 *
	 * When an existing question is changed and when it has existing statitics records
	 * the existing question will be marked offline and a new question inserted. This
	 * function will keep track of the previous question ID.
	 *
	 * @since 3.5.0
	 *
	 * @param int $questionId         Current Question ID.
	 * @param int $previousQuestionId Previous Question ID.
	 */
	public function setPreviousId( $questionId = 0, $previousQuestionId = 0 ) {
		$questionId         = absint( $questionId );
		$previousQuestionId = absint( $previousQuestionId );

		if ( ( ! empty( $questionId ) ) && ( ! empty( $previousQuestionId ) ) ) {
			return $this->_wpdb->update(
				$this->_tableQuestion,
				array(
					'previous_id' => $previousQuestionId,
				),
				array(
					'id' => $questionId,
				),
				null,
				array( '%d' ),
				array( '%d' )
			);
		}
	}

	public function getQuizId( $questionId ) {
		return $this->_wpdb->get_var( $this->_wpdb->prepare( "SELECT quiz_id FROM {$this->_tableQuestion} WHERE id = %d", $questionId ) );
	}

	public function getMaxSort( $quizId ) {
		return $this->_wpdb->get_var(
			$this->_wpdb->prepare(
				"SELECT MAX(sort) AS max_sort FROM {$this->_tableQuestion} WHERE quiz_id = %d AND online = 1",
				$quizId
			)
		);
	}

	/**
	 * Saves the question.
	 *
	 * @param WpProQuiz_Model_Question $question Question model.
	 * @param bool                     $auto     Not sure what this is for. Default false.
	 *
	 * @return WpProQuiz_Model_Question
	 */
	public function save( WpProQuiz_Model_Question $question, $auto = false ) {
		$sort                 = null;
		$question_previous_id = null;

		if ( $auto && $question->getId() ) {
			$statisticMapper = new WpProQuiz_Model_StatisticMapper();

			if ( $statisticMapper->isStatisticByQuestionId( $question->getId() ) ) {
				$question_previous_id = $question->getId();
				$this->setOnlineOff( $question->getId() );
				$question->setQuizId( $this->getQuizId( $question->getId() ) );
				$question->setId( 0 );
				$sort = $question->getSort();
			}
		}

		/**
		 * Convert emoji to HTML entities to allow saving in DB.
		 *
		 * @since 2.6.0.
		 */
		$question_title = $question->getTitle();
		$question_title = wp_encode_emoji( $question_title );

		$question_question = $question->getQuestion();
		$question_question = wp_encode_emoji( $question_question );

		if ( $question->getId() != 0 ) {
			$this->_wpdb->update(
				$this->_table,
				array(
					'quiz_id'                            => $question->getQuizId(),
					'title'                              => $question_title,
					'points'                             => $question->getPoints(),
					'question'                           => $question_question,
					'correct_msg'                        => $question->getCorrectMsg(),
					'incorrect_msg'                      => $question->getIncorrectMsg(),
					'correct_same_text'                  => (int) $question->isCorrectSameText(),
					'tip_enabled'                        => (int) $question->isTipEnabled(),
					'tip_msg'                            => $question->getTipMsg(),
					'answer_type'                        => $question->getAnswerType(),
					'show_points_in_box'                 => (int) $question->isShowPointsInBox(),
					'answer_points_activated'            => (int) $question->isAnswerPointsActivated(),
					'answer_data'                        => $question->getAnswerData( true ),
					'category_id'                        => $question->getCategoryId(),
					'answer_points_diff_modus_activated' => (int) $question->isAnswerPointsDiffModusActivated(),
					'disable_correct'                    => (int) $question->isDisableCorrect(),
					'matrix_sort_answer_criteria_width'  => $question->getMatrixSortAnswerCriteriaWidth(),
				),
				array( 'id' => $question->getId() ),
				array( '%s', '%s', '%.2f', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d', '%d' ),
				array( '%d' )
			);
		} else {
			$this->_wpdb->insert(
				$this->_table,
				array(
					'quiz_id'                            => $question->getQuizId(),
					'online'                             => 1,
					'sort'                               => $sort !== null ? $sort : ( $this->getMaxSort( $question->getQuizId() ) + 1 ),
					'title'                              => $question_title,
					'points'                             => $question->getPoints(),
					'question'                           => $question_question,
					'correct_msg'                        => $question->getCorrectMsg(),
					'incorrect_msg'                      => $question->getIncorrectMsg(),
					'correct_same_text'                  => (int) $question->isCorrectSameText(),
					'tip_enabled'                        => (int) $question->isTipEnabled(),
					'tip_msg'                            => $question->getTipMsg(),
					'answer_type'                        => $question->getAnswerType(),
					'show_points_in_box'                 => (int) $question->isShowPointsInBox(),
					'answer_points_activated'            => (int) $question->isAnswerPointsActivated(),
					'answer_data'                        => $question->getAnswerData( true ),
					'category_id'                        => $question->getCategoryId(),
					'answer_points_diff_modus_activated' => (int) $question->isAnswerPointsDiffModusActivated(),
					'disable_correct'                    => (int) $question->isDisableCorrect(),
					'matrix_sort_answer_criteria_width'  => $question->getMatrixSortAnswerCriteriaWidth(),
				),
				array( '%d', '%d', '%d', '%s', '%.2f', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d', '%d' )
			);

			$question->setId( $this->_wpdb->insert_id );
		}

		if ( ( ! empty( $question_previous_id ) ) && ( absint( $question_previous_id ) !== absint( $question->getId() ) ) ) {
			$this->setPreviousId( $question->getId(), $question_previous_id );
		}

		return $question;
	}

	public function fetch( $id ) {

		$row = $this->_wpdb->get_row(
			$this->_wpdb->prepare(
				'SELECT
					*
				FROM
					' . $this->_table . '
				WHERE
					id = %d AND online = 1',
				$id
			),
			ARRAY_A
		);

		$model = new WpProQuiz_Model_Question( $row );
		return $model;
	}

	/**
	 * Fetches questions by IDs.
	 *
	 * @since 2.6.0
	 *
	 * @param mixed $id      Array of IDs or single ID.
	 * @param mixed $online  Online status. Default 1.
	 *
	 * @return null|WpProQuiz_Model_Question|array<WpProQuiz_Model_Question>
	 */
	public function fetchById( $id, $online = 1 ) {
		$ids = array_map(
			function ( $value ) {
				return Cast::to_int( $value );
			},
			(array) $id
		);

		$a = array();

		if ( empty( $ids ) ) {
			return null;
		}

		$sql_str = 'SELECT * FROM ' . $this->_table . ' WHERE id IN(' . implode( ', ', $ids ) . ') ';

		if ( ( $online === 1 ) || ( $online === 1 ) ) {
			$sql_str .= ' AND online = ' . $online;
		}

		$results = $this->_wpdb->get_results(
			$sql_str,
			ARRAY_A
		);

		if ( ! $results ) {
			return null;
		}

		foreach ( $results as $row ) {
			$a[] = new WpProQuiz_Model_Question( $row );

		}

		return is_array( $id ) ? $a : ( isset( $a[0] ) ? $a[0] : null );
	}

	/**
	 * Fetches questions by quiz.
	 *
	 * @since 2.6.0
	 *
	 * @param int|WpProQuiz_Model_Quiz $quiz_id     Quiz ID or model.
	 * @param bool                     $rand        Random order. Default false.
	 * @param int                      $max         Maximum number of questions to fetch. Default all (zero).
	 * @param int                      $offset      Data offset. Default zero.
	 * @param bool                     $only_online Only online questions. Default true.
	 *
	 * @return array<WpProQuiz_Model_Question>
	 */
	public function fetchAll( $quiz_id = 0, $rand = false, $max = 0, $offset = 0, $only_online = true ) {
		$quiz_post_id = 0;

		if ( $quiz_id instanceof WpProQuiz_Model_Quiz ) {
			$quiz    = $quiz_id;
			$quiz_id = $quiz->getId();
			if ( empty( $quiz_post_id ) ) {
				$quiz_post_id = $quiz->getPostId();
			}
		} else {
			$quiz_post_id = learndash_get_quiz_id_by_pro_quiz_id( $quiz_id );

			if ( empty( $quiz_post_id ) ) {
				if ( ( isset( $_GET['post'] ) ) && ( ! empty( $_GET['post'] ) ) ) {
					$quiz_post_id = learndash_get_quiz_id( absint( $_GET['post'] ) );
				}
			}
		}

		if ( ( ! empty( $quiz_post_id ) ) && ( LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Quizzes_Builder', 'enabled' ) === 'yes' ) && ( true === learndash_is_data_upgrade_quiz_questions_updated() ) ) {
			$ld_quiz_questions_object = LDLMS_Factory_Post::quiz_questions( intval( $quiz_post_id ) );
			if ( $ld_quiz_questions_object ) {
				$pro_questions = $ld_quiz_questions_object->get_questions( 'pro_objects' );

				/**
				 * Filters pro quiz questions list.
				 *
				 * @since 2.6.0
				 *
				 * Used in `fetchAll` method of `WpProQuiz_Model_QuestionMapper` class to fetch all pro quiz questions.
				 *
				 * @param array   $pro_questions An array of pro quiz question IDs.
				 * @param int     $quiz_id       ID of the quiz.
				 * @param boolean $random        Whether to fetch questions in random order.
				 * @param int     $max           The maximum number of questions to be fetched.
				 */
				$pro_questions = apply_filters( 'learndash_fetch_quiz_questions', $pro_questions, $quiz_id, $rand, $max );
				if ( ! empty( $pro_questions ) ) {
					if ( $rand ) {
						shuffle( $pro_questions );

						$max = absint( $max );
						if ( $max > 0 ) {
							$pro_questions = array_slice( $pro_questions, 0, $max, true );
						}
					}
				}

				if ( ! empty( $pro_questions ) ) {
					$category_mapper = new WpProQuiz_Model_CategoryMapper();

					foreach ( $pro_questions as $pro_question ) {
						$q_catId = $pro_question->getCategoryId(); // cspell:disable-line.
						$q_catId = absint( $q_catId ); // cspell:disable-line.
						if ( ! empty( $q_catId ) ) { // cspell:disable-line.
							$q_cat = $category_mapper->fetchById( $q_catId ); // cspell:disable-line.
							if ( ( $q_cat ) && ( is_a( $q_cat, 'WpProQuiz_Model_Category' ) ) ) {
								$_catName = $q_cat->getCategoryName();
								if ( ! empty( $_catName ) ) {
									$pro_question->setCategoryName( $_catName );
								}
							}
						}
					}
				}
				return $pro_questions;
			}
		} else {
			if ( $rand ) {
				$orderBy = 'ORDER BY RAND()';
			} else {
				$orderBy = 'ORDER BY sort ASC';
			}

			$limit = '';

			if ( $max > 0 ) {
				$limit = 'LIMIT ' . ( (int) $offset ) . ', ' . ( (int) $max );
			}

			if ( $only_online ) {
				$where = ' quiz_id = %d AND q.online = 1 ';
			} else {
				$where = ' quiz_id = %d ';
			}

			$a       = array();
			$results = $this->_wpdb->get_results(
				$this->_wpdb->prepare(
					'SELECT
									q.*,
									c.category_name
								FROM
									' . $this->_table . ' AS q
									LEFT JOIN ' . $this->_tableCategory . ' AS c
										ON c.category_id = q.category_id
								WHERE
									' . $where . '
								' . $orderBy . '
								' . $limit,
					$quiz_id
				),
				ARRAY_A
			);

			if ( ! $results ) {
				return $a;
			}

			foreach ( $results as $row ) {
				$model = new WpProQuiz_Model_Question( $row );

				$a[] = $model;
			}
		}

		return $a;
	}

	public function fetchAllList( $quizId, $list ) {
		$quiz_post_id = 0;
		if ( is_a( $quizId, 'WpProQuiz_Model_Quiz' ) ) {
			$quiz         = $quizId;
			$quizId       = $quiz->getId();
			$quiz_post_id = $quiz->getPostId();
		}

		if ( ( ! empty( $quiz_post_id ) ) && ( LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Quizzes_Builder', 'enabled' ) === 'yes' ) && ( true === learndash_is_data_upgrade_quiz_questions_updated() ) ) {
			$ld_quiz_questions_object = LDLMS_Factory_Post::quiz_questions( intval( $quiz_post_id ) );
			if ( $ld_quiz_questions_object ) {
				$questions = $ld_quiz_questions_object->get_questions();
				if ( ! empty( $questions ) ) {
					$sql_str = 'SELECT ' . implode( ', ', (array) $list ) . ' FROM ' . $this->_tableQuestion . ' WHERE id IN (' . implode( ',', $questions ) . ') AND online = 1';
					$results = $this->_wpdb->get_results( $sql_str, ARRAY_A );
					return $results;
				}
			}
		} else {
			$results = $this->_wpdb->get_results(
				$this->_wpdb->prepare(
					'SELECT
									' . implode( ', ', (array) $list ) . '
								FROM
									' . $this->_tableQuestion . '
								WHERE
									quiz_id = %d AND online = 1',
					$quizId
				),
				ARRAY_A
			);

			return $results;
		}
	}

	/**
	 * Fetch questions based on statistics ID.
	 *
	 * @param int $stat_id     Statistics reference ID.
	 * @param int $question_id Question ID.
	 * @param int $limit       Limit for the records to fetch.
	 * @param int $offset      Offset for the records to fetch.
	 *
	 * @throws Exception If Id is empty, throws Exception.
	 *
	 * @return array Questions list.
	 */
	public function fetchByStatId( $stat_id, $question_id, $limit, $offset ) {
		$questions = array();

		$stat_id     = absint( $stat_id );
		$question_id = absint( $question_id );

		if ( ! $stat_id ) {
			throw new Exception(
				sprintf(
				// translators: questions
					esc_html_x( 'Statistics ID cannot be empty in order to fetch %s for statistics.', 'placeholder: questions', 'learndash' ),
					learndash_get_custom_label_lower( 'questions' )
				)
			);
		}

		if ( ! empty( $question_id ) ) {
			$sql     = "SELECT qn.* from $this->_tableQuestion as qn INNER JOIN $this->_tableStatistic as stat ON qn.id = stat.question_id
				WHERE stat.statistic_ref_id = %1d AND stat.question_id = %2d LIMIT %3d, %4d";
			$results = $this->_wpdb->get_results( $this->_wpdb->prepare( $sql, $stat_id, $question_id, $offset, $limit ), ARRAY_A );
		} else {
			$sql         = "SELECT qn.* from $this->_tableQuestion as qn INNER JOIN $this->_tableStatistic as stat ON qn.id = stat.question_id
				WHERE stat.statistic_ref_id=%1d LIMIT %2d, %3d";
				$results = $this->_wpdb->get_results( $this->_wpdb->prepare( $sql, $stat_id, $offset, $limit ), ARRAY_A );
		}

		if ( $results ) {
			foreach ( $results as $result ) {
				$questions[] = new WpProQuiz_Model_Question( $result );
			}
		}

		return $questions;
	}

	/**
	 * Fetch questions count based on statistics ID.
	 *
	 * @param int $stat_id Statistics reference ID.
	 * @param int $question_id Question ID.
	 *
	 * @throws Exception If Id is empty, throws Exception.
	 *
	 * @return int Questions count.
	 */
	public function fetchByStatIdCount( $stat_id = 0, $question_id = 0 ) {

		if ( ! $stat_id ) {
			throw new Exception(
				sprintf(
				// translators: questions
					esc_html_x( 'Statistics ID cannot be empty in order to fetch %s for statistics.', 'placeholder: questions', 'learndash' ),
					learndash_get_custom_label_lower( 'questions' )
				)
			);
		}

		if ( ! empty( $question_id ) ) {
			$sql     = "SELECT count(*) from $this->_tableQuestion as qn INNER JOIN $this->_tableStatistic as stat ON qn.id = stat.question_id
				WHERE stat.statistic_ref_id=%1d and stat.question_id=%2d";
			$results = $this->_wpdb->get_var( $this->_wpdb->prepare( $sql, $stat_id, $question_id ) );
		} else {
			$sql     = "SELECT count(*) from $this->_tableQuestion as qn INNER JOIN $this->_tableStatistic as stat ON qn.id = stat.question_id
				WHERE stat.statistic_ref_id=%1d";
			$results = $this->_wpdb->get_var( $this->_wpdb->prepare( $sql, $stat_id ) );
		}

		return $results;
	}

	public function count( $quizId ) {
		return $this->_wpdb->get_var( $this->_wpdb->prepare( "SELECT COUNT(*) FROM {$this->_table} WHERE quiz_id = %d AND online = 1", $quizId ) );
	}

	public function exists( $id ) {
		return $this->_wpdb->get_var( $this->_wpdb->prepare( "SELECT COUNT(*) FROM {$this->_table} WHERE id = %d AND online = 1", $id ) );
	}

	public function existsAndWritable( $id ) {
		return $this->_wpdb->get_var( $this->_wpdb->prepare( "SELECT COUNT(*) FROM {$this->_table} WHERE id = %d AND online = 1", $id ) );
	}

	/**
	 * Fetches points grouped by category.
	 *
	 * @param int $quizId Quiz ID.
	 *
	 * @return array<int, float>
	 */
	public function fetchCategoryPoints( $quizId ) {
		$results = $this->_wpdb->get_results(
			$this->_wpdb->prepare(
				'SELECT SUM( points ) AS sum_points , category_id
						FROM ' . $this->_tableQuestion . '
						WHERE quiz_id = %d AND online = 1
						GROUP BY category_id',
				$quizId
			),
			ARRAY_A
		);

		$a = array();

		if ( ! $results ) {
			return $a;
		}

		foreach ( $results as $result ) {
			$a[ Cast::to_int( $result['category_id'] ) ] = learndash_format_course_points( $result['sum_points'] );
		}

		return $a;
	}
}
