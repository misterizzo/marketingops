<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVWPFormHandler')) :

class BVWPFormHandler {
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
		add_filter('wpforms_process_bypass_captcha', '__return_true', PHP_INT_MAX);
	}

	public function bypassEmail() {
		add_filter('wpforms_entry_email', '__return_false', PHP_INT_MAX);
	}
}

endif;