<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf;

use LearnDash\Certificate_Builder\Mpdf\Color\ColorConverter;
use LearnDash\Certificate_Builder\Mpdf\Writer\BaseWriter;

class Gradient
{

	const TYPE_LINEAR = 2;
	const TYPE_RADIAL = 3;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\SizeConverter
	 */
	private $sizeConverter;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Color\ColorConverter
	 */
	private $colorConverter;

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Writer\BaseWriter
	 */
	private $writer;

	public function __construct(Mpdf $mpdf, SizeConverter $sizeConverter, ColorConverter $colorConverter, BaseWriter $writer)
	{
		$this->mpdf = $mpdf;
		$this->sizeConverter = $sizeConverter;
		$this->colorConverter = $colorConverter;
		$this->writer = $writer;
	}

	// mPDF 5.3.A1
	public function CoonsPatchMesh($x, $y, $w, $h, $patch_array = [], $x_min = 0, $x_max = 1, $y_min = 0, $y_max = 1, $colspace = 'RGB', $return = false)
	{
		$s = ' q ';
		$s.=sprintf(' %.3F %.3F %.3F %.3F re W n ', $x * Mpdf::SCALE, ($this->mpdf->h - $y) * Mpdf::SCALE, $w * Mpdf::SCALE, -$h * Mpdf::SCALE);
		$s.=sprintf(' %.3F 0 0 %.3F %.3F %.3F cm ', $w * Mpdf::SCALE, $h * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->mpdf->h - ($y + $h)) * Mpdf::SCALE);
		$n = count($this->mpdf->gradients) + 1;
		$this->mpdf->gradients[$n]['type'] = 6; //coons patch mesh
		$this->mpdf->gradients[$n]['colorspace'] = $colspace; //coons patch mesh
		$bpcd = 65535; //16 BitsPerCoordinate
		$trans = false;
		$this->mpdf->gradients[$n]['stream'] = '';

		for ($i = 0; $i < count($patch_array); $i++) {
			$this->mpdf->gradients[$n]['stream'] .= chr($patch_array[$i]['f']); //start with the edge flag as 8 bit

			for ($j = 0; $j < count($patch_array[$i]['points']); $j++) {

				// each point as 16 bit
				if (($j % 2) == 1) { // Y coordinate (adjusted as input is From top left)
					$patch_array[$i]['points'][$j] = (($patch_array[$i]['points'][$j] - $y_min) / ($y_max - $y_min)) * $bpcd;
					$patch_array[$i]['points'][$j] = $bpcd - $patch_array[$i]['points'][$j];
				} else {
					$patch_array[$i]['points'][$j] = (($patch_array[$i]['points'][$j] - $x_min) / ($x_max - $x_min)) * $bpcd;
				}
				if ($patch_array[$i]['points'][$j] < 0) {
					$patch_array[$i]['points'][$j] = 0;
				}
				if ($patch_array[$i]['points'][$j] > $bpcd) {
					$patch_array[$i]['points'][$j] = $bpcd;
				}

				$this->mpdf->gradients[$n]['stream'] .= chr(floor($patch_array[$i]['points'][$j] / 256));
				$this->mpdf->gradients[$n]['stream'] .= chr(floor(round($patch_array[$i]['points'][$j]) % 256));
			}

			for ($j = 0; $j < count($patch_array[$i]['colors']); $j++) {
				//each color component as 8 bit
				if ($colspace === 'RGB') {
					$this->mpdf->gradients[$n]['stream'] .= $patch_array[$i]['colors'][$j][1];
					$this->mpdf->gradients[$n]['stream'] .= $patch_array[$i]['colors'][$j][2];
					$this->mpdf->gradients[$n]['stream'] .= $patch_array[$i]['colors'][$j][3];
					if (isset($patch_array[$i]['colors'][$j][4]) && ord($patch_array[$i]['colors'][$j][4]) < 100) {
						$trans = true;
					}
				} elseif ($colspace === 'CMYK') {
					$this->mpdf->gradients[$n]['stream'] .= chr(ord($patch_array[$i]['colors'][$j][1]) * 2.55);
					$this->mpdf->gradients[$n]['stream'] .= chr(ord($patch_array[$i]['colors'][$j][2]) * 2.55);
					$this->mpdf->gradients[$n]['stream'] .= chr(ord($patch_array[$i]['colors'][$j][3]) * 2.55);
					$this->mpdf->gradients[$n]['stream'] .= chr(ord($patch_array[$i]['colors'][$j][4]) * 2.55);
					if (isset($patch_array[$i]['colors'][$j][5]) && ord($patch_array[$i]['colors'][$j][5]) < 100) {
						$trans = true;
					}
				} elseif ($colspace === 'Gray') {
					$this->mpdf->gradients[$n]['stream'] .= $patch_array[$i]['colors'][$j][1];
					if ($patch_array[$i]['colors'][$j][2] == 1) {
						$trans = true;
					} // transparency converted from rgba or cmyka()
				}
			}
		}

		// TRANSPARENCY
		if ($trans) {
			$this->mpdf->gradients[$n]['stream_trans'] = '';

			for ($i = 0; $i < count($patch_array); $i++) {

				$this->mpdf->gradients[$n]['stream_trans'] .= chr($patch_array[$i]['f']);

				for ($j = 0; $j < count($patch_array[$i]['points']); $j++) {
					// each point as 16 bit
					$this->mpdf->gradients[$n]['stream_trans'] .= chr(floor($patch_array[$i]['points'][$j] / 256));
					$this->mpdf->gradients[$n]['stream_trans'] .= chr(floor(round($patch_array[$i]['points'][$j]) % 256));
				}

				for ($j = 0; $j < count($patch_array[$i]['colors']); $j++) {
					// each color component as 8 bit // OPACITY
					if ($colspace === 'RGB') {
						$this->mpdf->gradients[$n]['stream_trans'] .= chr((int) (ord($patch_array[$i]['colors'][$j][4]) * 2.55));
					} elseif ($colspace === 'CMYK') {
						$this->mpdf->gradients[$n]['stream_trans'] .= chr((int) (ord($patch_array[$i]['colors'][$j][5]) * 2.55));
					} elseif ($colspace === 'Gray') {
						$this->mpdf->gradients[$n]['stream_trans'] .= chr((int) (ord($patch_array[$i]['colors'][$j][3]) * 2.55));
					}
				}
			}

			$this->mpdf->gradients[$n]['trans'] = true;
			$s .= ' /TGS' . $n . ' gs ';
		}

		// paint the gradient
		$s .= '/Sh' . $n . ' sh' . "\n";

		// restore previous Graphic State
		$s .= 'Q' . "\n";

		if ($return) {
			return $s;
		}

		$this->writer->write($s);
	}

	// type = linear:2; radial: 3;
	// Linear: $coords - array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg).
	//    The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
	// Radial: $coords - array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1,
	//    (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg).
	//    (fx, fy) should be inside the circle, otherwise some areas will not be defined
	// $col = array(R,G,B/255); or array(G/255); or array(C,M,Y,K/100)
	// $stops = array('col'=>$col [, 'opacity'=>0-1] [, 'offset'=>0-1])
	public function Gradient($x, $y, $w, $h, $type, $stops = [], $colorspace = 'RGB', $coords = '', $extend = '', $return = false, $is_mask = false)
	{
		if ($type && stripos($type, 'L') === 0) {
			$type = self::TYPE_LINEAR;
		} elseif ($type && stripos($type, 'R') === 0) {
			$type = self::TYPE_RADIAL;
		}

		if ($colorspace !== 'CMYK' && $colorspace !== 'Gray') {
			$colorspace = 'RGB';
		}

		$bboxw = $w;
		$bboxh = $h;
		$usex = $x;
		$usey = $y;
		$usew = $bboxw;
		$useh = $bboxh;

		if ($type < 1) {
			$type = self::TYPE_LINEAR;
		}

		if ($coords[0] !== false && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i', $coords[0], $m)) {
			$tmp = $this->sizeConverter->convert($m[1], $this->mpdf->w, $this->mpdf->FontSize, false);
			if ($tmp) {
				$coords[0] = $tmp / $w;
			}
		}

		if ($coords[1] !== false && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i', $coords[1], $m)) {
			$tmp = $this->sizeConverter->convert($m[1], $this->mpdf->w, $this->mpdf->FontSize, false);
			if ($tmp) {
				$coords[1] = 1 - ($tmp / $h);
			}
		}

		if ($type == self::TYPE_LINEAR) {
			$angle = (isset($coords[4]) ? $coords[4] : false);
			$repeat = (isset($coords[5]) ? $coords[5] : false);
			// ALL POINTS SET (default for custom mPDF linear gradient) - no -moz
			if ($coords[0] !== false && $coords[1] !== false && $coords[2] !== false && $coords[3] !== false) {
				// do nothing - coords used as they are
			} elseif ($angle !== false && $coords[0] !== false && $coords[1] !== false && $coords[2] === false && $coords[3] === false) {
				// If both a <point> and <angle> are defined, the gradient axis starts from the point and runs along the angle. The end point is
				// defined as before - in this case start points may not be in corners, and axis may not correctly fall in the right quadrant.
				// NO end points (Angle defined & Start points)
				if ($angle == 0 || $angle == 360) {
					$coords[3] = $coords[1];
					if ($coords[0] == 1) {
						$coords[2] = 2;
					} else {
						$coords[2] = 1;
					}
				} elseif ($angle == 90) {
					$coords[2] = $coords[0];
					$coords[3] = 1;
					if ($coords[1] == 1) {
						$coords[3] = 2;
					} else {
						$coords[3] = 1;
					}
				} elseif ($angle == 180) {
					if ($coords[4] == 0) {
						$coords[2] = -1;
					} else {
						$coords[2] = 0;
					}
					$coords[3] = $coords[1];
				} elseif ($angle == 270) {
					$coords[2] = $coords[0];
					if ($coords[1] == 0) {
						$coords[3] = -1;
					} else {
						$coords[3] = 0;
					}
				} else {
					$endx = 1;
					$endy = 1;
					if ($angle <= 90) {
						if ($angle <= 45) {
							$endy = tan(deg2rad($angle));
						} else {
							$endx = tan(deg2rad(90 - $angle));
						}
						$b = atan2($endy * $bboxh, $endx * $bboxw);
						$ny = 1 - $coords[1] - (tan($b) * (1 - $coords[0]));
						$tx = sin($b) * cos($b) * $ny;
						$ty = cos($b) * cos($b) * $ny;
						$coords[2] = 1 + $tx;
						$coords[3] = 1 - $ty;
					} elseif ($angle <= 180) {
						if ($angle <= 135) {
							$endx = tan(deg2rad($angle - 90));
						} else {
							$endy = tan(deg2rad(180 - $angle));
						}
						$b = atan2($endy * $bboxh, $endx * $bboxw);
						$ny = 1 - $coords[1] - (tan($b) * $coords[0]);
						$tx = sin($b) * cos($b) * $ny;
						$ty = cos($b) * cos($b) * $ny;
						$coords[2] = -$tx;
						$coords[3] = 1 - $ty;
					} elseif ($angle <= 270) {
						if ($angle <= 225) {
							$endy = tan(deg2rad($angle - 180));
						} else {
							$endx = tan(deg2rad(270 - $angle));
						}
						$b = atan2($endy * $bboxh, $endx * $bboxw);
						$ny = $coords[1] - (tan($b) * $coords[0]);
						$tx = sin($b) * cos($b) * $ny;
						$ty = cos($b) * cos($b) * $ny;
						$coords[2] = -$tx;
						$coords[3] = $ty;
					} else {
						if ($angle <= 315) {
							$endx = tan(deg2rad($angle - 270));
						} else {
							$endy = tan(deg2rad(360 - $angle));
						}
						$b = atan2($endy * $bboxh, $endx * $bboxw);
						$ny = $coords[1] - (tan($b) * (1 - $coords[0]));
						$tx = sin($b) * cos($b) * $ny;
						$ty = cos($b) * cos($b) * $ny;
						$coords[2] = 1 + $tx;
						$coords[3] = $ty;
					}
				}
			} elseif ($angle !== false && $coords[0] === false && $coords[1] === false) {
				// -moz If the first parameter is only an <angle>, the gradient axis starts from the box's corner that would ensure the
				// axis goes through the box. The axis runs along the specified angle. The end point of the axis is defined such that the
				// farthest corner of the box from the starting point is perpendicular to the gradient axis at that point.
				// NO end points or Start points (Angle defined)
				if ($angle == 0 || $angle == 360) {
					$coords[0] = 0;
					$coords[1] = 0;
					$coords[2] = 1;
					$coords[3] = 0;
				} elseif ($angle == 90) {
					$coords[0] = 0;
					$coords[1] = 0;
					$coords[2] = 0;
					$coords[3] = 1;
				} elseif ($angle == 180) {
					$coords[0] = 1;
					$coords[1] = 0;
					$coords[2] = 0;
					$coords[3] = 0;
				} elseif ($angle == 270) {
					$coords[0] = 0;
					$coords[1] = 1;
					$coords[2] = 0;
					$coords[3] = 0;
				} else {
					if ($angle <= 90) {
						$coords[0] = 0;
						$coords[1] = 0;
						if ($angle <= 45) {
							$endx = 1;
							$endy = tan(deg2rad($angle));
						} else {
							$endx = tan(deg2rad(90 - $angle));
							$endy = 1;
						}
					} elseif ($angle <= 180) {
						$coords[0] = 1;
						$coords[1] = 0;
						if ($angle <= 135) {
							$endx = tan(deg2rad($angle - 90));
							$endy = 1;
						} else {
							$endx = 1;
							$endy = tan(deg2rad(180 - $angle));
						}
					} elseif ($angle <= 270) {
						$coords[0] = 1;
						$coords[1] = 1;
						if ($angle <= 225) {
							$endx = 1;
							$endy = tan(deg2rad($angle - 180));
						} else {
							$endx = tan(deg2rad(270 - $angle));
							$endy = 1;
						}
					} else {
						$coords[0] = 0;
						$coords[1] = 1;
						if ($angle <= 315) {
							$endx = tan(deg2rad($angle - 270));
							$endy = 1;
						} else {
							$endx = 1;
							$endy = tan(deg2rad(360 - $angle));
						}
					}
					$b = atan2($endy * $bboxh, $endx * $bboxw);
					$h2 = $bboxh - ($bboxh * tan($b));
					$px = $bboxh + ($h2 * sin($b) * cos($b));
					$py = ($bboxh * tan($b)) + ($h2 * sin($b) * sin($b));
					$x1 = $px / $bboxh;
					$y1 = $py / $bboxh;
					if ($angle <= 90) {
						$coords[2] = $x1;
						$coords[3] = $y1;
					} elseif ($angle <= 180) {
						$coords[2] = 1 - $x1;
						$coords[3] = $y1;
					} elseif ($angle <= 270) {
						$coords[2] = 1 - $x1;
						$coords[3] = 1 - $y1;
					} else {
						$coords[2] = $x1;
						$coords[3] = 1 - $y1;
					}
				}
			} elseif ((!isset($angle) || $angle === false) && $coords[0] !== false && $coords[1] !== false) {
				// -moz If the first parameter to the gradient function is only a <point>, the gradient axis starts from the specified point,
				// and ends at the point you would get if you rotated the starting point by 180 degrees about the center of the box that the
				// gradient is to be applied to.
				// NO angle and NO end points (Start points defined)
				$coords[2] = 1 - $coords[0];
				$coords[3] = 1 - $coords[1];
				$angle = rad2deg(atan2($coords[3] - $coords[1], $coords[2] - $coords[0]));
				if ($angle < 0) {
					$angle += 360;
				} elseif ($angle > 360) {
					$angle -= 360;
				}
				if ($angle != 0 && $angle != 360 && $angle != 90 && $angle != 180 && $angle != 270) {
					if ($w >= $h) {
						$coords[1] *= $h / $w;
						$coords[3] *= $h / $w;
						$usew = $useh = $bboxw;
						$usey -= ($w - $h);
					} else {
						$coords[0] *= $w / $h;
						$coords[2] *= $w / $h;
						$usew = $useh = $bboxh;
					}
				}
			} else {
				// default values T2B
				// -moz If neither a <point> or <angle> is specified, i.e. the entire function consists of only <stop> values, the gradient
				// axis starts from the top of the box and runs vertically downwards, ending at the bottom of the box.
				// All values are set in parseMozGradient - so won't appear here
				$coords = [0, 0, 1, 0]; // default for original linear gradient (L2R)
			}
		} elseif ($type == self::TYPE_RADIAL) {
			$radius = (isset($coords[4]) ? $coords[4] : false);
			$shape = (isset($coords[6]) ? $coords[6] : false);
			$size = (isset($coords[7]) ? $coords[7] : false);
			$repeat = (isset($coords[8]) ? $coords[8] : false);
			// ALL POINTS AND RADIUS SET (default for custom mPDF radial gradient) - no -moz
			if ($coords[0] !== false && $coords[1] !== false && $coords[2] !== false && $coords[3] !== false && $coords[4] !== false) {
				// If a <point> is defined
				// do nothing - coords used as they are
			} elseif ($shape !== false && $size !== false) {
				if ($coords[2] == false) {
					$coords[2] = $coords[0];
				}
				if ($coords[3] == false) {
					$coords[3] = $coords[1];
				}
				// ELLIPSE
				if ($shape === 'ellipse') {
					$corner1 = sqrt(($coords[0] ** 2) + ($coords[1] ** 2));
					$corner2 = sqrt(($coords[0] ** 2) + ((1 - $coords[1]) ** 2));
					$corner3 = sqrt(((1 - $coords[0]) ** 2) + ($coords[1] ** 2));
					$corner4 = sqrt(((1 - $coords[0]) ** 2) + ((1 - $coords[1]) ** 2));
					if ($size === 'closest-side') {
						$radius = min($coords[0], $coords[1], 1 - $coords[0], 1 - $coords[1]);
					} elseif ($size === 'closest-corner') {
						$radius = min($corner1, $corner2, $corner3, $corner4);
					} elseif ($size === 'farthest-side') {
						$radius = max($coords[0], $coords[1], 1 - $coords[0], 1 - $coords[1]);
					} else {
						$radius = max($corner1, $corner2, $corner3, $corner4);
					} // farthest corner (default)
				} elseif ($shape === 'circle') {
					if ($w >= $h) {
						$coords[1] = $coords[3] = ($coords[1] * $h / $w);
						$corner1 = sqrt(($coords[0] ** 2) + ($coords[1] ** 2));
						$corner2 = sqrt(($coords[0] ** 2) + ((($h / $w) - $coords[1]) ** 2));
						$corner3 = sqrt(((1 - $coords[0]) ** 2) + ($coords[1] ** 2));
						$corner4 = sqrt(((1 - $coords[0]) ** 2) + ((($h / $w) - $coords[1]) ** 2));
						if ($size === 'closest-side') {
							$radius = min($coords[0], $coords[1], 1 - $coords[0], ($h / $w) - $coords[1]);
						} elseif ($size === 'closest-corner') {
							$radius = min($corner1, $corner2, $corner3, $corner4);
						} elseif ($size === 'farthest-side') {
							$radius = max($coords[0], $coords[1], 1 - $coords[0], ($h / $w) - $coords[1]);
						} elseif ($size === 'farthest-corner') {
							$radius = max($corner1, $corner2, $corner3, $corner4);
						} // farthest corner (default)
						$usew = $useh = $bboxw;
						$usey -= ($w - $h);
					} else {
						$coords[0] = $coords[2] = ($coords[0] * $w / $h);
						$corner1 = sqrt(($coords[0] ** 2) + ($coords[1] ** 2));
						$corner2 = sqrt(($coords[0] ** 2) + ((1 - $coords[1]) ** 2));
						$corner3 = sqrt(((($w / $h) - $coords[0]) ** 2) + ($coords[1] ** 2));
						$corner4 = sqrt(((($w / $h) - $coords[0]) ** 2) + ((1 - $coords[1]) ** 2));
						if ($size === 'closest-side') {
							$radius = min($coords[0], $coords[1], ($w / $h) - $coords[0], 1 - $coords[1]);
						} elseif ($size === 'closest-corner') {
							$radius = min($corner1, $corner2, $corner3, $corner4);
						} elseif ($size === 'farthest-side') {
							$radius = max($coords[0], $coords[1], ($w / $h) - $coords[0], 1 - $coords[1]);
						} elseif ($size === 'farthest-corner') {
							$radius = max($corner1, $corner2, $corner3, $corner4);
						} // farthest corner (default)
						$usew = $useh = $bboxh;
					}
				}
				if ($radius == 0) {
					$radius = 0.001;
				} // to prevent error
				$coords[4] = $radius;
			} else {
				// -moz If entire function consists of only <stop> values
				// All values are set in parseMozGradient - so won't appear here
				$coords = [0.5, 0.5, 0.5, 0.5]; // default for radial gradient (centred)
			}
		}
		$s = ' q';
		$s .= sprintf(' %.3F %.3F %.3F %.3F re W n', $x * Mpdf::SCALE, ($this->mpdf->h - $y) * Mpdf::SCALE, $w * Mpdf::SCALE, -$h * Mpdf::SCALE) . "\n";
		$s .= sprintf(' %.3F 0 0 %.3F %.3F %.3F cm', $usew * Mpdf::SCALE, $useh * Mpdf::SCALE, $usex * Mpdf::SCALE, ($this->mpdf->h - ($usey + $useh)) * Mpdf::SCALE) . "\n";

		$n = count($this->mpdf->gradients) + 1;
		$this->mpdf->gradients[$n]['type'] = $type;
		$this->mpdf->gradients[$n]['colorspace'] = $colorspace;
		$trans = false;
		$this->mpdf->gradients[$n]['is_mask'] = $is_mask;
		if ($is_mask) {
			$trans = true;
		}
		if (count($stops) == 1) {
			$stops[1] = $stops[0];
		}
		if (!isset($stops[0]['offset'])) {
			$stops[0]['offset'] = 0;
		}
		if (!isset($stops[count($stops) - 1]['offset'])) {
			$stops[count($stops) - 1]['offset'] = 1;
		}

		// Fix stop-offsets set as absolute lengths
		if ($type == self::TYPE_LINEAR) {
			$axisx = ($coords[2] - $coords[0]) * $usew;
			$axisy = ($coords[3] - $coords[1]) * $useh;
			$axis_length = sqrt(($axisx ** 2) + ($axisy ** 2));
		} else {
			$axis_length = $coords[4] * $usew;
		} // Absolute lengths are meaningless for an ellipse - Firefox uses Width as reference

		for ($i = 0; $i < count($stops); $i++) {
			if (isset($stops[$i]['offset']) && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i', $stops[$i]['offset'], $m)) {
				$tmp = $this->sizeConverter->convert($m[1], $this->mpdf->w, $this->mpdf->FontSize, false);
				$stops[$i]['offset'] = $axis_length ? $tmp / $axis_length : 0;
			}
		}


		if (isset($stops[0]['offset']) && $stops[0]['offset'] > 0) {
			$firststop = $stops[0];
			$firststop['offset'] = 0;
			array_unshift($stops, $firststop);
		}
		if (!$repeat && isset($stops[count($stops) - 1]['offset']) && $stops[count($stops) - 1]['offset'] < 1) {
			$endstop = $stops[count($stops) - 1];
			$endstop['offset'] = 1;
			$stops[] = $endstop;
		}
		if ($stops[0]['offset'] > $stops[count($stops) - 1]['offset']) {
			$stops[0]['offset'] = 0;
			$stops[count($stops) - 1]['offset'] = 1;
		}

		for ($i = 0; $i < count($stops); $i++) {
			// mPDF 5.3.74
			if ($colorspace === 'CMYK') {
				$this->mpdf->gradients[$n]['stops'][$i]['col'] = sprintf('%.3F %.3F %.3F %.3F', ord($stops[$i]['col'][1]) / 100, ord($stops[$i]['col'][2]) / 100, ord($stops[$i]['col'][3]) / 100, ord($stops[$i]['col'][4]) / 100);
			} elseif ($colorspace === 'Gray') {
				$this->mpdf->gradients[$n]['stops'][$i]['col'] = sprintf('%.3F', ord($stops[$i]['col'][1]) / 255);
			} else {
				$this->mpdf->gradients[$n]['stops'][$i]['col'] = sprintf('%.3F %.3F %.3F', ord($stops[$i]['col'][1]) / 255, ord($stops[$i]['col'][2]) / 255, ord($stops[$i]['col'][3]) / 255);
			}
			if (!isset($stops[$i]['opacity'])) {
				$stops[$i]['opacity'] = 1;
			} elseif ($stops[$i]['opacity'] > 1 || $stops[$i]['opacity'] < 0) {
				$stops[$i]['opacity'] = 1;
			} elseif ($stops[$i]['opacity'] < 1) {
				$trans = true;
			}
			$this->mpdf->gradients[$n]['stops'][$i]['opacity'] = $stops[$i]['opacity'];
			// OFFSET
			if ($i > 0 && $i < (count($stops) - 1)) {
				if (!isset($stops[$i]['offset']) || (isset($stops[$i + 1]['offset']) && $stops[$i]['offset'] > $stops[$i + 1]['offset']) || $stops[$i]['offset'] < $stops[$i - 1]['offset']) {
					if (isset($stops[$i - 1]['offset']) && isset($stops[$i + 1]['offset'])) {
						$stops[$i]['offset'] = ($stops[$i - 1]['offset'] + $stops[$i + 1]['offset']) / 2;
					} else {
						for ($j = ($i + 1); $j < count($stops); $j++) {
							if (isset($stops[$j]['offset'])) {
								break;
							}
						}
						$int = ($stops[$j]['offset'] - $stops[$i - 1]['offset']) / ($j - $i + 1);
						for ($f = 0; $f < ($j - $i - 1); $f++) {
							$stops[$i + $f]['offset'] = $stops[$i + $f - 1]['offset'] + $int;
						}
					}
				}
			}
			$this->mpdf->gradients[$n]['stops'][$i]['offset'] = $stops[$i]['offset'];
		}

		if ($repeat) {
			$ns = count($this->mpdf->gradients[$n]['stops']);
			$offs = [];
			for ($i = 0; $i < $ns; $i++) {
				$offs[$i] = $this->mpdf->gradients[$n]['stops'][$i]['offset'];
			}
			$gp = 0;
			$inside = true;
			while ($inside) {
				$gp++;
				for ($i = 0; $i < $ns; $i++) {
					$this->mpdf->gradients[$n]['stops'][($ns * $gp) + $i] = $this->mpdf->gradients[$n]['stops'][($ns * ($gp - 1)) + $i];
					$tmp = $this->mpdf->gradients[$n]['stops'][($ns * ($gp - 1)) + ($ns - 1)]['offset'] + $offs[$i];
					if ($tmp < 1) {
						$this->mpdf->gradients[$n]['stops'][($ns * $gp) + $i]['offset'] = $tmp;
					} else {
						$this->mpdf->gradients[$n]['stops'][($ns * $gp) + $i]['offset'] = 1;
						$inside = false;
						break;
					}
				}
			}
		}

		if ($trans) {
			$this->mpdf->gradients[$n]['trans'] = true;
			$s .= ' /TGS' . $n . ' gs ';
		}
		if (!is_array($extend) || count($extend) < 1) {
			$extend = ['true', 'true']; // These are supposed to be quoted - appear in PDF file as text
		}
		$this->mpdf->gradients[$n]['coords'] = $coords;
		$this->mpdf->gradients[$n]['extend'] = $extend;
		//paint the gradient
		$s .= '/Sh' . $n . ' sh ' . "\n";
		//restore previous Graphic State
		$s .= ' Q ' . "\n";
		if ($return) {
			return $s;
		}

		$this->writer->write($s);
	}

	private function parseMozLinearGradient($m, $repeat)
	{
		$g = [];
		$g['type'] = self::TYPE_LINEAR;
		$g['colorspace'] = 'RGB';
		$g['extend'] = ['true', 'true'];
		$v = trim($m[1]);

		// Change commas inside e.g. rgb(x,x,x)
		while (preg_match('/(\([^\)]*?),/', $v)) {
			$v = preg_replace('/(\([^\)]*?),/', '\\1@', $v);
		}

		// Remove spaces inside e.g. rgb(x, x, x)
		while (preg_match('/(\([^\)]*?)[ ]/', $v)) {
			$v = preg_replace('/(\([^\)]*?)[ ]/', '\\1', $v);
		}

		$bgr = preg_split('/\s*,\s*/', $v);

		for ($i = 0; $i < count($bgr); $i++) {
			$bgr[$i] = preg_replace('/@/', ',', $bgr[$i]);
		}
		// Is first part $bgr[0] a valid point/angle?
		$first = preg_split('/\s+/', trim($bgr[0]));
		if (preg_match('/(left|center|right|bottom|top|deg|grad|rad)/i', $bgr[0]) && !preg_match('/(<#|rgb|rgba|hsl|hsla)/i', $bgr[0])) {
			$startStops = 1;
		} elseif (trim($first[count($first) - 1]) === '0') {
			$startStops = 1;
		} else {
			$check = $this->colorConverter->convert($first[0], $this->mpdf->PDFAXwarnings);
			$startStops = 1;
			if ($check) {
				$startStops = 0;
			}
		}

		// first part a valid point/angle?
		if ($startStops === 1) { // default values

			// [<point> || <angle>,] = [<% em px left center right bottom top> || <deg grad rad 0>,]
			if (preg_match('/([\-]*[0-9\.]+)(deg|grad|rad)/i', $bgr[0], $m)) {
				$angle = $m[1] + 0;
				if (strtolower($m[2]) === 'grad') {
					$angle *= (360 / 400);
				} elseif (strtolower($m[2]) === 'rad') {
					$angle = rad2deg($angle);
				}
				while ($angle < 0) {
					$angle += 360;
				}
				$angle %= 360;
			} elseif (trim($first[count($first) - 1]) === '0') {
				$angle = 0;
			}

			if (stripos($bgr[0], 'left') !== false) {
				$startx = 1;
			} elseif (stripos($bgr[0], 'right') !== false) {
				$startx = 0;
			}

			if (stripos($bgr[0], 'top') !== false) {
				$starty = 1;
			} elseif (stripos($bgr[0], 'bottom') !== false) {
				$starty = 0;
			}

			// Check for %? ?% or %%
			if (preg_match('/(\d+)[%]/i', $first[0], $m)) {
				$startx = $m[1] / 100;
			} elseif (!isset($startx) && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i', $first[0], $m)) {
				$tmp = $this->sizeConverter->convert($m[1], $this->mpdf->w, $this->mpdf->FontSize, false);
				if ($tmp) {
					$startx = $m[1];
				}
			}

			if (isset($first[1]) && preg_match('/(\d+)[%]/i', $first[1], $m)) {
				$starty = 1 - ($m[1] / 100);
			} elseif (!isset($starty) && isset($first[1]) && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i', $first[1], $m)) {
				$tmp = $this->sizeConverter->convert($m[1], $this->mpdf->w, $this->mpdf->FontSize, false);
				if ($tmp) {
					$starty = $m[1];
				}
			}

			if (isset($startx) && !isset($starty)) {
				$starty = 0.5;
			}

			if (!isset($startx) && isset($starty)) {
				$startx = 0.5;
			}

		} else {
			// If neither a <point> or <angle> is specified, i.e. the entire function consists of only <stop> values,
			// the gradient axis starts from the top of the box and runs vertically downwards, ending at the bottom of
			// the box.
			$starty = 1;
			$startx = 0.5;
			$endy = 0;
			$endx = 0.5;
		}

		if (!isset($startx)) {
			$startx = false;
		}

		if (!isset($starty)) {
			$starty = false;
		}

		if (!isset($endx)) {
			$endx = false;
		}

		if (!isset($endy)) {
			$endy = false;
		}

		if (!isset($angle)) {
			$angle = false;
		}

		$g['coords'] = [$startx, $starty, $endx, $endy, $angle, $repeat];
		$g['stops'] = [];

		for ($i = $startStops; $i < count($bgr); $i++) {

			// parse stops
			$el = preg_split('/\s+/', trim($bgr[$i]));
			// mPDF 5.3.74
			$col = $this->colorConverter->convert($el[0], $this->mpdf->PDFAXwarnings);

			if (!$col) {
				$col = $this->colorConverter->convert(255, $this->mpdf->PDFAXwarnings);
			}

			if ($col[0] == 1) {
				$g['colorspace'] = 'Gray';
			} elseif ($col[0] == 4 || $col[0] == 6) {
				$g['colorspace'] = 'CMYK';
			}

			$g['stops'][] = $this->getStop($col, $el, true);
		}

		return $g;
	}

	private function parseMozRadialGradient($m, $repeat)
	{
		$g = [];
		$g['type'] = self::TYPE_RADIAL;
		$g['colorspace'] = 'RGB';
		$g['extend'] = ['true', 'true'];
		$v = trim($m[1]);
		// Change commas inside e.g. rgb(x,x,x)
		while (preg_match('/(\([^\)]*?),/', $v)) {
			$v = preg_replace('/(\([^\)]*?),/', '\\1@', $v);
		}
		// Remove spaces inside e.g. rgb(x, x, x)
		while (preg_match('/(\([^\)]*?)[ ]/', $v)) {
			$v = preg_replace('/(\([^\)]*?)[ ]/', '\\1', $v);
		}
		$bgr = preg_split('/\s*,\s*/', $v);
		for ($i = 0; $i < count($bgr); $i++) {
			$bgr[$i] = preg_replace('/@/', ',', $bgr[$i]);
		}

		// Is first part $bgr[0] a valid point/angle?
		$startStops = 0;
		$pos_angle = false;
		$shape_size = false;
		$first = preg_split('/\s+/', trim($bgr[0]));
		$checkCol = $this->colorConverter->convert($first[0], $this->mpdf->PDFAXwarnings);
		if (preg_match('/(left|center|right|bottom|top|deg|grad|rad)/i', $bgr[0]) && !preg_match('/(<#|rgb|rgba|hsl|hsla)/i', $bgr[0])) {
			$startStops = 1;
			$pos_angle = $bgr[0];
		} elseif (trim($first[count($first) - 1]) === '0') {
			$startStops = 1;
			$pos_angle = $bgr[0];
		} elseif (preg_match('/(circle|ellipse|closest-side|closest-corner|farthest-side|farthest-corner|contain|cover)/i', $bgr[0])) {
			$startStops = 1;
			$shape_size = $bgr[0];
		} elseif (!$checkCol) {
			$startStops = 1;
			$pos_angle = $bgr[0];
		}
		if (preg_match('/(circle|ellipse|closest-side|closest-corner|farthest-side|farthest-corner|contain|cover)/i', $bgr[1])) {
			$startStops = 2;
			$shape_size = $bgr[1];
		}

		// If valid point/angle?
		if ($pos_angle) { // default values
			// [<point> || <angle>,] = [<% em px left center right bottom top> || <deg grad rad 0>,]
			if (stripos($pos_angle, 'left') !== false) {
				$startx = 0;
			} elseif (stripos($pos_angle, 'right') !== false) {
				$startx = 1;
			}
			if (stripos($pos_angle, 'top') !== false) {
				$starty = 1;
			} elseif (stripos($pos_angle, 'bottom') !== false) {
				$starty = 0;
			}
			// Check for %? ?% or %%
			if (preg_match('/(\d+)[%]/i', $first[0], $m)) {
				$startx = $m[1] / 100;
			} elseif (!isset($startx) && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i', $first[0], $m)) {
				$tmp = $this->sizeConverter->convert($m[1], $this->mpdf->w, $this->mpdf->FontSize, false);
				if ($tmp) {
					$startx = $m[1];
				}
			}
			if (isset($first[1]) && preg_match('/(\d+)[%]/i', $first[1], $m)) {
				$starty = 1 - ($m[1] / 100);
			} elseif (!isset($starty) && isset($first[1]) && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i', $first[1], $m)) {
				$tmp = $this->sizeConverter->convert($m[1], $this->mpdf->w, $this->mpdf->FontSize, false);
				if ($tmp) {
					$starty = $m[1];
				}
			}

			if (!isset($starty)) {
				$starty = 0.5;
			}
			if (!isset($startx)) {
				$startx = 0.5;
			}
		} else {
			// If neither a <point> or <angle> is specified, i.e. the entire function consists of only <stop> values,
			// the gradient axis starts from the top of the box and runs vertically downwards, ending at the bottom of
			// the box. default values Center
			$starty = 0.5;
			$startx = 0.5;
			$endy = 0.5;
			$endx = 0.5;
		}

		// If valid shape/size?
		$shape = 'ellipse'; // default
		$size = 'farthest-corner'; // default
		if ($shape_size) { // default values
			if (preg_match('/(circle|ellipse)/i', $shape_size, $m)) {
				$shape = $m[1];
			}
			if (preg_match('/(closest-side|closest-corner|farthest-side|farthest-corner|contain|cover)/i', $shape_size, $m)) {
				$size = $m[1];
				if ($size === 'contain') {
					$size = 'closest-side';
				} elseif ($size === 'cover') {
					$size = 'farthest-corner';
				}
			}
		}

		if (!isset($startx)) {
			$startx = false;
		}
		if (!isset($starty)) {
			$starty = false;
		}
		if (!isset($endx)) {
			$endx = false;
		}
		if (!isset($endy)) {
			$endy = false;
		}
		$radius = false;
		$angle = 0;
		$g['coords'] = [$startx, $starty, $endx, $endy, $radius, $angle, $shape, $size, $repeat];

		$g['stops'] = [];
		for ($i = $startStops; $i < count($bgr); $i++) {
			// parse stops
			$el = preg_split('/\s+/', trim($bgr[$i]));
			// mPDF 5.3.74
			$col = $this->colorConverter->convert($el[0], $this->mpdf->PDFAXwarnings);
			if (!$col) {
				$col = $this->colorConverter->convert(255, $this->mpdf->PDFAXwarnings);
			}
			if ($col[0] == 1) {
				$g['colorspace'] = 'Gray';
			} elseif ($col[0] == 4 || $col[0] == 6) {
				$g['colorspace'] = 'CMYK';
			}
			$g['stops'][] = $this->getStop($col, $el);
		}
		return $g;
	}

	private function getStop($col, $el, $convertOffset = false)
	{
		$stop = [
			'col' => $col,
		];

		if ($col[0] == 5) {
			// transparency from rgba()
			$stop['opacity'] = ord($col[4]) / 100;
		} elseif ($col[0] == 6) {
			// transparency from cmyka()
			$stop['opacity'] = ord($col[5]) / 100;
		} elseif ($col[0] == 1 && $col[2] == 1) {
			// transparency converted from rgba or cmyka()
			$stop['opacity'] = ord($col[3]) / 100;
		}

		if (isset($el[1])) {
			if (preg_match('/(\d+)[%]/', $el[1], $m)) {
				$stop['offset'] = $m[1] / 100;
				if ($stop['offset'] > 1) {
					unset($stop['offset']);
				}
			} elseif (preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i', $el[1], $m)) {
				if ($convertOffset) {
					$tmp = $this->sizeConverter->convert($m[1], $this->mpdf->w, $this->mpdf->FontSize, false);
					if ($tmp) {
						$stop['offset'] = $m[1];
					}
				} else {
					$stop['offset'] = $el[1];
				}
			}
		}

		return $stop;
	}

	public function parseMozGradient($bg)
	{
		//	background[-image]: -moz-linear-gradient(left, #c7Fdde 20%, #FF0000 );
		//	background[-image]: linear-gradient(left, #c7Fdde 20%, #FF0000 ); // CSS3
		$repeat = strpos($bg, 'repeating-') !== false;

		if (preg_match('/linear-gradient\((.*)\)/', $bg, $m)) {
			$g = $this->parseMozLinearGradient($m, $repeat);
			if (count($g['stops'])) {
				return $g;
			}
		} elseif (preg_match('/radial-gradient\((.*)\)/', $bg, $m)) {
			$g = $this->parseMozRadialGradient($m, $repeat);
			if (count($g['stops'])) {
				return $g;
			}
		}
		return [];
	}

	public function parseBackgroundGradient($bg)
	{
		// background-gradient: linear #00FFFF #FFFF00 0 0.5 1 0.5;  or
		// background-gradient: radial #00FFFF #FFFF00 0.5 0.5 1 1 1.2;

		$v = trim($bg);
		$bgr = preg_split('/\s+/', $v);
		$count_bgr = count($bgr);
		$g = [];
		if ($count_bgr > 6) {
			if (stripos($bgr[0], 'L') === 0 && $count_bgr === 7) {  // linear
				$g['type'] = self::TYPE_LINEAR;
				//$coords = array(0,0,1,1 );	// 0 0 1 0 or 0 1 1 1 is L 2 R; 1,1,0,1 is R2L; 1,1,1,0 is T2B; 1,0,1,1 is B2T
				// Linear: $coords - array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg).
				//    The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
				$g['coords'] = [$bgr[3], $bgr[4], $bgr[5], $bgr[6]];
			} elseif ($count_bgr === 8) { // radial
				$g['type'] = self::TYPE_RADIAL;
				// Radial: $coords - array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1,
				//    (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg).
				//    (fx, fy) should be inside the circle, otherwise some areas will not be defined
				$g['coords'] = [$bgr[3], $bgr[4], $bgr[5], $bgr[6], $bgr[7]];
			}
			$g['colorspace'] = 'RGB';
			// mPDF 5.3.74
			$cor = $this->colorConverter->convert($bgr[1], $this->mpdf->PDFAXwarnings);
			if ($cor[0] == 1) {
				$g['colorspace'] = 'Gray';
			} elseif ($cor[0] == 4 || $cor[0] == 6) {
				$g['colorspace'] = 'CMYK';
			}
			if ($cor) {
				$g['col'] = $cor;
			} else {
				$g['col'] = $this->colorConverter->convert(255, $this->mpdf->PDFAXwarnings);
			}
			$cor = $this->colorConverter->convert($bgr[2], $this->mpdf->PDFAXwarnings);
			if ($cor) {
				$g['col2'] = $cor;
			} else {
				$g['col2'] = $this->colorConverter->convert(255, $this->mpdf->PDFAXwarnings);
			}
			$g['extend'] = ['true', 'true'];
			$g['stops'] = [['col' => $g['col'], 'opacity' => 1, 'offset' => 0], ['col' => $g['col2'], 'opacity' => 1, 'offset' => 1]];
			return $g;
		}
		return false;
	}
}
