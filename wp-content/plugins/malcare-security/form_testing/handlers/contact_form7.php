<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVContactForm7Handler')) :

class BVContactForm7Handler {
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

	private function bypassCaptcha() {
		add_filter('wpcf7_skip_spam_check', '__return_true', PHP_INT_MAX);
	}

	private function bypassEmail() {
		add_action('wpcf7_before_send_mail', function ($contact_form, &$abort) {
			$abort = true;
		}, PHP_INT_MAX, 2);
	}
}

endif;