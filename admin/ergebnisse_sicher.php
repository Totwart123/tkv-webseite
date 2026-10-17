<?php
include ("../header.inc.php");
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
	
	if (substr($staffelbericht, 2, 1) == "V" && substr($staffelbericht, 3, 1) == "1")
		$filename .= "/Liga1";
	if (substr($staffelbericht, 2, 1) == "L" && substr($staffelbericht, 3, 1) == "1")
		$filename .= "/Liga2";
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
			case("NHV10");
			$kennung = "KETH050 $staffelbericht";
			break;		
			case("NHL11");
			$kennung = "KETH052 $staffelbericht";
			break;
			case("NHL12");
			$kennung = "KETH053 $staffelbericht";
			break;
			case("NHK11");
			$kennung = "KETH056 $staffelbericht";
			break;
			case("NHK12");
			$kennung = "KETH057 $staffelbericht";
			break;
			case("NHK13");
			$kennung = "KETH058 $staffelbericht";
			break;
			case("NHK14");
			$kennung = "KETH066 $staffelbericht";
			break;
			case("NHK15");
			$kennung = "KETH067 $staffelbericht";
			break;		
			case("NHK21");
			$kennung = "KETH060 $staffelbericht";
			break;
			case("NHK22");
			$kennung = "KETH061 $staffelbericht";
			break;
			case("NHK23");
			$kennung = "KETH062 $staffelbericht";
			break;
			case("NHK24");
			$kennung = "KETH063 $staffelbericht";
			break;
			case("NHK25");
			$kennung = "KETH064 $staffelbericht";
			break;
			case("NDV11");
			$kennung = "KETH051 $staffelbericht";
			break;
			case("NDV12");
			$kennung = "KETH059 $staffelbericht";
			break;
			//Playoff / Quali Frauen Verbandsliga
			//case("NDV13");
			//$kennung = "KETH064 $staffelbericht";
			//break;
			case("NDL11");
			$kennung = "KETH054 $staffelbericht";
			break;
			case("NDL12");
			$kennung = "KETH055 $staffelbericht";
			break;
			case("NJV11");
			$kennung = "KETH140 $staffelbericht";
			break;
			//case("NJV12");
			//$kennung = "KETH142 $staffelbericht";
			//break;		
			case("NSV10");
			$kennung = "KETH150 $staffelbericht";
			break;
			case("NSL11");
			$kennung = "KETH151 $staffelbericht";
			break;
			case("NSL12");
			$kennung = "KETH152 $staffelbericht";
			break;
			case("AHL10");
			$kennung = "KETH003 $staffelbericht";
			break;
			case("AHK11");
			$kennung = "KETH046 $staffelbericht";
			break;
			case("AHK12");
			$kennung = "KETH047 $staffelbericht";
			break;
			case("AHK13");
			$kennung = "KETH048 $staffelbericht";
			break;
			//case("AHK14");
			//$kennung = "KETH049 $staffelbericht";
			//break;		
			case("ADV10");
			$kennung = "KETH005 $staffelbericht";
			break;
			case("ADL11");
			$kennung = "KETH011 $staffelbericht";
			break;
			case("ADL12");
			$kennung = "KETH012 $staffelbericht";
			break;
			case("ADL13");
			$kennung = "KETH013 $staffelbericht";
			break;
			case("ADL14");
			$kennung = "KETH014 $staffelbericht";
			break;		
			case("ASV10");
			$kennung = "KETH101 $staffelbericht";
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
			case("ASK14");
			$kennung = "KETH109 $staffelbericht";
			break;
			case("ASK15");
			$kennung = "KETH125 $staffelbericht";
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
