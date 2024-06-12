<?php

namespace WPOAuth2\GrantType;

use WPOAuth2\ResponseType\AccessTokenInterface;
use WPOAuth2\RequestInterface;
use WPOAuth2\ResponseInterface;

/**
 * Interface for all OAuth2 Grant Types
 */
interface GrantTypeInterface {

	public function getQuerystringIdentifier();

	public function validateRequest( RequestInterface $request, ResponseInterface $response );

	public function getClientId();

	public function getUserId();

	public function getScope();

	public function createAccessToken( AccessTokenInterface $accessToken, $client_id, $user_id, $scope );
}
