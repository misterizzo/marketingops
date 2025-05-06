<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Tag;

use LearnDash\Certificate_Builder\Mpdf\Strict;

use LearnDash\Certificate_Builder\Mpdf\Cache;
use LearnDash\Certificate_Builder\Mpdf\Color\ColorConverter;
use LearnDash\Certificate_Builder\Mpdf\CssManager;
use LearnDash\Certificate_Builder\Mpdf\Form;
use LearnDash\Certificate_Builder\Mpdf\Image\ImageProcessor;
use LearnDash\Certificate_Builder\Mpdf\Language\LanguageToFontInterface;
use LearnDash\Certificate_Builder\Mpdf\Mpdf;
use LearnDash\Certificate_Builder\Mpdf\Otl;
use LearnDash\Certificate_Builder\Mpdf\SizeConverter;
use LearnDash\Certificate_Builder\Mpdf\TableOfContents;

abstract class Tag
{

	use Strict;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Mpdf
	 */
	protected $mpdf;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Cache
	 */
	protected $cache;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\CssManager
	 */
	protected $cssManager;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Form
	 */
	protected $form;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Otl
	 */
	protected $otl;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\TableOfContents
	 */
	protected $tableOfContents;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\SizeConverter
	 */
	protected $sizeConverter;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Color\ColorConverter
	 */
	protected $colorConverter;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Image\ImageProcessor
	 */
	protected $imageProcessor;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Language\LanguageToFontInterface
	 */
	protected $languageToFont;

	const ALIGN = [
		'left' => 'L',
		'center' => 'C',
		'right' => 'R',
		'top' => 'T',
		'text-top' => 'TT',
		'middle' => 'M',
		'baseline' => 'BS',
		'bottom' => 'B',
		'text-bottom' => 'TB',
		'justify' => 'J'
	];

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

	public function getTagName()
	{
		$tag = get_class($this);
		return strtoupper(str_replace('LearnDash\Certificate_Builder\Mpdf\Tag\\', '', $tag));
	}

	protected function getAlign($property)
	{
		$property = strtolower($property);
		return array_key_exists($property, self::ALIGN) ? self::ALIGN[$property] : '';
	}

	abstract public function open($attr, &$ahtml, &$ihtml);

	abstract public function close(&$ahtml, &$ihtml);

}
