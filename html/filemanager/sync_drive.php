<?php

$config = include 'config/config.php';

include 'include/utils.php';

$ftp = ftp_con($config);

if ($_SESSION['RF']["verify"] != "RESPONSIVEfilemanager") {
	response(trans('forbiden') . AddErrorLocation(), 403)->send();
	exit;
}


if (!checkRelativePath($_POST['path']) || strpos($_POST['path'], '/') === 0) {
	response(trans('wrong path'), 400)->send();
	exit;
}


if (strpos($_POST['name'], '/') !== false) {
	response(trans('wrong path' ), 400)->send();
	exit;
}

if ($ftp) {
	$path = $config['ftp_base_url'] . $config['upload_dir'] . $_POST['path'];
} else {
	$path = $config['current_path'] . $_POST['path'];
}

$name = $_POST['name'];
$info = pathinfo($name);

if (!check_extension($info['extension'], $config)) {
	response(trans('wrong extension' . AddErrorLocation()), 400)->send();
	exit;
}

$file_name  = $info['basename'];
$file_ext   = $info['extension'];
$file_path  = $path . $name;

// make sure the file exists
if ($ftp) {
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary");
	header("Content-disposition: attachment; filename=\"" . $file_name . "\"");
	readfile($file_path);
} elseif (is_file($file_path) && is_readable($file_path)) {
	if (!file_exists($path . $name)) {
		response(trans('File_Not_Found' . AddErrorLocation()), 404)->send();
		exit;
	}
	$fileToCopy = realpath($path) . "/" . $name;
	$remoteName = "gdrive:TeslaCam/";
//	$command = "/usr/local/bin/rclone --config /home/pi/.config/rclone/rclone.conf copyto \"" .
	$command = "sudo /usr/bin/rclone --config /tmp/rclone.conf copyto \"" .
		$fileToCopy . "\" \"" . $remoteName . $name . "\"";
	exec($command, $commandOutput, $retval);
	$dialogToShow = "";
	if (strpos($_SERVER['HTTP_USER_AGENT'], "Tesla") == false) {
		$dialogToShow = "dialog.php";
	} else {
		$dialogToShow = "tdialog.php";
	}
	$messageDisplay = "<div style=\"font-family: Arial, Helvetica, sans-serif; font-size: 1.1em\"";
	if ($retval == 0) {
		$messageDisplay = $messageDisplay . "<font color='green'>Success</font>, uploaded " .
			$name . " to " . $remoteName . ". <br> <p> " .
			"<form action=\"". $dialogToShow . "?type=0\"> <button type=\"submit\" style=\"font-family: Arial, Helvetica, sans-serif; " .
			"font-size: 1.1em; background-color: #000; border: none; color: white; padding: 10px 20px; text-align: center; " .
			"text-decoration: none; display: inline-block; margin: 4px 2px;\">Go back " .
			"to video list</button> </form> <br> <p>";
	} else {
		$messageDisplay = $messageDisplay . "<font color='red'>Error</font> uploading file. Ran command: <br> <pre>".
			$command. "</pre> <br>Output:<br> <pre>" . implode("\n", $commandOutput) .
			"</pre><br>Returned: " . $retval;
	}
	$messageDisplay = $messageDisplay . "</div>";
	response($messageDisplay)->send();
} else {
	// file does not exist
	header("HTTP/1.0 404 Not Found");
}

exit;

?>
