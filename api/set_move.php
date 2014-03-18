<?php
	include 'connect.php';
	$db = get_db_handle();
	
	$game_id = $_POST['game_id'];
	$timestamp = $_POST['timestamp'];
	$player = $_POST['player'];
	$move = $_POST['move'];

	try{
		if($timestamp==1){
				$db->beginTransaction();
				$comm = "insert into game values(:game_id, date('now'))";
				$result = $db->prepare($comm);
				$result->execute(array(":game_id" => $game_id));
				$db->commit();
			}

		$db->beginTransaction();
		$comm = "insert into moves values(:game_id, :player, :timestamp, :move)";
		$result = $db->prepare($comm);
		$result->execute(array(":game_id"=> $game_id, ":player"=>$player, ":timestamp"=>$timestamp, ":move"=>$move));
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
