<?php
/**
 * This example loads a page from Slashdot and displays articles in a custom
 * format.
 *
 * @license MIT
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
include_once '../../HtmlWeb.php';
use LearnDash\Certificate_Builder\simplehtmldom\HtmlWeb;

// Load the page into memory
$doc = new HtmlWeb();
$html = $doc->load('https://slashdot.org/');

// Find and extract all articles
foreach($html->find('#firehoselist > [id^="firehose-"]') as $article) {
	$item['title'] = trim($article->find('[id^="title-"]', 0)->plaintext);
	$item['body'] = trim($article->find('[id^="text-"]', 0)->plaintext);

	$data[] = $item;
}

// clean up memory
$html->clear();
unset($html);

// Return custom page
foreach($data as $item) {
	echo <<<EOD

<h2>{$item['title']}</h2>
<p>{$item['body']}</p>

EOD;
}
