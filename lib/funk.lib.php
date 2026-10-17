<?php

function is_vs ($id)
{
	$query  = "SELECT v.id, v.name FROM vereine v";
	$query .= " WHERE v.vs_id = $id AND !(v.id % 1000)";
	
	$result = mysql_query ($query);
	
	
	
	if ($result)
	{
		$row = mysql_fetch_array ($result);
		return $row;
		echo "=";
	}
	else
	{
		return 0;
	}	
	

}

function is_kv ($id)
{
	$query  = "SELECT v.id, v.name FROM vereine v";
	$query .= " WHERE v.vs_id = $id AND (v.id % 1000)";
	$result = mysql_query ($query);
	
	
	
	if ($result)
	{
		$row = mysql_fetch_array ($result);
		return $row;
		echo "=";
	}
	else
	{
		return 0;
	}
}

function is_sw ($id)
{
	$query  = "SELECT v.id, v.name FROM vereine v";
	$query .= " WHERE v.sw_id = $id";
	
	$result = mysql_query ($query);
	
	if ($result)
	{
		$row = mysql_fetch_array ($result);
		return $row;
	}
	else
	{
		return 0;
	}	
}

function is_jw ($id)
{
	$query  = "SELECT v.id, v.name FROM vereine v";
	$query .= " WHERE v.jw_id = $id";
	
	$result = mysql_query ($query);
	
	if ($result)
	{
		$row = mysql_fetch_array ($result);
		return $row;
	}
	else
	{
		return 0;
	}	
}

function is_fw ($id)
{
	$query  = "SELECT v.id, v.name FROM vereine v";
	$query .= " WHERE v.fw_id = $id";
	
	$result = mysql_query ($query);
	
	if ($result)
	{
		$row = mysql_fetch_array ($result);
		return $row;
	}
	else
	{
		return 0;
	}	
}


function print_funk ($id, $vorname, $name, $strasse, $plz, $ort, $tel, $handy, $fax, $email, $funk=0) {
	

	printf ("<p><strong>%s %s</strong><font size=\"-1\">",
		$vorname,
		$name);
	
	if ($funk) {
		$vs = is_vs($id);
		$kv = is_kv($id);	
		$sw = is_sw($id);
		$jw = is_jw($id);
		$fw = is_fw($id);
		echo "<br>";
	}
	
	if ($vs || $sw || $jw || $fw) {
		echo "<br>";
		if ($vs) {
			echo "Vereinsvorsitzender";
			$verein = $vs;
			
		}		
		if ($sw){
			echo "Kreissportwart";
			$verein = $sw;		
		}
		if ($jw){
			echo "Kreisdwart";
			$verein = $jw;
		}
		if ($fw){
			echo "Kreisjugendwart";	
			$verein = $fw;
		}
		printf (' - <a href="kvs.php?id=%d">%s</a>',
			$verein["id"],
			$verein["name"]);			
	}
	
	if ($kv) {
		echo "<br>";
		echo "Klubvorsitzender";
		
		printf (' - <a href="kvs.php?id=%d">%s</a>',
			$kv["id"],
			$kv["name"]);
	}
		
	printf ("<br><br>%s<br>%05d %s<br>",
		$strasse,
		$plz,
		$ort);
					
	printf ("<br>Tel.: %s",
		$tel);
	
	
	printf ("<br>Fax: %s",
		$fax);
		
	printf ("<br>Handy: %s",
		$handy);		
		
	printf ('<br>E-Mail: <a href="mailto:%s">%s</a></font></p>',
		$email,
		$email);
}

?>