<?php
$datei = "LEM-2016-ER-Erg.pdf";
$dateiname = basename($datei);
$groesse = filesize($datei);
header("Content-Type: application/force-download");
header("Content-Disposition: attachment; filename=".$dateiname);
header("Content-Length:".$groesse);
readfile($datei);
?> 