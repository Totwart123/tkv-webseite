<?
// CAPTCHA-Skript zur Vermeidung von SPAM-Einträgen
// Überprüfung, ob eingegebener Code korrekt ist 

if($GLOBALS['REQUEST_METHOD'] == 'POST' || count($_POST) > 0) 
{ 
$pwf_message = "Der eingegebene Code stimmt nicht mit dem im Bild hinterlegten Code &uuml;berein. 
Klicken Sie in Ihrem Browser auf die Schaltfl&auml;che 'Zur&uuml;ck' und geben Sie den korrekten Code ein.";

if(strlen($_POST['protectwebformcode']) > 30) 
{
		die($pwf_message);
}

$protectwebformresult = 
	@file_get_contents( "http://protectwebform.com/verify01?vui=34997&vp=hpxc2nsg5v0w&ri=" . 
	urlencode($_SERVER['REMOTE_ADDR']) . "&vs=" . 
	urlencode($_POST['protectwebformcode'])); 
if(preg_match("|<authorization status=\"0\"|", $protectwebformresult)) 
{ 
    echo "Warning. You are not authorithed to use image protection provided by 
		http://www.protectwebform.com. Read http://www.protectwebform.com 
		FAQ for more information test"; 
} 
elseif(!preg_match("|<verification result=\"yes\"/>|", $protectwebformresult)) 
{ 
    die($pwf_message); 
}
}                                                                            
//


function db2News ($foo = 0, $old=0) 
{
	global $dbHost, $dbUser, $dbPass, $dbName;
	
	mysql_connect ($dbHost, $dbUser, $dbPass);
	mysql_select_db ($dbName);
	
	if ($foo && !$id)
		$query = "SELECT * FROM neuigkeiten ORDER BY datum DESC";
	elseif ($id)
		$query = "SELECT * FROM neuigkeiten WHERE id = $id ORDER BY datum DESC";
	else 
		$query = "SELECT * FROM neuigkeiten WHERE active = 1 ORDER BY datum DESC";

	if (!$old)
		$query .= " LIMIT 0,12";

	$r = mysql_query ($query);

	for ($i=0; $res = mysql_fetch_array ($r); $i++) 
	{
		$news[$i] = new News ($res["id"], $res["datum"], $res["autor"],
			$res["neuigkeit"], $res["info"], $res["active"]);
	}
	
	mysql_close();	
	
	return $news;
}

function db2termine () 
{
        global $dbHost, $dbUser, $dbPass, $dbName;

        mysql_connect ($dbHost, $dbUser, $dbPass);
        mysql_select_db ($dbName);
                             
	$query = "SELECT * FROM termine ORDER BY datum DESC";	
	
	$r = mysql_query ($query);

	for ($i=0; $res = mysql_fetch_array ($r); $i++) 
	{
		$termine[$i] = new Termin ($res["id"], $res["datum"], $res["termin"],
			$res["info"]);
	}
	
	mysql_close();	
	
	return $termine;
}

function isAdmin() 
{
	return 1;
}

class NewsList 
{
	var $news;
	
	function NewsList ($old = 0) 
	{
		$this->news = db2News (1, $old);
	}
	
	function NewsById ($id) 
	{
		for ($i = 0; $this->news[$i]; $i++) 
		{
			if ($this->news[$i]->getId() == $id) 			
				return $this->news[$i];
		}	
	
	}		
	
	function active ($id) 
	{
		$nws = $this->NewsById ($id);
		$nws->toggleActive();
		$nws->update();	
		
		echo "Die Nachicht mit der ID " . $nws->getId() . " (\"" .
			$nws->getTitle() . "\") und dem Datum " . $nws->getDate() . " wurde " . ($nws->isActive()?"":"de") .
			"aktiviert.";
	}
	
	function add ($admin, $foo, $date, $author, $title, $text) 
	{
		if ($foo) 
		{
			$nws = new News (-1, $date, $author, $title, $text);
			if ($admin) 
			{
				$nws->setActive (1);
			} 
			else 
			{
				$nws->setActive (0);
				mail ("webmaster@tkv-kegeln.de", "Neuigkeit vorgeschlagen","Title: $title\nAutor: $author\n$text");
			}
			$nws->insert();
		} 
		else 
		{
			if ($admin)
				$SCRIPT_NAME = "news.php";
			else
				$SCRIPT_NAME = "index.php";
				
                        echo "<p>Bitte prüfen Sie zunächst, ob es sich wirklich um eine " .
                             "Neuigkeit handelt, die Sie vorschlagen wollen. Für Kommentare, " .
                             "Lob oder Kritik, Verbesserungsvorschläge usw. steht unser " .
                             "Gästebuch zur Verfügung!</p>";
			echo "<form action=\"$SCRIPT_NAME\" method=\"POST\">";
		
			echo "<table>";
			
			echo "<tr><td><strong>Datum:</strong></td>";
			echo "<td><input type=\"text\" name=\"datum\" size=\"12\" value=\"" .
				date ("d. m. Y") .
				"\"></td>";
			echo "<tr>";
			
			echo "<tr><td><strong>Autor:</strong></td>";
			echo "<td><input type=\"text\" name=\"author\" value=\"" .
				"\"></td>";
			echo "<tr>";
		
			echo "<tr><td><strong>Titel:</strong></td>";
			echo "<td><input type=\"text\" name=\"title\" value=\"" .
				"\"></td>";
			echo "<tr>";
		
			echo "<tr><td><strong>Text:</strong></td>";
			echo "<td><textarea name=\"text\" rows=\"7\" cols=\"100\">".
				"</textarea></td>";
			echo "<tr>";
			
			echo "<tr><td><strong>Bildercode:</strong></td>";
			echo "<td><img src=\"http://protectwebform.com/image/34997/".
				"\"></td>";
			echo "<tr>";
			
			echo "<tr><td><strong>Code aus Grafik:</strong></td>";
			echo "<td><input type=\"text\" name=\"protectwebformcode\" value=\"".
				"\"></td>";
			echo "<tr>";
					
			echo "</table>";
			
			echo "<input name=\"add_submit\" value=\"Absenden\" type=\"submit\">";
			echo "<input value=\"Formular löschen\" type=\"reset\">";
			
			echo "</form>";
		}
	}
	
	function edit ($id, $date, $author, $title, $text) 
	{
		$datum = $_POST['datum'];
		$nws = $this->NewsById ($id);
		
		$SCRIPT_NAME = "news.php";
		
		if ($date) 
		{
			$nws->setDate ($date);
			$nws->setAuthor ($author);
			$nws->setTitle ($title);
			$nws->setText ($text);
			$nws->update();		
		} 
		else 
		{
			echo "<form action=\"$SCRIPT_NAME?edit=$id\" method=\"POST\">";
		
			echo "<table>";
		
			echo "<tr><td><strong>Datum:</strong></td>";
			echo "<td><input type=\"text\" name=\"datum\" size=\"12\" value=\"" .
				$nws->getDate() .
				"\"></td>";
			echo "<tr>";
		
			echo "<tr><td><strong>Autor:</strong></td>";
			echo "<td><input type=\"text\" name=\"author\" value=\"" .
				$nws->getAuthor() .
				"\"></td>";
			echo "<tr>";
		
			echo "<tr><td><strong>Titel:</strong></td>";
			echo "<td><input type=\"text\" name=\"title\" value=\"" .
				$nws->getTitle() .
				"\"></td>";
			echo "<tr>";
		
			echo "<tr><td><strong>Text:</strong></td>";
			echo "<td><textarea name=\"text\" rows=\"7\" cols=\"50\">".
				$nws->getText() .
				"</textarea></td>";
			echo "<tr>";
			
			echo "<tr><td><strong>Bildercode:</strong></td>";
			echo "<td><img src=\"http://protectwebform.com/image/34997/".
				"\"></td>";
			echo "<tr>";
			
			echo "<tr><td><strong>Code aus Grafik:</strong></td>";
			echo "<td><input type=\"text\" name=\"protectwebformcode\" value=\"".
				"\"></td>";
			echo "<tr>";
					
			echo "</table>";
			
			echo "<input name=\"submit\" value=\"Absenden\" type=\"submit\">";
			echo "<input value=\"Formular löschen\" type=\"reset\">";
				
			echo "</form>";
		}
	}
	
	function delete ($id) 
	{
		$nws = $this->NewsById ($id);
		$nws->delete();
		
		echo "Die Nachicht mit der ID " . $nws->getId() . " (\"" .
			$nws->getTitle() . "\") wurde gelöscht.";
	}
	
	function show ($admin = 0) 
	{
		if ($admin && !isAdmin()) 
		{
			return 0;
		}
		
		if ($admin)
			echo "<table width=\"100%\">";
		
		for ($i=0; $this->news[$i]; $i++) 
		{
			/* don't show inactive entries to normal users */
			if (!$admin && !$this->news[$i]->isActive())
				continue;
		
			if ($admin)
				echo "<tr><td>";
			
			$this->news[$i]->printOut();
					
				
			if ($admin) 
			{
				echo "</td><td>";
				
				/* TODO */
				$PHP_SELF = "news.php";
				/* TODO */
				
				echo "[ <a href=\"$PHP_SELF?active=" . $this->news[$i]->getId() . "\">" .
					($this->news[$i]->isActive()?"de":"") .
					"aktivieren</a> ]<br>";
					
				echo "[ <a href=\"$PHP_SELF?edit=" . $this->news[$i]->getId() .
					"\">ändern</a> ]<br>";
					
				echo "[ <a href=\"$PHP_SELF?delete=" . $this->news[$i]->getId() .
					"\">löschen</a> ]";
				
				echo "</td></tr>";				
			}
		}
		
		if ($admin)
			echo "</table>";

	}
	
	function showAdmin () 
	{
		$this->show(1);
	}
}


class News 
{
	var $id;
	var $date;
	var $author;
	var $title;
	var $text;
	var $active;

	function News ($id, $date, $author, $title, $text, $active = 0)
	{
		$this->id = $id;					 
		$this->setDate ($date);
		$this->setAuthor ($author);
		$this->setTitle ($title);
		$this->setText ($text);
		$this->setActive ($active);
		
	}
	
	/*******************
	 * getting methods *
	 *******************/	

	function getId () 
	{
		return $this->id;
	}
	
	function getDate ($foo) 
	{	
		if ($foo) 
		{
			list ($day, $month, $year) = split ("[.][ ]", $this->date);
			
			return "$year-$month-$day";	
		} 
		else 
		{					
			return date ("d. m. Y", strtotime($this->date)); /* TODO */
		}
	}
	
	function getAuthor () 
	{
		return $this->author;
	}
	
	function getTitle () 
	{
		return $this->title;
	}
	
	function getText () 
	{
		return $this->text;
	}
	
	function isActive () 
	{
		return $this->active;
	}
	
	/*******************
	 * setting methods *
	 *******************/
	
	function setDate ($date) 
	{
		/*
		 * verify date
		 * TODO
		 */
		/*
		if ($foo)
			list ($day, $month, $year) = split ("[.]", $date);
		
		$date = "$year-$m"
		*/
		
		/*
		if (checkdate ($month, $day, $year)) 
		{*/
			$this->date = $date;
			/*return 1;
		} else {
			return 0;
		}*/	 
	}
	
	function setAuthor ($author) 
	{
		$this->author = $author;
	}
	
	function setTitle ($title) 
	{
		$this->title = $title;
	}
	
	function setText ($text) 
	{
		$this->text = $text;
	}
	
	function setActive ($active)
	{
		$this->active = $active?1:0;
	}
	
	function printOut () 
	{
		echo "<strong>" . $this->getDate() . "</strong><br><br>";
		echo "<strong>" . $this->getTitle() . ":</strong> " .
			$this->getText();
		echo "<div align=\"right\"><font size=\"-1\">" .
			$this->getAuthor() . "</font></div>";
					
		echo "<hr>";
	}
	
	/************************
	 * db-dependent methods *
	 ************************/
	 
	/*
	 * updates $this in the db
	 */
	  
	function update () 
	{
	   	global $dbHost, $dbUser, $dbPass, $dbName;

                mysql_connect ($dbHost, $dbUser, $dbPass);
                mysql_select_db ($dbName);
	
		$query 	= "UPDATE neuigkeiten SET";
		$query .= " datum = '" . $this->getDate (1) . "',";
		$query .= " autor = '" . $this->getAuthor() . "',";
		$query .= " neuigkeit = '" . $this->getTitle() . "',";
		$query .= " info = '" . $this->getText() . "',";
		$query .= " active = '" . $this->isActive() . "'";
		$query .= " WHERE id = '" . $this->getId() . "'";
	
		//echo "$query <br><br>";
	
		$r = mysql_query ($query);	
			
		mysql_close();		 
	}
	
	/*
	 * removes $this from db
	 */
	
	function delete () 
	{
 	        global $dbHost, $dbUser, $dbPass, $dbName;

                mysql_connect ($dbHost, $dbUser, $dbPass);
                mysql_select_db ($dbName);  	
		
		$query = "DELETE FROM neuigkeiten WHERE id = '" . $this->id . "'";
		
		$r = mysql_query ($query);	
			
		mysql_close();	
	}
	
	/*
	 * inserts $this into db
	 */
	 
	function insert () 
	{
		global $dbHost, $dbUser, $dbPass, $dbName;

		mysql_connect ($dbHost, $dbUser, $dbPass);
		mysql_select_db ($dbName);
		
		$query  = "INSERT INTO neuigkeiten (datum, autor, neuigkeit, info, active)";
		$query .= " VALUES (";
		$query .= "'" . $this->getDate(1) . "', ";		
		$query .= "'" . $this->getAuthor() . "', ";	
		$query .= "'" . $this->getTitle() . "', ";		
		$query .= "'" . $this->getText() . "', ";
		$query .= "'" . $this->isActive() . "'";
		$query .= ")";
		
		//echo $query;
		$r = mysql_query ($query);	
			
		mysql_close();
	}
	
	/*****************
	 * other methods *
	 *****************/
	
	/*
	 * make active entries deactive and vice versa
	 */
	 
	function toggleActive () 
	{
		$this->setActive ($this->active?0:1);
	}
}

/************************
 *                      *
 *   Abschnitt Termine  *
 *                      *
 ************************/

class Termin 
{
	var $id;
	var $datum;
	var $termin;
	var $info;
	
	function Termin ($id, $datum, $termin, $info) 
	{
		$this->id = $id;
		$this->setDate ($datum);
		$this->setTermin ($termin);
		$this->setInfo ($info);
	}
	
	function getId () 
	{
		return $this->id;
	}
	
	/*function getDate ($foo = 0) 
	{	
		if ($foo) 
		{
			list ($day, $month, $year) = split ("[.][ ]", $this->date);
			
			return "$year-$month-$day";	
		} 
		else 
		{					
			return date ("d. m. Y", strtotime($this->date)); /* TODO */
	/*	}
	
		return $this->datum;
	}*/
	
	function getTermin () 
	{
		return $this->termin;
	}
	
	function getInfo () 
	{
		return $this->info;
	}
	
	function setDate ($date) 
	{
		/*
		 * verify date
		 * TODO
		 */
		
		/*list ($day, $month, $year) = split ("[.]", $date);
		
		if (checkdate ($month, $day, $year)) 
		{*/
			$this->date = $date;
			/*return 1;
		} else {
			return 0;
		}*/	 
	}
	
	function setTermin ($termin)
	{
		$this->termin = $termin;
	}
	
	function setInfo ($info) 
	{
		$this->info = $info;
	}
}
?>
