<?php
namespace WPO\IPS;

use WPO\IPS\Vendor\Dompdf\Dompdf;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\FontSynchronizer' ) ) :

class FontSynchronizer {

	/**
	 * Filename for the dompdf 'font cache'
	 *
	 * @var string
	 */
	public $font_cache_filename = "installed-fonts.json";

	/**
	 * Vanilla instance of dompdf
	 *
	 * @var Dompdf
	 */
	public $dompdf;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->dompdf = new Dompdf();
	}

	/**
	 * Synchronize/update local fonts with plugin fonts, removing duplicates of the dompdf fonts
	 *
	 * @param string $destination path to the local fonts
	 * @return void
	 */
	public function sync( $destination, $merge_with_local = true ) {
		$destination  = trailingslashit( wp_normalize_path( $destination ) );
		$plugin_fonts = $this->get_plugin_fonts();
		$dompdf_fonts = $this->get_dompdf_fonts();
		
		if ( $merge_with_local ) {
			$local_fonts = $this->get_local_fonts( $destination );
		} else {
			$local_fonts = array();
		}

		// we always load dompdf fonts directly from the vendor folder, so delete local copies
		foreach( $dompdf_fonts as $font_name => $filenames ) {
			if ( array_key_exists( $font_name, $local_fonts ) ) {
				$this->delete_font_files( $local_fonts[ $font_name ] );
				unset( $local_fonts[ $font_name ] );
			}
		}

		// update / add plugin fonts in local folder
		foreach( $plugin_fonts as $font_name => $filenames ) {
			$plugin_filenames = array_map( function( $file ) {
				return WPO_WCPDF()->plugin_path() . '/assets/fonts/' . $file;
			}, $filenames );
			$local_filenames           = $this->copy_font_files( $plugin_filenames, $destination );
			$local_fonts[ $font_name ] = $local_filenames;
		}

		// normalize one last time
		$local_fonts = $this->normalize_font_paths( $local_fonts );
		// rebuild font cache file
		$cacheData   = wp_json_encode( $local_fonts, JSON_PRETTY_PRINT );
		// write file with merged cache data
		WPO_WCPDF()->file_system->put_contents( $destination . $this->font_cache_filename, $cacheData, FS_CHMOD_FILE );
	}

	/**
	 * Delete an array of files with all known extensions
	 *
	 * @param array $filenames array of filenames without the extension
	 * @return void
	 */
	public function delete_font_files( $filenames ) {
		$plugin_folder = wp_normalize_path( WPO_WCPDF()->plugin_path() );
		$extensions = array( '.ttf', '.ufm', '.ufm.php', '.afm', '.afm.php' );
		foreach ( $filenames as $filename ) {
			// never delete files in our own plugin folder
			if ( ! empty( $filename ) && false !== strpos( $filename, $plugin_folder ) ) {
				continue;
			}
			foreach ( $extensions as $extension ) {
				$file = $filename . $extension;
				if ( WPO_WCPDF()->file_system->exists( $file ) ) {
					wp_delete_file( $file );
				}
			}
		}
	}

	/**
	 * Copy font files
	 *
	 * @param  array  $filenames   array of filenames without the extension
	 * @param  string $destination path to the local fonts
	 * @return array
	 */
	public function copy_font_files( $filenames, $destination ) {
		$destination = trailingslashit( $destination );
		$extensions = array( '.ttf', '.ufm', '.afm' );
		$local_filenames = array();
		foreach ( $filenames as $variant => $filename ) {
			foreach ( $extensions as $extension ) {
				$file = $filename . $extension;
				if ( WPO_WCPDF()->file_system->is_readable( $file ) ) {
					$local_filename = $destination . basename( $file );
					copy( $file, $local_filename );
				}
			}
			$local_filenames[$variant] = $destination . basename( $filename );
		}
		return $local_filenames;
	}

	/**
	 * Get an array of all known local fonts (stored in the cache file)
	 *
	 * @param string $path path to the local fonts
	 * @return array
	 */
	public function get_local_fonts( $path ) {
		// prepare variables used in the cache list
		$fontDir           = $path;
		$rootDir           = $this->dompdf->getOptions()->getRootDir();
		$cache_file        = trailingslashit( $path ) . $this->font_cache_filename;
		$legacy_cache_file = trailingslashit( $path ) . 'dompdf_font_family_cache.php'; // Dompdf <2.0

		if ( WPO_WCPDF()->file_system->is_readable( $cache_file ) ) {
			$json_data = WPO_WCPDF()->file_system->get_contents( $cache_file );
			$font_data = json_decode( $json_data, true );
		} elseif ( WPO_WCPDF()->file_system->is_readable( $legacy_cache_file ) ) {
			$font_data = include $legacy_cache_file;
			wp_delete_file( $legacy_cache_file );
		} else {
			$font_data = array();
		}

		// if include fails it returns false - we'll log an error in that case
		if ( $font_data === false ) {
			wcpdf_log_error( sprintf( "Could not read font cache file (%s)", $cache_file ), 'critical' );
		}

		// dompdf 1.1.X uses a closure to return the fonts, instead of a plain array (1.0.X and older)
		if ( ! is_array( $font_data ) && is_callable( $font_data ) ) {
			$font_data = $font_data( $fontDir, $rootDir );
		}

		return is_array( $font_data ) ? $this->normalize_font_paths( $font_data ) : array();
	}

	/**
	 * Get all fonts included in dompdf
	 *
	 * @return array
	 */
	public function get_dompdf_fonts() {
		$fonts = $this->dompdf->getFontMetrics()->getFontFamilies();
		return $this->normalize_font_paths( $fonts );
	}

	/**
	 * Get all fonts from the plugin (excluding base path!)
	 *
	 * @return array
	 */
	public function get_plugin_fonts() {
		return array (
			'open sans'   => array (
				'normal'      => 'OpenSans-Normal',
				'bold'        => 'OpenSans-Bold',
				'italic'      => 'OpenSans-Italic',
				'bold_italic' => 'OpenSans-BoldItalic',
			),
			'segoe'       => array (
				'normal'      => 'Segoe-Normal',
				'bold'        => 'Segoe-Bold',
				'italic'      => 'Segoe-Italic',
				'bold_italic' => 'Segoe-BoldItalic',
			),
			'roboto slab' => array (
				'normal'      => 'RobotoSlab-Normal',
				'bold'        => 'RobotoSlab-Bold',
				'italic'      => 'RobotoSlab-Italic',
				'bold_italic' => 'RobotoSlab-BoldItalic',
			),
			'currencies' => array (
				'normal'      => 'currencies',
				'bold'        => 'currencies',
				'italic'      => 'currencies',
				'bold_italic' => 'currencies',
			),
		);
	}

	/**
	 * Apply path normalization to a font list
	 *
	 * @param  array  $fonts array of font entries
	 * @return array  Normalized array of font entries
	 */
	public function normalize_font_paths( $fonts ) {
		foreach( $fonts as $font_name => $filenames ) {
			if ( ! is_array( $filenames ) ) {
				continue;
			}
			foreach ( $filenames as $variant => $filename ) {
				$fonts[$font_name][$variant] = wp_normalize_path( $filename );
			}
		}
		return $fonts;
	}
}

endif; // class_exists
