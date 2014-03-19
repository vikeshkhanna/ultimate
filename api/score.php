<?php
	include "connect.php";
	$db = get_db_handle();
	
	try{
		$db->beginTransaction();

		$comm = "select min(score), max(score) from result where winner=:winner;";
		$result = $db->prepare($comm);
		$result->execute(array(":winner"=>"X"));
		$row = $result->fetch();
		$response['best']=$row[0];
		$response['worst']=$row[1];
	}
	catch(PDOException $e){
		$response['status'] = 500;
		$response['message'] = $e->getMessage();
	}

	echo json_encode($response);
?>
