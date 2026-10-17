<?php

include ("../lib/error.lib.php");
?> 

<?php
$sb=false; /* Staffelbericht */
$rw=false; /* Umlaute umschreiben */

$staffelbericht = $_FILES['staffelbericht']['name'];
$action = $_POST['action'];
if ($action == "add")
{
	$staffelbericht = strtoupper($staffelbericht);
	$filename = "../ergebnisse";
	if (substr($staffelbericht, 0, 1) == "A")
		$filename .= "/100-200-Wurf";
	if (substr($staffelbericht, 0, 1) == "N")
		$filename .= "/120-Wurf";
  
	if (substr($staffelbericht, 1, 1) == "H")
		$filename .= "/Maenner";
	if (substr($staffelbericht, 1, 1) == "D")
		$filename .= "/Frauen";
	if (substr($staffelbericht, 1, 1) == "S")
		$filename .= "/Senioren-A";
	if (substr($staffelbericht, 1, 1) == "B")
		$filename .= "/Senioren-B";
	if (substr($staffelbericht, 1, 1) == "J")
		$filename .= "/Jugend";
	
	//if (substr($staffelbericht, 2, 1) == "V" && substr($staffelbericht, 3, 1) == "1")
	//	$filename .= "/Liga1";
	//if (substr($staffelbericht, 2, 1) == "L" && substr($staffelbericht, 3, 1) == "1")
	//	$filename .= "/Liga2";
	if (substr($staffelbericht, 2, 1) == "L" && substr($staffelbericht, 3, 1) == "1")
		$filename .= "/Liga1";
	if (substr($staffelbericht, 2, 1) == "K" && substr($staffelbericht, 3, 1) == "1")
		$filename .= "/Klasse1";
	if (substr($staffelbericht, 2, 1) == "K" && substr($staffelbericht, 3, 1) == "2")
		$filename .= "/Klasse2";
		
	if (substr($staffelbericht, 4, 1) != "0")
		$filename .= "/St-" . $staffelbericht[4];
		
	if (!dir ("../ergebnisse/$filename"))
		$add_error = 1;
	
	if (substr($staffelbericht, 5, 1) == "E")
	{
		$rw=true;
		$filename .= "/" . 
		$staffelbericht[0] .
		$staffelbericht[1] .
		$staffelbericht[2] .
		$staffelbericht[3] .
		$staffelbericht[4] . "EW.txt";
	} 
	else if (substr($staffelbericht, 5, 1) == "A")
	{
		$filename .= "/". 
		$staffelbericht[0] .
        $staffelbericht[1] .
        $staffelbericht[2] .
		$staffelbericht[3] .
        $staffelbericht[4] . "AN.pdf";
	} 
	else if (substr($staffelbericht, 5, 1) == "M" && substr($staffelbericht, 6, 1) == "L")
	{
		$filename .= "/". 
		$staffelbericht[0] .
        $staffelbericht[1] .
        $staffelbericht[2] .
		$staffelbericht[3] .
        $staffelbericht[4] . "ML.pdf";
	} 
	else if (substr($staffelbericht, 5, 1) == "M" && substr($staffelbericht, 6, 1) == "S")
	{
		$filename .= "/".
	    $staffelbericht[0] .
		$staffelbericht[1] .
		$staffelbericht[2] .
		$staffelbericht[3] .
		$staffelbericht[4] . "MS.pdf";
	} 
	else if (substr($staffelbericht, 5, 1) == "S" && substr($staffelbericht, 6, 1) == "I")
	{
		$filename .= "/".
	    $staffelbericht[0] .
		$staffelbericht[1] .
		$staffelbericht[2] .
		$staffelbericht[3] .
		$staffelbericht[4] . "SI.7z";
	} 
	else if (substr($staffelbericht, 5, 1) == "O")
	{
		$filename .= "/".
        $staffelbericht[0] .
        $staffelbericht[1] .
        $staffelbericht[2] .
		$staffelbericht[3] .
        $staffelbericht[4] . "OE.pdf";
	} 
	else 
	{
		$sb=true;
		$rw=true;

		$filename .= "/spieltag";
		if (substr($staffelbericht, 6, 1) != "0")
			$filename .= $staffelbericht[6].$staffelbericht[7].".txt";
		else
			$filename .= $staffelbericht[7].".txt";
		$liga = (substr($staffelbericht, 0, 5));
		switch($liga)
		{
			case("AHK11");
			$kennung = "KETH046 $staffelbericht";
			break;
			case("AHK12");
			$kennung = "KETH047 $staffelbericht";
			break;
			case("AHK13");
			$kennung = "KETH048 $staffelbericht";
			break;
			case("ADK11");
			$kennung = "KETH011 $staffelbericht";
			break;
			case("ADK12");
			$kennung = "KETH012 $staffelbericht";
			break;
			case("ASL11");
			$kennung = "KETH104 $staffelbericht";
			break;
			case("ASL12");
			$kennung = "KETH105 $staffelbericht";
			break;
			case("ASK11");
			$kennung = "KETH106 $staffelbericht";
			break;
			case("ASK12");
			$kennung = "KETH107 $staffelbericht";
			break;
			case("ASK13");
			$kennung = "KETH108 $staffelbericht";
			break;					
			case("ABK11");
			$kennung = "KETH118 $staffelbericht";
			break;
			case("ABK12");
			$kennung = "KETH119 $staffelbericht";
			break;
			case("ABK13");
			$kennung = "KETH120 $staffelbericht";
			break;		
			default;
			$add_error = 1;
			break; 
		}
	}
	
	if (!$add_error) 
	{
		if (file_exists("$filename"))
		{
			chmod("$filename", 0777);
			unlink("$filename");
		}
		move_uploaded_file($_FILES['staffelbericht']['tmp_name'], "$filename");
		
		if ($rw)
		{
			$fp1 = fopen($filename, "r");
			$tmp="";
			//while ($buf = fread ($fp1, filesize($filename)))
			while ($buf = fread ($fp1, 20480))
			{
				if ($rw) 
				{
					$buf = strtr($buf, "\x81", "ü");
					$buf = strtr($buf, "\x84", "ä");
					$buf = strtr($buf, "\x94", "ö");
					$buf = strtr($buf, "á", "ß");
					$buf = strtr($buf, "\x8e", "Ä");
					$buf = strtr($buf, "\x9a", "Ü");
					$buf = strtr($buf, "\x99", "Ö");
				}
				fclose($fp1);
				$fp1 = fopen($filename, "w");
				fwrite ($fp1, $buf);
				$tmp.=$buf;
				fclose($fp1);
			}
		}
		status ("Die Daten wurden in $filename geschrieben.");
		if ($sb) 
		{
			if(mail("tabellen@tkv-kegeln.de","Ergebnismeldung $kennung",$tmp))
				status("Die Staffelbericht wurde versandt."); 
			else status("FEHLER: Der Staffelbericht wurde nicht an die " .
				"Tabellenredaktion geschickt. Bitte sende noch eine Mail " .
				"mit den Ergebnissen an tabellen@zgt.de!");
				mail("webmaster@tkv-kegeln.de","Ergebnismeldung $kennung",$tmp);
		}
	}
	if ($add_error)
		warning ("Der Dateiname $filename ist nicht korrekt nach dem Schema benannt! Der Dateiname lautete $staffelbericht !");
}

{
	

?>

<h3>Administration / Ergebnisse</h3>

<form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>"
	method="POST"
	enctype="multipart/form-data">
	
	<input type="hidden"
		name="MAX_FILE_SIZE"
		value="3000000">	
	<input type="file"
		name="staffelbericht"
		
		size="50">
	<input type="hidden" name="action" value="add"><br>
	<input value="Eintragen" type="submit">
</form>


<?php
}
?>

<?php
include ("../footer.inc.php");
?>
