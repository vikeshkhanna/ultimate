<?php
	function get_db_handle()
		{
			$dbname = dirname(__FILE__)."/ultimate2.db"; 

			try {
				$db = new PDO("sqlite:" . $dbname);
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$db->exec("PRAGMA foreign_keys = ON;");
			} 
		 catch (PDOException $e) {
				"SQLite connection failed: " . $e->getMessage();
				throw $e;
			}

		return $db;
	}
?>
