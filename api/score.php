<?php
	include "connect.php";
	$db = get_db_handle();
	
	try{
		$db->beginTransaction();

		$comm = "select min(score), winner from result";
		$result = $db->prepare($comm);
		$result->execute();
		$row = $result->fetch();
		$response['best']['score']=$row[0];
		$response['best']['winner']=$row[1];

		$comm = "select max(score), winner from result";
		$result = $db->prepare($comm);
		$result->execute();
		$row = $result->fetch();
		$response['worst']['score']=$row[0];
		$response['worst']['winner']=$row[1];

		$db->commit();
		$response['status']=200;
	}
	catch(PDOException $e){
		$response['status'] = 500;
		$response['message'] = $e->getMessage();
	}

	echo json_encode($response);
?>
