<?
include ("../header.inc.php");
?>
<table width="100%" body bgcolor="ffff74" border="0">
<tr height="100"><td width="75%"><h3><font color="blue">Lehrteam Bildung</font></h3>J&uuml;rgen Seland<br>E-mail: <a href="mailto:landeslehrwart@tkv-kegeln.de">landeslehrwart @ tkv-kegeln.de</a>
<br><br>
<font color="red">
<?php
$mtime=filemtime($_SERVER["SCRIPT_FILENAME"])-date("Z");
$gmt_mtime = date('d.m.Y', $mtime);
echo 'letztes Update: '.$gmt_mtime;
?>
</font>
</td><td align="right"><img src="../pics/juergen-seeland.jpg"></td></tr></table>
<br>
<table>
<th>Termine / Veranstaltungen 2015/2016</th>
<!--<tr><td>01.06.2013</td><td>Praxislehrgang in Weimar <a href="2013/13-06-01-JS-Praxislehrgang-Weimar.pdf">Ausschreibung</a></td><td><font color=red> <small> Update 23.04.2013</small></font></td></tr>-->
<!--<tr><td>29.-31.08.2014</td><td>Pr&uuml;fung in Bad Blankenburg <a href="2014/14-08-29-JS-Pruefung-Blankenburg.pdf">Ausschreibung</a></td><td><font color=red> <small> Update 03.07.2014</small></font></td></tr>--></tr>
<tr>
<td>29.08.2015</td>
<td>Fortbildung in Bad Blankenburg <a href="2015/15-08-29-JS-Fortbildung-Blankenburg.pdf">Ausschreibung</a> * <a href="2015/15-08-29-JS-Fortbildung-Konzept-Blankenburg.pdf">Konzept</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
<td><font color="red"> <small> Update 31.07.2015</small></font></td>
</tr>
<tr>
<td>28.-30.08.2015 / 04.-06.12.2015<br>
</td>
<td>Ausbildung in Bad Blankenburg <a href="2015/15-08-28-JS-Ausbildung-Teil1.pdf">Konzept Teil 1</a></td>
<td><font color="red"> <small> Update 31.07.2015</small></font></td>
</tr>
<tr>
<td>04.-06.12.2015</td>
<td>Ausbildung in Bad Blankenburg <a href="2015/15-10-03-JS-Ausbildung-Teil2.pdf">Konzept Teil 2</a></td>
<td><font color="red"> <small> Update 30.11.2015</small></font></td>
</tr>
<tr>
<td>15.-17.04.2016</td>
<td>Ausbildung in Bad Blankenburg <a href="2016/16-02-01-JS-Ausbildung-Teil3.pdf">Konzept Teil 3</a></td>
<td><font color="red"> <small> Update 04.02.2016</small></font></td>
</tr>
<!--<tr><td colspan="3">Terminkalender <a href="2013/13-03-18-JS-Bildung-Auschreibung.pdf">Ausschreibung</a></td></tr>-->
</table>
<hr>

<?
include ("../../../footer.inc.php");
?>
