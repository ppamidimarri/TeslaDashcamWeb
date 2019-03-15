<?php
include "header.php";

echo "<div id='maininfo'>Pi Zero W is connected to WiFi network: ";
$ssid_string = exec("iwlist wlan0 scan | grep ESSID");
$ssid = rtrim(explode('ESSID:"', $ssid_string)[1], '"');
echo "$ssid <br> <p>";

$usblist = exec("ls /mnt/cam", $output, $retval);

if ($retval == 0) {
	if (count($output) == 0) {
		// No output from ls on USB share: likely connected to car
		echo "Drive should be connected to the car. <br> <p>If you don't see the dashcam icon ";
		echo "on the car, click \"Fix connection to car.\" <br> <p>";
		echo "For extra safety, stop dashcam saves by long-pressing the car dashcam before clicking \"Prepare video list.\" <br> <p>";
		echo "<form action='readyShow.php'> <button type='submit'>Prepare video list</button> ";
		echo "<button type='submit' formaction='fixCar.php'>Fix connection to car</button> </form>";
	} else {
		// USB drive is mounted
		echo "Drive is mounted on the Pi. <br> <p>";
		echo "<form action='videoList.php'> <button type='submit'>Show video list</button> ";
		echo "<button type='submit' formaction='readyRecord.php'>Switch to recording mode</button> </form>";
	}
} else {
	echo "Drive status is unknown, <font color='red'>error listing USB share</font>. ";
	echo "Try fixing the drive, and if that doesn't fix it, reboot. <br> <p>";
	echo "<form action='fixCar.php'> <button type='submit'>Fix connection to car</button> </form>";
}
echo "</div>";

include "footer.php";
?>
