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

final class OptionalContentWriter
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

	public function writeOptionalContentGroups() // _putocg Optional Content Groups
	{
		if ($this->mpdf->hasOC) {

			$this->writer->object();
			$this->mpdf->n_ocg_print = $this->mpdf->n;
			$this->writer->write('<</Type /OCG /Name ' . $this->writer->string('Print only'));
			$this->writer->write('/Usage <</Print <</PrintState /ON>> /View <</ViewState /OFF>>>>>>');
			$this->writer->write('endobj');

			$this->writer->object();
			$this->mpdf->n_ocg_view = $this->mpdf->n;
			$this->writer->write('<</Type /OCG /Name ' . $this->writer->string('Screen only'));
			$this->writer->write('/Usage <</Print <</PrintState /OFF>> /View <</ViewState /ON>>>>>>');
			$this->writer->write('endobj');

			$this->writer->object();
			$this->mpdf->n_ocg_hidden = $this->mpdf->n;
			$this->writer->write('<</Type /OCG /Name ' . $this->writer->string('Hidden'));
			$this->writer->write('/Usage <</Print <</PrintState /OFF>> /View <</ViewState /OFF>>>>>>');
			$this->writer->write('endobj');
		}

		if (count($this->mpdf->layers)) {

			ksort($this->mpdf->layers);
			foreach ($this->mpdf->layers as $id => $layer) {
				$this->writer->object();
				$this->mpdf->layers[$id]['n'] = $this->mpdf->n;

				if (isset($this->mpdf->layerDetails[$id]['name']) && $this->mpdf->layerDetails[$id]['name']) {
					$name = $this->mpdf->layerDetails[$id]['name'];
				} else {
					$name = $layer['name'];
				}

				$this->writer->write('<</Type /OCG /Name ' . $this->writer->utf16BigEndianTextString($name) . '>>');
				$this->writer->write('endobj');
			}
		}
	}

}
