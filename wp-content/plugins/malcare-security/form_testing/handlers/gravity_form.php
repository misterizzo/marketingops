<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVGravityFormHandler')) :

class BVGravityFormHandler {
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
		if ($this->form_id === null) {
			return;
		}

		add_filter('gform_pre_validation_' . $this->form_id, function($form) {
			foreach ($form['fields'] as &$field) {
				if ($field->type === 'captcha') {
					$field->visibility = 'hidden';
				}
			}
			return $form;
		}, PHP_INT_MAX);
	}

	public function bypassEmail() {
		add_filter('gform_pre_send_email', function ($email_data) {
			$email_data['abort_email'] = true;
			return $email_data;
		}, PHP_INT_MAX);
	}
}

endif;