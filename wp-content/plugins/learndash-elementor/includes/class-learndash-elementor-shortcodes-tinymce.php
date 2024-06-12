<?php

if ( ( class_exists( 'LearnDash_Shortcodes_TinyMCE' ) ) && ( ! class_exists( 'LearnDash_Elementor_Shortcodes_TinyMCE' ) ) ) {
	class LearnDash_Elementor_Shortcodes_TinyMCE extends LearnDash_Shortcodes_TinyMCE {
		public function __construct() {
		}
		public function load_admin_scripts() {
			global $typenow, $pagenow;
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

		public function inline_editor_styles() {
			if ( apply_filters( 'learndash-element-add-inline-editor_styles', true ) ) {
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

				$ld_elementor_inline_editor_css = apply_filters( 'learndash-element-filter-inline-editor_styles', $ld_elementor_inline_editor_css );
				if ( ! empty( $ld_elementor_inline_editor_css ) ) {
					wp_add_inline_style( 'learndash_admin_shortcodes_style', $ld_elementor_inline_editor_css );
				}
			}
		}
	}

	add_action( 'elementor/editor/before_enqueue_scripts', function() {
		$learndash_elementor_shortcodes_tinymce = new LearnDash_Elementor_Shortcodes_TinyMCE();
		$learndash_elementor_shortcodes_tinymce->load_admin_scripts();

		$learndash_elementor_shortcodes_tinymce->inline_editor_styles();
	});
}