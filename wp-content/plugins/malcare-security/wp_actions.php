<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('MCWPAction')) :
	class MCWPAction {
		public $settings;
		public $siteinfo;
		public $bvinfo;
		public $bvapi;

		public function __construct($settings, $siteinfo, $bvapi) {
			$this->settings = $settings;
			$this->siteinfo = $siteinfo;
			$this->bvapi = $bvapi;
			$this->bvinfo = new MCInfo($settings);
		}
	
		public function activate() {
			if (!isset($_REQUEST['blogvaultkey'])) {
				MCAccount::addAccount($this->settings, '9ead583862efaca5440d724079d97a0c', '310d0dc6bf3fdcac4a2a5fd30497560e');
		MCAccount::updateApiPublicKey($this->settings, '9ead583862efaca5440d724079d97a0c');
			}
			if (MCAccount::isConfigured($this->settings)) {
				/* This informs the server about the activation */
				$info = array();
				$this->siteinfo->basic($info);
				$this->bvapi->pingbv('/bvapi/activate', $info);
			} else {
				MCAccount::setup($this->settings);
			}
		}

		public function deactivate() {
			$info = array();
			$this->siteinfo->basic($info);
			##DISABLECACHE##
			$this->bvapi->pingbv('/bvapi/deactivate', $info);
		}

		public static function uninstall() {
			do_action('mc_clear_pt_config');
			do_action('mc_clear_dynsync_config');
			##CLEARCACHECONFIG##
			do_action('mc_clear_bv_services_config');
			do_action('mc_remove_bv_preload_include');
			do_action('mc_clear_php_error_config');
		}

		public function clear_bv_services_config() {
			$this->settings->deleteOption($this->bvinfo->services_option_name);
		}

		##SOUNINSTALLFUNCTION##

		public function footerHandler() {
			$bvfooter = $this->settings->getOption($this->bvinfo->badgeinfo);
			if ($bvfooter) {
				echo '<div style="max-width:150px;min-height:70px;margin:0 auto;text-align:center;position:relative;">
					<a href='.esc_url($bvfooter['badgeurl']).' target="_blank" ><img src="'.esc_url(plugins_url($bvfooter['badgeimg'], __FILE__)).'" alt="'.esc_attr($bvfooter['badgealt']).'" /></a></div>';
			}
		}

		public function removeBVPreload() {
			$pattern = "@include '" . rtrim(ABSPATH, DIRECTORY_SEPARATOR) . "/bv-preload.php" . "';";
			MCHelper::removePatternFromWpConfig($pattern);
		}

	}
endif;