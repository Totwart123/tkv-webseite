<?php
    // Upload und Auswertung Spielerliste Ergebnisdienst 
    // Version 20250526
    
    
            // Dateityp pruefen
            $allowed = array('csv'); 
            $einzelfrage = 'datei';   
            
            // CSV-Festlegungen
            $feldliste = array();
            $feldliste['lizenz'] = array("spalte" => 0, "label" => "Art");
            $feldliste['gender'] = array("spalte" => 3, "label" => "Geschlecht");
            $feldliste['name'] = array("spalte" => 5, "label" => "Nachname");
            $feldliste['vorname'] = array("spalte" => 6, "label" => "Vorname");
            $feldliste['geboren'] = array("spalte" => 7, "label" => "Geburtsdatum");
            $feldliste['passnr'] = array("spalte" => 9, "label" => "Lizenz-ID");
            $feldliste['klub'] = array("spalte" => 20, "label" => "Klub");
            $feldliste['adv'] = array("spalte" => 11, "label" => "Antidoping-Nummer");
            $korrektespaltenzahl = 32;
            
            $spielarray = array();
            $spielarray['spieler'] = array();
            $spielarray['spielerzahl'] = 0;
            
            if ( $booking['step'] == 1)
            {
                // Hier der Upload                              
                echo '<div class="alert alert-info">Es wird eine neue Spielerliste hochgeladen.</div>';    
                
                if(isset($_FILES[ $einzelfrage ]) && $_FILES[ $einzelfrage ]['error'] == 0)
                {                                
                    $extension = pathinfo($_FILES[ $einzelfrage ]['name'], PATHINFO_EXTENSION);
                    if ( (!empty($allowed)) AND (!in_array(strtolower($extension), $allowed)) ) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die gewählte Datei ist nicht geeignet. Nur CSV-Dateien mit der Endung CSV sind möglich.</div>'; $error[] = $einzelfrage; }
                    //if (empty( $booking['geschlecht'] )) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span>  wurde kein Geschlecht ausgewählt.</div>'; $error[] = "geschlecht"; }      
                    
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
                            
                            // Jetzt die Auswertung starten      
                            echo '<div class="alert alert-info">Verarbeitung der CSV-Datei wird gestartet...</div>';   
                            $tmp_kundenfile = @fopen( $file_to_include, "r");   
                            $tmp_values = @fgetcsv($tmp_kundenfile, 9999, ";");
                            $spaltenzahl = count( $tmp_values );
                            @fclose($tmp_kundenfile); 
                            
                            
                            if ($spaltenzahl != $korrektespaltenzahl) 
                            {                       
                                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die CSV-Datei wurde nicht erkannt, die Spaltenzahl ist fehlerhaft ('.$spaltenzahl.' statt '.$korrektespaltenzahl.')!</div>';   
                                $error[] = "csv";     
                            }     
                            else
                            {                       
                                $spaltenfehler = ""; 
                                
                                foreach($feldliste AS $fname => $fdata)
                                {
                                    // spaltenweise testen
                                    if ( $tmp_values[ $fdata['spalte'] ] != $fdata['label'] ) $spaltenfehler = $fname;
                                }


                                if (!empty($spaltenfehler)) 
                                {                       
                                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die CSV-Datei wurde nicht erkannt, die '. intval( $feldliste[$spaltenfehler]['spalte'] + 1).'. Spalte muss <strong>'.$feldliste[$spaltenfehler]['label'].'</strong> heißen!</div>';   
                                    $error[] = "csvlabel";         
                                }
                                else 
                                {                                                                 
                                    echo '<div class="alert alert-success"><span class="badge pull-right">OK</span> Erkannte CSV-Datei: <strong>Spielerliste</strong></div>';  
                                } 
                            } 
                            
                            if (empty($error))
                            { 
                                // Formular für nächsten Schritt anbieten
                                ?>  
                                <form class="formgen ledupload clearfix" id="ledupload" action="<?php rex_geturl( rex_article::getCurrentId() ); ?>" method="post" enctype="multipart/form-data">          
                                  <input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden">               
                                  <input name="func" value="spielerupload" type="hidden">    
                                  <input name="geschlecht" value="<?php echo $booking['geschlecht']; ?>" type="hidden">
                                  <input name="step" value="2" type="hidden">    
                                  <input name="importid" value="<?php echo $importfileid; ?>" type="hidden">    
                                       
                                  <div class="formblock formsendbtn">         
                                    <button type="submit" class="btn formsubmit formgreen">Jetzt die Daten importieren</button>      
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
            echo '<div class="alert alert-info">Die neue Spielerliste wird überprüft.</div>';     

            
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
                
                
                // Jetzt die Auswertung     
            }
            else
            {           
                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die CSV-Datei konnte nicht gelesen werden!</div>';   
                $error[] = "file";    
            
            }
            
            if (empty($error))
            {
               
                echo '<div class="alert alert-info"><strong>Erkennung Spieler</strong><br />';
            
                $spielercounter = 0;
                $tmp_kundenfile = @fopen( $file_to_include, "r");  
                while (($tmp_values = @fgetcsv($tmp_kundenfile, 9999, ";"))) 
                {
                  if ( $spielercounter == 0) {}
                  else
                  {
                       // Hier Spielername, Passnummer und Geburtstag auslesen
                       // Eintragen in DB oder Update in DB
                       // relevantes Jahr zum Stichtag ermitteln (also bei nach 30.06. so lassen, sonst Jahr minus 1)
                       $spielarray['spielerzahl'] = $spielarray['spielerzahl'] + 1 ;
                  }
                  $spielercounter++;
                }
                @fclose($tmp_kundenfile); 
            
                echo '</div>';  
                
                if (empty( $spielarray['spielerzahl'] ))        
                {
                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es wurden keine Spieler erkannt!</div>'; 
                    $error[] = 'keinespielererkannt';
                }         
            }
            
            if (empty($error))
            {   
                // Zum Start alle Spielberechtigungen löschen 
                $passtutup = rex_sql::factory();
                //$sql = 'UPDATE '.rex::getTablePrefix() .'950_spieler SET spielrecht = "" WHERE 1=1';
                $sql = 'UPDATE '.rex::getTablePrefix() .'950_spieler SET spielrecht = "", klub= "" WHERE 1=1';
                $passtutup->setQuery( $sql );   
                
                echo '<div class="alert alert-warning"><strong>Es wurden '.$spielarray['spielerzahl'].' Zeilen ausgewertet:</strong><br /><ul>';
                    
                    $tmp_kundenfile = @fopen( $file_to_include, "r");  
                    while (($tmp_values = @fgetcsv($tmp_kundenfile, 9999, ";"))) 
                    {
                      if ( $spielercounter == 0) {}
                      else
                      {
                        // Hier Spielername, Passnummer und Geburtstag auslesen
                       
                        //$testlizenz = mb_convert_encoding( trim(@$tmp_values[0]), "UTF-8"); 
                        $testlizenz = utf8_encode( trim(@$tmp_values[ $feldliste['lizenz']['spalte'] ]) ); 
                        $testgender = substr( trim(@$tmp_values[ $feldliste['gender']['spalte'] ]), 0, 1); 
                        $testname = utf8_encode( trim(@$tmp_values[ $feldliste['name']['spalte'] ]).", ".trim(@$tmp_values[ $feldliste['vorname']['spalte'] ]));  
                        $testgeboren = date ( "d.m.Y" , strtotime( trim(@$tmp_values[ $feldliste['geboren']['spalte'] ] )) );
                        $testpass = led_passnummer(trim(@$tmp_values[ $feldliste['passnr']['spalte'] ]));  
                        //$testklub = mb_convert_encoding( trim(@$tmp_values[21]), "UTF-8"); 
                        $testklub = utf8_encode( trim(@$tmp_values[ $feldliste['klub']['spalte'] ]) ); 
                        $testadv = utf8_encode( trim(@$tmp_values[ $feldliste['adv']['spalte'] ]) ); 

                        if ($testgender == "w") $testgender = "w";
                        else if ($testgender == "m") $testgender = "m";
                        else $testgender = "d";
                        
                        // relevantes Jahr zum Stichtag ermitteln (also bei nach 30.06. so lassen, sonst Jahr minus 1)
                        $testrealjahr = 0;
                        $jahreslaenge = strlen( $testgeboren );
                        if ($jahreslaenge == 10)
                        {
                          $tmpjahresarray = explode ('.', $testgeboren);
                          $tmpdatenzahl = count ($tmpjahresarray);
                          if ($tmpdatenzahl == 3)
                          {
                               $jahreslaenge =  strlen( intval($tmpjahresarray[2]) );
                               if ($jahreslaenge == 4)
                               {
                                   $testrealjahr =  intval($tmpjahresarray[2]);
                                   $testmonat = intval( $tmpjahresarray[1] );
                                   if ($testmonat < 7 ) $testrealjahr = $testrealjahr - 1;
                               }
                          }
                        }
                        
                        
                        // Eintragen in DB oder Update in DB
                        if (!empty($testpass))
                        {
                            if (!empty($testpass))
                            {
                                      
                              // Gibt es die Passnummer
                              $passtutup = rex_sql::factory();
                              $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr = "'.$testpass.'"';
                              $passtutup->setQuery( $sql );

                              $passzahl = $passtutup->getRows(); 

                              $spielsql = rex_sql::factory();
                              $spielsql->setTable(''.rex::getTablePrefix() .'950_spieler');
                              $spielsql->setValue('spielrecht', $testlizenz );   
                              $spielsql->setValue('spieler', $testname );   
                              $spielsql->setValue('passnr', $testpass );    
                              $spielsql->setValue('klub', $testklub );    
                              $spielsql->setValue('geboren', $testgeboren );                 
                              $spielsql->setValue('stichtagsgeburtsjahr', $testrealjahr );   
                              $spielsql->setValue('geschlecht', $testgender );  
                              $spielsql->setValue('adv', $testadv );  
                              $spielsql->setValue('importid', $booking['importid']);
                                                   
                              if ($passzahl > 0) 
                              {
                                  // Update          
                                  $spielsql->setWhere('id='.$passtutup->getValue("id", 0));   
                                  $tmpres = $spielsql->update();   
                                  
                                  if ($tmpres) echo '<li>'.$testname.' <span class="badge">geändert</span></li>';
                                  else echo '<li><strong>'.$testname.'</strong> <span class="badge">ERROR</span></li>';
                              }
                              else 
                              {
                                  // Jetzt Spieler neu anlegen   
                                  $tmpres = $spielsql->insert();  
                                  
                                  if ($tmpres) echo '<li>'.$testname.' <span class="badge">NEU</span></li>';       
                                  else echo '<li><strong>'.$testname.'</strong> <span class="badge">ERROR</span></li>';
                              }
                              $spielarray['spielerzahl'] = $spielarray['spielerzahl'] + 1 ;
                            }
                        }
                           
                           
                      }
                      $spielercounter++;
                    }
                    @fclose($tmp_kundenfile); 
                                    /*
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
                                    */
                                    echo '</ul>';
                                    echo '</div>'; 
            }
            
            
            echo '<div class="clearfix"><a class="btn btn-info pull-right" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a></div>';
                                 
        }
                
   
?>