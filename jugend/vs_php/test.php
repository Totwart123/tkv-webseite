<?php
//Version 7.0
//Diese Skripts duerfen nur in Verbindung mit Programmen von ViennaSoft eingesetzt werden.
//www.viennasoft.at Alle Rechte vorbehalten. office@viennasoft.at


header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$fehler=0;

echo "<html><head><title>ViennaSoft PHP Installationstest</title></head><body><h3>ViennaSoft PHP Installationstest</h3>Der Installationstest &uuml;berpr&uuml;ft ob alle erforderlichen Dateien installiert und die Dateirechte dieser Dateien richtig gestetzt sind.<br><br><hr>";

//ADMIN
echo "<br><b>Dateien f&uuml;r den internen gebrauch (Grundinstallation)</b><br><br>";
dateitest("admin.php",0);
dateitest("admin.txt",1);
dateitest("vs_counter.php",0);
dateitest("vs_daten.php",0);
dateitest("vs_formular.php",0);

$configfile="";
$handle=opendir('.');
	while (($file = readdir($handle))!==false) {
		$pos = strpos ($file, ".vsconfig");
		if ($pos !== false) {
			$configfile=$file;
			break;
		}
	}
closedir($handle);
if($configfile!=""){
	dateitest($configfile,2);
}
else{
	echo "<font color=\"red\">Fehler:</font> Es wurde keine Konfiguratiosdatei gefunden.<br>";
}


//DATEN
echo "<br><br><hr><br>"; 
echo "<br><b>Datendateien</b> (*.vsphp f&uuml;r G&auml;steb&uuml;cher, Foren, Newsletter usw.)<br><br>";

$filecount=0;
$handle=opendir('.');
while (($file = readdir($handle))!==false) {
	$pos = strpos ($file, ".vsphp");
	if ($pos !== false) {
		dateitest($file,1);
		$filecount++;
	}
}
closedir($handle);
if($filecount==0){
	echo "Es wurden keine Dateien mit der Dateierweiterung .vsphp gefunden.<br><br>";
}



//COUNTER
echo "<br><hr><br>"; 
echo "<br><b>Counter</b><br><br>";

$c_file[0] = $path."counter_total.txt";
$c_file[1] = $path."counter_heute.txt";
$c_file[2] = $path."counter_gestern.txt";
$c_file[3] = $path."counter_tagesrekord.txt";
$c_file[4] = $path."counter_ips.txt";
$c_file[5] = $path."counter_referer.txt";
$c_file[6] = $path."counter_tage.txt";


for ($i = 0; $i < 7; $i++){
	dateitest($c_file[$i],1);
}




echo "<br><br><hr><br><b>Serverinfos für den Dateiupload</b><br><br>"; 

echo "Uploads zugelassen: " . get_cfg_var("file_uploads") . "<br>"; 
echo "Max. Upload Größe (Dateigröße der hochzuladenen Datei): " . get_cfg_var("upload_max_filesize") . "<br>"; 
echo "Max. Script Speicher (Speicher den ein Script benutzen darf): " . get_cfg_var("memory_limit") . "<br>"; 
echo "Max. \"post\" Daten (Menge an Daten die per \"post\" übermittelt werden): " . get_cfg_var("post_max_size") . "<br>"; 
echo "Max. Laufzeit (Zeit des Ausführens eines Scripts): " . get_cfg_var("max_execution_time") . " Sek"; 
echo "<br><br>"; 

#Testauswertung
echo "<br><br><hr><br><b><font color=\"red\">Testergebnis der Installation der PHP-Skrips</font></b><br><br>"; 

if($fehler2!=0){
	echo "Es sind ".$fehler2." Warnungen bei der Installation gefunden worden. <br>Die Warnungen beziehen sich auf nicht installierte Dateien und haben keine Relevanz falls Sie auf Ihrer Homepage die betreffenden Bereiche z.B. Counter nicht verwenden.<br><br>";
}

if($fehler==0){
	echo "Es sind keine Fehler bei der Installation gefunden worden. Alle Funktionen sollten einwandfrei arbeiten.
	<a href=\"admin.php\">Als Administrator anmelden</a>";
}
else{
	echo "Es sind ".$fehler." Fehler bei der Installation gefunden worden. <br>Versuchen Sie die Fehler anhand der Anleitungen zu beheben und wiederholen Sie danach den Test.";
}




echo "<br><br><hr><br><b>PHP INFO</b><br>Informationen zu den PHP und Apache Einstellungen Ihres Servers.<br><br>";

echo phpinfo();

function dateitest($datei,$typ){
global $fehler,$fehler2;

	if (!file_exists($datei)){
		echo "<font color=\"orange\">Warnung:</font> Die Datei ".$datei." ist nicht installiert.<br>";
		$fehler2=$fehler2+1;
	}
	else{
		if($typ==0){
			if (!is_executable($datei)){
				echo "<font color=\"red\">Fehler:</font> Falsche Dateirechte f&uuml;r Datei <b>".$datei."</b><br>\n";
				$fehler=$fehler+1;
			}
			else{
				echo "<font color=\"green\">OK:</font> Die Datei <b>".$datei."</b> ist korrekt installiert<br>\n";
			}
		}
		elseif($typ==2){
			if (!is_readable($datei) || !is_writable($datei)){
				echo "<font color=\"red\">Fehler:</font> Falsche Dateirechte f&uuml;r die Konfigurationsdatei <b>*.vsconfig</b><br>\n";
				$fehler=$fehler+1;
			}
			else{
				echo "<font color=\"green\">OK:</font> Die <b>Konfigurationsdatei</b> ist korrekt installiert.<br>\n";
			}
		}
		else{
			if (!is_readable($datei) || !is_writable($datei)){
				echo "<font color=\"red\">Fehler:</font> Falsche Dateirechte f&uuml;r Datei <b>".$datei."</b><br>\n";
				$fehler=$fehler+1;
			}
			else{
				echo "<font color=\"green\">OK:</font> Die Datei <b>".$datei."</b> ist korrekt installiert.<br>\n";
			}
		}
	}
}

?>
