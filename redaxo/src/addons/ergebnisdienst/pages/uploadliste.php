<?php

$func = rex_request('func', 'string');    
$LED_UPLOADFOLDER = rex_path::media()."../leduploads/";
$LED_UPLOADURL =  "../leduploads/";


if (!function_exists("outputCSV")) 
{
    function outputCSV($data) {
        $outputBuffer = fopen("php://output", 'w');
        foreach($data as $val) {
            fputcsv($outputBuffer, $val, ";", '"');
        }
        fclose($outputBuffer);
    }  
}  

// Funktion zur Umlautkovertierung
if (!function_exists('ls_json_csv_umlaut'))
{
  function ls_json_csv_umlaut($str)
  {
        $str = str_replace( "u00", "\u00", $str);
        $str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
        }, $str);        
        $str = stripslashes($str);
        return $str;
  }
}

if (1== 1)
{      
      
      // Label sammeln                                    
      $meinelabels = array();  
      $meinelabels['originalname'] = array("DB", "Originalname");  
      $meinelabels['filesize'] = array("DB", "Gr��e"); 
      $meinelabels['changed'] = array("DB", "Zeitstempel");
      $meinelabels['uploadername'] = array("DB", "Uploadername");       
      $meinelabels['filename'] = array("DB", "Filename");   
      
      // Kopfbereich vorbereiten    
      $data = array();
      foreach ( $meinelabels AS $k=>$v) $data[0][$k] = $v[1];
      
      // Jetzt Einträge auslesen
      $eintragscounter = 1;    
      $tagezahl = 16; // Hier die Anzahl der Tage eintragen
      $countsql  = "SELECT * FROM ".rex::getTablePrefix()."950_uploads WHERE 1=1 AND changed > '".date("Y-m-d", time()-(60*60*24*$tagezahl) )."' ORDER BY changed DESC, id DESC";
      $prozahltutup = rex_sql::factory();
      $prozahltutup->setQuery( $countsql);
      $prozahltrefferzahl = $prozahltutup->getRows();
      if (!empty( $prozahltrefferzahl ))
      {
          while($prozahltutup->hasNext()) 
          {                     
              foreach ( $meinelabels AS $k=>$v) 
              {
                  if ( $v[0] == "DB" )
                  {
                    $tmpwert =  $prozahltutup->getValue( $k );
                    $data[ $eintragscounter ][ $k ] =  utf8_decode( ls_json_csv_umlaut( $tmpwert ));      
                  }
              }
              $eintragscounter++;
              $prozahltutup->next(); // nächster Eintrag laden.      
          }
      }   
}  

// Exportfunktion
if($func == "export") 
{       
  	// Header ausgeben    
    // Bisherige Ausgabe von Redaxo löschen
    ob_end_clean();
    header('Content-type: text/csv;');
    header ('Content-Disposition: inline; filename="uploads_' . gmdate("Ymd") . '.csv"');
  	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');  
    header("Pragma: no-cache");  
    header("Expires: 0");                  
    outputCSV($data);	
    exit;                                                         
    
    
}  
else
{
    ?>

      
    <div class="panel panel-default clearfix">
      <div class="panel-heading">
        <h3 class="panel-title">Uploads der letzten <?php echo $tagezahl; ?> Tage
        <span class="badge pull-right float-right"><?php echo $prozahltrefferzahl; ?></span></h3>
      </div>
      <div class="panel-body clearfix">
          Hier können Sie eine CSV-Datei der aktuellen Liste herunterladen für die spätere Auswertung z.B. in Excel.
      </div>        
      <div class="panel-footer clearfix">                               
      <a class="btn btn-success" href="<?php print rex_url::currentBackendPage( array( "func"=> "export") ); ?>">Download (CSV)</a>
      </div>
    </div>    
    <?php
    //echo '<div class="alert alert-warning">'.$countsql."</div>";
    if (!empty( $data ))
    {
    ?>
    <table class="table">
    <?php
    
    foreach($data AS $k=>$entry)
    {
        if ($k < 1)
        {
            echo '<thead><tr>';
            foreach ( $entry AS $mylabel=>$mycols) 
            if ($mylabel=="filename") echo '<th><br /></th>';
            else echo '<th>'.utf8_encode($mycols).'</th>';    
            echo '</tr></thead><tbody>';
        }  
        else
        {   
            echo '<tr>';
            foreach ( $entry AS $mylabel=>$mycols) 
            {
              if ($mylabel == "filename") echo '<td><a href="'.$LED_UPLOADURL.utf8_encode($mycols).'" target="_blank">[Download]</a></td>';
              else echo '<td>'.utf8_encode($mycols).'</td>';
            }    
            echo '</tr>';
        }
    }
    
    
    ?>
    </tbody>
    </table>
    <?php     
    }
    else echo '<div class="alert alert-danger">Es gibt keine passenden Einträge.</div>';
    ?>
<?php  
}   
?>
