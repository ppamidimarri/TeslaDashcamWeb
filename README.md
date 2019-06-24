# TeslaDashcamWeb

## Introduction
This code sets up a website using nginx to display the footage from a Tesla dashcam, using a Raspberry Pi Zero W as the USB drive. It uses the [teslausb project](https://github.com/cimryan/teslausb) for creating the USB drives on the Pi. 

Note: The Tesla car browser doesn't load local websites. So if you want TeslaDashcamWeb to load on the car browser, you will need to perform the steps mentioned [here](https://github.com/ppamidimarri/TeslaDashcamWeb/issues/7#issuecomment-505220969) after you have TeslaDashcamWeb working. 

## Hardware
* Raspberry Pi Zero W
* Micro-USB to USB cable, plugged into one of the Tesla's USB ports
* Tesla car!
* If you want to access this on your car browser, another Raspberry Pi (3B+ works well for me) to run an nginx reverse proxy server

## Software
* [Raspbian Stretch Lite](https://downloads.raspberrypi.org/raspbian_lite_latest)
* [nginx web server](https://www.nginx.com/resources/wiki/)
* [rclone](https://rclone.org/)
* [Responsive File Manager](https://www.responsivefilemanager.com/)
* If you want to access this on your car browser, an update client from a dynamic DNS service to run on your nginx reverse proxy server

## Instructions

**Load a Pi Zero W with Raspbian Stretch Lite and get SSH going**

1. [Load Raspbian Stretch Lite on a Micro SD card](https://projects.raspberrypi.org/en/projects/raspberry-pi-setting-up)
2. On your computer, install [Notepad++](https://notepad-plus-plus.org/) or similar text editor that saves Unix-style line endings correctly
3. In the `boot` drive of the Micro SD card, open `config.txt` in Notepad++ and add a new line at the end with this content and save it: `dtoverlay=dwc2`
4. Open `cmdline.txt`, make these changes and save it:
      * Before `rootwait`, add `modules-load=dwc2,g_ether `
      * Remove the ` init=/usr/lib/raspi-config/init_resize.sh` at the end
5. Add an empty file called `ssh` (no extension) in the same folder
6. Safely eject the Micro SD card from your computer
7. Insert the Micro SD card into a Pi and boot it up 
8. Install [Putty](https://www.putty.org/) on your computer and try to connect to `raspberrypi.local`; you may need to install [Bonjour for Windows](https://support.apple.com/downloads/bonjour_for_windows) if that doesn't work
9. Login in Putty using the id `pi` and password `raspberry`
10. Change password, locale, timezone, etc. and enter your WiFi credentials using `sudo raspi-config`; do not reboot when exiting `raspi-config`
11. Check that your Pi is connnecting to WiFi using `sudo wpa_cli -i wlan0 reconfigure`
12. Confirm you have an IP address with `ifconfig wlan0`
13. Edit `/boot/cmdline.txt` with your favorite editor using `sudo` to delete `g_ether` from the `modules-load` phrase so it looks like `modules-load=dwc2`
14. Reboot with `sudo reboot`
15. Use Putty and login with your new password to the `pi` account, you now have a working SSH connection

**Update the Pi and load required software**

1. `sudo apt update`
2. `sudo apt upgrade`
3. `sudo apt install nginx php-fpm php-mbstring git`

**Configure php-fpm**

`sudo nano /etc/php/7.0/fpm/php.ini` and make these changes:
* Replace the line that contains `cgi.fix_pathinfo=1` with `cgi.fix_pathinfo=0`
* Replace the line that contains `session.save_path` with `session.save_path = /tmp/php/sessions`

**Configure nginx**

1. `sudo nano /etc/nginx/sites-available/default`, edit these lines to look like:
```
      index index.html index.htm index.php index.nginx-debian.html;
      location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            # With php-fpm (or other unix sockets):
            fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
      }
 ```

2. `sudo nano /etc/nginx/nginx.conf`, edit the log location lines:
```
      access_log /tmp/log/nginx/access.log;
      error_log /tmp/log/nginx/error.log;	
```
3. `mkdir -p /tmp/log/nginx`
4. `sudo nginx -t`
5. Restart nginx with `sudo /etc/init.d/nginx restart` 
6. On your computer browser, navigate to the LAN IP of your Pi, you should see the default nginx welcome page 

**Configure [rclone](https://rclone.org/)**

1. `sudo -i`
2. Install rclone with `curl -L https://raw.github.com/pageauc/rclone4pi/master/rclone-install.sh | bash`
3. `rclone config` and follow prompts; recommended:
     * Name the drive as `gdrive`
     * Use `drive` for Google Drive
     * Set up scope as `3` for `drive.file`
4. `exit` (from interactive sudo session)

**Update sudoers and rc.local**

1. `sudo -i` 
2. `echo "www-data ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/020_www-data-nopasswd`
3. Check the location of your rclone configuration file: try `ls /root/.config/rclone/rclone.conf` and `ls /root/.rclone.conf` and see which one it is. If your file is not at `/root/.config/rclone/rclone.conf`, you need to replace this with the correct location of that .conf file in the next step when updating `/etc/rc.local`
3. `nano /etc/rc.local` and update the first line of the file to `#!/bin/bash -e`, then add this block of code just before the line `exit 0`
```
LOGFILE=/tmp/rc.local.log

function log () {
	echo -n "$( date )" >> "$LOGFILE"
	echo -n ": " >> "$LOGFILE"
	echo "$1" >> "$LOGFILE"
}

log "Running fsck..."
/sbin/fsck /mnt/cam -- -a >> "$LOGFILE" 2>&1 || echo ""
log "Running modprobe..."
/sbin/modprobe g_mass_storage >> "$LOGFILE" 2>&1
log "Preparing temp files..."
/bin/cp /root/.config/rclone/rclone.conf /tmp/rclone.conf >> "$LOGFILE" 2>&1
/bin/chmod 644 /tmp/rclone.conf >> "$LOGFILE" 2>&1
/bin/mkdir -p /var/log/nginx >> "$LOGFILE" 2>&1
/bin/mkdir -p /tmp/php/sessions >> "$LOGFILE" 2>&1
/bin/chown www-data:pi /tmp/php/sessions >> "$LOGFILE" 2>&1
/bin/mkdir -p /tmp/log/nginx >> "$LOGFILE" 2>&1
log "Starting nginx..."
/usr/sbin/service nginx start >> "$LOGFILE" 2>&1
log "All done"
```
4. `exit` (from interactive sudo session)

**Load website scripts**

1. `mkdir /home/pi/dash`
2. Set up some permissions:
      * `sudo chown -R pi:www-data /home/pi/dash`
      * `sudo chown -R pi:www-data /var/www/html`
3. `cd /home/pi/dash`
4. Download the scripts with `git clone https://github.com/ppamidimarri/TeslaDashcamWeb`
5. Move the website scripts with `cp -r /home/pi/dash/TeslaDashcamWeb/html/ /var/www/`
6. Move python scripts with `mv /home/pi/dash/TeslaDashcamWeb/*connect* /home/pi/dash`
7. More permissions:
      * `chmod +x /home/pi/dash/*`
8. On your computer browser, navigate to the LAN IP of your Pi, you should see the Tesla Dashcam welcome page

**Create USB drives on the Pi**

This section is work-in-progress as the teslausb project hasn't merged in latest changes needed to work with Tesla software versions 2019.5.1 and newer. For now, follow [these instructions](https://github.com/cimryan/teslausb/issues/119#issuecomment-473346734) as root with `sudo -i` to get it working. 

Once you run the script fully and reboot the Pi, you should see the drive CAM automatically mount on your laptop (and MUSIC if you set that up). The CAM drive should have a folder called `TeslaCam` in it. If you then SSH into your Pi, you should see that your root filesystem is now read-only. After this point, if you need to change anything on the Pi, you can do it by running `sudo mount -o remount,rw /` and that will remount the root filesystem until the next reboot. 

**Create some symlinks for the dashcam clips on the Pi**

1. `sudo mount -o remount,rw /`
2. `cd /var/www/html`
3. `mount /mnt/cam`
3. `ln -s /mnt/cam/TeslaCam .`
4. `ls /mnt/cam/TeslaCam` and see if folders `RecentClips` and `SavedClips` exist; if they don't create them with `mkdir /mnt/cam/TeslaCam/RecentClips` and `mkdir /mnt/cam/TeslaCam/SavedClips`
4. `mkdir thumbs`
5. `cd thumbs`
6. `ln -s /mnt/cam/TeslaCam/RecentClips .` 
7. `ln -s /mnt/cam/TeslaCam/SavedClips .` 
4. `chmod 775 /var/www/html/thumbs/`
8. `umount /mnt/cam`
9. `sudo shutdown now`

Congratulations, you are done now! Plug your Pi into the Tesla and wait for the dashcam icon to appear. Once it appears, open the browser in the car and navigate to the hostname/IP of the Pi. 

## [Screenshots](https://imgur.com/a/JcjnGYA)

**1. Front page of the website**

![Main page](https://i.imgur.com/3kkqZfe.png)

**2. View of folders within the TeslaCam folder**

![Folder view](https://i.imgur.com/0Jm7qqu.png)

**3. View of clips available**, with options to Preview, Rename, Delete or Upload them to Google Drive

![Folder view](https://i.imgur.com/3UusX2P.png)

**4. This is how Preview looks**

![Previewing a clip](https://i.imgur.com/hhtgNjC.png)

**5. Uploading a clip to Google Drive**

![Uploading...](https://i.imgur.com/um2Pbmr.png)

**6. Upload status after it is done**

![Upload done](https://i.imgur.com/O0NRdr8.png)
