<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectLogger_V577')) :
require_once dirname( __FILE__ ) . '/logger/fs.php';
require_once dirname( __FILE__ ) . '/logger/db.php';

class MCProtectLogger_V577 {
	private $log_destination;

	const TYPE_FS = 0;
	const TYPE_DB = 1;

	function __construct($name, $type = MCProtectLogger_V577::TYPE_DB) {
		if ($type == MCProtectLogger_V577::TYPE_FS) {
			$this->log_destination = new MCProtectLoggerFS_V577($name);
		} else {
			$this->log_destination = new MCProtectLoggerDB_V577($name);
		}
	}

	public function log($data) {
		$this->log_destination->log($data);
	}
}
endif;