<div class="footer">
<hr> <p> <form action="shutdown.php">
<button type="submit" onclick="shutdownOverlay();">Shut the Pi down now</button>
<button type="submit" onclick="rebootOverlay();" formaction="reboot.php">Reboot the Pi</button> <br> <p>
<button type="submit" formaction="index.php">Go back to main page</button> <br> <p>
<div class="overlay" id="shutdownoverlay"><div class="overlaytext">Shutting down, please wait a few seconds.</div></div>
<div class="overlay" id="rebootoverlay"><div class="overlaytext">Rebooting, please wait until the dashcam icon appears on the car again.</div></div>
<div class="date"> <?php echo strftime("%a %b %-d, %-I:%M:%S %p %Z", time()); ?> </div> </div> </body> </html>
