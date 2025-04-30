<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVFormTesting')) :

class BVFormTesting {
	private $form_type;
	private $form_id;
	private $spam_bypass;
	private $bypass_params = array();

	public function __construct($params) {
		$this->form_type = isset($params['bv_frm_typ']) ? sanitize_text_field(wp_unslash($params['bv_frm_typ'])) : null;
		$this->form_id = isset($params['bv_frm_id']) ? sanitize_text_field(wp_unslash($params['bv_frm_id'])) : null;
		$this->spam_bypass = isset($params['bv_ignr_spm_plgns']);

		if (isset($params['bv_ignr_frm_cptch'])) {
			$this->bypass_params['should_bypass_captcha'] = true;
		}

		if (isset($params['bv_ignr_eml'])) {
			$this->bypass_params['should_bypass_email'] = true;
		}
	}

	public function init() {
		if ($this->spam_bypass === true) {
			$this->bypassSpamPlugins();
		}

		$handler = $this->getHandler($this->form_type);
		if ($handler === null) {
			return;
		}

		if (empty($this->bypass_params) === false) {
			$handler->bypass();
		}
	}

	private function getHandler($form_type) {
		switch ($form_type) {
		case 'ContactForm7':
			require_once dirname(__FILE__) . '/handlers/contact_form7.php';
			return new BVContactForm7Handler($this->form_id, $this->bypass_params);
		case 'WPForm':
			require_once dirname(__FILE__) . '/handlers/wp_form.php';
			return new BVWPFormHandler($this->form_id, $this->bypass_params);
		case 'NinjaForm':
			require_once dirname(__FILE__) . '/handlers/ninja_form.php';
			return new BVNinjaFormHandler($this->form_id, $this->bypass_params);
		case 'ForminatorForm':
			require_once dirname(__FILE__) . '/handlers/forminator_form.php';
			return new BVForminatorFormHandler($this->form_id, $this->bypass_params);
		case 'GravityForm':
			require_once dirname(__FILE__) . '/handlers/gravity_form.php';
			return new BVGravityFormHandler($this->form_id, $this->bypass_params);
		case 'FormidableForm':
			require_once dirname(__FILE__) . '/handlers/formidable_form.php';
			return new BVFormidableFormHandler($this->form_id, $this->bypass_params);
		default:
			return null;
		}
	}

	private function bypassSpamPlugins() {
		add_action('init', function() {
			global $apbct;
			if (isset($apbct) && is_object($apbct)) {
				$apbct->settings['forms__contact_forms_test'] = 0;
			}
		}, PHP_INT_MAX);

		add_filter('akismet_get_api_key', function () {
			return null;
		}, PHP_INT_MAX);
	}
}

endif;