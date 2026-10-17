<?php
$datei = "LEM-2016-VR-BP.pdf";
$dateiname = basename($datei);
$groesse = filesize($datei);
header("Content-Type: application/force-download");
header("Content-Disposition: attachment; filename=".$dateiname);
header("Content-Length:".$groesse);
readfile($datei);
?> 