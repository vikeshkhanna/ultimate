<?php
	include "connect.php";
	$db = get_db_handle();
	

	try{
		$db->beginTransaction();
		$comm = "select winner, count(*) cnt from result group by winner;";
		$result = $db->prepare($comm);
		$result->execute();
		$items = $result->fetchAll();

		foreach($items as $item)
		{
			$response[$item["winner"]]=$item["cnt"];
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
