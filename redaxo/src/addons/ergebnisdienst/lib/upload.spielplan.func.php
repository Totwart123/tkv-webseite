<?php
    // Upload und Auswertung Einzelspiel Ergebnisdienst 
    // Version 20210105
    
    
            // Dateityp pruefen
            $allowed = array('xlsx'); 
            if ($istdkbc) $allowed = array('xls', 'xlsx'); 
            $einzelfrage = 'datei';    
            
            $spielarray = array();
            $spielarray['nummern'] = array();
            $spielarray['mannschaften'] = array();
            $spielarray['mannschaftenid'] = array();
            $spielarray['spiele'] = array();
            
            if ( $booking['step'] == 1)
            {
                // Hier der Upload                              
                echo '<div class="alert alert-info">Es wird ein neuer Spielplan hochgeladen.</div>';    
                
                if(isset($_FILES[ $einzelfrage ]) && $_FILES[ $einzelfrage ]['error'] == 0)
                {                                
                    $extension = pathinfo($_FILES[ $einzelfrage ]['name'], PATHINFO_EXTENSION);
                    if ( (!empty($allowed)) AND (!in_array(strtolower($extension), $allowed)) ) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die gewählte Datei ist nicht geeignet. Nur Excel-Dateien mit der Endung XLSX sind möglich.</div>'; $error[] = $einzelfrage; }
                    if (empty( $booking['saison'] )) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span>  wurde keine Saison ausgewählt.</div>'; $error[] = "saison"; }      
                    if (empty( $booking['klasse'] )) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es wurde keine Spielklasse ausgewählt.</div>'; $error[] = "klasse"; }
                    
                    if (empty($error))
                    {                  
                                                      
                        // Dateinamen umwandeln
                        $filename = $_FILES[ $einzelfrage ]['name'];
                        $final_name = led_filename($filename);      
                        
                        // Datei hochladen
                        if(move_uploaded_file($_FILES[$einzelfrage]['tmp_name'], $LED_UPLOADFOLDER.'/'.$final_name))
                        {         
                            led_saveUpload($_FILES[$einzelfrage], $final_name, $filename);     
                            echo '<div class="alert alert-success"><span class="badge pull-right">OK</span>Die Datei wurde erfolgreich hochgeladen.</div>';  
                            
                            $booking['showform'] = false;
                            
                            // Jetzt Excel inkludieren   
                            //$file_to_include = $LED_UPLOADFOLDER."/"."automat.xls";
                            $file_to_include = $LED_UPLOADFOLDER."/".$final_name;
                            
                            // Import-ID mitsichern. falls etwas rückgängig gemacht werden mann
                            $importfileid = '';
                            $datentutup = rex_sql::factory();
                            $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_uploads WHERE filename = "'.$final_name.'"';
                            $datentutup->setQuery( $sql );
                            $datenzahl = $datentutup->getRows();     
                            if (!empty($datenzahl)) $importfileid = $datentutup->getValue("id", 0);   
                            
                            
                            if ($istdkbc)
                            {            
                                // Jetzt die Excel-Funktionen     
                                include ( rex_path::src().'/addons/ergebnisdienst/vendor/classes/PHPExcel/IOFactory.php' );    
                                
                                //echo '<div class="alert alert-warning">Datei: '.$file_to_include.'</div>'; 
                                $inputFileType = PHPExcel_IOFactory::identify( $file_to_include );      
                                //echo '<div class="alert alert-warning">TYP: '.$inputFileType.'</div>';    
                                $objReader = PHPExcel_IOFactory::createReader($inputFileType);           
                                $sheetList = $objReader->listWorksheetNames($file_to_include);   
                                
                                $blattzahl =  count($sheetList);    
                
                                if ($blattzahl < 1)
                                {
                                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Excel-Datei wurde nicht erkannt!</div>';   
                                    $error[] = "excel";    
                                } 
                            }
                            else if (1==2)
                            {             
                                // entfaellt, da veraltet
                                // Jetzt die Excel-Funktionen
                                require_once (rex_path::src().'/addons/ergebnisdienst/vendor/reader.php');
                                $data = new Spreadsheet_Excel_Reader();
                                $data->setOutputEncoding('CPa25a');
                                $data->read($file_to_include);
                                error_reporting(E_ALL ^ E_NOTICE);
                                
                                // Jetzt die Auswertung starten      
                                echo '<div class="alert alert-info">Verarbeitung der Excel-Datei wird gestartet...</div>';    
                                $blattzahl = count($data->sheets);
                            
                                if ($blattzahl == 1) 
                                {         
                                    echo '<div class="alert alert-success"><span class="badge pull-right">OK</span> Erkannte Excel-Datei: <strong>Spielplan</strong></div>';   
                                }
                                else 
                                {
                                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Excel-Datei wurde nicht erkannt!</div>';   
                                    $error[] = "excel";    
                                }  
                            }
                            else
                            {             
                            
                                // Jetzt die Excel-Funktionen
                                include ( rex_path::src().'/addons/ergebnisdienst/vendor/classes/PHPExcel/IOFactory.php' );    
                                
                                $inputFileType = PHPExcel_IOFactory::identify( $file_to_include );         
                                $objReader = PHPExcel_IOFactory::createReader($inputFileType);           
                                $sheetList = $objReader->listWorksheetNames($file_to_include);   
                                
                                $blattzahl =  count($sheetList);    
                                
                                // Jetzt die Auswertung starten      
                                echo '<div class="alert alert-info">Verarbeitung der Excel-Datei wird gestartet...</div>';    
                            
                                if ($blattzahl == 1) 
                                {         
                                    echo '<div class="alert alert-success"><span class="badge pull-right">OK</span> Erkannte Excel-Datei: <strong>Spielplan</strong></div>';   
                                }
                                else 
                                {
                                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Excel-Datei wurde nicht erkannt!</div>';   
                                    $error[] = "excel";    
                                }  
                            }
                            
                            if (empty($error))
                            { 
                                // Formular für nächsten Schritt anbieten
                                ?>  
                                <form class="formgen ledupload clearfix" id="ledupload" action="<?php rex_geturl( rex_article::getCurrentId() ); ?>" method="post" enctype="multipart/form-data">          
                                  <?php if ( !rex_addon::get('yrewrite')->isAvailable() ) { ?><input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden"><?php } ?>           
                                  <input name="func" value="planupload" type="hidden">    
                                  <input name="saison" value="<?php echo $booking['saison']; ?>" type="hidden">
                                  <input name="klasse" value="<?php echo $booking['klasse']; ?>" type="hidden">    
                                  <input name="step" value="2" type="hidden">    
                                  <input name="importid" value="<?php echo $importfileid; ?>" type="hidden">    
                                  <?php
                                  
                                  if ($istdkbc)
                                  {
                                      // Blätter für Hin- und Rückrunde aussuchen
                                      ?>
                                      <div class="clearfix">
                                      <fieldset class="fieldset">
                                        <legend>Bitte wählen sie das Blatt mit den Spielen der Hinrunde aus:</legend>
                                        <label for="hinrunde">Blatt</label>
                                        <select name="hinrunde" class="form-control" style="width: 100%;" required>
                                        <option value="">Bitte auswählen...</option>   
                                        <?php
                                        
                                        $objPHPExcel = $objReader->load($file_to_include);
                                        foreach( $sheetList AS $sheetid=>$mysheet)    
                                				{ 
                                          $objPHPExcel->setActiveSheetIndex( $sheetid );
                                          $tmp_spielklasse = trim( $objPHPExcel->getActiveSheet()->getCell('D3')->getValue() );   
                                          if (empty($tmp_spielklasse)) $tmp_spielklasse = trim( $objPHPExcel->getActiveSheet()->getCell('C3')->getValue() );   
                                          $tmp_spielklasse = str_replace(" ", "", $tmp_spielklasse);
                                          $tmp_spielrunde = trim( $objPHPExcel->getActiveSheet()->getCell('F2')->getValue() );
                                          echo '<option value="'.$sheetid.'">'.$mysheet.': '.$tmp_spielklasse.' ('.$tmp_spielrunde.')'.'</option>';
                                        }
                                        
                                        ?>
                                        </select>       
                                      </fieldset>     
                                      <fieldset class="fieldset">
                                        <legend>Bitte wählen sie das Blatt mit den Spielen der Rückrunde aus:</legend>
                                        <label for="rueckrunde">Blatt</label>
                                        <select name="rueckrunde" class="form-control" style="width: 100%;" required >
                                        <option value="">Bitte auswählen...</option>   
                                        <?php
                                        
                                        $objPHPExcel = $objReader->load($file_to_include);
                                        foreach( $sheetList AS $sheetid=>$mysheet)    
                                				{ 
                                          $objPHPExcel->setActiveSheetIndex( $sheetid );
                                          $tmp_spielklasse = trim( $objPHPExcel->getActiveSheet()->getCell('D3')->getValue() );   
                                          if (empty($tmp_spielklasse)) $tmp_spielklasse = trim( $objPHPExcel->getActiveSheet()->getCell('C3')->getValue() );   
                                          $tmp_spielklasse = str_replace(" ", "", $tmp_spielklasse);
                                          $tmp_spielrunde = trim( $objPHPExcel->getActiveSheet()->getCell('F2')->getValue() );
                                          echo '<option value="'.$sheetid.'">'.$mysheet.': '.$tmp_spielklasse.' ('.$tmp_spielrunde.')'.'</option>';
                                        }
                                        
                                        ?>
                                        </select>       
                                      </fieldset>  
                                      </div>
                                      <br>
                                      <?php
                                  }
                                  
                                  ?>
                                       
                                  <div class="formblock formsendbtn">         
                                    <button type="submit" class="btn formsubmit formgreen">Jetzt die Daten prüfen</button>      
                                  </div>       
                                </form>
                                <?php
                            
                            }                                 
                            else echo '<a class="btn btn-info pull-right" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a>';
                                            
                            
                            
                        }
                        else 
                        { 
                            if (($_FILES[ $einzelfrage ]['size']) > intval(return_bytes(ini_get($maxsizeusevalue)))) echo '<div class="alert alert-danger">Die Datei konnte leider nicht auf den Server geladen werden. Sie ist zu groß ('.return_bytes($_FILES['userfile']['size']).').</div>';
                            else echo '<div class="alert alert-danger">Die Datei konnte leider nicht auf den Server geladen werden.</div>';       
                            
                            
                            echo '<a class="btn btn-info pull-right" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a>';
                        }  
                    }
            }
        }
        
        // Jetzt Schritt 2 und 3     
        if ( in_array($booking['step'], array(2,3)))
        {
            // Hier die Prüfung                              
            echo '<div class="alert alert-info">Der neue Spielplan wird überprüft.</div>';     

            
            // Import-ID auslesen und Dateinamen ermitteln
            $booking['importfile'] = '';
            $datentutup = rex_sql::factory();
            $sql = 'SELECT filename FROM '.rex::getTablePrefix() .'950_uploads WHERE id = "'.$booking['importid'].'"';
            $datentutup->setQuery( $sql );
            $datenzahl = $datentutup->getRows();     
            if (!empty($datenzahl)) 
            {
                $booking['importfile'] = $datentutup->getValue("filename", 0);     
                $file_to_include = $LED_UPLOADFOLDER."/".$booking['importfile'] ;
                
                if ($istdkbc)
                {        
                    // Jetzt die Excel-Funktionen     
                    include ( rex_path::src().'/addons/ergebnisdienst/vendor/classes/PHPExcel/IOFactory.php' );    
                    
                    //echo '<div class="alert alert-warning">Datei: '.$file_to_include.'</div>'; 
                    $inputFileType = PHPExcel_IOFactory::identify( $file_to_include );      
                    //echo '<div class="alert alert-warning">TYP: '.$inputFileType.'</div>';    
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);           
                    $sheetList = $objReader->listWorksheetNames($file_to_include);     
                }
                else if (1==2)
                {
                    // veraltet, Excel XLS
                    // Jetzt die Excel-Funktionen
                    require_once (rex_path::src().'/addons/ergebnisdienst/vendor/reader.php');
                    $data = new Spreadsheet_Excel_Reader();
                    //$data->setOutputEncoding('CPa25a');        
                    $data->setOutputEncoding('UTF-16LE');
                    $data->read($file_to_include);
                    error_reporting(E_ALL ^ E_NOTICE);   
                }
                else
                {        
                    // Jetzt die Excel-Funktionen     
                    include ( rex_path::src().'/addons/ergebnisdienst/vendor/classes/PHPExcel/IOFactory.php' );    
                    
                    //echo '<div class="alert alert-warning">Datei: '.$file_to_include.'</div>'; 
                    $inputFileType = PHPExcel_IOFactory::identify( $file_to_include );      
                    //echo '<div class="alert alert-warning">TYP: '.$inputFileType.'</div>';    
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);           
                    $sheetList = $objReader->listWorksheetNames($file_to_include);     
                }
            }
            else
            {           
                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Excel-Datei konnte nicht gelesen werden!</div>';   
                $error[] = "file";    
            
            }
            
            if (($booking['hinrunde'] == $booking['rueckrunde'])  AND ($istdkbc))
            {      
                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Auswahl der Blätter für Hin- und Rückrunde ist fehlerhaft!</div>';   
                $error[] = "runde";  
            }
            
            if (empty($error))
            {
               
                echo '<div class="alert alert-info"><strong>Erkennung Spiele und Mannschaften</strong><br />';
                $showresultsonline = false;
            
                if ($istdkbc)
                {      
                
                    // Jetzt die Datei lesen
                    $objPHPExcel = $objReader->load($file_to_include);    
                    $aktuellerspieltag = $aktuellerspieltagrueck = 1;   
                    
                    foreach ( array( $booking['hinrunde'], $booking['rueckrunde'] ) AS $meinspielblatt)
                    {
                        //Hin- und Rückrunde auslesen   
                        $objPHPExcel->setActiveSheetIndex( $meinspielblatt );
                        
                        $zeilenzahl = intval( $objPHPExcel->getActiveSheet()->getHighestRow() );   
                        
                        //echo '<div class="alert alert-warning">ZEILEN: '.$zeilenzahl.'</div>';
                        
                        for($zi=0;$zi<$zeilenzahl;$zi++)
                				{
                					// Ist es eine Spieltagsangabe
                          $testisteinspieltag = trim( $objPHPExcel->getActiveSheet()->getCell('C'.$zi)->getValue() );        
                          //echo '<div class="alert alert-warning">ZEILE '.$zi.': '.$testisteinspieltag.'</div>';
                          $txtspeiltag = substr( $testisteinspieltag, -strlen('Spieltag') );
                          if ( $txtspeiltag == "Spieltag")
                          {
                              $aktuellerspieltag = intval($testisteinspieltag);                                     
                              //echo '<div class="alert alert-danger">SPIELTAG Zeile '.$zi.': '.$aktuellerspieltag.'</div>';
                          }        
                          
                          $testnummerhin = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getValue() );  // Blatt HIN, Spalte A      
                          //echo '<div class="alert alert-success">Spielnummer vor Prüfung '.$zi.': '.$objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getValue().'</div>';
                          //echo '<div class="alert alert-success">Spielnummer nach Prüfung '.$zi.': '.$testnummerhin.'</div>';
                          if (!empty($testnummerhin))
                          {
                              // Hinrunde
                              if (!in_array( $testnummerhin, $spielarray['nummern'] )) 
                              {
                                  $spielarray['nummern'][] = $testnummerhin;      
                                  
                                  //$spieldatum = led_importdatumformat( $objPHPExcel->getActiveSheet()->getCell('C'.$zi)->getValue() ); 
                                  $spieldatum = trim( $objPHPExcel->getActiveSheet()->getCell('C'.$zi)->getValue() );  // Blatt HIN, Spalte C
                        					$spielzeit = led_datumformat( $objPHPExcel->getActiveSheet()->getCell('D'.$zi)->getValue() );  // Blatt HIN, Spalte D
                                  
                        					$heimmannschaft = make_alphaonly(utf8_encode(trim( $objPHPExcel->getActiveSheet()->getCell('E'.$zi)->getValue() )));  // Blatt HIN, Spalte E    Zeichensatz beachten
                        					$gastmannschaft = make_alphaonly(utf8_encode(trim( $objPHPExcel->getActiveSheet()->getCell('H'.$zi)->getValue() ))); // Blatt HIN, Spalte H   Zeichensatz beachten
                                  
                                  if (!in_array( $heimmannschaft, $spielarray['mannschaften'] )) $spielarray['mannschaften'][] = $heimmannschaft;  // nur Hinspiele werden gewertet  
                                  if (!in_array( $gastmannschaft, $spielarray['mannschaften'] ))  $spielarray['mannschaften'][] = $gastmannschaft;  
                                  
                                  // Hinspieldaten
                                  $spielarray['spiele'][$testnummerhin] = array( "spielnr" => $testnummerhin, "datum" => $spieldatum,"uhrzeit" => $spielzeit,"klassenid" => $booking['klasse'],"saisonid" => $booking['saison'],"heimmannschaft" => $heimmannschaft,"gastmannschaft" => $gastmannschaft, "spieltag" => $aktuellerspieltag );
                              
                              }
                              else 
                              { 
                                  echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielnummer <strong>'.$testnummerhin.'</strong> ist mehrfach in der Datei auf den gewählten Blättern hinterlegt!</div>'; 
                                  $error[] = 'spielnummerdoppelt'; 
                              }
                          }
                				}    
                    }
                }
                else if (1==2)
                {
                    $zeilenzahl = $data->sheets[0]['numRows'];    
                    $aktuellerspieltag = $aktuellerspieltagrueck = 1;                  
                    for($zi=0;$zi<$zeilenzahl;$zi++)
            				{
            					// Ist es eine Spieltagsangabe
                      $testisteinspieltag = trim($data->sheets[0]['cells'][$zi][4]);   
                      $testisteinspieltagrueck = trim($data->sheets[0]['cells'][$zi][7]);
                      $txtspeiltag = substr( $testisteinspieltag, -strlen('Spieltag') );
                      if ( $txtspeiltag == "Spieltag")
                      {
                          //echo 'Neuer Spieltag Hinspiel: '.intval($testisteinspieltag)."<br />";      
                          //echo 'Neuer Spieltag Rückspeil: '.intval($testisteinspieltagrueck)."<br />";
                          $aktuellerspieltag = intval($testisteinspieltag);
                          $aktuellerspieltagrueck = intval($testisteinspieltagrueck);   
                      }
                      
                      $testnummerhin = led_spielnummer($data->sheets[0]['cells'][$zi][1]);  // Blatt Null, Spalte 1
            					$testnummerrueck = led_spielnummer($data->sheets[0]['cells'][$zi][11]); // Blatt 0, Spalte 11
                      if ((!empty($testnummerhin)) AND (!empty($testnummerrueck)))
                      {
                          // Spiele in zwei Spalten
                          // Hinrunde
                          if (!in_array( $testnummerhin, $spielarray['nummern'] )) 
                          {
                              $spielarray['nummern'][] = $testnummerhin;      
                              
                    					//$spieldatum = led_datumformat($data->sheets[0]['cells'][$zi][2]);  // Blatt Null, Spalte 2
                              $spieldatum = led_importdatumformat($data->sheets[0]['cells'][$zi][2]);  // Blatt Null, Spalte 2
                    					$spielzeit = led_datumformat($data->sheets[0]['cells'][$zi][3]);  // Blatt Null, Spalte 3
    if ($showresultsonline) echo '<br />Datum: '.$spieldatum;     
    if ($showresultsonline) echo '<br />Zeit: '.$spielzeit;
                              
                    					$heimmannschaft = make_alphaonly(utf8_encode(trim($data->sheets[0]['cells'][$zi][4])));  // Blatt Null, Spalte 4    Zeichensatz beachten
                    					$gastmannschaft = make_alphaonly(utf8_encode(trim($data->sheets[0]['cells'][$zi][7]))); // Blatt 0, Spalte 7   Zeichensatz beachten
                              
                              if (!in_array( $heimmannschaft, $spielarray['mannschaften'] )) $spielarray['mannschaften'][] = $heimmannschaft;  // nur Hinspiele werden gewertet  
                              if (!in_array( $gastmannschaft, $spielarray['mannschaften'] ))  $spielarray['mannschaften'][] = $gastmannschaft;  
                              
                              // Hinspieldaten
                              $spielarray['spiele'][$testnummerhin] = array( "spielnr" => $testnummerhin, "datum" => $spieldatum,"uhrzeit" => $spielzeit,"klassenid" => $booking['klasse'],"saisonid" => $booking['saison'],"heimmannschaft" => $heimmannschaft,"gastmannschaft" => $gastmannschaft, "spieltag" => $aktuellerspieltag );
                          
                              // Rückspieldaten
                    					//$spieldatum = led_datumformat($data->sheets[0]['cells'][$zi][9]);  // Blatt Null, Spalte 9     
                    					$spieldatum = led_importdatumformat($data->sheets[0]['cells'][$zi][9]);  // Blatt Null, Spalte 9
                    					$spielzeit = led_datumformat($data->sheets[0]['cells'][$zi][10]);  // Blatt Null, Spalte 10
                              
                              $spielarray['spiele'][$testnummerrueck] = array( "spielnr" => $testnummerrueck, "datum" => $spieldatum,"uhrzeit" => $spielzeit,"klassenid" => $booking['klasse'],"saisonid" => $booking['saison'],"heimmannschaft" => $gastmannschaft,"gastmannschaft" =>  $heimmannschaft, "spieltag" => $aktuellerspieltagrueck );
                          
                          }
                          else 
                          { 
                              echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielnummer <strong>'.$testnummerhin.'</strong> ist mehrfach in der Datei hinterlegt!</div>'; 
                              $error[] = 'spielnummerdoppelt'; 
                          }
                          
                          $spielarray['nummern'][] = $testnummerrueck;  
                      }
            				}
                
                }
                else
                {
                    
                    // Jetzt die Datei lesen
                    $objPHPExcel = $objReader->load($file_to_include);    
                    $aktuellerspieltag = $aktuellerspieltagrueck = 1;   
                    
                    //foreach ( array( $booking['hinrunde'], $booking['rueckrunde'] ) AS $meinspielblatt)
                    foreach ( array( 0 ) AS $meinspielblatt)
                    {
                        //Hin- und Rückrunde auslesen   
                        $objPHPExcel->setActiveSheetIndex( $meinspielblatt );
                        $zeilenzahl = intval( $objPHPExcel->getActiveSheet()->getHighestRow() );   
                        
                        for($zi=0;$zi<$zeilenzahl;$zi++)
                		{
                		  // Ist es eine Spieltagsangabe
                          $testisteinspieltag = trim( $objPHPExcel->getActiveSheet()->getCell('D'.$zi)->getValue() );      
                          $testisteinspieltagrueck = trim( $objPHPExcel->getActiveSheet()->getCell('G'.$zi)->getValue() );      
                          if ($showresultsonline) echo '<div class="alert alert-warning">ZEILE '.$zi.': '.$testisteinspieltag.'</div>';
                          $txtspeiltag = substr( $testisteinspieltag, -strlen('Spieltag') );
                          if ( $txtspeiltag == "Spieltag")
                          {
                              $aktuellerspieltag = intval($testisteinspieltag);     
                              $aktuellerspieltagrueck = intval($testisteinspieltagrueck);                                   
                              if ($showresultsonline) echo '<div class="alert alert-danger">SPIELTAG Zeile '.$zi.': '.$aktuellerspieltag.'</div>';
                          }        
                          
                          $testnummerhin = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getValue() );  // Blatt HIN, Spalte A      
                          $testnummerrueck = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('K'.$zi)->getValue() ); 

                          if (empty($testnummerhin)) $testnummerhin = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getOldCalculatedValue() );  // Blatt HIN, Spalte A      
                          if (empty($testnummerrueck)) $testnummerrueck = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('K'.$zi)->getOldCalculatedValue() ); 
                          
                          if ($showresultsonline) echo '<div class="alert alert-success">Spielnummer vor Prüfung '.$zi.': '.$objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getValue().'</div>';
                          if ($showresultsonline) echo '<div class="alert alert-success">Spielnummer nach Prüfung '.$zi.': '.$testnummerhin.'</div>';
                          if ((!empty($testnummerhin)) AND (!empty($testnummerrueck)))
                          {
                              // Hinrunde
                              if (!in_array( $testnummerhin, $spielarray['nummern'] )) 
                              {
                                  $spielarray['nummern'][] = $testnummerhin;      
                                  
                                  //$spieldatum = led_importdatumformat( $objPHPExcel->getActiveSheet()->getCell('C'.$zi)->getValue() ); 
                                  //trim oder led_importdatumformat
                                  
                              $cell = $objPHPExcel->getActiveSheet()->getCell( 'B'.intval( $zi) );
                              $format = "d.m.Y";
                              //$InvDate= $cell->getOldCalculatedValue();
                              $InvDate= $cell->getValue();
                              if(PHPExcel_Shared_Date::isDateTime($cell)) $spieldatum = date($format, PHPExcel_Shared_Date::ExcelToPHP($InvDate )); 
                              else $spieldatum = str_replace( ".", ":", $objPHPExcel->getActiveSheet()->getCell('B'.$zi)->getOldCalculatedValue()  );  // Blatt HIN, Spalte C
                              
                              /*
                              $cell = $objPHPExcel->getActiveSheet()->getCell( 'C'.intval( $zi) );
                              $format = "H:i";
                              //$InvDate= $cell->getOldCalculatedValue();
                              $InvDate= $cell->getValue();
                              if(PHPExcel_Shared_Date::isDateTime($cell)) $spielzeit = date($format, PHPExcel_Shared_Date::ExcelToPHP($InvDate  + date('Z', $timeStart) ) - 3600 ); 
                              else $spielzeit = ( $objPHPExcel->getActiveSheet()->getCell('C'.$zi)->getOldCalculatedValue()  );  // Blatt HIN, Spalte D
                              */
                              
                              $spielzeit = led_datumformat( str_replace( ".", ":", $objPHPExcel->getActiveSheet()->getCell('C'.$zi)->getValue() ));  // Blatt HIN, Spalte D

                                  //$spieldatum = 'bb'.( $objPHPExcel->getActiveSheet()->getCell('B'.$zi)->getValue() );  // Blatt HIN, Spalte C
                        		  //$spielzeit = 'aa'.( $objPHPExcel->getActiveSheet()->getCell('C'.$zi)->getValue() );  // Blatt HIN, Spalte D
                                  
          if ($showresultsonline) echo '<br />Datum: '.$spieldatum;     
          if ($showresultsonline) echo '<br />Zeit: '.$spielzeit;

                        		  $heimmannschaft = make_alphaonly(utf8_encode(trim( $objPHPExcel->getActiveSheet()->getCell('D'.$zi)->getValue() )));  // Blatt HIN, Spalte E    Zeichensatz beachten
                        		  $gastmannschaft = make_alphaonly(utf8_encode(trim( $objPHPExcel->getActiveSheet()->getCell('G'.$zi)->getValue() ))); // Blatt HIN, Spalte H   Zeichensatz beachten
                                  
                                  if (!in_array( $heimmannschaft, $spielarray['mannschaften'] )) $spielarray['mannschaften'][] = $heimmannschaft;  // nur Hinspiele werden gewertet  
                                  if (!in_array( $gastmannschaft, $spielarray['mannschaften'] ))  $spielarray['mannschaften'][] = $gastmannschaft;  
                                  
                                  // Hinspieldaten
                                  $spielarray['spiele'][$testnummerhin] = array( "spielnr" => $testnummerhin, "datum" => $spieldatum,"uhrzeit" => $spielzeit,"klassenid" => $booking['klasse'],"saisonid" => $booking['saison'],"heimmannschaft" => $heimmannschaft,"gastmannschaft" => $gastmannschaft, "spieltag" => $aktuellerspieltag );
                              
                                  // Rückspieldaten
                              $cell = $objPHPExcel->getActiveSheet()->getCell( 'I'.intval( $zi) );
                              $format = "d.m.Y";
                              //$InvDate= $cell->getOldCalculatedValue();
                              $InvDate= $cell->getValue();
                              if(PHPExcel_Shared_Date::isDateTime($cell)) $spieldatum = date($format, PHPExcel_Shared_Date::ExcelToPHP($InvDate )); 
                              else $spieldatum = str_replace( ".", ":", $objPHPExcel->getActiveSheet()->getCell('B'.$zi)->getOldCalculatedValue()  );  // Blatt HIN, Spalte C
                                  //$spieldatum = 'cc'.led_importdatumformat( $objPHPExcel->getActiveSheet()->getCell('I'.$zi)->getValue() );  // Blatt HIN, Spalte C

                                  $spielzeit = led_datumformat( str_replace( ".", ":", $objPHPExcel->getActiveSheet()->getCell('J'.$zi)->getValue() ));  // Blatt HIN, Spalte D
                              
          if ($showresultsonline) echo '<br />Datum: '.$spieldatum;     
          if ($showresultsonline) echo '<br />Zeit: '.$spielzeit;
                                  $spielarray['spiele'][$testnummerrueck] = array( "spielnr" => $testnummerrueck, "datum" => $spieldatum,"uhrzeit" => $spielzeit,"klassenid" => $booking['klasse'],"saisonid" => $booking['saison'],"heimmannschaft" => $gastmannschaft,"gastmannschaft" =>  $heimmannschaft, "spieltag" => $aktuellerspieltagrueck );
                          
                              }
                              else 
                              { 
                                  echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielnummer <strong>'.$testnummerhin.'</strong> ist mehrfach in der Datei auf den gewählten Blättern hinterlegt!</div>'; 
                                  $error[] = 'spielnummerdoppelt'; 
                              }
                          }
                		}    
                    }

                }
            
                echo '</div>';  
                
                $mannschaftzahl = count($spielarray['mannschaften']);      
                
                // Jetzt neu: Pokal-Anordnung auswerten, falls das erste nicht funktioniert    
                if (($mitpokal) AND (empty($mannschaftzahl)))
                {      
                    // Zurücksetzen
                    $spielarray = array();
                    $spielarray['nummern'] = array();
                    $spielarray['mannschaften'] = array();
                    $spielarray['mannschaftenid'] = array();
                    $spielarray['spiele'] = array();

                    $objPHPExcel->setActiveSheetIndex( 0 );
                    if ($showresultsonline) echo 'Ist POKAL! '."<br />";      
                        
                    // neu auslesen
                    $zeilenzahl = intval( $objPHPExcel->getActiveSheet()->getHighestRow() );   
                    $aktuellerspieltag = 1;                  
                    for($zi=0;$zi<$zeilenzahl;$zi++)
            				{
            					// Ist es eine Spieltagsangabe
                      $testisteinspieltag = trim( $objPHPExcel->getActiveSheet()->getCell('D'.$zi)->getValue() );      
                      $txtspeiltag = substr( $testisteinspieltag, -strlen('Spieltag') );
                      if ( $txtspeiltag == "Spieltag")
                      {
                          if ($showresultsonline) echo 'Neuer Spieltag Hinspiel: '.intval($testisteinspieltag)."<br />";      
                          if ($showresultsonline) echo 'Neuer Spieltag Rückspiel: '.intval($testisteinspieltagrueck)."<br />";
                          $aktuellerspieltag = intval($testisteinspieltag);
                          $aktuellerspieltagrueck = intval($testisteinspieltagrueck);   
                      }
                      
                      //$testnummerhin = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getValue() );
                      //$testnummerhin = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getCalculatedValue() );
                      $testnummerhin = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getOldCalculatedValue() );
                      if (empty($testnummerhin)) $testnummerhin = led_spielnummer( $objPHPExcel->getActiveSheet()->getCell('A'.$zi)->getValue() ); // Ersatz für erste Zeile
                      
                      if ($showresultsonline) echo '<br /><br />'.$zi.'NR: '.$testnummerhin;
                      if (!empty($testnummerhin))
                      {
                          // Spiele in zwei Spalten
                          // Hinrunde
                          if (!in_array( $testnummerhin, $spielarray['nummern'] )) 
                          {
                              $spielarray['nummern'][] = $testnummerhin;      
                              
                              $cell = $objPHPExcel->getActiveSheet()->getCell( 'B'.intval( $zi) );
                              $format = "d.m.Y";
                              //$InvDate= $cell->getOldCalculatedValue();
                              $InvDate= $cell->getValue();
                              if(PHPExcel_Shared_Date::isDateTime($cell)) $spieldatum = date($format, PHPExcel_Shared_Date::ExcelToPHP($InvDate )); 
                              else $spieldatum = ( $objPHPExcel->getActiveSheet()->getCell('B'.$zi)->getOldCalculatedValue()  );  // Blatt HIN, Spalte C
                              
                              $cell = $objPHPExcel->getActiveSheet()->getCell( 'C'.intval( $zi) );
                              $format = "H:i";
                              //$InvDate= $cell->getOldCalculatedValue();
                              $InvDate= $cell->getValue();
                              if(PHPExcel_Shared_Date::isDateTime($cell)) $spielzeit = date($format, PHPExcel_Shared_Date::ExcelToPHP($InvDate  + date('Z', $timeStart) ) - 3600 ); 
                              else $spielzeit = ( $objPHPExcel->getActiveSheet()->getCell('C'.$zi)->getOldCalculatedValue()  );  // Blatt HIN, Spalte D
                              
          if ($showresultsonline) echo '<br />Datum: '.$spieldatum;     
          if ($showresultsonline) echo '<br />Zeit: '.$spielzeit;
                              
          $heimmannschaft = make_alphaonly(utf8_encode(trim( $objPHPExcel->getActiveSheet()->getCell('D'.$zi)->getValue() )));  // Blatt HIN, Spalte E    Zeichensatz beachten
          $gastmannschaft = make_alphaonly(utf8_encode(trim( $objPHPExcel->getActiveSheet()->getCell('G'.$zi)->getValue() )));
                              
                              if (!in_array( $heimmannschaft, $spielarray['mannschaften'] )) $spielarray['mannschaften'][] = $heimmannschaft;  // nur Hinspiele werden gewertet  
                              if (!in_array( $gastmannschaft, $spielarray['mannschaften'] ))  $spielarray['mannschaften'][] = $gastmannschaft;  
                              
                              //echo '<br />Heim: '.$heimmannschaft;
                              //echo '<br />Gast: '.$gastmannschaft;
                              
                              // Hinspieldaten
                              $spielarray['spiele'][$testnummerhin] = array( "spielnr" => $testnummerhin, "datum" => $spieldatum,"uhrzeit" => $spielzeit,"klassenid" => $booking['klasse'],"saisonid" => $booking['saison'],"heimmannschaft" => $heimmannschaft,"gastmannschaft" => $gastmannschaft, "spieltag" => $aktuellerspieltag );
                          
                              // Rückspieldaten entfallen
                              
                          }
                          else 
                          { 
                              echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielnummer <strong>'.$testnummerhin.'</strong> ist mehrfach in der Datei hinterlegt!</div>'; 
                              $error[] = 'spielnummerdoppelt'; 
                          }
                      }
            				}
                    
                    $mannschaftzahl = count($spielarray['mannschaften']);    

                }
                
                //gab es Fehler?
                if (empty($mannschaftzahl))        
                {
                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es wurden keine Mannschaften erkannt!</div>'; 
                    $error[] = 'keinemannschaftenerkannt';
                }    
                
                $spielzahl = count($spielarray['spiele']);
                if (empty($spielzahl))        
                {
                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es wurden keine Spiele erkannt!</div>'; 
                    $error[] = 'keinespieleerkannt';
                }        
            }
            
            if (empty($error))
            {   
                                    echo '<div class="alert alert-warning"><strong>Es wurden '.$mannschaftzahl.' Mannschaften erkannt:</strong><br /><ul>';
                                    foreach( $spielarray['mannschaften'] AS $einzelmannschaft)
                                    {
                                        // Abfrage, ob in der Liste, sonst eintragen    
                                        $tmp_id = 0;
                                        $datentutup = rex_sql::factory();
                                        $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_mannschaft WHERE mannschaft = "'.$einzelmannschaft.'"';
                                        $datentutup->setQuery( $sql );
                                        $datenzahl = $datentutup->getRows();     
                                        if ($datenzahl > 1)
                                        {                                                                                                                    
                                              $tmp_id = $datentutup->getValue("id", 0);   
                                              $spielarray['mannschaftenid'][ $einzelmannschaft ] = $tmp_id;  
                                              echo '<li><span class="badge pull-right">!</span> Für die Mannschaft <strong>'.$einzelmannschaft.'</strong> gibt es mehrere Einträge! Es wird die Mannschaft mit der <i>ID-Nummer '.$tmp_id.'</i> genutzt.</li>';     
                                        }
                                        else if ($datenzahl == 1)
                                        {               
                                              $tmp_id = $datentutup->getValue("id", 0);   
                                              $spielarray['mannschaftenid'][ $einzelmannschaft ] = $tmp_id;  
                                              //echo $tmpfieldname.": ".$field['beschreibung']."<br />";            
                                              echo '<li>'.$einzelmannschaft." (<i>ID-Nummer ".$tmp_id."</i> vorhanden)</li>";
                                        }
                                        else
                                        {      
                                              $tmp_id = 99999;
                                              if ( in_array($booking['step'], array(3))) 
                                              {
                                                  $updtutup = rex_sql::factory();
                                                  $sql = 'INSERT INTO '.rex::getTablePrefix() .'950_mannschaft SET mannschaft = "'.$einzelmannschaft.'", importid = '.$booking['importid'];
                                                  $updtutup->setQuery( $sql );
                                                                            
                                                  // Jetzt neu auslesen
                                                  $datentutup = rex_sql::factory();
                                                  $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_mannschaft WHERE mannschaft = "'.$einzelmannschaft.'"';
                                                  $datentutup->setQuery( $sql );
                                                  $datenzahl = $datentutup->getRows();  
                                                  
                                                  if (!empty($datenzahl)) $tmp_id = $datentutup->getValue("id", 0);   
                                              }
                                              
                                              $spielarray['mannschaftenid'][ $einzelmannschaft ] = $tmp_id;  
                                              //echo $tmpfieldname.": ".$field['beschreibung']."<br />";            
                                              if ( in_array($booking['step'], array(2))) echo '<li><span class="badge pull-right">+</span> '.$einzelmannschaft." ( wird neu angelegt)</li>";      
                                              if ( in_array($booking['step'], array(3))) echo '<li><span class="badge pull-right">+</span> '.$einzelmannschaft." (<i>ID-Nummer ".$tmp_id."</i> neu)</li>";
                                            
                                        }
                                        
                                        
                                        
                                    }
                                    echo '</ul>';
                                    echo '</div>'; 
                                    
                                    
                                    echo '<div class="alert alert-warning"><strong>Es wurden '.$spielzahl.' Spiele erkannt:</strong><br /><ul>';
                                    ksort($spielarray['spiele']);
                                    
                                    foreach( $spielarray['spiele'] AS $einzelspiel)
                                    {
                                        $korrekterspieltag =  $einzelspiel['spieltag'];
                                        
                                        
                                        // Gibt es das Spiel schon?   Saison prüfen $booking['saison']   
                                        $datentutup = rex_sql::factory();
                                        if (!$istdkbc) $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spielplan WHERE spielnr = "'.$einzelspiel['spielnr'].'" AND saisonid="'.$einzelspiel['saisonid'].'"';
                                        else  $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spielplan WHERE spielnr = "'.$einzelspiel['spielnr'].'" AND saisonid="'.$einzelspiel['saisonid'].'" AND klassenid="'.$einzelspiel['klassenid'].'"';
                                        $datentutup->setQuery( $sql );
                                        $datenzahl = $datentutup->getRows(); 
                                        if ($datenzahl > 0)
                                        {           
                                            echo '<li>UPDATE: ';
                                            if (empty( $spielarray['mannschaftenid'][ $einzelspiel['heimmannschaft'] ] )) { echo '<span class="badge error pull-right">?'.$einzelspiel['heimmannschaft'].'</span> '; $error[] = 'mannschaftunbekannt'; }   
                                            if (empty( $spielarray['mannschaftenid'][ $einzelspiel['gastmannschaft'] ] )) { echo '<span class="badge error pull-right">?'.$einzelspiel['gastmannschaft'].'</span> '; $error[] = 'mannschaftunbekannt'; }
                                            echo $einzelspiel['spielnr'].' am '.$einzelspiel['datum'].' um '.$einzelspiel['uhrzeit'].' Uhr ( '.$korrekterspieltag.'. Spieltag)</li>';
                                                              
                                            // Spiele speichern
                                            if ( in_array($booking['step'], array(3))) 
                                            {
                                                $spielsql = rex_sql::factory();
                                                $spielsql->setTable(''.rex::getTablePrefix() .'950_spielplan');
                                                $spielsql->setValue('spielnr',$einzelspiel['spielnr'] );   
                                                $spielsql->setValue('datum',$einzelspiel['datum'] );
                                                $spielsql->setValue('uhrzeit', $einzelspiel['uhrzeit'] );   
                                                $spielsql->setValue('klassenid', $einzelspiel['klassenid'] );
                                                $spielsql->setValue('saisonid', $einzelspiel['saisonid'] );
                                                $spielsql->setValue('heimmannschaftid', $spielarray['mannschaftenid'][ $einzelspiel['heimmannschaft'] ] );
                                                $spielsql->setValue('gastmannschaftid', $spielarray['mannschaftenid'][ $einzelspiel['gastmannschaft'] ] );
                                                $spielsql->setValue('importid', $booking['importid']);                                               
                                                $spielsql->setValue('spieltagnr', $korrekterspieltag );    
                                                $spielsql->setWhere('id='.$datentutup->getValue("id", 0));   
                                                $spielsql->update();     
                                            }
                                        }  
                                        else
                                        {
                                            echo '<li>NEU: ';
                                            if (empty( $spielarray['mannschaftenid'][ $einzelspiel['heimmannschaft'] ] )) { echo '<span class="badge error pull-right">?'.$einzelspiel['heimmannschaft'].'</span> '; $error[] = 'mannschaftunbekannt'; }   
                                            if (empty( $spielarray['mannschaftenid'][ $einzelspiel['gastmannschaft'] ] )) { echo '<span class="badge error pull-right">?'.$einzelspiel['gastmannschaft'].'</span> '; $error[] = 'mannschaftunbekannt'; }
                                            echo $einzelspiel['spielnr'].' am '.$einzelspiel['datum'].' um '.$einzelspiel['uhrzeit'].' Uhr ( '.$korrekterspieltag.'. Spieltag)</li>';
                                            
                                            // Spiele speichern
                                            if ( in_array($booking['step'], array(3))) 
                                            {
                                                $spielsql = rex_sql::factory();
                                                $spielsql->setTable(''.rex::getTablePrefix() .'950_spielplan');
                                                $spielsql->setValue('spielnr',$einzelspiel['spielnr'] );   
                                                $spielsql->setValue('datum',$einzelspiel['datum'] );
                                                $spielsql->setValue('uhrzeit', $einzelspiel['uhrzeit'] );   
                                                $spielsql->setValue('klassenid', $einzelspiel['klassenid'] );
                                                $spielsql->setValue('saisonid', $einzelspiel['saisonid'] );
                                                $spielsql->setValue('heimmannschaftid', $spielarray['mannschaftenid'][ $einzelspiel['heimmannschaft'] ] );
                                                $spielsql->setValue('gastmannschaftid', $spielarray['mannschaftenid'][ $einzelspiel['gastmannschaft'] ] );
                                                $spielsql->setValue('importid', $booking['importid']);                                                
                                                $spielsql->setValue('spieltagnr', $korrekterspieltag );  
                                                $spielsql->insert();       
                                            }
                                        }
                                        
                                    }
                                    echo '</ul>';
                                    echo '</div>'; 
                                    if ( in_array($booking['step'], array(3))) echo '<div class="alert alert-danger"><strong>Bitte prüfen sie, ob die Spieltage korrekt zugeordnet wurden. Falls nicht, kann dies in Redaxo geändert werden.</strong></div>';
                                    
            }
            
            
            if (empty($error))
            { 
                // Formular für nächsten Schritt anbieten
                if ( in_array($booking['step'], array(2))) 
                {
                ?>  
                <form class="formgen ledupload clearfix" id="ledupload" action="<?php rex_geturl( rex_article::getCurrentId() ); ?>" method="post" enctype="multipart/form-data">          
                  <?php if ( !rex_addon::get('yrewrite')->isAvailable() ) { ?><input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden"><?php } ?>        
                  <input name="func" value="planupload" type="hidden">    
                  <input name="saison" value="<?php echo $booking['saison']; ?>" type="hidden">
                  <input name="klasse" value="<?php echo $booking['klasse']; ?>" type="hidden">    
                  <input name="step" value="3" type="hidden">    
                  <input name="importid" value="<?php echo $booking['importid']; ?>" type="hidden">   
                  <?php if ($istdkbc) { ?>  
                  <input name="hinrunde" value="<?php echo $booking['hinrunde']; ?>" type="hidden">    
                  <input name="rueckrunde" value="<?php echo $booking['rueckrunde']; ?>" type="hidden">    
                  <?php } ?>     
                  <div class="formblock formsendbtn">         
                    <button type="submit" class="btn formsubmit formgreen">Jetzt die Daten speichern</button>      
                  </div>       
                </form>
                <?php  
                }
                else echo '<div class="clearfix"><a class="btn btn-info pull-right" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a></div>';
                
            
            }                                 
            else echo '<div class="clearfix"><a class="btn btn-info pull-right" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a></div>';
                                 
        }
        
        
        
        
        //print_r( $booking );
                
   
?>