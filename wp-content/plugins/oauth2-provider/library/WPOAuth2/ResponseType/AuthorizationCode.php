<?php

namespace WPOAuth2\ResponseType;

use WPOAuth2\Storage\AuthorizationCodeInterface as AuthorizationCodeStorageInterface;

/**
 *
 * @author Brent Shaffer <bshafs at gmail dot com>
 */
class AuthorizationCode implements AuthorizationCodeInterface {

	protected $storage;
	protected $config;

	public function __construct( AuthorizationCodeStorageInterface $storage, array $config = array() ) {
		$this->storage = $storage;
		$this->config  = array_merge(
			array(
				'enforce_redirect'   => false,
				'auth_code_lifetime' => apply_filters( 'wo_auth_code_lifetime', 30 ),
			),
			$config
		);
	}

	public function getAuthorizeResponse( $params, $user_id = null ) {

		// build the URL to redirect to
		$result = array( 'query' => array() );

		$params += array(
			'scope'                 => null,
			'id_token'              => null,
			'state'                 => null,
			'code_challenge'        => null,
			'code_challenge_method' => null,
		);

		$result['query']['code'] = $this->createAuthorizationCode( $params['client_id'], $user_id, $params['redirect_uri'], $params['scope'], $params['id_token'], $params['code_challenge'], $params['code_challenge_method'] );

		if ( isset( $params['state'] ) ) {
			$result['query']['state'] = $params['state'];
		}

		return array( $params['redirect_uri'], $result );
	}

	/**
	 * Handle the creation of the authorization code.
	 *
	 * @param $client_id
	 * Client identifier related to the authorization code
	 * @param $user_id
	 * User ID associated with the authorization code
	 * @param $redirect_uri
	 * An absolute URI to which the authorization server will redirect the
	 * user-agent to when the end-user authorization step is completed.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @see     http://tools.ietf.org/html/rfc6749#section-4
	 * @ingroup oauth2_section_4
	 */
	public function createAuthorizationCode( $client_id, $user_id, $redirect_uri, $scope = null, $id_token = null, $code_challenge = null, $code_challenge_method = null ) {
		$code = $this->generateAuthorizationCode();
		$this->storage->setAuthorizationCode( $code, $client_id, $user_id, $redirect_uri, current_time( 'timestamp' ) + $this->config['auth_code_lifetime'], $scope, $id_token, $code_challenge, $code_challenge_method );

		return $code;
	}

	/**
	 * @return
	 * TRUE if the grant type requires a redirect_uri, FALSE if not
	 */
	public function enforceRedirect() {

		return $this->config['enforce_redirect'];
	}

	/**
	 * Generates an unique auth code.
	 *
	 * Implementing classes may want to override this function to implement
	 * other auth code generation schemes.
	 *
	 * @return
	 * An unique auth code.
	 *
	 * @ingroup oauth2_section_4
	 *
	 * @since 3.1.94 The function has been converted to use wp_generate_password
	 */
	protected function generateAuthorizationCode() {

		$tokenLen = 40;

		return strtolower( wp_generate_password( $tokenLen, false, $extra_special_chars = false ) );
	}
}
