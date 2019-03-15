<?php
include "header.php";

echo "<iframe width='100%' height='66%' style='border:none;' src='filemanager/";
if (strpos($_SERVER['HTTP_USER_AGENT'], "Tesla") == false) {
	echo "dialog.php";
} else {
	echo "tdialog.php";
}
echo "?type=0&fldr=/&sort_by=date&descending=1'> ";
echo "</iframe> <br> <p>";

include "footer.php";
?>
