<?php
    // Upload und Auswertung Einzelspiel Ergebnisdienst 
    // Version 20240517
    
    // alten Import löschen 
    /*    
    $delspielnr = '000981';  
    $existsql = 'DELETE FROM '.rex::getTablePrefix() .'950_spieltabelle WHERE spielnr = "'.$delspielnr.'" ';
    $newtutup = rex_sql::factory();
    $newtutup->setQuery( $existsql );
                                                                                                    
    $existsql = 'DELETE FROM '.rex::getTablePrefix() .'950_spielergebnisse WHERE spielnr = "'.$delspielnr.'" ';
    $newtutup = rex_sql::factory();
    $newtutup->setQuery( $existsql );                              
    */
    
    
            // Dateityp pruefen
            $allowed = array('xls', 'xlsx'); 
            $einzelfrage = 'datei'; 
            $importwarnung = false;   
            $showloginfos = false;
            if ($booking['usergruppe'] == "admin") $showloginfos = false;
            
            if ( $booking['step'] == 1)
            {
                // Hier der Upload                              
                if ($showloginfos) echo '<div class="alert alert-info">Es wird ein neues Spiel hochgeladen.</div>';    
                
                if(isset($_FILES[ $einzelfrage ]) && $_FILES[ $einzelfrage ]['error'] == 0)
                {                                
                    $extension = pathinfo($_FILES[ $einzelfrage ]['name'], PATHINFO_EXTENSION);
                    if ( (!empty($allowed)) AND (!in_array(strtolower($extension), $allowed)) ) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die gewählte Datei ist nicht geeignet. Nur Excel-Dateien mit der Endung XLS sind möglich.</div>'; $error[] = $einzelfrage; }
                    if (empty( $booking['saison'] )) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span>  wurde keine Saison ausgewählt.</div>'; $error[] = "saison"; }      
                    //if (empty( $booking['klasse'] )) { echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es wurde keine Spielklasse ausgewählt.</div>'; $error[] = "klasse"; }
                    
                    if (empty($error))
                    {                  
                                                      
                        // Dateinamen umwandeln
                        $filename = $_FILES[ $einzelfrage ]['name'];
                        $final_name = led_filename($filename);      
                        
                        // Datei hochladen
                        if(move_uploaded_file($_FILES[$einzelfrage]['tmp_name'], $LED_UPLOADFOLDER.'/'.$final_name))
                        {          
                          if (!empty( @$erlaubtespielberichtgroesse ))
                            {
                              // Prüfung erlaubte Dsateigröße
                              if (($_FILES[ $einzelfrage ]['size']) > intval($erlaubtespielberichtgroesse))
                              {

                                echo '<div class="alert alert-danger alert-pulsing"><strong style="display: inline-block; font-size: 240%; line-height: 1.2;">Fehlerhaftes Hochladen!<br />Die hochgeladene Datei ist zu groß! <br /><br /><br />Bitte benutzen Sie die schriftliche Bedienanleitung !</strong><br /><br />';       
                                //echo 'Bitte betätigen Sie zum hochladen des Spielberichtes den Schalter<br />
                                //<br />
                                //"Upload zum TKV“ <br />
                                //<br />
                                //Benutzen Sie die schriftliche Anleitung bzw. folgen sie den Bildschirmhinweisen.';                                                     
                                echo '</div>';
                                echo '<p><small class="text-muted text-help">   Die erlaubte Größe ist maximal: '.return_bytes( intval($erlaubtespielberichtgroesse) ).'; Die tatsächliche Größe ist '.return_bytes( $_FILES[ $einzelfrage ]['size'] ).'</small></p>';
                              
                                echo '<hr />';       
                                $error[] = "dateigroesse";                            
                              }
                              
                            }
                          
                            if (empty($error))
                            {
                              led_saveUpload($_FILES[$einzelfrage], $final_name, $filename);     
                              if ($showloginfos) echo '<div class="alert alert-success"><span class="badge pull-right">OK</span>Die Datei wurde erfolgreich hochgeladen.</div>';  
                              
                              $booking['showform'] = false;        
                              
                              // Import-ID mitsichern. falls etwas rückgängig gemacht werden soll
                              $importfileid = '';
                              $datentutup = rex_sql::factory();
                              $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_uploads WHERE filename = "'.$final_name.'"';
                              $datentutup->setQuery( $sql );
                              $datenzahl = $datentutup->getRows();     
                              if (!empty($datenzahl)) $importfileid = $datentutup->getValue("id", 0);   
                            }
                              
                            
                            if (empty($error))
                            { 
                                // Formular für nächsten Schritt anbieten
                                if (1==2)
                                {
                                    ?>  
                                    <form class="formgen ledupload clearfix" id="ledupload" action="<?php rex_geturl( rex_article::getCurrentId() ); ?>" method="post" enctype="multipart/form-data">          
                                      <?php if ( !rex_addon::get('yrewrite')->isAvailable() ) { ?><input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden"><?php } ?>           
                                      <input name="func" value="startupload" type="hidden">    
                                      <input name="saison" value="<?php echo $booking['saison']; ?>" type="hidden">
                                      <input name="klasse" value="<?php echo $booking['klasse']; ?>" type="hidden">    
                                      <input name="step" value="2" type="hidden">    
                                      <input name="importid" value="<?php echo $importfileid; ?>" type="hidden">    
                                           
                                      <div class="formblock formsendbtn">         
                                        <button type="submit" class="btn formsubmit formgreen">Jetzt die Daten prüfen</button>      
                                      </div>       
                                    </form>
                                    <?php      
                                }
                                $booking['importid'] = $importfileid;
                                $booking['step'] = 2;
                            
                            }                                 
                            else echo '<a class="btn btn-link pull-left up1" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a>';
                                            
                            
                            
                        }
                        else 
                        { 
                            if (($_FILES[ $einzelfrage ]['size']) > intval(return_bytes(ini_get($maxsizeusevalue)))) echo '<div class="alert alert-danger">Die Datei konnte leider nicht auf den Server geladen werden. Sie ist zu groß ('.return_bytes($_FILES['userfile']['size']).').</div>';
                            else echo '<div class="alert alert-danger">Die Datei konnte leider nicht auf den Server geladen werden.</div>';       
                            
                            
                            echo '<a class="btn btn-info pull-right up2" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a>';
                        }  
                    }
            }
        }
        
        // Jetzt Schritt 2 und 3     
        if ( $booking['step'] == 2)
        {  
          include(rex_path::src() ."/addons/ergebnisdienst/lib/upload.einzelspiel.step23.php");                   
        }    
        if ( $booking['step'] == 3)
        {            
          include(rex_path::src() ."/addons/ergebnisdienst/lib/upload.einzelspiel.step23.php");              
        }
        
        
        //print_r( $booking );
                
   
?>