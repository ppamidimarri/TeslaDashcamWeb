<?php
include "header.php";

echo "<div id='maininfo'>Unmounting the drive from the Pi... ";

exec("/home/pi/dash/disconnectPi", $output1, $ret1);
if ($ret1 == 0) {
	echo "<font color='green'>done</font>. <br> <p>";
} else {
	echo "<font color='red'>FAILED</font>:<br> <p> <pre>";
	echo implode("\n", $output1);
	echo "</pre> <br> <p>";
}

echo "Connecting the drive to the car... ";
exec("/home/pi/dash/connectCar", $output2, $ret2);
if ($ret2 == 0) {
	echo "<font color='green'>done</font>. <br> <p>";
} else {
	echo "<font color='red'>FAILED</font>:<br> <p> <pre>";
	echo implode("\n", $output2);
	echo "</pre> <br> <p>";
}
echo "</div>";

include "footer.php";
?>
