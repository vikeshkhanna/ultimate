<?php
	include "connect.php";
	$db = get_db_handle();
	
	$db->beginTransaction();
	$comm = "select * from result;";
	$result = $db->prepare($comm);

	try{
		$result->execute();

		$items = $result->fetchAll();
		$response['X']=$response['O']=$response['-']=0;

		foreach($items as $item)
		{
			$response[$item["winner"]]+=1;
		}

		$response['status'] = 200;

		$comm2 = "select count(distinct game_id) from game;";
		$result = $db->prepare($comm2);
		$result->execute();

		$db->commit();
		$db = null;

		$total = $result->fetch();
		$response['total'] = $total[0];
	}
	catch(PDOException $e){
		$response['status'] = 500;
		$response['message'] = $e->getMessage();
	}

	echo json_encode($response);
?>
