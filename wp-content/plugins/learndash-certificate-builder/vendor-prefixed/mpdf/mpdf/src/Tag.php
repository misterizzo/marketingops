<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf;

use LearnDash\Certificate_Builder\Mpdf\Strict;
use LearnDash\Certificate_Builder\Mpdf\Color\ColorConverter;
use LearnDash\Certificate_Builder\Mpdf\Image\ImageProcessor;
use LearnDash\Certificate_Builder\Mpdf\Language\LanguageToFontInterface;

class Tag
{

	use Strict;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Cache
	 */
	private $cache;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\CssManager
	 */
	private $cssManager;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Form
	 */
	private $form;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Otl
	 */
	private $otl;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\TableOfContents
	 */
	private $tableOfContents;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\SizeConverter
	 */
	private $sizeConverter;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Color\ColorConverter
	 */
	private $colorConverter;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Image\ImageProcessor
	 */
	private $imageProcessor;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Language\LanguageToFontInterface
	 */
	private $languageToFont;

	/**
	 * @param \LearnDash\Certificate_Builder\Mpdf\Mpdf $mpdf
	 * @param \LearnDash\Certificate_Builder\Mpdf\Cache $cache
	 * @param \LearnDash\Certificate_Builder\Mpdf\CssManager $cssManager
	 * @param \LearnDash\Certificate_Builder\Mpdf\Form $form
	 * @param \LearnDash\Certificate_Builder\Mpdf\Otl $otl
	 * @param \LearnDash\Certificate_Builder\Mpdf\TableOfContents $tableOfContents
	 * @param \LearnDash\Certificate_Builder\Mpdf\SizeConverter $sizeConverter
	 * @param \LearnDash\Certificate_Builder\Mpdf\Color\ColorConverter $colorConverter
	 * @param \LearnDash\Certificate_Builder\Mpdf\Image\ImageProcessor $imageProcessor
	 * @param \LearnDash\Certificate_Builder\Mpdf\Language\LanguageToFontInterface $languageToFont
	 */
	public function __construct(
		Mpdf $mpdf,
		Cache $cache,
		CssManager $cssManager,
		Form $form,
		Otl $otl,
		TableOfContents $tableOfContents,
		SizeConverter $sizeConverter,
		ColorConverter $colorConverter,
		ImageProcessor $imageProcessor,
		LanguageToFontInterface $languageToFont
	) {

		$this->mpdf = $mpdf;
		$this->cache = $cache;
		$this->cssManager = $cssManager;
		$this->form = $form;
		$this->otl = $otl;
		$this->tableOfContents = $tableOfContents;
		$this->sizeConverter = $sizeConverter;
		$this->colorConverter = $colorConverter;
		$this->imageProcessor = $imageProcessor;
		$this->languageToFont = $languageToFont;
	}

	/**
	 * @param string $tag The tag name
	 * @return \LearnDash\Certificate_Builder\Mpdf\Tag\Tag
	 */
	private function getTagInstance($tag)
	{
		$className = self::getTagClassName($tag);
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
	}

	/**
	 * Returns the fully qualified name of the class handling the rendering of the given tag
	 *
	 * @param string $tag The tag name
	 * @return string The fully qualified name
	 */
	public static function getTagClassName($tag)
	{
		static $map = [
			'BARCODE' => 'BarCode',
			'BLOCKQUOTE' => 'BlockQuote',
			'COLUMN_BREAK' => 'ColumnBreak',
			'COLUMNBREAK' => 'ColumnBreak',
			'DOTTAB' => 'DotTab',
			'FIELDSET' => 'FieldSet',
			'FIGCAPTION' => 'FigCaption',
			'FORMFEED' => 'FormFeed',
			'HGROUP' => 'HGroup',
			'INDEXENTRY' => 'IndexEntry',
			'INDEXINSERT' => 'IndexInsert',
			'NEWCOLUMN' => 'NewColumn',
			'NEWPAGE' => 'NewPage',
			'PAGEFOOTER' => 'PageFooter',
			'PAGEHEADER' => 'PageHeader',
			'PAGE_BREAK' => 'PageBreak',
			'PAGEBREAK' => 'PageBreak',
			'SETHTMLPAGEFOOTER' => 'SetHtmlPageFooter',
			'SETHTMLPAGEHEADER' => 'SetHtmlPageHeader',
			'SETPAGEFOOTER' => 'SetPageFooter',
			'SETPAGEHEADER' => 'SetPageHeader',
			'TBODY' => 'TBody',
			'TFOOT' => 'TFoot',
			'THEAD' => 'THead',
			'TEXTAREA' => 'TextArea',
			'TEXTCIRCLE' => 'TextCircle',
			'TOCENTRY' => 'TocEntry',
			'TOCPAGEBREAK' => 'TocPageBreak',
			'VAR' => 'VarTag',
			'WATERMARKIMAGE' => 'WatermarkImage',
			'WATERMARKTEXT' => 'WatermarkText',
		];

		$className = 'LearnDash\Certificate_Builder\Mpdf\Tag\\';
		$className .= isset($map[$tag]) ? $map[$tag] : ucfirst(strtolower($tag));

		return $className;
	}

	public function OpenTag($tag, $attr, &$ahtml, &$ihtml)
	{
		// Correct for tags where HTML5 specifies optional end tags excluding table elements (cf WriteHTML() )
		if ($this->mpdf->allow_html_optional_endtags) {
			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['tag'])) {
				$closed = false;
				// li end tag may be omitted if immediately followed by another li element
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'LI' && $tag == 'LI') {
					$this->CloseTag('LI', $ahtml, $ihtml);
					$closed = true;
				}
				// dt end tag may be omitted if immediately followed by another dt element or a dd element
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'DT' && ($tag == 'DT' || $tag == 'DD')) {
					$this->CloseTag('DT', $ahtml, $ihtml);
					$closed = true;
				}
				// dd end tag may be omitted if immediately followed by another dd element or a dt element
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'DD' && ($tag == 'DT' || $tag == 'DD')) {
					$this->CloseTag('DD', $ahtml, $ihtml);
					$closed = true;
				}
				// p end tag may be omitted if immediately followed by an address, article, aside, blockquote, div, dl,
				// fieldset, form, h1, h2, h3, h4, h5, h6, hgroup, hr, main, nav, ol, p, pre, section, table, ul
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'P'
						&& ($tag == 'P' || $tag == 'DIV' || $tag == 'H1' || $tag == 'H2' || $tag == 'H3'
							|| $tag == 'H4' || $tag == 'H5' || $tag == 'H6' || $tag == 'UL' || $tag == 'OL'
							|| $tag == 'TABLE' || $tag == 'PRE' || $tag == 'FORM' || $tag == 'ADDRESS' || $tag == 'BLOCKQUOTE'
							|| $tag == 'CENTER' || $tag == 'DL' || $tag == 'HR' || $tag == 'ARTICLE' || $tag == 'ASIDE'
							|| $tag == 'FIELDSET' || $tag == 'HGROUP' || $tag == 'MAIN' || $tag == 'NAV' || $tag == 'SECTION')) {
					$this->CloseTag('P', $ahtml, $ihtml);
					$closed = true;
				}
				// option end tag may be omitted if immediately followed by another option element
				// (or if it is immediately followed by an optgroup element)
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'OPTION' && $tag == 'OPTION') {
					$this->CloseTag('OPTION', $ahtml, $ihtml);
					$closed = true;
				}
				// Table elements - see also WriteHTML()
				if (!$closed && ($tag == 'TD' || $tag == 'TH') && $this->mpdf->lastoptionaltag == 'TD') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
				if (!$closed && ($tag == 'TD' || $tag == 'TH') && $this->mpdf->lastoptionaltag == 'TH') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
				if (!$closed && $tag == 'TR' && $this->mpdf->lastoptionaltag == 'TR') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
				if (!$closed && $tag == 'TR' && $this->mpdf->lastoptionaltag == 'TD') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$this->CloseTag('TR', $ahtml, $ihtml);
					$this->CloseTag('THEAD', $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
				if (!$closed && $tag == 'TR' && $this->mpdf->lastoptionaltag == 'TH') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$this->CloseTag('TR', $ahtml, $ihtml);
					$this->CloseTag('THEAD', $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
			}
		}

		if ($object = $this->getTagInstance($tag)) {
			return $object->open($attr, $ahtml, $ihtml);
		}
	}

	public function CloseTag($tag, &$ahtml, &$ihtml)
	{
		if ($object = $this->getTagInstance($tag)) {
			return $object->close($ahtml, $ihtml);
		}
	}
}
