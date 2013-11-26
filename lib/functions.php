<?php

namespace AcrossTime;
use PDO;

function view($name) {
	return VIEW_DIR . $name . VIEW_EXT;
}

function layout($name) {
	return LAYOUT_DIR . $name . VIEW_EXT;
}

function initPDO() {
	
	// Connect to the database
	try {
		require ROOT_DIR .'private.php';

		# MySQL with PDO_MYSQL
		$DBH = new PDO("mysql:host={$private['SQL_HOST']};dbname={$private['SQL_DB']};port={$private['SQL_PORT']};charset=utf8",
			$private['SQL_USER'], $private['SQL_PASS'],
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
		$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
	catch(PDOException $e) {
		unset($private);
		echo $e->getMessage();
	}
	unset($private);
	
	return $DBH;
	
}