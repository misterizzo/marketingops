<?php
/**
 * Virtual Instructor chatbox init AJAX request handler class file.
 *
 * This file handles AJAX request for virtual instructor chatbox init request on frontend virtual instructor chatbox.
 *
 * @since 4.13.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Modules\AI\Virtual_Instructor\AJAX;

use LearnDash\Core\Models\Virtual_Instructor;
use LearnDash\Core\Modules\AI\Virtual_Instructor\Chat_Session;
use LearnDash\Core\Modules\AI\Virtual_Instructor\DTO;
use LearnDash\Core\Modules\AJAX\Request_Handler;
use LearnDash\Core\Template\Template;
use WP_Post;

/**
 * Virtual Instructor chatbox init AJAX request handler class.
 *
 * @since 4.13.0
 */
class Chat_Init extends Request_Handler {
	/**
	 * AJAX action.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	public static $action = 'learndash_virtual_instructor_chatbox_init';

	/**
	 * The request DTO object.
	 *
	 * @since 4.13.0
	 *
	 * @var DTO\Chat_Init_Request
	 */
	public $request;

	/**
	 * The results.
	 *
	 * @since 4.13.0
	 *
	 * @var array{html: string}
	 */
	protected $results;

	/**
	 * The response DTO object.
	 *
	 * @since 4.13.0
	 *
	 * @var DTO\Chat_Response
	 */
	protected $response;

	/**
	 * Checks user capability.
	 *
	 * Required to bypass the parent class' check_user_capability() method.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	protected function check_user_capability(): void {
		// Empty method to bypass the parent class' check_user_capability() method. The parent method always check for LEARNDASH_ADMIN_CAPABILITY_CHECK capability.
	}

	/**
	 * Sets up the request.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	protected function set_up_request(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is done in the parent class' verify_nonce() method before this method is called.
		$model_id = absint( $_REQUEST['id'] ?? 0 );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is done in the parent class' verify_nonce() method later.
		$course_id = absint( $_REQUEST['course_id'] ?? 0 );
		$user_id   = get_current_user_id();

		if ( $model_id === 0 ) {
			wp_send_json_error( [ 'message' => 'Invalid model ID.' ], 400 );
		}

		$post = get_post( $model_id );

		if ( ! $post instanceof WP_Post ) {
			wp_send_json_error( [ 'message' => 'Invalid model ID.' ], 400 );
		}

		if (
			$post->post_status !== 'publish'
			|| post_password_required( $post )
		) {
			wp_send_json_error( [ 'message' => 'Invalid model post status or visibility.' ], 400 );
		}

		if ( ! sfwd_lms_has_access( $course_id, $user_id ) ) {
			wp_send_json_error( [ 'message' => 'You do not have access to this course.' ], 401 );
		}

		$args = [
			'model_id'  => $model_id,
			'user_id'   => $user_id,
			'course_id' => $course_id,
		];

		$this->request = DTO\Chat_Init_Request::create( $args );
	}

	/**
	 * Processes the request.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	protected function process(): void {
		/**
		 * Required session args.
		 *
		 * @var array{model_id: int, user_id: int, course_id: int} $session_args Required session args.
		 */
		$session_args = $this->request->to_array();
		$session      = Chat_Session::get_instance( $session_args );

		/**
		 * The model post object.
		 *
		 * We don't need to check if the post is an instance of WP_Post because we already checked it in the
		 * set_up_request() method.
		 *
		 * @var WP_Post $post The model post object.
		 */
		$post = get_post( $this->request->model_id );

		$this->results = [
			'html' => Template::get_template(
				'modules/ai/virtual-instructor/chatbox',
				[
					'messages'   => $session->get_messages(),
					'model'      => Virtual_Instructor::create_from_post( $post ),
					'max_length' => Chat_Session::get_max_message_length(),
				]
			),
		];
	}

	/**
	 * Prepares the response.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	protected function prepare_response(): void {
		$this->response = DTO\Chat_Response::create( $this->results );
	}
}
