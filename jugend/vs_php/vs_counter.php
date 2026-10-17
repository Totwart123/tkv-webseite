<?php
//Version 7.0
//Diese Skripts duerfen nur in Verbindung mit Programmen von ViennaSoft eingesetzt werden.
//www.viennasoft.at Alle Rechte vorbehalten. office@viennasoft.at

//An diesem Skript muessen keine Einstellungen vorgenommen werden.

//Die folgenden Eistellungen sind Grundeinstellungen die von den Einstellungen im Adminbereich Ueberschrieben werden.
$c_onlinetime = "300";		// Online-Zeit fuer einen Besucher in Sekunden
$c_iptime = "0";			// IP-Adressen nach xx Sekunden wieder zaehlen
$c_ipsavetime = "604000";	// IP-Adressen xx Sekunden speichern 3600=1Stunde 86400=1Tag 604000=1Woche
$c_anzeige = 5;			// Anzahl der Stellen fuer den grafischen Counter z.B: 4=0299 5=00299 6=000299



if (!isset($path) || $path==""){$path=".";}

$configfile="";
$handle=opendir($path);
	while (($file = readdir($handle))!==false) {
		$pos = strpos ($file, ".vsconfig");
		if ($pos !== false) {
			$configfile=$file;
			break;
		}
	}
closedir($handle);
if ($path == "."){$path="";}

if($configfile!=""){
	if(file_exists($path.$configfile)&& is_readable($path.$configfile)){
		$ftemp = fopen($path.$configfile,"r");
			$c_config_content = fread($ftemp, filesize($path.$configfile));
		fclose($ftemp);
		$c_config = explode("|", $c_config_content);
		$admin_name=$c_config[1];
		$admin_pw=$c_config[2];
		$c_iptime = $c_config[3];
		$c_ipsavetime = $c_config[4];
		$c_onlinetime = $c_config[5];
		$c_anzeige = $c_config[6];
	}
}

$vs_timestamp = time();
$vs_host = getenv('HTTP_HOST');
$vs_referer = getenv('HTTP_REFERER');
$vs_browser = getenv('HTTP_USER_AGENT');

$c_date = date("d.m.Y",$vs_timestamp);

if (getenv('HTTP_X_FORWARDED_FOR')){$vs_ip = getenv('HTTP_X_FORWARDED_FOR');}else{$vs_ip = getenv('REMOTE_ADDR');}

$c_file[0] = $path."counter_total.txt";
$c_file[1] = $path."counter_heute.txt";
$c_file[2] = $path."counter_gestern.txt";
$c_file[3] = $path."counter_tagesrekord.txt";
$c_file[4] = $path."counter_ips.txt";
$c_file[5] = $path."counter_referer.txt";
$c_file[6] = $path."counter_tage.txt";

for ($i = 0; $i < 7; $i++){
	if (!file_exists($c_file[$i])){
		echo "Counter Fehler! Die Datei $c_file[$i] wurde nicht gefunden. Erstellen Sie eine Datei mit dem Namen $c_file[$i] im Verzeichnis $path.";
		exit;
	}
	if (!is_readable($c_file[$i]) || !is_writable($c_file[$i])){
		echo "Counter Fehler! Falsche Dateirechte f&uuml;r die Datei $c_file[$i]";
		exit;
	}
	$ftemp = fopen($c_file[$i],"r");
		$c_file_content[$i] = fread($ftemp, filesize($c_file[$i]));
	fclose($ftemp);
}

//tagesrecord
$c_ips_details = explode("^", $c_file_content[3]);
$c_file_content[3] = $c_ips_details[0];
$c_recorddate = $c_ips_details[1];

$c_useronline = 1;
$c_ok = "ja";
$c_refok = "ja";

$c_ips = explode("|", $c_file_content[4]);
$c_ipscount = (count($c_ips)-1);
$c_file_content[4] = $vs_ip."^".$vs_timestamp."^".$vs_browser."|";

$c_file_content4 = "<tr><td><b>IP Adressen der letzten Besucher </b></td><td><b>Datum und Uhrzeit </b></td><td><b>Browser und Betriebssystem</b></td></tr>";
$c_file_content4 = $c_file_content4."<tr><td>".$vs_ip."</td><td>".date("d.m.Y H:i:s",$vs_timestamp)."</td><td>".$vs_browser."</td></tr>";

$i = 0;

while ($i < $c_ipscount){
	$c_ips_details = explode("^", $c_ips[$i]);
	$tdif = $vs_timestamp - $c_ips_details[1];

	//onlinezeit prfen
	if ($tdif < $c_onlinetime){
		if ($c_ips_details[0] != $vs_ip){$c_useronline++;}
	}

	//ip und ipzeit prfen
	if ($c_ips_details[0] == $vs_ip){
		if ($tdif < $c_iptime){$c_ok = "nein";}
	}
	else{
		if ($tdif < $c_ipsavetime){
			$c_file_content[4] = $c_file_content[4].$c_ips[$i]."|";
			$c_file_content4 = $c_file_content4."<tr><td>".$c_ips_details[0]."</td><td>".date("d.m.Y H:i:s",$c_ips_details[1])."</td><td>".$c_ips_details[2]."</td></tr>";
		}
	}
	$i++;
}


//zugriff z„hlen
if($c_ok != "nein"){
	$c_file_content[0]=$c_file_content[0]+1;
	$c_file_content[1]=$c_file_content[1]+1;
}


//tagesrecord
	if($c_file_content[1]>$c_file_content[3]){
		$c_file_content[3]=$c_file_content[1];
		$c_recorddate=$c_date;
	}

//check neuer Tag
$c_filedate = date("d.m.Y",filemtime($c_file[1]));

if($c_filedate!=$c_date){
	//gestern
	$c_file_content[2]=$c_file_content[1];
	$c_file_content[1]=1;
	$c_file_content[6]=$c_file_content[6].$c_date."^".$c_file_content[2]."|";
}




//referer
if ($vs_referer != ""){
	$pos = strpos ($vs_host, $vs_referer);
	if ($pos === false) {
		$c_ips = explode("|", $c_file_content[5]);
		$c_ipscount = (count($c_ips)-1);
		$c_file_content[5] = "";
		$c_file_content5 = "";
		$i = 0;
		while ($i < $c_ipscount){
			$c_ips_details = explode("^", $c_ips[$i]);
			if ($c_ips_details[0] == $vs_referer){
				$c_ips_details[1] = $c_ips_details[1]+1;
				$c_refok = "nein";
			}
			$c_file_content[5] = $c_file_content[5].$c_ips_details[0]."^".$c_ips_details[1]."|";
			$i++;
		}
		if($c_refok != "nein"){
			$c_file_content[5] = $c_file_content[5].$vs_referer."^1|";
		}
	}
}

//umwandeln integer
for ($i = 0; $i < 4; $i++){
	$c_file_content[$i]=$c_file_content[$i]+0;
}	


//schreiben
for ($i = 0; $i < 7; $i++){
	$ftemp = fopen($c_file[$i], "w");
		if($i == 3){
			fwrite($ftemp, $c_file_content[3]."^".$c_recorddate);
		}
		else{
			fwrite($ftemp, "$c_file_content[$i]");
		}
	fclose($ftemp);
}



//ausgabe
//ips
$c_file_content[4] = "<table border='1'>".$c_file_content4."</table>";

//referer
$c_file_content[5] = str_replace ("|", "</td></tr><tr><td>", $c_file_content[5]);
$c_file_content[5] = str_replace ("^", "</td><td>", $c_file_content[5]);
$c_file_content[5] = "<table border='1'><tr><td><b>Refferenzadressen </b></td><td><b>Zugriffe</b></td></tr><tr><td>".$c_file_content[5]."</td></tr></table>";

//tage
$c_file_content[6] = str_replace ("|", "</td></tr><tr><td>", $c_file_content[6]);
$c_file_content[6] = str_replace ("^", "</td><td>", $c_file_content[6]);
$c_file_content[6] = "<table border='1'><tr><td><b>Datum </b></td><td><b>Zugriffe dieses Tages</b></td></tr><tr><td>".$c_file_content[6]."</td></tr></table>";

//grafisch
	if(isset($_GET['javascript'])){
		$path=$_GET['javascript'];
	}
	else{
		$path=$path."bilder/";	
	}
	if ($path == "/"){$path="";}
	for ($i = 0; $i < 5; $i++){
		$tempcode="<table border='0' cellspacing='0' cellpadding='0'><tr>";

		if($i==4){
			$tempstring=$c_useronline;
		}
		else{
			$tempstring=$c_file_content[$i];
		}
		
		$tempstring=(string)$tempstring;
		
		while (strlen($tempstring) < $c_anzeige){
			$tempstring="0".$tempstring;
		}
		for($j=0;$j<strlen($tempstring);$j++){
			$tempcode = $tempcode."<td><img src='".$path.$tempstring[$j].".gif'></td>";
		}
		$tempcode = $tempcode."</tr></table>";
		if($i==4){
			$c_img_useronline=$tempcode;
		}
		else{
			$c_img_content[$i]=$tempcode;
		}
	}


//Javascript
if(isset($_GET['javascript'])){
	echo "c_useronline=\"".$c_useronline."\";";
	echo "c_img_useronline=\"".$c_img_useronline."\";";
	echo "c_recorddate=\"".$c_recorddate."\";";
	for ($i = 0; $i < 4; $i++){
		echo "c_content".$i."=\"".$c_file_content[$i]."\";";
		echo "c_img_content".$i."=\"".$c_img_content[$i]."\";";
	}
}


?>