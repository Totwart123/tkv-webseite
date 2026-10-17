<?
include ("../header.inc.php");

include ("../conf/config.inc.php");
include ("../lib/error.lib.php");
include ("../lib/db.lib.php");
include ("../lib/funk.lib.php");
include ("../lib/misc.lib.php");

?>

<?
if ($slide) {
	$row = mysql_fetch_array (db_query ("SELECT COUNT(*) count FROM vereine"));

	fuh ($row["count"], 1, $show, $SCRIPT_NAME . "?slide=1&show");
}

if (!$subid)
	$subid = 0;

if ($id%1000){
	$sql = "SELECT name FROM vereine
		WHERE id = ($id - ($id%1000))";
	$result = db_query($sql);	
	$row = mysql_fetch_array ($result);	
		
	echo "<h2>Klubs des " . $row["name"] . "</h2>";
} else
	echo "<h2>Kreisvereine im TKV</h2>";

$sql = "SELECT v.id v_id, v.v_subid v_subid, v.name v_name, v.id v_id, 
	v_subid, v.vs_id v_vs,
	v.website v_website FROM vereine v";

if ($id) {
	$sql .= " WHERE v.id = $id AND v.v_subid = $subid ";
} else if ($slide) {
	if (!$show)
		$show=0;		
	
	$sql .= " ORDER BY id LIMIT $show, 1";
}
	
$result = db_query ($sql);

while ($res = mysql_fetch_array ($result))
{
	$id = $res["v_id"];
	
	$subid = $res["v_subid"];	
	
	
	printf ("<h3>%s (<font color=\"#FF3030\">%02d %03d</font>) <font size=\"-2\">[<a href=\"edit_verein.php?id=%d&subid=%d\">ändern</a>
	| <a href=\"del_verein.php?id=%d&subid=%d\">löschen</a>]</font></h3>",		
		$res["v_name"],
		$res["v_id"]/1000,
		$res["v_id"]%1000,
		$res["v_id"],
		$res["v_subid"],
		$res["v_id"],
		$res["v_subid"]);
		
	printf ("Website: <a href=\"%s\">%s</a><br><br>",
		$res["v_website"],
		$res["v_website"]);
		
	if (!($id%1000))
		printf ('[ <a href="kv.php?id=%d">Klubs im %s</a> ]',
			$id,
			$res["v_name"]);
			
	
	$sql = "SELECT f.* FROM funktionaere f, vereine v
		WHERE v.vs_id = f.id AND v.id = $id AND v.v_subid=$subid";		
	$result = db_query ($sql);	
	if ($row = mysql_fetch_array ($result))	{
		
		echo "<h4>" . 
			(($id%1000)?"Klubvorsitzender":"Vereinsvorsitzender") .
		"</h4>";
		
		echo "<font size=\"-1\">[<a href=\"edit_funktionaer.php?id=" . $row["id"] ."\">
			ändern</a> ]</font>";
		print_funk ($row["id"], $row["vorname"], $row["name"],
			 $row["strasse"], $row["plz"], $row["ort"],
			 $row["tel"], $row["handy"], $row["fax"], $row["email"]);	
	} else {
		echo "<hr><h4>Vorsitzender</h4> Nicht besetzt";
	}
	
	if (!($id%1000))
	{
	
	
	$sql = "SELECT f.* FROM funktionaere f, vereine v
		WHERE v.sw_id = f.id AND v.id = $id AND v.v_subid=$subid";		
	$result = mysql_query ($sql);	
	if ($row = mysql_fetch_array ($result)) {		
		echo "<hr>";
		echo "<h4>Kreissportwart</h4>";
		echo "<font size=\"-1\">[<a href=\"edit_funktionaer.php?id=" . $row["id"] ."\">
			ändern</a> ]</font>";
		print_funk ($row["id"], $row["vorname"], $row["name"],
			 $row["strasse"], $row["plz"], $row["ort"],
			 $row["tel"], $row["handy"], $row["fax"], $row["email"]);	
	} else {
		echo "<hr><h4>Kreissportwart</h4> Nicht besetzt";
	}
	
	$sql = "SELECT f.* FROM funktionaere f, vereine v
	WHERE v.jw_id = f.id AND v.id = $id AND v.v_subid=$subid";		
	$result = mysql_query ($sql);	
	if ($row = mysql_fetch_array ($result)) {	
		echo "<hr>";		
		echo "<h4>Kreisjugend</h4>";	
		echo "<font size=\"-1\">[<a href=\"edit_funktionaer.php?id=" . $row["id"] ."\">
			ändern</a> ]</font>";		
		print_funk ($row["id"], $row["vorname"], $row["name"],
			 $row["strasse"], $row["plz"], $row["ort"],
			 $row["tel"], $row["handy"], $row["fax"], $row["email"]);
		
	} else {
		echo "<hr><h4>Kreisjugendwart</h4> Nicht besetzt";
	}
	
		
	$sql = "SELECT f.* FROM funktionaere f, vereine v
		WHERE v.fw_id = f.id AND v.id = $id AND v.v_subid=$subid";		
	$result = db_query ($sql);
	
	if ($row = mysql_fetch_array ($result))	{	
		echo "<hr>";		
		echo "<h4>Finanzwart</h4>";
		echo "<font size=\"-1\">[<a href=\"edit_funktionaer.php?id=" . $row["id"] ."\">
			ändern</a> ]</font>";			
		print_funk ($row["id"], $row["vorname"], $row["name"],
			 $row["strasse"], $row["plz"], $row["ort"],
			 $row["tel"], $row["handy"], $row["fax"], $row["email"]);	
	} else	{
		echo "<hr><h4>Finanzwart</h4> Nicht besetzt";
	}
		
	}
	
	
	if ($id%1000) {	
		$query = "SELECT v.kb1_name, v.kb1_addr, v.kb1_nrb, v.kb1_tel, v.kb1_art, v.kb1_drucker FROM vereine v WHERE v.id = $id AND v.v_subid=$subid";
		
		$res = mysql_query ($query);
	
		$row = mysql_fetch_array ($res);		
		
	
		if ($row["kb1_name"]) {
			printf('<hr>
				<h4>%s:</h4>
				%s<br>
				Tel.: %s<br><br>
				%s Bahnen - %s, %s Drucker<br>',
				$row["kb1_name"],
				$row["kb1_addr"],
				$row["kb1_tel"],
				$row["kb1_nrb"],
				$row["kb1_art"],
				($row["kb1_drucker"]?"mit":"ohne"));				
		}
		
		

		$query = "SELECT v.kb2_name, v.kb2_addr, v.kb2_nrb, v.kb2_tel, v.kb2_art, v.kb2_drucker FROM vereine v WHERE v.id = $id AND v.v_subid=$subid";
		
		$res = mysql_query ($query);	
		
		$row = mysql_fetch_array ($res);		
		
	
		if ($row["kb2_name"]) {
			printf('<hr>
				<h4>%s:</h4>
				%s<br>
				Tel.: %s<br><br>
				%s Bahnen - %s, %s Drucker',
				$row["kb2_name"],
				$row["kb2_addr"],
				$row["kb2_tel"],
				$row["kb2_nrb"],
				$row["kb2_art"],
				($row["kb2_drucker"]?"mit":"ohne"));				
		}	
		
	}
	
	echo '<hr noshade size="2">';
	
	
	
	
}

mysql_close ();	

include ("../footer.inc.php");
?>
