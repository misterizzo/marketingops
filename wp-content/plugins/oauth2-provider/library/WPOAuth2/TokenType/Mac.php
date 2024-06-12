<?php

namespace WPOAuth2\TokenType;

use WPOAuth2\RequestInterface;
use WPOAuth2\ResponseInterface;

/**
 * This is not yet supported!
 */
class Mac implements TokenTypeInterface {

	public function getTokenType() {
		return 'mac';
	}

	public function getAccessTokenParameter( RequestInterface $request, ResponseInterface $response ) {
		throw new \LogicException( 'Not supported' );
	}
}
