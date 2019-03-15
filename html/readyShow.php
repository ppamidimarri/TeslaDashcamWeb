<?php
include "header.php";
?>

<div id="maininfo">Disconnecting the drive from the car...

<?php
exec("/home/pi/dash/disconnectCar", $output1, $ret1);
if ($ret1 == 0) {
?>	<font color='green'>done</font>.
<?php } else {
?>	<font color='red'>FAILED</font>:<br> <p> <pre>
<?php	echo implode("\n", $output1);
?>	</pre>
<?php } ?>

<br> <p> Mounting the drive on the Pi...
<?php
exec("/home/pi/dash/connectPi", $output2, $ret2);
if ($ret2 == 0) {
?>	<font color='green'>done</font>.
<?php } else {
?>	<font color='red'>FAILED</font>:<br> <p> <pre>
<?php	echo implode("\n", $output2);
?>	</pre>
<?php }

if (($ret1 == 0) && ($ret2 == 0)) {
?>	<br> <p> <form action='videoList.php'> <button type='submit'>Show video list</button> </form>
<?php } ?>
</div>

<?php
include "footer.php";
?>
