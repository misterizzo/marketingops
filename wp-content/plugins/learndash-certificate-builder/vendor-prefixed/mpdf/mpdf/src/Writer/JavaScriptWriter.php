<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Writer;

use LearnDash\Certificate_Builder\Mpdf\Strict;
use LearnDash\Certificate_Builder\Mpdf\Mpdf;

final class JavaScriptWriter
{

	use Strict;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Writer\BaseWriter
	 */
	private $writer;

	public function __construct(Mpdf $mpdf, BaseWriter $writer)
	{
		$this->mpdf = $mpdf;
		$this->writer = $writer;
	}

	public function writeJavascript() // _putjavascript
	{
		$this->writer->object();
		$this->mpdf->n_js = $this->mpdf->n;
		$this->writer->write('<<');
		$this->writer->write('/Names [(EmbeddedJS) ' . (1 + $this->mpdf->n) . ' 0 R ]');
		$this->writer->write('>>');
		$this->writer->write('endobj');

		$this->writer->object();
		$this->writer->write('<<');
		$this->writer->write('/S /JavaScript');
		$this->writer->write('/JS ' . $this->writer->string($this->mpdf->js));
		$this->writer->write('>>');
		$this->writer->write('endobj');
	}

}
