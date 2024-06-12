<?php

namespace WPOAuth2\TokenType;

use WPOAuth2\RequestInterface;
use WPOAuth2\ResponseInterface;

interface TokenTypeInterface {

	/**
	 * Token type identification string
	 *
	 * ex: "bearer" or "mac"
	 */
	public function getTokenType();

	/**
	 * Retrieves the token string from the request object
	 */
	public function getAccessTokenParameter( RequestInterface $request, ResponseInterface $response );
}
