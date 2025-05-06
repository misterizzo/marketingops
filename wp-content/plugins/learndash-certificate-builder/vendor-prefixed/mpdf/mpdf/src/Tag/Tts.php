<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Tag;

class Tts extends SubstituteTag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->tts = true;
		$this->mpdf->InlineProperties['TTS'] = $this->mpdf->saveInlineProperties();
		$this->mpdf->setCSS(['FONT-FAMILY' => 'csymbol', 'FONT-WEIGHT' => 'normal', 'FONT-STYLE' => 'normal'], 'INLINE');
	}

}
