<?php

define ("NOUSER", (1<<1));
define ("USEREXISTS", (1<<2));
define ("PASSWORDMISMATCH", (1<<3));
define ("PASSWORDLENGTH", (1<<4));
define ("NOTEMAILADDR", (1<<5));


define ("DBERR", (1<<4));

function warning ($string) {
	echo "<div style=\"color: #FF0000; font-weight: bold; \">";
			echo $string;
	echo "</div>";
}

function status ($string) {
	echo "<div style=\"color: #000000; font-weight: bold; \">";
			echo $string;
	echo "</div>";
}


?>