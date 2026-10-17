<?php
    // Upload und Auswertung Einzelspiel Ergebnisdienst 
    // Version 20170110
    
    
            $booking['spielerneu']  = rex_request('spielerneu', 'int');   
            if ( $booking['spielerneu'] == 1 )  $booking['overwrite'] = true;
            
            // Dateityp pruefen
            $allowed = array('xls'); 
            $einzelfrage = 'datei'; 
            
            if(isset($_FILES[ $einzelfrage ]) && $_FILES[ $einzelfrage ]['error'] == 0)
            {                                
                $extension = pathinfo($_FILES[ $einzelfrage ]['name'], PATHINFO_EXTENSION);
                if ( (!empty($allowed)) AND (!in_array(strtolower($extension), $allowed)) ) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die gewählte Datei ist nicht geeignet. Nur Excel-Dateien mit der Endung XLS sind möglich.</div>'; $error[] = $einzelfrage; }
                else
                {                  
                                                  
                    // Dateinamen umwandeln
                    $filename = $_FILES[ $einzelfrage ]['name'];
                    $final_name = led_filename($filename);      
                    
                    // Datei hochladen
                    if(move_uploaded_file($_FILES[$einzelfrage]['tmp_name'], $LED_UPLOADFOLDER.'/'.$final_name))
                    {         
                        led_saveUpload($_FILES[$einzelfrage], $final_name, $filename);     
                        echo '<div class="alert alert-success"><span class="badge pull-right">OK</span> Die Datei wurde erfolgreich hochgeladen.</div>';  
                        
                        $booking['showform'] = false;
                                                                                              
                        $file_to_include = $LED_UPLOADFOLDER."/".$final_name;    
                                                                                       
                        // Import-ID mitsichern. falls etwas rückgängig gemacht werden mann
                        $importfileid = '';
                        $datentutup = rex_sql::factory();
                        $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_uploads WHERE filename = "'.$final_name.'"';
                        $datentutup->setQuery( $sql );
                        $datenzahl = $datentutup->getRows();     
                        if (!empty($datenzahl)) $importfileid = $datentutup->getValue("id", 0);   
                                                    
                        // Jetzt Excel inkludieren   
                        require_once (rex_path::src().'/addons/ergebnisdienst/vendor/reader.php');
                        $data = new Spreadsheet_Excel_Reader();
                        $data->setOutputEncoding('CPa25a');
                        $data->read($file_to_include);
                        error_reporting(E_ALL ^ E_NOTICE);
                        
                        
                            
                        // Hier bitte Testzeile ausgeben
                        $spaltenzahl = $data->sheets[1]['numCols'];      
                        $zeilenzahl = $data->sheets[1]['numRows'];                   
                        for($zi=0;$zi<$spaltenzahl;$zi++)
                        {
                          //echo ''.$zi.': '.$data->sheets[1]['cells'][13][$zi].'<br />';   
                        }
                        for($zi=0;$zi<$zeilenzahl;$zi++)
                				{
                            //echo ''.$zi.': '.$data->sheets[1]['cells'][8][$zi].'<br />';   
                            //echo ''.$zi.': '.$data->sheets[1]['cells'][$zi][1].'<br />';    
                        } 
                        // Bis dahin entfernen, da nur Test
                        
                        // Jetzt die Auswertung starten      
                        echo '<div class="alert alert-info">Verarbeitung der Excel-Datei wird gestartet...</div>';    
                        $blattzahl = count($data->sheets);
                        if ($blattzahl == 4) 
                        {         
                            echo '<div class="alert alert-success"><span class="badge pull-right">OK</span> Erkannte Excel-Datei: <strong>vom Automat ausgefüllt</strong></div>';  
                            $booking['dateityp'] = 'automat'; 
                            $booking['dkbblattnr'] = '1';     
                            //$booking['spielnr'] = led_spielnummer($data->sheets[0]['cells'][1][6]);  // erstes Blatt, erste Reihe, sechste Spalte     
                            $booking['spielnr'] = led_spielnummer($data->sheets[ $booking['dkbblattnr'] ]['cells'][8][12]);  // zweites Blatt DKB, 8. Reihe, 12. Spalte    
                        }
                        else if ($blattzahl > 0) 
                        {
                            echo '<div class="alert alert-success"><span class="badge pull-right">OK</span> Erkannte Excel-Datei: <strong>per Hand ausgefüllt</strong></div>';     
                            $booking['dateityp'] = 'perhand';        
                            $booking['dkbblattnr'] = '0';     
                            //$booking['spielnr'] = led_spielnummer($data->sheets[0]['cells'][1][6]);  // erstes Blatt, erste Reihe, sechste Spalte     
                            $booking['spielnr'] = led_spielnummer($data->sheets[ $booking['dkbblattnr'] ]['cells'][8][12]);  // zweites Blatt DKB, 8. Reihe, 12. Spalte   
                        }  
                        else
                        {
                            echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Excel-Datei konnte nicht zugeordnet werden.</div>';    
                            $error[] = "falscheblattzahl";
                        }
                        
                        if (empty($error))
                        {                                                                                                                                  
                            if (!empty($booking['spielnr'])) echo '<div class="alert alert-warning"><span class="badge pull-right">OK</span> Erkannte Spielnummer: <strong>'.$booking['spielnr'].'</strong></div>';    
                            else
                            {
                                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es wurde keine Spielnummer erkannt.</div>';    
                                $error[] = "spielnummernichterkannt";
                            }
                        }    
                        
                        if (empty($error))
                        {                            
                            // Gibt es die Spielnummer
                            $datentutup = rex_sql::factory();
                            $sql = 'SELECT id, klassenid FROM '.rex::getTablePrefix() .'950_spielplan WHERE spielnr = "'.$booking['spielnr'].'" AND saisonid = "'. $booking['saison'].'"';
                            $datentutup->setQuery( $sql );
                            $datenzahl = $datentutup->getRows(); 
                            if ($datenzahl == 1)
                            {      
                              echo '<div class="alert alert-warning"><span class="badge pull-right">OK</span> Die Spielnummer ist im Spielplan vorhanden.</div>';   
                              $booking['klasse'] = $datentutup->getValue("klassenid", 0);   
                            }     
                            else if ($datenzahl > 1)
                            {      
                                echo '<div class="alert alert-warning"><span class="badge pull-right">!</span> Die Spielnummer ist mehrfach im Spielplan vorhanden. Bitte prüfen Sie, ob die Spielklasse korrekt erkannt wurde.</div>';     
                                $error[] = "spielnummermehrfach";
                            }
                            else
                            {
                                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielnummer ist noch nicht vorhanden. Bitte erst den Spielplan aktualisieren!</div>';    
                                $error[] = "spielnummerunbekannt";
                            }
                        } 
                             
                        if (empty($error))
                        {             
                            // Gibt es bereits einen Import
                            $datentutup = rex_sql::factory();
                            $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spielergebnisse WHERE spielnr = "'.$booking['spielnr'].'" AND saisonid = "'. $booking['saison'].'" AND klassenid = "'. $booking['klasse'].'"';
                            $datentutup->setQuery( $sql );
                            $datenzahl = $datentutup->getRows(); 
                            if ($datenzahl > 0)
                            {      
                              echo '<div class="alert alert-warning"><span class="badge pull-right">!</span> Es ist ein älterer Spielbericht vorhanden. Dieser wird ersetzt.</div>';    
                            }
                            else
                            {
                              echo '<div class="alert alert-warning"><span class="badge pull-right">OK</span> Der Spielbericht wird neu angelegt.</div>';    
                            }
                        }      
                        
                        $spielerzaehler = 0;
                        if (empty($error))
                        {             
                            // Passnummern auslesen und Spieler eintragen
                            $unbekanntepassnr = array();
                            echo '<div class="alert alert-info"><strong>Beteiligte Spieler:</strong><br />';
                            
                            // Alte Version ohne DKB
                            if ( $booking['dateityp'] == 'automat' )
                            {
                                  $zeilenzahl = $data->sheets[0]['numRows'];
                                  echo '<ul>';
                                  for($zi=3;$zi<$zeilenzahl;$zi++)
                          				{
                          					$testname = utf8_encode(trim($data->sheets[0]['cells'][$zi][1])); // Blatt 0, Spalte 1
                                    $testnamezahl = intval($testname);
                                    if ((!empty($testname)) AND (empty($testnamezahl)))
                                    {
                                        $testpass = led_passnummer($data->sheets[0]['cells'][$zi][3]);  // Blatt 0, Spalte 3
                                        if (!empty($testpass))
                                        {
                                                  
                                          // Gibt es die Passnummer
                                          $passtutup = rex_sql::factory();
                                          $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr = "'.$testpass.'"';
                                          $passtutup->setQuery( $sql );
                                          $passzahl = $passtutup->getRows(); 
                                          
                                          echo "<li>";                      
                                          if ($passzahl > 0) echo '<span class="badge pull-right">OK</span> ';
                                          else 
                                          {
                                              if ($booking['overwrite'])
                                              {
                                                  // Jetzt Spieler neu anlegen   
                                                  $spielsql = rex_sql::factory();
                                                  $spielsql->setTable(''.rex::getTablePrefix() .'950_spieler');
                                                  $spielsql->setValue('spieler', $testname );   
                                                  $spielsql->setValue('passnr', $testpass );     
                                                  $spielsql->setValue('mannschaftid', '0'); 
                                                  $spielsql->setValue('importid', $importfileid);
                                                  $spielsql->insert();  
                                                  
                                                  echo '<span class="badge pull-right">NEU</span> ';
                                              }
                                              else
                                              {
                                                echo '<span class="badge error pull-right">!</span> ';
                                                $unbekanntepassnr[] =  $testpass;
                                              }
                                          }
                                          echo "<strong>".$testpass."</strong> ";   
                                          echo $testname;   
                                          echo '</li>';    
                                          $spielerzaehler++;
                                        }
                                    }
                          				}     
                                  echo '</ul>';    
                                echo '</div>';  
                            }
                            
                            if ( $booking['dateityp'] == 'perhand' )
                            {
                                $spaltenzahl = $data->sheets[ $booking['dkbblattnr'] ]['numCols'];      
                                $zeilenzahl = $data->sheets[ $booking['dkbblattnr'] ]['numRows'];    
                                
                                echo '<ul>';  
                                for($zi=0;$zi<$zeilenzahl;$zi++)
                        				{
                                    $testpass = led_passnummer($data->sheets[ $booking['dkbblattnr'] ]['cells'][$zi][1]);  // erste Spallte auswerten
                                    $testname = utf8_encode(trim( $data->sheets[ $booking['dkbblattnr'] ]['cells'][$zi][2] ));  // zweite Spalte auswerten
                                    // gibt es die Passnummer
                                    if (!empty($testpass))
                                    {
                                              
                                      // Gibt es die Passnummer
                                      $passtutup = rex_sql::factory();
                                      $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr = "'.$testpass.'"';
                                      $passtutup->setQuery( $sql );
                                      $passzahl = $passtutup->getRows();     
                                              
                                      echo "<li>";                      
                                      if ($passzahl > 0) echo '<span class="badge pull-right">OK</span> ';
                                      else 
                                      {
                                          if ($booking['overwrite'])
                                          {
                                              // Jetzt Spieler neu anlegen   
                                              $spielsql = rex_sql::factory();
                                              $spielsql->setTable(''.rex::getTablePrefix() .'950_spieler');
                                              $spielsql->setValue('spieler', $testname );   
                                              $spielsql->setValue('passnr', $testpass );     
                                              $spielsql->setValue('mannschaftid', '0'); 
                                              $spielsql->setValue('importid', $importfileid);
                                              $spielsql->insert();  
                                              
                                              echo '<span class="badge pull-right">NEU</span> ';
                                          }
                                          else
                                          {
                                            echo '<span class="badge error pull-right">!</span> ';
                                            $unbekanntepassnr[] =  $testpass;
                                          }
                                      }
                                      echo "<strong>".$testpass."</strong> ";   
                                      echo $testname;   
                                      echo '</li>';    
                                      $spielerzaehler++;
                                    }
                                } 
                                echo '</ul>';  
                                echo '</div>';  
                            }
                        }
                        
                        // Bei unbekannten Passnummern neu importieren mit Hinweis!
                        if (!empty($unbekanntepassnr))
                        {
                            //echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es sind unbekannte Passnummern vorhanden!<br /><small>Bitte laden sie den Spielbericht erneut hoch und markieren Sie das Feld bei "Unbekannte Spielernummer neu eintragen".</small></div>';     
                            echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es sind unbekannte Passnummern vorhanden!<br /><small>Bitte prüfen und laden sie den Spielbericht erneut hoch.</small></div>';     
                            $error[] = "passnummernunbekannt";
                            $booking['showform'] = true;          
                            $booking['overwrite'] = true;
                        } 
                        else if (empty($spielerzaehler)) 
                        {
                            echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es wurden keine Spieler gefunden.</div>';     
                            $error[] = "keinespieler";
                        }
                        else echo '<div class="alert alert-warning"><span class="badge pull-right">OK</span> Alle Passnummern der Spieler sind korrekt.</div>';      
                                 
                        // Mannschaftsid auslesen
                        $teamtutup = rex_sql::factory();
                        $sql = 'SELECT heimmannschaftid, gastmannschaftid FROM '.rex::getTablePrefix() .'950_spielplan WHERE spielnr = "'.$booking['spielnr'].'" AND saisonid = "'. $booking['saison'].'" AND klassenid = "'. $booking['klasse'].'"';
                        $teamtutup->setQuery( $sql );
                        $datenzahl = $teamtutup->getRows();           
                      	$booking['mp_heim'] = '';
                      	$booking['mp_gast'] = '';    
                      	$booking['heimid'] = $teamtutup->getValue("heimmannschaftid", 0);
                      	$booking['gastid'] = $teamtutup->getValue("gastmannschaftid", 0);
                          
                        if (empty($error))
                        {   
                            // Jetzt das Spielergebnis auslesen   
                            if ( $booking['dateityp'] == 'automat' )
                            { 
                              	$booking['mp_heim'] = floatval(trim($data->sheets[2]['cells'][4][4])); // Blatt 2, Spalte 4   Bildschirm
                              	$booking['mp_gast'] = floatval(trim($data->sheets[2]['cells'][4][8])); // Blatt 2, Spalte 6    Bildschirm           
                            } 
                                   
                            if ( $booking['dateityp'] == 'perhand' )
                            { 
                              	$booking['mp_heim'] = floatval(trim($data->sheets[3]['cells'][4][4])); // Blatt 2, Spalte 4   Bildschirm
                              	$booking['mp_gast'] = floatval(trim($data->sheets[3]['cells'][4][8])); // Blatt 2, Spalte 6    Bildschirm           
                            } 
    
                            // Mannschaftsnamen auslesen
                            $mannschaftsnamen = array();
                            $manntutup = rex_sql::factory();
                            $sql = 'SELECT id, mannschaft FROM '.rex::getTablePrefix() .'950_mannschaft';
                            $manntutup->setQuery( $sql );
                            $mannzahl = $manntutup->getRows();   
                            for ($spi = 0; $spi<$mannzahl; $spi++) 
                            { 
                                $mannschaftsnamen[ $manntutup->getValue("id", $spi) ] = $manntutup->getValue("mannschaft", $spi);   
                            }  
                            
                            // Mannschaftspunkte sind immer 8
                            $mp_summe= $booking['mp_heim'] + $booking['mp_gast'];
                            if ( $mp_summe == 8)
                            {
                              echo '<div class="alert alert-warning"><span class="badge pull-right">OK</span> Das Spielergebnis wurde erkannt.</div>';  
                              echo '<div class="alert alert-info"><i>Mannschaftspunkte:</i> '.$mannschaftsnamen[ $booking['heimid'] ].' <strong>'.$booking['mp_heim'].'</strong>-<strong>'.$booking['mp_gast'].'</strong> '.$mannschaftsnamen[ $booking['gastid'] ].'</div>';   
                               
                            }
                            else
                            {
                              echo '<div class="alert alert-info"><i>Mannschaftspunkte:</i> '.$mannschaftsnamen[ $booking['heimid'] ].' <strong>'.$booking['mp_heim'].'</strong>-<strong>'.$booking['mp_gast'].'</strong> '.$mannschaftsnamen[ $booking['gastid'] ].'</div>';   
                              echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Das Spielergebnis ist fehlerhaft!</div>';     
                              $error[] = "MPSUMME";
                            }
                              
                              
                              //Tabellenpunkte
                              
                              // Satzpunkte Mann gegen Mann 
                        }
                        
                        // Abschluss
                        if (empty($error))
                        {                    
                            // Alte Werte löschen   
                            $manntutup = rex_sql::factory();
                            $sql = 'DELETE FROM '.rex::getTablePrefix() .'950_spielergebnisse WHERE spielnr = "'.$booking['spielnr'].'"  AND saisonid = "'. $booking['saison'].'" AND klassenid = "'. $booking['klasse'].'"';
                            $manntutup->setQuery( $sql );
                            
                            // Jetzt speichern Heimergebnis   
                            $spielsql = rex_sql::factory();
                            $spielsql->setTable(''.rex::getTablePrefix() .'950_spielergebnisse');
                            $spielsql->setValue('spielnr',$booking['spielnr'] );       
                            $spielsql->setValue('klassenid',$booking['klasse'] );        
                            $spielsql->setValue('saisonid',$booking['saison'] );   
                            $spielsql->setValue('importid', $importfileid);    
                            $spielsql->setValue('punkttyp', 'MP' );       
                            $spielsql->setValue('teamid', $booking['heimid'] );   
                            $spielsql->setValue('spielerid', '' );   
                            $spielsql->setValue('punktzahl', $booking['mp_heim'] );  
                            $spielsql->insert();     
                            
                            // Jetzt speichern Gastergebnis   
                            $spielsql = rex_sql::factory();
                            $spielsql->setTable(''.rex::getTablePrefix() .'950_spielergebnisse');
                            $spielsql->setValue('spielnr',$booking['spielnr'] );          
                            $spielsql->setValue('klassenid',$booking['klasse'] );        
                            $spielsql->setValue('saisonid',$booking['saison'] );   
                            $spielsql->setValue('importid', $importfileid);    
                            $spielsql->setValue('punkttyp', 'MP' );       
                            $spielsql->setValue('teamid', $booking['gastid'] );   
                            $spielsql->setValue('spielerid', '' );   
                            $spielsql->setValue('punktzahl', $booking['mp_gast'] );  
                            $spielsql->insert();   
                            
                            
                            echo '<div class="alert alert-success"><span class="badge pull-right">OK</span> Das Spiel wurde erfolgreich importiert.</div>';  
                            //echo '<div class="alert alert-info">Eine EMail mit dem Import-Ergebnis wurde an meine@email.de gesendet.</div>';  
                        }
                           
                        /*
                        if (empty($error))
                        {
                          ?>
                          <a class="btn btn-info" href="<?php echo rex_geturl( 67 , 0 , array( 'spiel' => 176 )); ?>">Ansicht des importierten Spiels</a>   
                          <?php
                        }
                        */
                        ?>       
                          <a class="btn btn-text pull-right" href="<?php echo rex_geturl( rex_article::getCurrentId() ); ?>">Weiteres Spiel hochladen</a>
                        <?php
                        
                        
                    }
                    else 
                    { 
                        if (($_FILES[ $einzelfrage ]['size']) > intval(return_bytes(ini_get($maxsizeusevalue)))) echo '<div class="alert alert-danger">Die Datei konnte leider nicht auf den Server geladen werden. Sie ist zu groß ('.return_bytes($_FILES['userfile']['size']).').</div>';
                        else echo '<div class="alert alert-danger">Die Datei konnte leider nicht auf den Server geladen werden.</div>';   
                    }  
                }
            } 
   
?>