<?php
	$sample = "O\n".
		"2 2\n".
		"---------\n".
		"---------\n".
		"--X------\n".
		"---------\n".
		"---------\n".
		"---------\n".
		"---------\n".
		"---------\n".
		"---------\n";

	$ai = exec("echo \"".$sample."\" | python ai.py", $output);
	var_dump($output);
?>