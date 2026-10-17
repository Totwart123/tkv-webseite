<?php
$datei = "16-05-08-Top12-Bahnplan.pdf";
$dateiname = basename($datei);
$groesse = filesize($datei);
header("Content-Type: application/force-download");
header("Content-Disposition: attachment; filename=".$dateiname);
header("Content-Length:".$groesse);
readfile($datei);
?> 