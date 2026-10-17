<?php
//Version 7.1
//Diese Skripts duerfen nur in Verbindung mit Programmen von ViennaSoft eingesetzt werden.
//www.viennasoft.at Alle Rechte vorbehalten. office@viennasoft.at

$admin_name = "admin";
$admin_pw = "admin";
$c_onlinetime = "300";
$c_iptime = "0";
$c_ipsavetime = "604000";
$c_anzeige = 5;
$vs_servertimedif = 0;

$vs_host = getenv('HTTP_HOST');
$vs_browser = getenv('HTTP_USER_AGENT');
$vs_referer = getenv('HTTP_REFERER');
$vs_formular_adresse = @$HTTP_REFERER;

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
	if(file_exists($configfile)&& is_readable($configfile)){
		$ftemp = fopen($configfile,"r");
			$c_config_content = fread($ftemp, filesize($configfile));
		fclose($ftemp);
		$c_config = explode("|", $c_config_content);
		$admin_name=$c_config[1];
		$admin_pw=$c_config[2];
		$c_iptime = $c_config[3];
		$c_ipsavetime = $c_config[4];
		$c_onlinetime = $c_config[5];
		$c_anzeige = $c_config[6];
		$vs_servertimedif = $c_config[7];
	}
}
else{
	echo "<html><head><title>ViennaSoft PHP Administrator Bereich</title></head><body><h3>ViennaSoft PHP Administrator Bereich</h3><br><hr><br>
		Es ist ein Fehler aufgetreten der ohne Ihr Eingreifen nicht behoben werden kann. F&uuml;hren Sie das Testprogramm aus um festzustellen welche 
		Fehler bei der Installation der PHP Dateien gemacht wurden und wie diese Fehler behoben werden k&ouml;nnen.<br><br><a href=\"test.php\">Das Testprogramm test.php starten.</a>
		<br><br><br><hr></body></html>";
exit;
}


if ($admin_name == ""){
	$admin_name="admin";
}
if ($admin_pw == ""){
	$admin_pw="admin";
}



$vs_timestamp = time() - $vs_servertimedif;


//admin abmelden
if(isset($_GET['aktion'])) {
  if($_GET['aktion'] == "logout"){
  	$ftemp = fopen("admin.txt", "w");
  	fwrite($ftemp, " ");
  	fclose($ftemp);
	setcookie("admin_name", " ");
	setcookie("admin_pw", " ");
  	header("Location: admin.php");
  	exit;
  }
}


if(vslogin()!=1){exit;}


if(!isset($_GET['bearbeiten'])) {
	echo "<html><head><title>ViennaSoft PHP Administrator Auswahl</title></head><body><h3>ViennaSoft PHP Administrator &Uuml;bersicht</h3><br><hr><br>
	<b>W&auml;hlen Sie welche der Einstellungen Sie bearbeiten m&ouml;chten.</b><br><br>
	<b>Insatallation</b><br>
	<a href=\"test.php\">Installation Pr&uuml;fen</a> Pr&uuml;ft ob alle Dateien vorhanden und deren Dateirechte richtig gestetzt sind.<br><br>
	<b>Grundeinstellungen</b><br>
	<a href=\"admin.php?admin=$admin_login&bearbeiten=config&aktion=edit_admin\">Grundeinstellungen  bearbeiten</a> (*.vsconfig)<br><br>
	<b>Counter</b><br>
	<a href=\"admin.php?admin=$admin_login&bearbeiten=config&aktion=edit_counter\">Einstellungen Ihres Counters bearbeiten</a> (*.vsconfig)<br>
	<a href=\"admin.php?admin=$admin_login&bearbeiten=counter\">Z&auml;hlerst&auml;nde Ihres Counters bearbeiten</a><br><br>";

//Datenfiles
	
	echo "<b>Bearbeiten von Datendateien</b> f&uuml;r G&auml;steb&uuml;cher, Foren usw. *.vsphp<br>";

		$filecount=0;
		$handle=opendir('.');
		while (($file = readdir($handle))!==false) {
			$pos = strpos ($file, ".vsphp");
			if ($pos !== false) {
				echo "Die Datei <a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=$file\">$file</a> bearbeiten.<br><br>";
				$filecount++;
			}
		}
		closedir($handle);
		if($filecount==0){
			echo "Es wurden keine Dateien mit der Dateierweiterung .vsphp gefunden.<br><br>";
		}
	
		echo "<b>Abmelden</b> (Logout)<br>
		<a href=\"admin.php?admin=$admin_login&aktion=logout\">Die aktuelle Anmeldung als Admin l&ouml;schen</a><br><br><br><hr></body></html>";
exit;
}


//DATEN
if ($_GET['bearbeiten'] == "vsphp"){
	$vs_dataname = array("id", "parent_id", "name", "vorname", "anrede", "email", "homepage", "adresse", "land", "plz", "wohnort", "titel", "text", "komentar", "datum", "zeit", "ip", "user1", "user2", "user3", "user4", "user5", "user6", "image1", "image2", "image3", "image4","image5", "file1", "file2", "moderiert","user_id");
	$vs_dataname_description = array("ID", "Abh&auml;ngig von ID", "Name", "Vorname", "Anrede", "e-Mail Adresse", "Homepage Adresse", "Adresse", "Land", "PLZ", "Wohnort", "Titel", "Text", "Kommentar", "Datum", "Zeit", "IP-Adresse", "Benutzerdef. Feld1", "Benutzerdef. Feld2", "Benutzerdef. Feld3", "Benutzerdef. Feld4", "Benutzerdef. Feld5", "Benutzerdef. Feld6", "Bild 1", "Bild 2", "Bild 3", "Bild 4", "Bild 5", "Datei 1", "Datei 2", "Eintrag aktiviert","Verkn&uuml;pft mit Benutzer");
	$vs_dataname_count = (count($vs_dataname));

	if(!isset($_GET['aktion'])) {
		echo "Die Aktion ist nicht eindeutig. Bitte wiederholen Sie den Vorgang.";
		
	}
	elseif ($_GET['aktion'] == "edit"){
		if(!isset($_GET['filepath'])) {
			echo "Fehler: Bearbeiten einer Datei ohne Dateipfad. Datei nicht gefunden.";
		}
		else{
			vs_getdata();

			if(!isset($_GET['id'])) {
				echo "<html><head><title>ViennaSoft PHP Daten bearbeiten</title></head><body><h3>ViennaSoft PHP Daten</h3><a href=\"admin.php?admin=$admin_login\">Zur&uuml;ck zur &Uuml;bersicht</a><br><br>Sie sehen hier eine &Uuml;bersicht aller Datens&auml;tze der Datei ".$_GET['filepath']."<hr><br>\n";
				if ($vs_data_count==0){
					echo "In dieser Datei sind noch keine Datens&auml;tze gespeichert.";
				}
				else{
					echo "<table border='0' cellspacing='15'><tr><td></td>";
					for($i=0;$i<$vs_dataname_count;$i++){
						echo "<td valign='bottom'><b>".$vs_dataname_description[$i]."</b></td>\n";
					}
					echo "</tr>";
					echo "<tr><td colspan='".($vs_dataname_count+1)."'><hr></td></tr>\n";

					for($i=0;$i<$vs_data_count;$i++){
						$eintrag[$i] = explode("^", $read_arr[$i]);
						echo "<tr><td>";
						echo "Nr.$i <a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=".$_GET['filepath']."&id=$i\">bearbeiten</a>\n";
						echo "<a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=del&filepath=".$_GET['filepath']."&id=$i\">l&ouml;schen</a>\n";
						if($eintrag[$i][$vs_dataname_count-2]!="ja"){
							echo "<a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=act&filepath=".$_GET['filepath']."&id=$i&code=ja\">aktivieren</a>\n";
						}
						else{
							echo "<a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=act&filepath=".$_GET['filepath']."&id=$i&code=nein\">deaktivieren</a>\n";
						}
						echo "</td>";
						for($j=0;$j<$vs_dataname_count;$j++){
							echo "<td>".$eintrag[$i][$j]."</td>";
						}
						echo "<tr><td colspan='".($vs_dataname_count+1)."'><hr></td></tr>";
					}
					echo "</tr></table>";
				}
			}
			else{
				$id=$_GET['id'];
				echo "<html><head><title>ViennaSoft PHP Daten bearbeiten</title><style type=\"text/css\">\n";
				
				echo "</style></head><body><h3>ViennaSoft PHP Daten bearbeiten</h3>Sie bearbeiten den Datensatz NR.<b>".$_GET['id']."</b> in der Datei <b>".$_GET['filepath']."</b>\n";
				echo "<span id=\"keinrahmen\"><a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=".$_GET['filepath']."\"><br>Datensatz nicht bearbeiten. Zur&uuml;ck zur &Uuml;bersicht.</a></span><hr><br>\n";
				echo "<form action=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=save&filepath=".$_GET['filepath']."&id=".$id."\" method=\"post\">\n";
				echo "<table border='0'>\n";
				
				$eintrag[$id] = explode("^", $read_arr[$id]);				

				for($j=0;$j<$vs_dataname_count;$j++){
					echo "<tr><td align=\"right\" valign=\"top\">".$vs_dataname_description[$j]."=</td>";
					if($j<2){
						echo "<td>\n<input type=\"hidden\" name=\"".$vs_dataname[$j]."\" value=\"".$eintrag[$id][$j]."\">".$eintrag[$id][$j]."</td></tr>\n";
					}
					elseif($j==12||$j==13){
						echo "<td>\n<textarea cols=\"50\" rows=\"6\" name=\"".$vs_dataname[$j]."\">".$eintrag[$id][$j]."</textarea></td></tr>\n";
					}
					elseif($j==23||$j==24||$j==25||$j==26||$j==27||$j==28||$j==29){
						echo "<td><input type=\"text\" size=\"55\" name=\"".$vs_dataname[$j]."\" value=\"".$eintrag[$id][$j]."\">";
						if($j==28||$j==29){
							echo "<a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=search_file&filepath=".$_GET['filepath']."&id=".$id."&file_id=".$j."\">Datei suchen</a>\n";
							echo "| <a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=delete_file&filepath=".$_GET['filepath']."&id=".$id."&file_id=".$j."\">Datei löschen</a>\n";
						}
						else{
							echo "<a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=search_image&filepath=".$_GET['filepath']."&id=".$id."&file_id=".$j."\">Bild suchen</a>\n";
							echo "| <a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=delete_file&filepath=".$_GET['filepath']."&id=".$id."&file_id=".$j."\">Bild löschen</a>\n";							
						}
						echo "</td></tr>\n";						
					}
					else{
						echo "<td>\n<input type=\"text\" size=\"55\" name=\"".$vs_dataname[$j]."\" value=\"".$eintrag[$id][$j]."\"></td></tr>\n";
					}
				}
				echo "</table><input type=\"submit\" name=\"button\" value=\"Speichern\"></form>\n";
			}
		}
	}

	elseif($_GET['aktion'] == "search_image" || $_GET['aktion'] == "search_file"){
		vs_getdata();
		$id=$_GET['id'];
		$vs_file=intval($_GET['file_id']);
	
		echo "<html><head><title>ViennaSoft PHP Daten bearbeiten</title></head><body><h3>ViennaSoft PHP Daten bearbeiten</h3>Sie bearbeiten den Datensatz NR.<b>".$_GET['id']."</b> in der Datei <b>".$_GET['filepath']."</b>";
				echo "<a href=\"javascript:history.back();\"><br>Datei nicht übertragen. Zur&uuml;ck zum Datensatz.</a><hr><br>";
				
				#echo "<a href=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=".$_GET['filepath']."&id=".$id."\"><br>Datei nicht übertragen. Zur&uuml;ck zum Datensatz.</a><hr><br>";
				echo "<form action=\"admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=savefile&filepath=".$_GET['filepath']."&id=".$id."&vs_file=".$vs_file."&redirpath=".$_GET['redirpath']."\" enctype=\"multipart/form-data\" method=\"post\">\n";
				echo "<table border='0'>\n";

				$eintrag[$id] = explode("^", $read_arr[$id]);

				//for($j=0;$j<$vs_dataname_count;$j++){
					echo "<tr><td align=\"right\" valign=\"top\">".$vs_dataname_description[$vs_file]." =</td>";
					//if($j==$vs_file){
						echo "<td>\n<input type=\"file\" name=\"".$vs_dataname[$vs_file]."\" value=\"\">".$eintrag[$id][$vs_file]."</td></tr>\n";
					//}
					//else{
						//echo "<td>\n<input type=\"hidden\" name=\"".$vs_dataname[$j]."\" value=\"".$eintrag[$id][$j]."\">".$eintrag[$id][$j]."</td></tr>\n";
					//}
				//}
				echo "</table><input type=\"submit\" name=\"button\" value=\"Speichern\"></form>\n";
	}

	elseif($_GET['aktion'] == "delete_file"){ // löschen datei
		vs_getdata();
		$id=$_GET['id'];
		$vs_file=intval($_GET['file_id']);

		//eintrag bearbeiten
		$eintrag[$id] = explode("^", $read_arr[$id]);
		$neu=$eintrag[$id][0]."^".$eintrag[$id][1];
		for($j=2;$j<$vs_dataname_count;$j++){
			if($j==$vs_file){
				if ($eintrag[$id][$j] != "." && $eintrag[$id][$j] != ".." && $eintrag[$id][$j] != "") { 
  					unlink($eintrag[$id][$j]);
 				}
				$neu=$neu."^";
			}
			else{
				$neu=$neu."^".$eintrag[$id][$j];
			}
		}
		$read_arr[$id]=$neu;
				
		vs_setdata();
		header("Location: ".$vs_referer);
		#header("Location: admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=".$_GET['filepath']."&id=".$id);
	}

	elseif($_GET['aktion'] == "save"){
		vs_getdata();
		$id=$_GET['id'];

		//eintrag bearbeiten
		$eintrag[$id] = explode("^", $read_arr[$id]);
		$neu=$eintrag[$id][0]."^".$eintrag[$id][1];		
		for($j=2;$j<$vs_dataname_count;$j++){
			if(isset($_POST[$vs_dataname[$j]])){
				$eintrag[$id][$j]=$_POST[$vs_dataname[$j]];
			}
			$neu=$neu."^".$eintrag[$id][$j];
		}
		$read_arr[$id]=$neu;

		vs_setdata();
			

		header("Location: admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=".$_GET['filepath']);
		
	}
	elseif($_GET['aktion'] == "savefile"){
		vs_getdata();
		$id=$_GET['id'];
		$vs_file=$_GET['vs_file'];
		//eintrag bearbeiten
		$eintrag[$id] = explode("^", $read_arr[$id]);

		//if ($eintrag[$id][$vs_file]==""){
		//	$eintrag[$id][$vs_file]=$eintrag[$id][0].$vs_dataname[$vs_file];
		//}


		if ($vs_file==28 || $vs_file==29){
			if(isset($_FILES["$vs_dataname[$vs_file]"])){
				if($_FILES["$vs_dataname[$vs_file]"]["error"]==0){
					$uimg = vs_fileupload("$vs_dataname[$vs_file]",$eintrag[$id][0].$vs_dataname[$vs_file]);
				}
			}
		}
		else{
			if(isset($_FILES["$vs_dataname[$vs_file]"])){
				if($_FILES["$vs_dataname[$vs_file]"]["error"]==0){
					$uimg = vs_imgupload("$vs_dataname[$vs_file]",$eintrag[$id][0].$vs_dataname[$vs_file]);
				}
			}
		}
		
		$neu=$eintrag[$id][0]."^".$eintrag[$id][1];
		for($j=2;$j<$vs_dataname_count;$j++){
			if($j==$vs_file){
				$neu=$neu."^".$uimg;
			}
			else{
				$neu=$neu."^".$eintrag[$id][$j];
			}
		}
		$read_arr[$id]=$neu;
				
		vs_setdata();

		if($_GET['redirpath']!=""){
			header("Location: ".$_GET['redirpath']);
		}
		else{
			header("Location: admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=".$_GET['filepath']);
		}

	}

	elseif($_GET['aktion'] == "del"){
		vs_getdata();
		$id=$_GET['id'];
		
		//schreiben
		$speichern = fopen($_GET['filepath'],"w");
		for($i=0;$i<$vs_data_count;$i++){
			if($i!=$id){
				fwrite($speichern,$read_arr[$i]."|");
			}
			else{
				//löschen der dateien des DS
				//eintrag bearbeiten
				$eintrag[$id] = explode("^", $read_arr[$id]);
				for($j=23;$j<30;$j++){
					if ($eintrag[$id][$j] != "." && $eintrag[$id][$j] != ".." && $eintrag[$id][$j] != "") { 
  						unlink($eintrag[$id][$j]);
 					}
				}
			}
		}
	  	fclose($speichern);

		if($_GET['redirpath']!=""){
			header("Location: ".$_GET['redirpath']);
		}
		else{
			header("Location: admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=".$_GET['filepath']);
		}

	}

	elseif($_GET['aktion'] == "act"){
		vs_getdata();
		$id=$_GET['id'];

		//eintrag bearbeiten
		$eintrag[$id] = explode("^", $read_arr[$id]);
		$neu=$eintrag[$id][0]."^".$eintrag[$id][1];
		for($j=2;$j<$vs_dataname_count;$j++){
			if($j==30){
				if(isset($_GET['code'])){
					if($_GET['code']=="nein"){
						$neu=$neu."^nein";
					}
					else{		
						$neu=$neu."^ja";
					}
				}
				else{
					$neu=$neu."^".$eintrag[$id][$j];
				}
			}
			else{
				if(isset($_POST[$vs_dataname[$j]])){
					$eintrag[$id][$j]=$_POST[$vs_dataname[$j]];
				}
				$neu=$neu."^".$eintrag[$id][$j];
			}
					}
		$read_arr[$id]=$neu;
				
		vs_setdata();

		header("Location: admin.php?admin=$admin_login&bearbeiten=vsphp&aktion=edit&filepath=".$_GET['filepath']);
		
	}
}




//CONFIGURATION
if ($_GET['bearbeiten'] == "config"){
	if ($_GET['aktion'] == "edit_counter"){
		if (!is_writable($configfile)){
			echo "Falsche Dateirechte f&uuml;r Configurationsdatei *.vsconfig";
			exit;
		}
		
		echo "<html><head><title>ViennaSoft PHP Administrator Einstellungen</title></head><body><h3>ViennaSoft PHP Counter Einstellungen bearbeiten</h3>
		<a href=\"admin.php?admin=$admin_login\">Zur&uuml;ck zur &Uuml;bersicht</a><br>
		<br><hr><br>
		<form action=\"admin.php?admin=$admin_login&bearbeiten=config&aktion=save\" method=\"post\">
		
		<input type=\"hidden\" name=\"adm_name\" value=\"$admin_name\">
		<input type=\"hidden\" name=\"adm_pw\" value=\"$admin_pw\">
		<input type=\"hidden\" name=\"vs_servertimedif\" value=\"$vs_servertimedif\">
		<br><br>Die Zeit in Sekunden bis ein Besucher Ihrer Homepage erneut gez&auml;hlt wird.(IP Sperre)<br>
		<input type=\"text\" name=\"iptime\" value=\"$c_iptime\" size=\"20\">0=jeder Seitenaufruf wird gez&auml;hlt 3600=1Stunde 7200=2Stunden 86400=1Tag<br>
		<br><br>Die Zeit in Sekunden wie lange die IP eines Besuchers gespeichert wird.<br>
		<input type=\"text\" name=\"ipsavetime\" value=\"$c_ipsavetime\" size=\"20\">86400=1Tag 604000=1Woche<br>
		<br><br>Die Zeit wie lange ein Besucher nach seinem letzten Seitenaufruf noch als Online gez&auml;hlt wird.<br>
		<input type=\"text\" name=\"onlinetime\" value=\"$c_onlinetime\" size=\"20\">300=5Minuten 600=10Minuten<br>
		<br><br>Anzahl der Stellen bei der grafischen Darstellung des Counters<br>
		<input type=\"text\" name=\"anzeige\" value=\"$c_anzeige\" size=\"20\">0=automatisch, 5 ergibt bei 999 Zugriffen 00999<br>
		<br>
		<input type=\"submit\" name=\"button\" value=\"Speichern\">
		</form><br><br><br><hr></body></html>";

	}
	if ($_GET['aktion'] == "edit_admin"){
		if (!is_writable($configfile)){
			echo "Falsche Dateirechte f&uuml;r Configurationsdatei *.vsconfig";
			exit;
		}
		
		echo "<html><head><title>ViennaSoft PHP Administrator Einstellungen</title></head><body><h3>ViennaSoft PHP Administrator Grundeinstellungen bearbeiten</h3>
		<a href=\"admin.php?admin=$admin_login\">Zur&uuml;ck zur &Uuml;bersicht</a><br>
		<br><hr><br>
		<form action=\"admin.php?admin=$admin_login&bearbeiten=config&aktion=save\" method=\"post\">
		
		<input type=\"hidden\" name=\"iptime\" value=\"$c_iptime\">
		<input type=\"hidden\" name=\"ipsavetime\" value=\"$c_ipsavetime\">
		<input type=\"hidden\" name=\"onlinetime\" value=\"$c_onlinetime\">
		<input type=\"hidden\" name=\"anzeige\" value=\"$c_anzeige\">
		<br>Ihr Benutzername zum Einw&auml;hlen in den Adminbereich<br>
		<input type=\"text\" name=\"adm_name\" value=\"$admin_name\" size=\"20\">
		<br><br>Ihr Passwort zum Einw&auml;hlen in den Adminbereich<br>
		<input type=\"text\" name=\"adm_pw\" value=\"$admin_pw\" size=\"20\">
		<br><br>Der Zeitunterschied auf dem Server in Sekunden<br>
		<input type=\"text\" name=\"vs_servertimedif\" value=\"$vs_servertimedif\" size=\"20\">
		3600=1Stunde 7200=2Stunden -3600= -1Stunde<br><b>"; 
		echo date("d.m.Y H:i:s",time());
		echo "</b> = Das aktuelle Datum und die Zeit auf dem Server<br><b>";	
		echo date("d.m.Y H:i:s",$vs_timestamp);		
		echo "</b> = Das aktuelle Datum und die Zeit unter Ber&uuml;cksichtigung des gespeicherten Differenwertes.<br>";
		echo "<br>
		<input type=\"submit\" name=\"button\" value=\"Speichern\">
		</form><br><br><br><hr></body></html>";
	}

	elseif ($_GET['aktion'] == "save"){
		$cfig="|";
		if(isset($_POST['adm_name'])) {
			$admin_name=$_POST['adm_name'];
		}
		$cfig=$cfig.$admin_name."|";
		
		if(isset($_POST['adm_pw'])) {
			$admin_pw=$_POST['adm_pw'];
		}
		$cfig=$cfig.$admin_pw."|";

		if(isset($_POST['iptime'])) {
			$c_iptime=$_POST['iptime'];
		}	
		$cfig=$cfig.$c_iptime."|";

		if(isset($_POST['ipsavetime'])) {
			$c_ipsavetime=$_POST['ipsavetime'];
		}
		$cfig=$cfig.$c_ipsavetime."|";

		if(isset($_POST['onlinetime'])) {
			$c_onlinetime=$_POST['onlinetime'];
		}
		$cfig=$cfig.$c_onlinetime."|";

		if(isset($_POST['anzeige'])) {
			$c_anzeige=$_POST['anzeige'];
		}
		$cfig=$cfig.$c_anzeige."|";

		if(isset($_POST['vs_servertimedif'])) {
			$vs_servertimedif=$_POST['vs_servertimedif'];
		}
		$cfig=$cfig.$vs_servertimedif."|";

		$ftemp = fopen($configfile, "w");
			fwrite($ftemp, $cfig);
		fclose($ftemp);

		header("Location: admin.php?admin=$admin_login");
	}
}





//COUNTER
if ($_GET['bearbeiten'] == "counter"){

$c_file[0] = "counter_total.txt";
$c_file[1] = "counter_heute.txt";
$c_file[2] = "counter_gestern.txt";
$c_file[3] = "counter_tagesrekord.txt";
$c_file[4] = "counter_ips.txt";
$c_file[5] = "counter_referer.txt";
$c_file[6] = "counter_tage.txt";

	if(!isset($_GET['aktion'])) {
	
		for ($i = 0; $i < 7; $i++){
			if (!file_exists($c_file[$i])){
	     			echo "Fehler! Dateizugriff nicht moeglich. $c_file[$i]";
				exit;
		     	}
			if (!is_readable($c_file[$i]) || !is_writable($c_file[$i])){
				echo "falsche Dateirechte f&uuml;r Datendatei $c_file[$i]";
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

		echo "<html><head><title>ViennaSoft PHP Administrator Counter</title></head><body><h3>ViennaSoft PHP Administrator Counter bearbeiten</h3>
		<a href=\"admin.php?admin=$admin_login\">Zur&uuml;ck zur &Uuml;bersicht</a>
		<br><hr><br>
		<form action=\"admin.php?admin=$admin_login&bearbeiten=counter&aktion=save\" method=\"post\">

		<br>Counterstand total<br>
		<input type=\"text\" name=\"total\" value=\"$c_file_content[0]\" size=\"20\">
		<br>Counterstand heute<br>
		<input type=\"text\" name=\"heute\" value=\"$c_file_content[1]\" size=\"20\">
		<br>Counterstand gestern<br>
		<input type=\"text\" name=\"gestern\" value=\"$c_file_content[2]\" size=\"20\">
		<br>Tagesrekord(Der Tag mit den meisten Zugriffen)<br>
		Datum des Tages und Zugriffe<br>
		<input type=\"text\" name=\"rekorddate\" value=\"$c_recorddate\" size=\"20\">
		<input type=\"text\" name=\"rekord\" value=\"$c_file_content[3]\" size=\"20\">
		<br>
		<input type=\"submit\" name=\"button\" value=\"Speichern\">
		</form><br><br>

		<a href=\"admin.php?admin=$admin_login&bearbeiten=counter&aktion=delete_ip\">Inhalt der Datei mit den IP-Adressen der letzten Zugriffe l&ouml;schen.</a><br>
		<a href=\"admin.php?admin=$admin_login&bearbeiten=counter&aktion=delete_referer\">Inhalt der Datei mit den Refferenzadressen l&ouml;schen.</a><br>
		<a href=\"admin.php?admin=$admin_login&bearbeiten=counter&aktion=delete_days\">Inhalt der Datei mit den Zugriffen der einzelnen Tage l&ouml;schen.</a><br><br>
		<a href=\"admin.php?admin=$admin_login\">Zur&uuml;ck zur &Uuml;bersicht</a>
		<br><br><br><hr></body></html>";

	}
	elseif ($_GET['aktion'] == "save"){
		if(isset($_POST['total'])) {
			$ftemp = fopen($c_file[0], "w");
				fwrite($ftemp, $_POST['total']);
			fclose($ftemp);
		}
		if(isset($_POST['heute'])) {
			$ftemp = fopen($c_file[1], "w");
				fwrite($ftemp, $_POST['heute']);
			fclose($ftemp);
		}
		if(isset($_POST['gestern'])) {
			$ftemp = fopen($c_file[2], "w");
				fwrite($ftemp, $_POST['gestern']);
			fclose($ftemp);
		}
		if(isset($_POST['rekord'])&&isset($_POST['rekorddate'])) {
			$ftemp = fopen($c_file[3], "w");
				fwrite($ftemp, $_POST['rekord']."^".$_POST['rekorddate']);
			fclose($ftemp);
		}
	
		header("Location: admin.php?admin=$admin_login");
	}
	elseif ($_GET['aktion'] == "delete_ip"){
		$ftemp = fopen($c_file[4], "w");
			fwrite($ftemp, "");
		fclose($ftemp);
		header("Location: admin.php?admin=$admin_login&bearbeiten=counter");
	}

	elseif ($_GET['aktion'] == "delete_referer"){
		$ftemp = fopen($c_file[5], "w");
			fwrite($ftemp, "");
		fclose($ftemp);
		header("Location: admin.php?admin=$admin_login&bearbeiten=counter");
	}

	elseif ($_GET['aktion'] == "delete_days"){
		$ftemp = fopen($c_file[6], "w");
			fwrite($ftemp, "");
		fclose($ftemp);
		header("Location: admin.php?admin=$admin_login&bearbeiten=counter");
	}
	
}



function vs_imgupload($file_type,$vs_file) {
	if(!is_dir("upload_img")){
		mkdir("upload_img", 0777);
		chmod("upload_img", 0777);
	}

	$vs_file=trim($vs_file);
	$file_ext = substr(strtolower(strrchr($_FILES["$file_type"]["name"], ".") ), 1 );


	$allowed_ext = array('jpg', 'jpeg', 'gif', 'png');
	if (empty($file_ext) || !in_array($file_ext, $allowed_ext)) {
		return "";
	}

	if (isset($_POST["$file_type"."all_ext"])){
		$user_ext = explode(",", $_POST["$file_type"."all_ext"]);
		if (!in_array($file_ext, $user_ext)) {
			return "";
		}
	}
	
	if(move_uploaded_file($_FILES["$file_type"]["tmp_name"], "upload_img/$vs_file.$file_ext")){
		if (isset($_POST["$file_type"."maxwidth"]) || isset($_POST["$file_type"."maxheight"])){
			ResizeImage("upload_img/$vs_file.$file_ext", $_POST["$file_type"."maxwidth"], $_POST["$file_type"."maxheight"]);
		}
		chmod("upload_img/"."$vs_file"."."."$file_ext", 0644);
		return "upload_img/$vs_file.$file_ext";}
	else{
		return "";}
}





function vs_fileupload($file_type,$vs_file) {
	if(!is_dir("upload_file")){
		mkdir("upload_file", 0777);
		chmod("upload_file", 0777);
	}

	$vs_file=trim($vs_file);
	$file_ext = substr(strtolower(strrchr($_FILES["$file_type"]["name"], ".") ), 1 );
	$notallowed_ext = array('php');

	if (empty($file_ext) || in_array($file_ext, $notallowed_ext)) {
		return "";
	}

	if (isset($_POST["$file_type"."all_ext"])){
		$user_ext = explode(",", $_POST["$file_type"."all_ext"]);
		if (!in_array($file_ext, $user_ext)) {
			return "";
		}
	}
	
	if(move_uploaded_file($_FILES["$file_type"]["tmp_name"], "upload_file/$vs_file.$file_ext")){
		chmod("upload_file/$vs_file.$file_ext", 0644);
		return "upload_file/$vs_file.$file_ext";}
	else{
		return "";}
} 



function ResizeImage($url, $maxwidth, $maxheight){

$maxwidth=intval($maxwidth);
$maxheight=intval($maxheight);

  $size=getimagesize($url); 
  $breite=$size[0]; 
  $hoehe=$size[1];


if($maxwidth==0){
$maxwidth=$breite;
}

if($maxheight==0){
$maxheight=$hoehe;
}


$neueBreite=$maxwidth; 
$neueHoehe=intval($hoehe*$neueBreite/$breite);


if($neueHoehe>$maxheight) { 
$neueHoehe=$maxheight; 
$neueBreite=intval($breite*$neueHoehe/$hoehe);
}

  if($size[2]==1) { 
  // GIF 
  $altesBild=ImageCreateFromGIF($url); 
  $neuesBild=ImageCreate($neueBreite,$neueHoehe); 
  ImageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe); 
  ImageGIF($neuesBild,$url); 
  }

  if($size[2]==2) { 
  // JPG 
  $altesBild=ImageCreateFromJPEG($url); 
  $neuesBild=imageCreateTrueColor($neueBreite,$neueHoehe); 
  ImageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe); 
  ImageJPEG($neuesBild,$url,80); 
  } 

  if($size[2]==3) { 
  // PNG 
  $altesBild=ImageCreateFromPNG($url); 
  $neuesBild=imageCreateTrueColor($neueBreite,$neueHoehe); 
  ImageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe); 
  ImagePNG($neuesBild,$url,9); 
  } 
} 




function vs_setdata() {//schreiben vs_php nach veränderung eines DS
global $read_arr, $vs_data_count;
	$speichern = fopen($_GET['filepath'],"w");
	for($i=0;$i<$vs_data_count;$i++){
		fwrite($speichern,$read_arr[$i]."|");
	}
	fclose($speichern);
}



function vs_getdata() {//lesen vs_php
global $read_arr, $vs_data_count;

	$filehandle = fopen($_GET['filepath'],"r");
	if(filesize($_GET['filepath'])>0){
		$read_file = fread($filehandle,filesize($_GET['filepath']));
	}
	else{
		$read_file = "";
	}
	fclose ($filehandle);
	$read_arr = explode("|", $read_file);
	$vs_data_count = (count($read_arr)-1);
}



function vsget_str($ersetz) {
$ersetz = stripslashes($ersetz);
$ersetz = htmlentities($ersetz, ENT_QUOTES);
$ersetz = str_replace("\n", "<br />", $ersetz);
$ersetz = str_replace("^","&#94;",$ersetz);
$ersetz = str_replace("|", "&#124;", $ersetz);
return $ersetz;
}

function vsget_strrev($ersetz) {
$ersetz = stripslashes($ersetz);
$ersetz = str_replace("<br />", "\n", $ersetz);
$ersetz = str_replace("^","&#94;",$ersetz);
$ersetz = str_replace("|", "&#124;", $ersetz);
return $ersetz;
}



function vslogin(){
global $admin_pw, $admin_name, $admin_login, $vs_timestamp;

//admin anmeldung
$cookieanmeldung = 0;
if(isset($_COOKIE['admin_pw']) && isset($_COOKIE['admin_name'])){ 
	if($_COOKIE['admin_pw'] == $admin_pw){
		if($_COOKIE['admin_name'] == $admin_name){
			$cookieanmeldung = 1;
		}
	}
}

if($cookieanmeldung != 1){
	if(!isset($_GET['admin'])) {
  		if(!isset($_POST['admin_pw']) || $_POST['admin_pw'] != $admin_pw || !isset($_POST['admin_name']) || $_POST['admin_name'] != $admin_name) {
			echo "<html><head><title>ViennaSoft PHP Administrator Anmeldung</title></head><body><h3>ViennaSoft PHP Administrator Anmeldung</h3><br><hr><br>
			Geben Sie Ihren Benutzernamen und Ihr Passwort ein. <br>Beachten Sie die Gro&szlig;-Kleinschreibung!
			
			<form action=\"admin.php\" method=\"post\">
			<br>Ihr Benutzername<br>
			<input type=\"text\" name=\"admin_name\" value=\"\" size=\"20\">
			<br>Ihr Passwort<br>
			<input type=\"password\" name=\"admin_pw\" value=\"\" size=\"20\">
			<input type=\"submit\" name=\"button\" value=\"Anmelden\">
			</form>
			<br><br><br><hr><br>Sollte diese Seite nach der Anmeldung nochmals angezeigt werden haben Sie das Passwort oder Ihren Benutzernamen falsch eingegeben.
			<br></body></html>";
			exit;
  		}
  		else{
			//admin login schreiben
			$ftemp = fopen("admin.txt", "w");
			fwrite($ftemp, $vs_timestamp);
			fclose($ftemp);
			$admin_login=$vs_timestamp;
			setcookie("admin_name", $admin_name, time()+36000, "/");
			setcookie("admin_pw", $admin_pw, time()+3600, "/");
  		}
	}
	else{
		if (file_exists("admin.txt")){
			$ftemp = fopen("admin.txt","r");
			$admin_login = fread($ftemp, filesize("admin.txt"));
			fclose($ftemp);
			
			if ($_GET['admin'] != $admin_login){
				echo "Bitte melden Sie sich als Admin an.<br><br><a href=\"admin.php\">Zum Anmeldeformular</a>";
				exit;
			}
		}
		else{
			echo "Bitte melden Sie sich als Admin an.<br><br><a href=\"admin.php\">Zum Anmeldeformular</a>";
			exit;
		}
	}
}

return 1;
}





?>
