<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Tag;

use LearnDash\Certificate_Builder\Mpdf\Utils\UtfString;

class Option extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->lastoptionaltag = '';
		$this->mpdf->selectoption['ACTIVE'] = true;
		$this->mpdf->selectoption['currentSEL'] = false;
		if (empty($this->mpdf->selectoption)) {
			$this->mpdf->selectoption['MAXWIDTH'] = '';
			$this->mpdf->selectoption['SELECTED'] = '';
		}
		if (isset($attr['SELECTED'])) {
			$this->mpdf->selectoption['SELECTED'] = '';
			$this->mpdf->selectoption['currentSEL'] = true;
		}
		if (isset($attr['VALUE'])) {
			$attr['VALUE'] = UtfString::strcode2utf($attr['VALUE']);
			$attr['VALUE'] = $this->mpdf->lesser_entity_decode($attr['VALUE']);
			if ($this->mpdf->onlyCoreFonts) {
				$attr['VALUE'] = mb_convert_encoding($attr['VALUE'], $this->mpdf->mb_enc, 'UTF-8');
			}
		}

		$this->mpdf->selectoption['currentVAL'] = isset($attr['VALUE']) ? $attr['VALUE'] : $ahtml[$ihtml + 1];
	}

	public function close(&$ahtml, &$ihtml)
	{
		$this->mpdf->selectoption['ACTIVE'] = false;
		$this->mpdf->lastoptionaltag = '';
	}
}
