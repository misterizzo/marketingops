<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Tag;

class Ttz extends SubstituteTag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->ttz = true;
		$this->mpdf->InlineProperties['TTZ'] = $this->mpdf->saveInlineProperties();
		$this->mpdf->setCSS(['FONT-FAMILY' => 'czapfdingbats', 'FONT-WEIGHT' => 'normal', 'FONT-STYLE' => 'normal'], 'INLINE');
	}

}
