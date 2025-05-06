<?php
/**
 * TinyMCE shortcode class file.
 *
 * @since 1.0.5
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Shortcodes;

use LearnDash_Shortcodes_TinyMCE;

/**
 * TinyMCE class.
 *
 * @since 1.0.5
 */
class TinyMCE extends LearnDash_Shortcodes_TinyMCE {
	/**
	 * Load admin scripts.
	 *
	 * @since 1.0.5
	 *
	 * @return void
	 */
	public function load_admin_scripts(): void {
		global $learndash_assets_loaded;

		wp_enqueue_style(
			'sfwd-module-style',
			LEARNDASH_LMS_PLUGIN_URL . 'assets/css/sfwd_module' . learndash_min_asset() . '.css',
			array(),
			LEARNDASH_SCRIPT_VERSION_TOKEN
		);
		$learndash_assets_loaded['styles']['sfwd-module-style'] = __FUNCTION__;

		wp_enqueue_script(
			'sfwd-module-script',
			LEARNDASH_LMS_PLUGIN_URL . 'assets/js/sfwd_module' . learndash_min_asset() . '.js',
			array( 'jquery' ),
			LEARNDASH_SCRIPT_VERSION_TOKEN,
			false
		);
		$learndash_assets_loaded['scripts']['sfwd-module-script'] = __FUNCTION__;

		$data = array();
		if ( ! isset( $data['ajaxurl'] ) ) {
			$data['ajaxurl'] = admin_url( 'admin-ajax.php' );
		}

		$data = array( 'json' => wp_json_encode( $data ) );
		wp_localize_script( 'sfwd-module-script', 'sfwd_data', $data );

		wp_enqueue_style(
			'learndash_admin_shortcodes_style',
			LEARNDASH_LMS_PLUGIN_URL . 'assets/css/learndash-admin-shortcodes' . learndash_min_asset() . '.css',
			array(),
			LEARNDASH_SCRIPT_VERSION_TOKEN
		);
		wp_style_add_data( 'learndash_admin_shortcodes_style', 'rtl', 'replace' );
		$learndash_assets_loaded['styles']['learndash_shortcodes_admin_style'] = __FUNCTION__;

		$this->shortcodes_assets_init();

		wp_enqueue_script(
			'learndash_admin_shortcodes_script',
			LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-admin-shortcodes' . learndash_min_asset() . '.js',
			array( 'jquery' ),
			LEARNDASH_SCRIPT_VERSION_TOKEN,
			false
		);
		$learndash_assets_loaded['styles']['learndash_admin_shortcodes_script'] = __FUNCTION__;
		wp_localize_script( 'learndash_admin_shortcodes_script', 'learndash_admin_shortcodes_assets', $this->learndash_admin_shortcodes_assets );

		if ( 'jQuery-dialog' === $this->learndash_admin_shortcodes_assets['popup_type'] ) {
			// Hold until after LD 3.0 release.
			learndash_admin_settings_page_assets();
		}
	}

	/**
	 * Output inline editor styles.
	 *
	 * @since 1.0.5
	 *
	 * @return void
	 */
	public function inline_editor_styles(): void {
		if ( has_filter( 'learndash-element-add-inline-editor_styles' ) ) {
			_deprecated_hook( 'learndash-element-add-inline-editor_styles', '1.0.5', 'learndash_element_add_inline_editor_styles' );

			/**
			 * Deprecated filter hook to add inline editor styles.
			 *
			 * @deprecated 1.0.5
			 *
			 * @since 1.0.0
			 *
			 * @param bool $add Whether to add inline styles or not. Default true.
			 */
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Deprecated filter hook.
			$add_inline_styles = apply_filters( 'learndash-element-add-inline-editor_styles', true );
		}

		/**
		 * Filter hook whether to add inline styles.
		 *
		 * @since 1.0.5
		 *
		 * @param bool $add Whether to add inline styles. Default true.
		 */
		$add_inline_styles = apply_filters( 'learndash_element_add_inline_editor_styles', true );

		if ( $add_inline_styles ) {
			ob_start();
			?>
			.wp-dialog.ld-shortcodes #learndash_shortcodes_wrap h2, .wp-dialog.ld-shortcodes #learndash_shortcodes_wrap h3 {
				font-size: 1.3em;
				margin: 1em 0;
			}

			.wp-dialog.ld-shortcodes .ui-dialog-title, .wp-dialog.ld-shortcodes #learndash_shortcodes_wrap h2, .wp-dialog.ld-shortcodes #learndash_shortcodes_wrap h3, .wp-dialog.ld-shortcodes #learndash_shortcodes_wrap p {
				color: #23282d;
			}

			.wp-dialog.ld-shortcodes #learndash_shortcodes_wrap input[type="text"], .wp-dialog.ld-shortcodes #learndash_shortcodes_wrap input[type="number"] {
				background-color: #fff;
				color: #32373c;
			}

			.wp-dialog.ld-shortcodes #learndash_shortcodes_wrap select {
				font-size: 14px;
				line-height: 2;
				color: #32373c;
				cursor: pointer;
			}

			.wp-dialog.ld-shortcodes #learndash_shortcodes_wrap fieldset {
				border: 0;
				padding: 0;
				margin: 0;
			}

			.wp-dialog.ld-shortcodes #learndash_shortcodes_wrap input[type="number"].small-text {
				width: 65px;
				padding-right: 0;
			}

			.wp-dialog.ld-shortcodes #learndash_shortcodes_wrap input.button-primary {
				background: #0071a1;
				border-color: #0071a1;
				color: #fff;
			}

			.wp-dialog.ld-shortcodes a {
				color: #0071a1;
			}
			<?php
			$ld_elementor_inline_editor_css = ob_get_clean();

			if ( has_filter( 'learndash-element-filter-inline-editor_styles' ) ) {
				_deprecated_hook( 'learndash-element-filter-inline-editor_styles', '1.0.5', 'learndash_element_filter_inline_editor_styles' );

				/**
				 * Deprecated filter hook to add inline style.
				 *
				 * @deprecated 1.0.5
				 *
				 * @since 1.0.0
				 *
				 * @param string $ld_elementor_inline_editor_css CSS inline styles.
				 */
				// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Deprecated filter hook.
				$ld_elementor_inline_editor_css = apply_filters( 'learndash-element-filter-inline-editor_styles', $ld_elementor_inline_editor_css );
			}

			/**
			 * Filter hook to add inline styles.
			 *
			 * @since 1.0.5
			 *
			 * @param string $ld_elementor_inline_editor_css Inline CSS styles.
			 */
			$ld_elementor_inline_editor_css = apply_filters( 'learndash_element_filter_inline_editor_styles', $ld_elementor_inline_editor_css );

			if ( ! empty( $ld_elementor_inline_editor_css ) ) {
				wp_add_inline_style( 'learndash_admin_shortcodes_style', $ld_elementor_inline_editor_css );
			}
		}
	}
}
