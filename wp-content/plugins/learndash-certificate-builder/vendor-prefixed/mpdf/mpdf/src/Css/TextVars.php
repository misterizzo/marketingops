<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Css;

class TextVars
{

	// font-decoration
	const FD_UNDERLINE = 1;
	const FD_LINETHROUGH = 2;
	const FD_OVERLINE = 4;

	// font-(vertical)-align
	const FA_SUPERSCRIPT = 8;
	const FA_SUBSCRIPT = 16;

	// font-transform
	const FT_UPPERCASE = 32;
	const FT_LOWERCASE = 64;
	const FT_CAPITALIZE = 128;

	// font-(other)-controls
	const FC_KERNING = 256;
	const FC_SMALLCAPS = 512;
}
