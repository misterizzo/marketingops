<?php
/**
 * @license MIT
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\PsrLogAwareTrait;

use LearnDash\Certificate_Builder\Psr\Log\LoggerInterface;

trait PsrLogAwareTrait 
{

	/**
	 * @var \LearnDash\Certificate_Builder\Psr\Log\LoggerInterface
	 */
	protected $logger;

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
	
}
