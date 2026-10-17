<?
include ("../header.inc.php");
?>
<table width="100%" body bgcolor="ffff74" border="0">
<tr height="100"><td width="75%"><h3><font color="blue">Archiv Ansetzungshefte TKV / Durchführungsbestimmungen TKV</font></h3>Frank Breitbarth<br>E-mail: <a href="mailto:webmaster@tkv-kegeln.de">webmaster @ tkv-kegeln.de</a>
<br><br>
<font color="red">
<?php
$mtime=filemtime($_SERVER["SCRIPT_FILENAME"])-date("Z");
$gmt_mtime = date('d.m.Y', $mtime);
echo 'letztes Update: '.$gmt_mtime;
?>
</font>
</td><td align="right"><img src="../pics/frank-breitbarth.jpg"></td></tr></tbody></table>
<h3>Archiv Ansetzungshefte TKV / Durchführungsbestimmungen TKV</h3>

<table width="80%">
<tbody><tr><td style="vertical-align: top;">Spieljahr 2014-15<br></td><td style="vertical-align: top;"><a href="AH-TKV/AH-TKV-2014-15-TEIL-1.pdf">AH-2014-15-TEIL-1</a><br></td><td style="vertical-align: top;"><a href="AH-TKV-2014-15-TEIL-1.pdf">AH-2014-15-TEIL-2</a><br></td></tr>
</tbody></table>
<hr>
<a href="http://www.tkv-kegeln.de/ordnungen/ordnungen.php">zurück zu Ordnungen/Formulare.</a>
<hr>
<?
include ("../footer.inc.php");
?>
