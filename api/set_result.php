<?php
	include 'connect.php';
	$db = get_db_handle();
	
	$game_id = $_POST['game_id'];
	$winner = $_POST['winner'];

	$db->beginTransaction();
	$comm = "insert into result values(:game_id, :winner)";
	$result = $db->prepare($comm);

	try{
		$result->execute(array(":game_id"=> $game_id, ":winner"=>$winner));
		$db->commit();
		$db = null;
		$response['status'] = 200;
	}
	catch(PDOException $e){
		$response['status'] = 500;
		$response['message'] = $e->getMessage();
	}

	echo json_encode($response);
?>
