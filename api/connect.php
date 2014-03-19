<?php
	function get_db_handle()
		{
			$dbname = dirname(__FILE__)."/ultimate.new.db"; 

			try {
				$db = new PDO("sqlite:" . $dbname);
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$db->exec("PRAGMA foreign_keys = ON;");
				$db->exec("PRAGMA journal_mode = OFF");
			} 
		 catch (PDOException $e) {
				"SQLite connection failed: " . $e->getMessage();
				throw $e;
			}

		return $db;
	}
?>
