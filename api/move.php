<?php
	$sample = "O\n".
		"-1 -1\n".
		"---------\n".
		"---------\n".
		"--X------\n".
		"---------\n".
		"---------\n".
		"---------\n".
		"---------\n".
		"---------\n".
		"---------\n";

	$state = $_GET["state"];
	$ai = exec("echo \"".$state."\" | python ai.py", $output);
	$response["move"] = $output[0];
	echo json_encode($response);
?>
