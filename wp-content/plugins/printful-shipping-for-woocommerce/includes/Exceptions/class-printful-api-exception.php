<?php

/**
 * Class PrintfulException Printful exception returned from the API
 */
class PrintfulApiException extends PrintfulException {
	public function isNotAuthorizedError() {
		return $this->getCode() === 401;
	}
}
