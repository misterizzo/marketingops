<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Tag;

class Columns extends Tag
{
	/**
	 * @param string $tag
	 * @return \LearnDash\Certificate_Builder\Mpdf\Tag\Tag
	 */
	private function getTagInstance($tag)
	{
		$className = \LearnDash\Certificate_Builder\Mpdf\Tag::getTagClassName($tag);
		if (class_exists($className)) {
			return new $className(
				$this->mpdf,
				$this->cache,
				$this->cssManager,
				$this->form,
				$this->otl,
				$this->tableOfContents,
				$this->sizeConverter,
				$this->colorConverter,
				$this->imageProcessor,
				$this->languageToFont
			);
		}

		return null;
	}

	public function open($attr, &$ahtml, &$ihtml)
	{
		if (isset($attr['COLUMN-COUNT']) && ($attr['COLUMN-COUNT'] || $attr['COLUMN-COUNT'] === '0')) {
			// Close any open block tags
			for ($b = $this->mpdf->blklvl; $b > 0; $b--) {
				if ($t = $this->getTagInstance($this->mpdf->blk[$b]['tag'])) {
					$t->close($ahtml, $ihtml);
				}
			}
			if (!empty($this->mpdf->textbuffer)) { //Output previously buffered content
				$this->mpdf->printbuffer($this->mpdf->textbuffer);
				$this->mpdf->textbuffer = [];
			}

			if (!empty($attr['VALIGN'])) {
				if ($attr['VALIGN'] === 'J') {
					$valign = 'J';
				} else {
					$valign = $this->getAlign($attr['VALIGN']);
				}
			} else {
				$valign = '';
			}
			if (!empty($attr['COLUMN-GAP'])) {
				$this->mpdf->SetColumns($attr['COLUMN-COUNT'], $valign, $attr['COLUMN-GAP']);
			} else {
				$this->mpdf->SetColumns($attr['COLUMN-COUNT'], $valign);
			}
		}
		$this->mpdf->ignorefollowingspaces = true;
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
