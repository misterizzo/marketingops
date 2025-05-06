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

final class ColorWriter
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

	public function writeSpotColors() // _putspotcolors
	{
		foreach ($this->mpdf->spotColors as $name => $color) {

			$this->writer->object();

			$this->writer->write('[/Separation /' . str_replace(' ', '#20', $name));
			$this->writer->write('/DeviceCMYK <<');
			$this->writer->write('/Range [0 1 0 1 0 1 0 1] /C0 [0 0 0 0] ');
			$this->writer->write(sprintf('/C1 [%.3F %.3F %.3F %.3F] ', $color['c'] / 100, $color['m'] / 100, $color['y'] / 100, $color['k'] / 100));
			$this->writer->write('/FunctionType 2 /Domain [0 1] /N 1>>]');
			$this->writer->write('endobj');

			$this->mpdf->spotColors[$name]['n'] = $this->mpdf->n;
		}
	}

}
