<?php
include "header.php";
?>

<div id="maininfo">Fixing connection to the car, please wait for the dashcam icon to appear on the car.  <br> <p>

<?php system("/home/pi/dash/connectCar"); ?>

</div>

<?php
include "footer.php";
?>
