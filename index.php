<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>RACHEL - HOME</title>
<link rel="stylesheet" href="css/normalize-1.1.3.css">
<link rel="stylesheet" href="css/style.css">
<!--[if IE]><script type="text/javascript" src="css3-multi-column.min.js"></script><![endif]-->
<script src="js/jquery-1.10.2.min.js"></script>
<script src="js/jquery-ui-1.10.4.custom.min.js"></script>
</head>

<body>
<div id="rachel" style="position: relative;">
    Rachel
    <div id="ip">
    <?php
        # some notes to prevent future regression:
        # the PHP suggested gethostbyname(gethostname())
        # brings back the unhelpful 128.0.0.1 on RPi systems,
        # as well as slowing down some Windows installations
        # with a DNS lookup. $_SERVER["SERVER_ADDR"] will just
        # display what's in the user's address bar, so also
        # not useful - using ifconfig/ipconfig is probably
        # the way to go, but may require some tweaking

        echo "<b>Server Address</b><br>\n";
        if (preg_match("/^win/i", PHP_OS)) {
            # under windows it's ipconfig
            $output = shell_exec("ipconfig");
            preg_match("/IPv4 Address.+?: (.+)/", $output, $match);
            if (isset($match[1])) { echo "$match[1]<br>\n"; }
        } else if (preg_match("/^darwin/i", PHP_OS)) {
            # OSX is unix, but it's a little different
            exec("/sbin/ifconfig", $output);
            preg_match("/en0.+?inet (.+?) /", join("", $output), $match);
            if (isset($match[1])) { echo "$match[1]<br>\n"; }
        } else {
            # most likely linux based - so ifconfig should work
            exec("/sbin/ifconfig", $output);
            preg_match("/eth0.+?inet addr:(.+?) /", join("", $output), $match);
            if (isset($match[1])) { echo "LAN: $match[1]<br>\n"; }
            preg_match("/wlan0.+?inet addr:(.+?) /", join("", $output), $match);
            if (isset($match[1])) { echo "WIFI: $match[1]<br>\n"; }
        }

    ?>
    <a href="admin.php" style="position: absolute; font-size: small; bottom: 6px; right: 8px; color: #999;">admin</a>
    </div>
</div>

<div class="menubar cf">
    <ul>
    <li><a href="index.php">HOME</a></li>
    <li><a href="about.html">ABOUT</a></li>
    </ul>
    
</div>

<div id="content">

<?php

    require_once("common.php");
    
    $fsmods = getmods_fs();

    if ($fsmods) {

        # next we go to the database
        try {

            $db = getdb();

            # find the sort order and visibility state
            $rv = $db->query("SELECT * FROM modules");
            if ($rv) {
                $dbmods = array();
                while ($row = $rv->fetchArray()) {
                    $dbmods[$row['moddir']] = $row;
                    if (isset($fsmods[$row['moddir']])) {
                        $fsmods[$row['moddir']]['position'] = $row['position'];
                        $fsmods[$row['moddir']]['hidden'] = $row['hidden'];
                    }
                }
            }

        } catch (Exception $ex) { }

# catch (Exception $ex) {

#            echo "<h2>" . $ex->getMessage() . "</h2>" .

#                 "You may need to change permissions on the RACHEL " .

#                 "root directory using: chmod 777";

#        }

        # custom sorting function in common.php
        uasort($fsmods, 'bypos');

        # whether or not we were able to get anything
        # from the DB, we show what we found in the filesystem
        foreach (array_values($fsmods) as $mod) {
            if ($mod['hidden']) { continue; }
            $dir  = $mod['dir'];
            include "$mod[dir]/index.htmlf";
        }

    } else {

        echo "<h2>No modules found.</h2>\n";
        echo "Please check your modules directory.\n";

    }

?>

</div>

<div class="menubar cf" style="margin-bottom: 80px;">
    <ul>
    <li><a href="index.php">HOME</a></li>
    <li><a href="about.html">ABOUT</a></li>
    </ul>
    <div id="footer_right">RACHEL - NOV 2015</div>
</div>

</body>
</html>
