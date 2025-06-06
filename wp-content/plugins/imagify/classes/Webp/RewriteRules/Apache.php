<?php
declare(strict_types=1);

namespace Imagify\Webp\RewriteRules;

use Imagify\WriteFile\AbstractApacheDirConfFile;

/**
 * Add and remove rewrite rules to the .htaccess file to display WebP images on the site.
 *
 * @since 1.9
 */
class Apache extends AbstractApacheDirConfFile {

	/**
	 * Name of the tag used as block delemiter.
	 *
	 * @var string
	 * @since 1.9
	 */
	const TAG_NAME = 'Imagify: rewrite rules for webp';

	/**
	 * Get unfiltered new contents to write into the file.
	 *
	 * @since  1.9
	 * @source https://github.com/vincentorback/WebP-images-with-htaccess
	 *
	 * @return string
	 */
	protected function get_raw_new_contents() {
		$extensions = $this->get_extensions_pattern();
		$extensions = str_replace( '|webp', '', $extensions );
		$home_root  = wp_parse_url( home_url( '/' ) );
		$home_root  = $home_root['path'];

		return trim(
			'
<IfModule mod_setenvif.c>
	# Vary: Accept for all the requests to jpeg, png, and gif.
	SetEnvIf Request_URI "\.(' . $extensions . ')$" REQUEST_image
</IfModule>

<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase ' . $home_root . '

	# Check if browser supports WebP images.
	RewriteCond %{HTTP_ACCEPT} image/webp

	# Check if WebP replacement image exists.
	RewriteCond %{REQUEST_FILENAME}.webp -f

	# Serve WebP image instead.
	RewriteRule (.+)\.(' . $extensions . ')$ $1.$2.webp [T=image/webp,NC]
</IfModule>

<IfModule mod_headers.c>
	Header append Vary Accept env=REQUEST_image
</IfModule>'
		);
	}
}
