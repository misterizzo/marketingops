<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVForminatorFormHandler')) :

class BVForminatorFormHandler {
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
		if (defined('WP_PLUGIN_DIR')) {
			$abstract_front_action_file_path = WP_PLUGIN_DIR . '/forminator/library/abstracts/abstract-class-front-action.php';
			$front_action_file_path = WP_PLUGIN_DIR . '/forminator/library/modules/custom-forms/front/front-action.php';
			if (is_file($abstract_front_action_file_path) && is_readable($abstract_front_action_file_path) &&
					is_file($front_action_file_path) && is_readable($front_action_file_path)) {
				require_once $abstract_front_action_file_path;
				require_once $front_action_file_path;
				if (class_exists('Forminator_CForm_Front_Action')) {
					Forminator_CForm_Front_Action::$hidden_fields[] = "bv-stripe-";
				}
			}
		}
	}

	public function bypassEmail() {
		foreach (['poll', 'quiz', 'form'] as $type) {
			add_filter("forminator_{$type}_get_admin_email_recipients", function() {
				return [];
			}, PHP_INT_MAX);
		}
	}
}

endif;