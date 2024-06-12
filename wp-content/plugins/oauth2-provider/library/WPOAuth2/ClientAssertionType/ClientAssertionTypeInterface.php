<?php

namespace WPOAuth2\ClientAssertionType;

use WPOAuth2\RequestInterface;
use WPOAuth2\ResponseInterface;

/**
 * Interface for all OAuth2 Client Assertion Types
 */
interface ClientAssertionTypeInterface {

	public function validateRequest( RequestInterface $request, ResponseInterface $response);
	public function getClientId();
}
