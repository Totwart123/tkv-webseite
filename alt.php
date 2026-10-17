<?
include ("header.inc.php");
include ("config.inc.php");
include ("libtkv.inc.php");
?>

<table body bgcolor="ffff74" border="1" width="100%">
<!--<tr><td colspan="3"align="center"><a href="http://newsletter.dosb.de/newsletter/newsletter.php?id=5026&html=1" target="_blank">Vier Gewinnaktionen beim Deutschen Sportabzeichen</a></td></tr>-->    
<tr>
<td width="33%" align="center">
<a href="http://www.kegelkugeln.de/" target="_blank">Spiel mit eigenen Kegelkugeln</a>
<br>
<br><img src="pics/kegelkugeln.gif" border="0">
<br>
<br><a href="http://dosb-newsletter.yum.de/newsletter/newsletter.php?id=6124&html=1" target="_blank">Aktion „1000 Apps für 1000 Vereine“</a>
</td>
<td width="33%" align="center">
<a href="news/2016/2016-05-18-DKB-Hinweise-Public-Viewing.pdf">Hinweise Public Viewing EM 2016 in Frankreich</a>
<!--<a href="jugend/ferienfreizeit.php">TKV Ferienfreizeit 2015</a>
<br><strong><font color="red">(Achtung! Eigenes Besteck ist mitzubringen! Bezgl. der zu bedruckenden T-Shirts sind die Namen nochmals zu &uuml;berpr&uuml;fen!)</font></strong>
<br><br>-->
<!--<a href="news/2015/2015-04-09-Flyer-Nationalmannschaftstrikot.pdf">Flyer Nationalmannschaftstrikot</a>
<br><br><br>-->
<a href="http://www.tkv-kegeln.de/jugend/ferienfreizeit.php"><h1><font color="red">TKV-Ferienfreizeit in Wernburg vom 16. bis 23.Juli 2016</font></h1></a>
<!--<a href="news/2012/Angebot-Chronik-50-Jahre-DKB.pdf">Angebot Chronik 50 Jahre DKB<br>(Achtung Dateigr&ouml;&szlig;e 20 MB!!!)</a>
<a href="news/2010/dosb_klimaschutz_2010-final.pdf">DOSB - Klimaschutz im Sport</a>-->
<a href="http://www.funklivestream.com/">Liveticker WM 2016 in Novigrad (Kroatien)</a>
</td>
<!--<td body width="34%" align="center">
<font color=red><strong>Vorschlag Spielstruktur TKV Spielzeit 2011 / 2012!!!</strong></font>
<br>
<br><a href="news/2010/Vorschlag-Spielstruktur_TKV_2011-2012.pdf">Weitere Informationen</a>
<br><!--<br><a href="http://tkv-kegeln.de/jugend/2009/DKB-Freizeitcamp/DKB-Freizeitcamp-2009.pdf">DKB Jugend-Keglercamp 2009</a>--><!--</td>-->
<td width="33%" align="center">
<a href="http://www.kegelnundbowling.de/umwerfende-sportarten/ideenservice-f%C3%BCr-vereine.html" target="_blank">Kegeln und Bowling - umwerfende Sportarten</a>
<br>
<br><img src="pics/DKB.gif" border="0">
<br>
<br><a href="http://www.kegelnundbowling.de/28-nachrichten/2015/dkb/76-ein-ganzes-dutzend-siegertypen.html" target="_blank">Sportler(innen) und Trainer(in) des Jahres</a>
<br><a href="news/2016/2016-03-17-DKB-Medien-Mitteilung.pdf">Medienmitteilung "Wir im DKB machen Schule"</a>
</td>
</tr>
<!--<tr><td colspan="3"align="center"><a href="news/2014/2014-05-22-Rechtstelegramm-Fussball-WM-2014.pdf">Merkblatt Fußball-WM 2014</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="news/2014/2014-05-22-GEMA-Sondertarif-Fussball-WM-2014.pdf">GEMA-Sondertarif Fußball-WM 2014</a></td></tr>-->
</table>
<br>
<table border="2" width="100%">
<tr><th><font color="red" size="6"><marquee direction=left>+++ Der TKV bietet heute und in Zukunft einen Spielbetrieb über 100, 120 und 200 Wurf an. +++</marquee></font></th></tr>
</table>

<h3>Neuigkeiten TKV</h3>
<hr>
<!--<strong>15. 04. 2016</strong>
<br>

<br><strong>Aus- und Weiterbildung Schiedsrichter: </strong>
<br>Schiedsrichterausbildung B Lizenz am 04.06.2016 Kegelbahn Langenorla. Anmeldung bis 20.05.2016 an Sportfreund H.J. Marek (Festnetz: 03647-413161, Mobil: 0171-5350430).
<br>Weiterbildung Schiedsrichter am 06.08.2016 Weimar Schöndorf Kegelbahn oder am 20.08.2016 in Auma Kegelbahn Beginn jeweils 9.00 Uhr. Anmeldung bis spätestens 31.07.2016 bei H.J. Marek (Festnetz: 03647-413161, Mobil: 0171-5350430).
<br><div align="right">Gunter Witzel (FB)</div>
<hr>-->


<?
$add = $_GET['add'];
$old = $_GET['old'];
$add_submit = $_POST['add_submit'];
$datum = $_POST['datum'];
$author = $_POST['author'];
$title = $_POST['title'];
$text = $_POST['text'];

$nl = new NewsList ($old);

if ($add) 
{
   $nl->add (0);
} 
elseif (isset ($add_submit)) 
{
   $nl->add (0, 1, $datum, $author, $title, $text);
?>
<p>Ihre Neuigkeit wurde vorgeschlagen.</p>
<?
} 
else 
{
   $nl->show ();
}
?>

[ <a href="<?=$PHP_SELF?>?add=1">Neuigkeit vorschlagen</a>
| <a href="<?=$PHP_SELF?>?old=1">alte Nachrichten</a> ]

<?
include ("footer.inc.php");		
?>
