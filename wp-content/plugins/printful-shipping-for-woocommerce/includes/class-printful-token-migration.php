<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Printful_Token_Migration {

	const MIGRATION_WAITING = '0';
	const MIGRATION_RUNNING = '1';
	const MIGRATION_FAILED = '-1';

	const OPTION_NAME_MIGRATION = 'pf-migration-in-progress';

	public static $_instance;

	private $integration;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		self::$_instance = $this;

		$this->integration = Printful_Integration::instance();
	}

	public static function init() {
		$instance = self::instance();

		if ($instance->shouldMigrate()) {
			try {
				$instance->startMigration();
				$instance->migrate();
			} catch (PrintfulApiException $exception) {
				if ($exception->isNotAuthorizedError()) {
					$instance->markMigrationFailed();

					return;
				}

				$instance->restartMigration();
				// allow migration to silently fail
			}
		}
	}

	public function shouldMigrate() {
		$restKey = $this->integration->get_option( 'printful_key' );
		$oauthKey = $this->integration->get_option( 'printful_oauth_key' );

		return $restKey && !$oauthKey && !$this->isMigrationRunning() && !$this->hasMigrationFailed();
	}

	public function migrate() {

		$client = $this->integration->get_client();

		$response = $client->post('integration-plugin/get-o-auth-credentials');

		if (isset($response['token'])) {
			$options = get_option( 'woocommerce_printful_settings', array() );

			$options['printful_oauth_key'] = $response['token'];

			$this->integration->update_settings( $options );

			$oauth_client = $this->integration->get_client();

			$response = $oauth_client->post('integration-plugin/finalize-migration');

			if (isset($response['status']) && 1 === $response['status']) {
				unset($options['printful_key']);
				$this->integration->update_settings( $options );
			}
		}
	}

	protected function isMigrationRunning() {
		return get_option(self::OPTION_NAME_MIGRATION, 0) === self::MIGRATION_RUNNING;
	}

	protected function startMigration() {
		update_option(self::OPTION_NAME_MIGRATION, self::MIGRATION_RUNNING);
	}

	protected function restartMigration() {
		update_option(self::OPTION_NAME_MIGRATION, self::MIGRATION_WAITING);
	}

	protected function markMigrationFailed() {
		update_option(self::OPTION_NAME_MIGRATION, self::MIGRATION_FAILED);
	}

	protected function hasMigrationFailed() {
		return get_option(self::OPTION_NAME_MIGRATION, 0) === self::MIGRATION_FAILED;
	}
}
