<?php
	include "connect.php";
	$db = get_db_handle();
	
	$db->beginTransaction();
	$comm = "select * from result;";
	$result = $db->prepare($comm);

	try{
		$result->execute();
		$db->commit();
		$db = null;
		
		$items = $result->fetchAll();
		$response['X']=$response['O']=$response['-']=0;

		foreach($items as $item)
		{
			$response[$item["winner"]]+=1;
		}

		$response['status'] = 200;
	}
	catch(PDOException $e){
		$response['status'] = 500;
		$response['message'] = $e->getMessage();
	}

	echo json_encode($response);
?>
