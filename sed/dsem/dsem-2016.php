<? include ("../../header.inc.php"); ?>
<table width="100%" body bgcolor="ffff74" border="0">
<tr height="100"><td width="75%"><h3><font color="red">Deutsche Einzelmeisterschaften Senioren 2016 (120 Wurf) in Sangerhausen</font></h3><!--Landessportwart Rolf Thieme<br>E-mail: <a href="mailto:landessportwart@tkv-kegeln.de">landessportwart @ tkv-kegeln.de</a>-->
<br><br><!--<font color=red>Seite zur Zeit in Überarbeitung</font>-->
<font color="red">
<?php
$mtime=filemtime($_SERVER["SCRIPT_FILENAME"])-date("Z");
$gmt_mtime = date('d.m.Y', $mtime);
echo 'letztes Update: '.$gmt_mtime;
?>
</font>
</td><!--<td align="right"><img src="dsem-2016.jpg"></td>--></tr></table>

<br>
<!--<font color=red>Seite zur Zeit in &Uuml;berarbeitung</font>
<hr>-->
<table border="0" width="100%">
<tr><td width="15%">27.-29.05.2016</td><td width="50%">100 Wurf Seniorinnen / Senioren in Sangerhausen</td><td width="25%"><font color=red><small> Update 26.05.2016</small></font></td><td rowspan="18" align=="right"><img src="dsem-2016.jpg"></td></tr>
<tr><td>Unterlagen</td><td><a href="DSEM-2016-DFB.pdf">Durchführungsbestimmungen</a> * <a href="DSEM-2016-Zuteilung.pdf">Zuteilung</a></td></tr>
<tr><td>Startliste</td><td><a href="DSEM-2016-SL-S-AB.pdf">SeniorInnen A/B</a> * <a href="DSEM-2016-SL-S-C.pdf">SeniorInnen C</a></td></tr>
<tr><td>Zeitplan Endlauf</td><td><a href="DSEM-2016-ZP-EL.pdf">SeniorInnen</a></td></tr>
<tr><td><br><h2><font color="red">LIVETICKER</font></h2></td><td colspan="2"><br><strong><a href="DSEM-2016-LT.php">Qualifikation/Finale</a></strong><font color=red><small>&nbsp;&nbsp;&nbsp;&nbsp;
<?php
$datei = "DSEM-2016-LT.pdf";
// Ergebnisdatei auslesen
if (file_exists ($datei))
{
	// Überschrift anzeigen
	$Datum = date("d.m.Y", filemtime ($datei));
	$Uhrzeit = date("H:i", filemtime ($datei));
	echo "Letztes Update am ".$Datum." um ".$Uhrzeit." Uhr!!!";
}
?>

</small></font></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
</table>
<hr>
<a href="http://www.tkv-kegeln.de/sed/sed.php">zur&uuml;ck zum Sonderergebnisdienst</a>
<hr>
 
<? include ("../../footer.inc.php"); ?>