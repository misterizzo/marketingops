<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf;

use LearnDash\Certificate_Builder\Mpdf\Color\ColorConverter;
use LearnDash\Certificate_Builder\Mpdf\Color\ColorModeConverter;
use LearnDash\Certificate_Builder\Mpdf\Color\ColorSpaceRestrictor;
use LearnDash\Certificate_Builder\Mpdf\File\LocalContentLoader;
use LearnDash\Certificate_Builder\Mpdf\Fonts\FontCache;
use LearnDash\Certificate_Builder\Mpdf\Fonts\FontFileFinder;
use LearnDash\Certificate_Builder\Mpdf\Http\CurlHttpClient;
use LearnDash\Certificate_Builder\Mpdf\Http\SocketHttpClient;
use LearnDash\Certificate_Builder\Mpdf\Image\ImageProcessor;
use LearnDash\Certificate_Builder\Mpdf\Pdf\Protection;
use LearnDash\Certificate_Builder\Mpdf\Pdf\Protection\UniqidGenerator;
use LearnDash\Certificate_Builder\Mpdf\Writer\BaseWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\BackgroundWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\ColorWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\BookmarkWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\FontWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\FormWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\ImageWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\JavaScriptWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\MetadataWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\OptionalContentWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\PageWriter;
use LearnDash\Certificate_Builder\Mpdf\Writer\ResourceWriter;
use LearnDash\Certificate_Builder\Psr\Log\LoggerInterface;

class ServiceFactory
{

	/**
	 * @var \LearnDash\Certificate_Builder\Mpdf\Container\ContainerInterface|null
	 */
	private $container;

	public function __construct($container = null)
	{
		$this->container = $container;
	}

	public function getServices(
		Mpdf $mpdf,
		LoggerInterface $logger,
		$config,
		$languageToFont,
		$scriptToLanguage,
		$fontDescriptor,
		$bmp,
		$directWrite,
		$wmf
	) {
		$sizeConverter = new SizeConverter($mpdf->dpi, $mpdf->default_font_size, $mpdf, $logger);

		$colorModeConverter = new ColorModeConverter();
		$colorSpaceRestrictor = new ColorSpaceRestrictor(
			$mpdf,
			$colorModeConverter
		);
		$colorConverter = new ColorConverter($mpdf, $colorModeConverter, $colorSpaceRestrictor);

		$tableOfContents = new TableOfContents($mpdf, $sizeConverter);

		$cacheBasePath = $config['tempDir'] . '/mpdf';

		$cache = new Cache($cacheBasePath, $config['cacheCleanupInterval']);
		$fontCache = new FontCache(new Cache($cacheBasePath . '/ttfontdata', $config['cacheCleanupInterval']));

		$fontFileFinder = new FontFileFinder($config['fontDir']);

		if ($this->container && $this->container->has('httpClient')) {
			$httpClient = $this->container->get('httpClient');
		} elseif (\function_exists('curl_init')) {
			$httpClient = new CurlHttpClient($mpdf, $logger);
		} else {
			$httpClient = new SocketHttpClient($logger);
		}

		$localContentLoader = $this->container && $this->container->has('localContentLoader')
			? $this->container->get('localContentLoader')
			: new LocalContentLoader();

		$assetFetcher = new AssetFetcher($mpdf, $localContentLoader, $httpClient, $logger);

		$cssManager = new CssManager($mpdf, $cache, $sizeConverter, $colorConverter, $assetFetcher);

		$otl = new Otl($mpdf, $fontCache);

		$protection = new Protection(new UniqidGenerator());

		$writer = new BaseWriter($mpdf, $protection);

		$gradient = new Gradient($mpdf, $sizeConverter, $colorConverter, $writer);

		$formWriter = new FormWriter($mpdf, $writer);

		$form = new Form($mpdf, $otl, $colorConverter, $writer, $formWriter);

		$hyphenator = new Hyphenator($mpdf);

		$imageProcessor = new ImageProcessor(
			$mpdf,
			$otl,
			$cssManager,
			$sizeConverter,
			$colorConverter,
			$colorModeConverter,
			$cache,
			$languageToFont,
			$scriptToLanguage,
			$assetFetcher,
			$logger
		);

		$tag = new Tag(
			$mpdf,
			$cache,
			$cssManager,
			$form,
			$otl,
			$tableOfContents,
			$sizeConverter,
			$colorConverter,
			$imageProcessor,
			$languageToFont
		);

		$fontWriter = new FontWriter($mpdf, $writer, $fontCache, $fontDescriptor);
		$metadataWriter = new MetadataWriter($mpdf, $writer, $form, $protection, $logger);
		$imageWriter = new ImageWriter($mpdf, $writer);
		$pageWriter = new PageWriter($mpdf, $form, $writer, $metadataWriter);
		$bookmarkWriter = new BookmarkWriter($mpdf, $writer);
		$optionalContentWriter = new OptionalContentWriter($mpdf, $writer);
		$colorWriter = new ColorWriter($mpdf, $writer);
		$backgroundWriter = new BackgroundWriter($mpdf, $writer);
		$javaScriptWriter = new JavaScriptWriter($mpdf, $writer);

		$resourceWriter = new ResourceWriter(
			$mpdf,
			$writer,
			$colorWriter,
			$fontWriter,
			$imageWriter,
			$formWriter,
			$optionalContentWriter,
			$backgroundWriter,
			$bookmarkWriter,
			$metadataWriter,
			$javaScriptWriter,
			$logger
		);

		return [
			'otl' => $otl,
			'bmp' => $bmp,
			'cache' => $cache,
			'cssManager' => $cssManager,
			'directWrite' => $directWrite,
			'fontCache' => $fontCache,
			'fontFileFinder' => $fontFileFinder,
			'form' => $form,
			'gradient' => $gradient,
			'tableOfContents' => $tableOfContents,
			'tag' => $tag,
			'wmf' => $wmf,
			'sizeConverter' => $sizeConverter,
			'colorConverter' => $colorConverter,
			'hyphenator' => $hyphenator,
			'localContentLoader' => $localContentLoader,
			'httpClient' => $httpClient,
			'assetFetcher' => $assetFetcher,
			'imageProcessor' => $imageProcessor,
			'protection' => $protection,

			'languageToFont' => $languageToFont,
			'scriptToLanguage' => $scriptToLanguage,

			'writer' => $writer,
			'fontWriter' => $fontWriter,
			'metadataWriter' => $metadataWriter,
			'imageWriter' => $imageWriter,
			'formWriter' => $formWriter,
			'pageWriter' => $pageWriter,
			'bookmarkWriter' => $bookmarkWriter,
			'optionalContentWriter' => $optionalContentWriter,
			'colorWriter' => $colorWriter,
			'backgroundWriter' => $backgroundWriter,
			'javaScriptWriter' => $javaScriptWriter,
			'resourceWriter' => $resourceWriter
		];
	}

	public function getServiceIds()
	{
		return [
			'otl',
			'bmp',
			'cache',
			'cssManager',
			'directWrite',
			'fontCache',
			'fontFileFinder',
			'form',
			'gradient',
			'tableOfContents',
			'tag',
			'wmf',
			'sizeConverter',
			'colorConverter',
			'hyphenator',
			'localContentLoader',
			'httpClient',
			'assetFetcher',
			'imageProcessor',
			'protection',
			'languageToFont',
			'scriptToLanguage',
			'writer',
			'fontWriter',
			'metadataWriter',
			'imageWriter',
			'formWriter',
			'pageWriter',
			'bookmarkWriter',
			'optionalContentWriter',
			'colorWriter',
			'backgroundWriter',
			'javaScriptWriter',
			'resourceWriter',
		];
	}

}
