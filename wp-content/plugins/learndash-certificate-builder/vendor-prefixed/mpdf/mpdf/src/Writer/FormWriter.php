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

final class FormWriter
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

	public function writeFormObjects() // _putformobjects
	{
		foreach ($this->mpdf->formobjects as $file => $info) {

			$this->writer->object();

			$this->mpdf->formobjects[$file]['n'] = $this->mpdf->n;

			$this->writer->write('<</Type /XObject');
			$this->writer->write('/Subtype /Form');
			$this->writer->write('/Group ' . ($this->mpdf->n + 1) . ' 0 R');
			$this->writer->write('/BBox [' . $info['x'] . ' ' . $info['y'] . ' ' . ($info['w'] + $info['x']) . ' ' . ($info['h'] + $info['y']) . ']');

			if ($this->mpdf->compress) {
				$this->writer->write('/Filter /FlateDecode');
			}

			$data = $this->mpdf->compress ? gzcompress($info['data']) : $info['data'];
			$this->writer->write('/Length ' . strlen($data) . '>>');
			$this->writer->stream($data);

			unset($this->mpdf->formobjects[$file]['data']);

			$this->writer->write('endobj');

			// Required for SVG transparency (opacity) to work
			$this->writer->object();
			$this->writer->write('<</Type /Group');
			$this->writer->write('/S /Transparency');
			$this->writer->write('>>');
			$this->writer->write('endobj');
		}
	}
}
