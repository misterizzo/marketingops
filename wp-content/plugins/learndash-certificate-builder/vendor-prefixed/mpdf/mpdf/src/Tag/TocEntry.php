<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Tag;

use LearnDash\Certificate_Builder\Mpdf\Mpdf;

class TocEntry extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		if (!empty($attr['CONTENT'])) {
			$objattr = [];
			$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES);
			$objattr['type'] = 'toc';
			$objattr['vertical-align'] = 'T';
			if (!empty($attr['LEVEL'])) {
				$objattr['toclevel'] = $attr['LEVEL'];
			} else {
				$objattr['toclevel'] = 0;
			}
			if (!empty($attr['NAME'])) {
				$objattr['toc_id'] = $attr['NAME'];
			} else {
				$objattr['toc_id'] = 0;
			}
			$e = Mpdf::OBJECT_IDENTIFIER . "type=toc,objattr=" . serialize($objattr) . Mpdf::OBJECT_IDENTIFIER;
			if ($this->mpdf->tableLevel) {
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = [$e];
			} // *TABLES*
			else { // *TABLES*
				$this->mpdf->textbuffer[] = [$e];
			} // *TABLES*
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
