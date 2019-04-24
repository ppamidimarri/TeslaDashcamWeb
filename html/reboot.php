<?php
include "header.php";

system("/home/pi/dash/disconnectCar");
system("/home/pi/dash/disconnectPi");

echo "Rebooting the Pi Zero W... this will take a couple of minutes.";
system("sudo reboot");

include "footer.php";
?>
