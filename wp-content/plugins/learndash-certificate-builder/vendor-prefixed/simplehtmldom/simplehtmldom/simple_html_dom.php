<?php

/**
 * Website: http://sourceforge.net/projects/simplehtmldom/
 * Acknowledge: Jose Solorzano (https://sourceforge.net/projects/php-html/)
 *
 * Licensed under The MIT License
 * See the LICENSE file in the project root for more information.
 *
 * Authors:
 *   S.C. Chen
 *   John Schlick
 *   Rus Carroll
 *   logmanoriginal
 *
 * Contributors:
 *   Yousuke Kumakura
 *   Vadim Voituk
 *   Antcs
 *
 * Version $Rev$
 *
 * @license MIT
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

if (defined('DEFAULT_TARGET_CHARSET')) {
	define('\LearnDash\Certificate_Builder\simplehtmldom\DEFAULT_TARGET_CHARSET', DEFAULT_TARGET_CHARSET);
}

if (defined('DEFAULT_BR_TEXT')) {
	define('\LearnDash\Certificate_Builder\simplehtmldom\DEFAULT_BR_TEXT', DEFAULT_BR_TEXT);
}

if (defined('DEFAULT_SPAN_TEXT')) {
	define('\LearnDash\Certificate_Builder\simplehtmldom\DEFAULT_SPAN_TEXT', DEFAULT_SPAN_TEXT);
}

if (defined('MAX_FILE_SIZE')) {
	define('\LearnDash\Certificate_Builder\simplehtmldom\MAX_FILE_SIZE', MAX_FILE_SIZE);
}

include_once 'HtmlDocument.php';
include_once 'HtmlNode.php';

if (!defined('DEFAULT_TARGET_CHARSET')) {
	define('DEFAULT_TARGET_CHARSET', \LearnDash\Certificate_Builder\simplehtmldom\DEFAULT_TARGET_CHARSET);
}

if (!defined('DEFAULT_BR_TEXT')) {
	define('DEFAULT_BR_TEXT', \LearnDash\Certificate_Builder\simplehtmldom\DEFAULT_BR_TEXT);
}

if (!defined('DEFAULT_SPAN_TEXT')) {
	define('DEFAULT_SPAN_TEXT', \LearnDash\Certificate_Builder\simplehtmldom\DEFAULT_SPAN_TEXT);
}

if (!defined('MAX_FILE_SIZE')) {
	define('MAX_FILE_SIZE', \LearnDash\Certificate_Builder\simplehtmldom\MAX_FILE_SIZE);
}

define('HDOM_TYPE_ELEMENT', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_TYPE_ELEMENT);
define('HDOM_TYPE_COMMENT', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_TYPE_COMMENT);
define('HDOM_TYPE_TEXT', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_TYPE_TEXT);
define('HDOM_TYPE_ROOT', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_TYPE_ROOT);
define('HDOM_TYPE_UNKNOWN', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_TYPE_UNKNOWN);
define('HDOM_QUOTE_DOUBLE', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_QUOTE_DOUBLE);
define('HDOM_QUOTE_SINGLE', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_QUOTE_SINGLE);
define('HDOM_QUOTE_NO', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_QUOTE_NO);
define('HDOM_INFO_BEGIN', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_INFO_BEGIN);
define('HDOM_INFO_END', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_INFO_END);
define('HDOM_INFO_QUOTE', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_INFO_QUOTE);
define('HDOM_INFO_SPACE', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_INFO_SPACE);
define('HDOM_INFO_TEXT', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_INFO_TEXT);
define('HDOM_INFO_INNER', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_INFO_INNER);
define('HDOM_INFO_OUTER', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_INFO_OUTER);
define('HDOM_INFO_ENDSPACE', \LearnDash\Certificate_Builder\simplehtmldom\HtmlNode::HDOM_INFO_ENDSPACE);

define('HDOM_SMARTY_AS_TEXT', \LearnDash\Certificate_Builder\simplehtmldom\HDOM_SMARTY_AS_TEXT);

class_alias('\LearnDash\Certificate_Builder\simplehtmldom\HtmlDocument', 'simple_html_dom', true);
class_alias('\LearnDash\Certificate_Builder\simplehtmldom\HtmlNode', 'simple_html_dom_node', true);

function file_get_html(
	$url,
	$use_include_path = false,
	$context = null,
	$offset = 0,
	$maxLen = -1,
	$lowercase = true,
	$forceTagsClosed = true,
	$target_charset = DEFAULT_TARGET_CHARSET,
	$stripRN = true,
	$defaultBRText = DEFAULT_BR_TEXT,
	$defaultSpanText = DEFAULT_SPAN_TEXT)
{
	if($maxLen <= 0) { $maxLen = MAX_FILE_SIZE; }

	$dom = new simple_html_dom(
		null,
		$lowercase,
		$forceTagsClosed,
		$target_charset,
		$stripRN,
		$defaultBRText,
		$defaultSpanText
	);

	$contents = file_get_contents(
		$url,
		$use_include_path,
		$context,
		$offset,
		$maxLen + 1 // Load extra byte for limit check
	);

	if (empty($contents) || strlen($contents) > $maxLen) {
		$dom->clear();
		return false;
	}

	return $dom->load($contents, $lowercase, $stripRN);
}

function str_get_html(
	$str,
	$lowercase = true,
	$forceTagsClosed = true,
	$target_charset = DEFAULT_TARGET_CHARSET,
	$stripRN = true,
	$defaultBRText = DEFAULT_BR_TEXT,
	$defaultSpanText = DEFAULT_SPAN_TEXT)
{
	$dom = new simple_html_dom(
		null,
		$lowercase,
		$forceTagsClosed,
		$target_charset,
		$stripRN,
		$defaultBRText,
		$defaultSpanText
	);

	if (empty($str) || strlen($str) > MAX_FILE_SIZE) {
		$dom->clear();
		return false;
	}

	return $dom->load($str, $lowercase, $stripRN);
}

/** @codeCoverageIgnore */
function dump_html_tree($node, $show_attr = true, $deep = 0)
{
	$node->dump($node);
}
