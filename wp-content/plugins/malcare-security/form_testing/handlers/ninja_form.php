<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVNinjaFormHandler')) :

class BVNinjaFormHandler {
	private $form_id;
	private $bypass_params = array();

	public function __construct($form_id, $bypass_params) {
		$this->form_id = $form_id;
		$this->bypass_params = $bypass_params;
	}

	public function bypass() {
		if (array_key_exists('should_bypass_captcha', $this->bypass_params)) {
			$this->bypassCaptcha();
		}

		if (array_key_exists('should_bypass_email', $this->bypass_params)) {
			$this->bypassEmail();
		}
	}

	public function bypassCaptcha() {
		add_filter('ninja_forms_pre_validate_field_settings', function ($field_settings) {
			if (isset($field_settings['type']) && in_array($field_settings['type'], ['recaptcha', 'spam'], true)) {
				$field_settings['type'] = null;
			}
			return $field_settings;
		}, PHP_INT_MAX);

		add_filter('ninja_forms_run_action_type_recaptcha', '__return_false', PHP_INT_MAX);
	}

	public function bypassEmail() {
		add_filter('ninja_forms_action_email_send', '__return_true', PHP_INT_MAX);
	}
}

endif;