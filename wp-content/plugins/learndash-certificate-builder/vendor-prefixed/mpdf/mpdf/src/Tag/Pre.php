<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Tag;

class Pre extends BlockTag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->ispre = true; // ADDED - Prevents left trim of textbuffer in printbuffer()
		parent::open($attr, $ahtml, $ihtml); // TODO: Change the autogenerated stub
	}
}
