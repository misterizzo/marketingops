<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVFormidableFormHandler')) :

class BVFormidableFormHandler {
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
		add_filter('frm_is_field_hidden', '__return_true', PHP_INT_MAX);
	}

	public function bypassEmail() {
		add_filter('frm_send_email', '__return_false', PHP_INT_MAX);
	}
}

endif;