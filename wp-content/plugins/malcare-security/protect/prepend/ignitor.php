<?php
if (!defined('MCDATAPATH')) exit;

if (defined('MCCONFKEY')) {
	require_once dirname( __FILE__ ) . '/../protect.php';

	MCProtect_V577::init(MCProtect_V577::MODE_PREPEND);
}