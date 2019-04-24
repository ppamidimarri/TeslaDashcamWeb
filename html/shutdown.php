<?php
include "header.php";

system("/home/pi/dash/disconnectCar");
system("/home/pi/dash/disconnectPi");

system("sudo shutdown now");

include "footer.php";
?>
