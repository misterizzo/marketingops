<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectLoggerDB_V577')) :
class MCProtectLoggerDB_V577 {
	private $tablename;
	private $bv_tablename;

	const MAXROWCOUNT = 100000;

	function __construct($tablename) {
		$this->tablename = $tablename;
		$this->bv_tablename = MCProtect_V577::$db->getBVTable($tablename);
	}

	public function log($data) {
		if (is_array($data)) {
			if (MCProtect_V577::$db->rowsCount($this->bv_tablename) > MCProtectLoggerDB_V577::MAXROWCOUNT) {
				MCProtect_V577::$db->deleteRowsFromtable($this->tablename, 1);
			}

			MCProtect_V577::$db->replaceIntoBVTable($this->tablename, $data);
		}
	}
}
endif;