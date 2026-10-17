<?php
    // Version 20250526
    // Kennzeichnung: Änderung 2025 mit Prüfung Ersatzspieler
    //$showloginfos = true;

if (isset($booking))
{
            // Hier die Prüfung                              
            if ($showloginfos) echo '<div class="alert alert-info">Das Spiel wird überprüft.</div>';   
            $gotostep3 = false;  
            $neuespielermailtext = '';

            
            // Import-ID auslesen und Dateinamen ermitteln
            $booking['importfile'] = '';
            $datentutup = rex_sql::factory();
            $sql = 'SELECT filename FROM '.rex::getTablePrefix() .'950_uploads WHERE id = "'.$booking['importid'].'"';
            $datentutup->setQuery( $sql );
            $datenzahl = $datentutup->getRows();     
            if (!empty($datenzahl)) 
            {
                $entry = $booking['importfile'] = $datentutup->getValue("filename", 0);     
                $inputFileName = $LED_UPLOADFOLDER."/".$booking['importfile'] ;
                
                
                // Jetzt die Excel-Funktionen     
                include_once ( rex_path::src().'/addons/ergebnisdienst/vendor/classes/PHPExcel/IOFactory.php' );    
                
                //echo '<div class="alert alert-warning">Datei: '.$inputFileName.'</div>'; 
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName); 
                //echo '<div class="alert alert-warning">TYP: '.$inputFileType.'</div>'; 
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $sheetList = $objReader->listWorksheetNames($inputFileName);   
                
                $tmp_sheetzahl =  count($sheetList);          
                $tmp_dkbsheet = '-';            
                $tmp_grundsheet = '-';          
                $tmp_datensheet = '-';            
                $tmp_einzelsheet = '-';    
                $tmp_teamsheet = '-';
                $tmp_spielnr = '-';    
                $tmp_spielklasse = '';     
                $tmp_erlaubteklassen = array();
                $kegeldateityp[$entry] = '';  
                $tmp_sheetnames = array();
                $tmp_sheetcounter = 0;
                foreach( $sheetList AS $sheetid=>$mysheet)
                {
                    //echo $sheetid.": ".$mysheet."<br />";
                    if ($mysheet == "DKB") $tmp_dkbsheet = $sheetid;     
                    if ($mysheet == "Grundeingaben") $tmp_grundsheet = $sheetid;     
                    if ($mysheet == "Daten") $tmp_datensheet = $sheetid;
                    $tmp_sheetnames[$tmp_sheetcounter]  = $mysheet;
                    $tmp_sheetcounter++;
                }
                
                // Jetzt die Datei lesen
                $objPHPExcel = $objReader->load($inputFileName);
                
                // Spielnummer ermitteln
                if ( $tmp_grundsheet === 2)
                {
                    $objPHPExcel->setActiveSheetIndex( $tmp_grundsheet );
                    $tmp_spielnr = intval( $objPHPExcel->getActiveSheet()->getCell('C8')->getValue() );
                    if (!empty($tmp_spielnr)) 
                    {
                      $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('C8')->getValue() );    
                      $kegeldateityp[$entry] = "A";
                    }  
                    if (empty($tmp_spielnr))
                    {
                        if (empty($tmp_dkbsheet))
                        {
                            $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
                            $tmp_spielnr = intval($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );        
                            if (!empty($tmp_spielnr)) 
                            {
                              $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );      
                              $kegeldateityp[$entry] = "B";
                            }    
                        }
                        else 
                        {
                            $objPHPExcel->setActiveSheetIndex( $tmp_datensheet );
                            $tmp_spielnr = intval($objPHPExcel->getActiveSheet()->getCell('F1')->getValue() );        
                            if (!empty($tmp_spielnr)) 
                            {
                              $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('F1')->getValue() );      
                              $kegeldateityp[$entry] = "H";
                            }    
                        }
                        
                    }      
                }
                else if ( $tmp_datensheet === 0)
                {
                    // Manchmal per Hand in DKB eingetragen                           
                    $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
                    $tmp_spielnr = intval($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );        
                    if (!empty($tmp_spielnr)) 
                    {
                      $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );      
                      $kegeldateityp[$entry] = "C";
                    }  
                    if (empty($tmp_spielnr))
                    {
                        $objPHPExcel->setActiveSheetIndex( $tmp_datensheet );
                        $tmp_spielnr = $objPHPExcel->getActiveSheet()->getCell('F1')->getValue();       
                        $kegeldateityp[$entry] = "D";
                    }
                }  
                else if ( $tmp_datensheet === '-')
                {
                    
                    $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
                    $tmp_spielnr = intval($objPHPExcel->getActiveSheet()->getCell('B3')->getValue() );        
                    if (!empty($tmp_spielnr)) 
                    {
                        $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('B3')->getValue() );   
                        $kegeldateityp[$entry] = "F";     
                    }
                    if (empty($tmp_spielnr))
                    {                                                                                   
                        //echo '<div class="alert alert-warning"><span class="badge pull-right">!</span> Dies ist ein Test für DKBC!</div>';   
                        $tmp_spielnr = intval($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );        
                        if (!empty($tmp_spielnr)) 
                        {
                          $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() ); 
                          
                          // Klassenname ermitteln   
                          $tmp_spielklasse = trim($objPHPExcel->getActiveSheet()->getCell('P6')->getValue() ); 
                             
                          $kegeldateityp[$entry] = "E";
                          
                          if ( @$tmp_sheetnames[1] == "Dialog")  $kegeldateityp[$entry] = "G";
                        }  
                    } 
                } 
                else if (( $tmp_grundsheet === 3) AND ($tmp_datensheet === 2))
                {
                    $objPHPExcel->setActiveSheetIndex( $tmp_datensheet );
                    $tmp_spielnr = intval( $objPHPExcel->getActiveSheet()->getCell('F1')->getValue() );
                    if (!empty($tmp_spielnr)) 
                    {
                      $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('F1')->getValue() );    
                      $kegeldateityp[$entry] = "H";
                    }  
                    if (empty($tmp_spielnr))
                    {
                        if (empty($tmp_dkbsheet))
                        {
                            $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
                            $tmp_spielnr = intval($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );        
                            if (!empty($tmp_spielnr)) 
                            {
                              $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );      
                              $kegeldateityp[$entry] = "B";
                            }    
                        }
                        else 
                        {
                            $objPHPExcel->setActiveSheetIndex( $tmp_datensheet );
                            $tmp_spielnr = intval($objPHPExcel->getActiveSheet()->getCell('F1')->getValue() );        
                            if (!empty($tmp_spielnr)) 
                            {
                              $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('F1')->getValue() );      
                              $kegeldateityp[$entry] = "H";
                            }    
                        }
                        
                    }      
                } 
                
                if (empty($kegeldateityp[$entry])) 
                {
                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Der passende Aufbau der Excel-Datei konnte nicht ermittelt werden.</div>';   
                    $error[] = "spieldateityp";    
                }
                if (empty($tmp_spielnr)) 
                {        
                    echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielnummer konnte nicht ermittelt werden.</div>';   
                    $error[] = "spielnummer";    
                }
                else
                {
                    // Spielklasse ermitteln
                    if (empty($booking['klasse']))
                    {        
                        $datentutup = rex_sql::factory();
                        $sql = 'SELECT klassenid FROM '.rex::getTablePrefix() .'950_spielplan WHERE spielnr = "'.led_spielnummer($tmp_spielnr).'" AND saisonid = "'. $booking['saison'].'"';
                        //echo $sql;
                        $datentutup->setQuery( $sql );
                        $datenzahl = $datentutup->getRows();     
                        if ($datenzahl == 1)
                        {
                            $booking['klasse'] = $datentutup->getValue("klassenid", 0);
                            
                            $datentutup = rex_sql::factory();
                            $sql = 'SELECT id, klasse, geschlecht, spielerzahl FROM '.rex::getTablePrefix() .'950_klasse WHERE id = "'.$booking['klasse'].'"';
                            $datentutup->setQuery( $sql );
                            $datenzahl = $datentutup->getRows();     
                            if (!empty($datenzahl))
                            {
                                   
                                $booking['klasse'] = $datentutup->getValue("id", 0);           
                                $booking['klassename'] = $datentutup->getValue("klasse", 0);     
                                $booking['geschlecht'] = $datentutup->getValue("geschlecht", 0);    
                                $booking['spielerzahl'] = $datentutup->getValue("spielerzahl", 0);    
                                if ($showloginfos) echo '<div class="alert alert-success"><span class="badge pull-right">i</span> Ermittelte Spielklasse: <strong>'.$booking['klassename'].' <small>('.$booking['spielerzahl'].' Spieler)</small></strong></div>';         
                            }
                            else
                            {
                                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielklasse ist noch nicht vom Admin hinterlegt.</div>';   
                                $error[] = "spielklasse";   
                            } 
                        }           
                        else if ($datenzahl > 1)  
                        {
                            
                            for ($spi = 0; $spi<$datenzahl; $spi++) 
                            {                                                           
                                $tmp_erlaubteklassen[] =  $datentutup->getValue("klassenid", $spi);  
                                $datentutup->next();        
                            }
                            
                            //$tmp_spielklasse = 'xx'.$tmp_spielklasse;
                            if (!empty($tmp_spielklasse))
                            {
                                
                                $klassentutup = rex_sql::factory();
                                $sql = 'SELECT id, klasse, geschlecht, spielerzahl FROM '.rex::getTablePrefix() .'950_klasse WHERE klasse = "'.$tmp_spielklasse.'" AND id IN ('.implode(',', $tmp_erlaubteklassen ).')';
                                $klassentutup->setQuery( $sql );
                                $klassenzahl = $klassentutup->getRows();  
                                if ($klassenzahl == 1)
                                {         
                                    $booking['klasse'] = $klassentutup->getValue("id", 0);           
                                    $booking['klassename'] = $klassentutup->getValue("klasse", 0);     
                                    $booking['geschlecht'] = $klassentutup->getValue("geschlecht", 0);    
                                    $booking['spielerzahl'] = $klassentutup->getValue("spielerzahl", 0);    
                                    if ($showloginfos) echo '<div class="alert alert-success"><span class="badge pull-right">i</span> Ermittelte Spielklasse: <strong>'.$booking['klassename'].' <small>('.$booking['spielerzahl'].' Spieler)</small></strong></div>';   
                                }
                                else
                                {                                                                                                                                                                      
                                    echo '<div class="alert alert-warning"><span class="badge pull-right">i</span> Die Spielklasse im Datenblatt <strong>'.$tmp_spielklasse.'</strong> konnte nicht ausgewertet werden.</div>';   
                                    echo '<div class="alert alert-warning"><span class="badge pull-right">i</span> Bitte wählen Sie weiter unten die Spielklasse für dieses Spiel aus.</div>';   
                                }   
                            }
                            else
                            {                          
                                echo '<div class="alert alert-warning"><span class="badge pull-right">i</span> Bitte wählen Sie unten die Spielklasse für dieses Spiel aus.</div>';   
                            }    
                            
                        }    
                        else
                        {
                            echo '<div class="alert alert-danger alert-pulsing"><span class="badge pull-right">!</span> Die Spielklasse konnte nicht ermittelt werden.<br /><small>Bitte überprüfen Sie die Spielnummer auf dem Spielbericht.</small></div>';   
                            $error[] = "spielklasse";   
                        }    
                    }
                    else
                    {
                        $datentutup = rex_sql::factory();
                        $sql = 'SELECT klassenid FROM '.rex::getTablePrefix() .'950_spielplan WHERE spielnr = "'.led_spielnummer($tmp_spielnr).'" AND saisonid = "'. $booking['saison'].'" AND klassenid = "'. $booking['klasse'].'"';
                        //echo $sql;
                        $datentutup->setQuery( $sql );
                        $datenzahl = $datentutup->getRows();     
                        if ($datenzahl == 1)
                        {
                            $booking['klasse'] = $datentutup->getValue("klassenid", 0);
                            
                            $datentutup = rex_sql::factory();
                            $sql = 'SELECT id, klasse, geschlecht, spielerzahl FROM '.rex::getTablePrefix() .'950_klasse WHERE id = "'.$booking['klasse'].'"';
                            $datentutup->setQuery( $sql );
                            $datenzahl = $datentutup->getRows();     
                            if (!empty($datenzahl))
                            {
                                   
                                $booking['klasse'] = $datentutup->getValue("id", 0);           
                                $booking['klassename'] = $datentutup->getValue("klasse", 0);     
                                $booking['geschlecht'] = $datentutup->getValue("geschlecht", 0);    
                                $booking['spielerzahl'] = $datentutup->getValue("spielerzahl", 0);    
                                if ($showloginfos) echo '<div class="alert alert-success"><span class="badge pull-right">i</span> Ermittelte Spielklasse: <strong>'.$booking['klassename'].' <small>('.$booking['spielerzahl'].' Spieler)</small></strong></div>';         
                            }
                            else
                            {
                                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielklasse ist noch nicht vom Admin hinterlegt.</div>';   
                                $error[] = "spielklasse";   
                            } 
                        }
                        else
                        {
                            echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Spielklasse konnte nicht ermittelt werden.</div>';   
                            $error[] = "spielklasse";   
                        }    
                    }               
                }
                
                if (!empty($error))
                {        
                     if (($booking['usergruppe'] == "admin") AND ($showloginfos))
                     {
                        //$tmp_teamsheet = 4;
                        //$tmp_sheetzahl
                        echo '<div class="alert alert-default"><strong> Analyse-Daten für den Admin:</strong><br />';       
                        echo "<br />DKB: ".$tmp_dkbsheet;                                                                   
                        echo "<br />Grundeingaben: ".$tmp_grundsheet;          
                        echo "<br />Daten: ".$tmp_datensheet;                 
                        echo "<br />Blätter: ".$tmp_sheetzahl;     
                        //if ($tmp_sheetzahl > 1) $objPHPExcel->setActiveSheetIndex( 2 );
                        echo '<pre>';
                        print_r( $tmp_sheetnames );  
                        echo '</pre>';
                        echo '</div><hr />';      
                     }
                }
                else
                {
                    //print_r( $booking );
                    if ($showloginfos) echo '<div class="alert alert-success"><span class="badge pull-right">ok</span> Die erkannte Spielnummer lautet: <strong>'.$tmp_spielnr.'</strong></div>';    
                    if (($booking['usergruppe'] == "admin") AND ($showloginfos)) echo '<div class="alert alert-success"><span class="badge pull-right">ok</span> Der erkannte Aufbau der Excel-Datei ist vom <strong>Typ '.$kegeldateityp[$entry].'</strong></div>';    
                    
                    if (!in_array( $kegeldateityp[$entry] , array("A","B","C","D","E","F", "G", "H")))
                    {  
                        if (($booking['usergruppe'] == "admin") AND ($showloginfos)) 
                        {
                            //$tmp_teamsheet = 4;
                            //$tmp_sheetzahl
                            echo '<div class="alert alert-default"><strong> Analyse-Daten für den Admin:</strong><br />';       
                            echo "<br />DKB: ".$tmp_dkbsheet;                                                                   
                            echo "<br />Grundeingaben: ".$tmp_grundsheet;          
                            echo "<br />Daten: ".$tmp_datensheet;                 
                            echo "<br />Blätter: ".$tmp_sheetzahl;     
                            //if ($tmp_sheetzahl > 1) $objPHPExcel->setActiveSheetIndex( 2 );
                            echo '<pre>';
                            print_r( $tmp_sheetnames );  
                            echo '</pre>';
                            echo '</div><hr />';
                        }      
                    }   
                    
                    // Jetzt Dateityp speichern
                    $csvtypetup = rex_sql::factory();
                    $sql = 'UPDATE '.rex::getTablePrefix() .'950_uploads SET csvdateityp = "'.$kegeldateityp[$entry].'" WHERE id = "'.$booking['importid'] .'" ';
                    $csvtypetup->setQuery( $sql );   
                    
                    if ((!in_array( $kegeldateityp[$entry] , array("A","D", "E"))) AND (!$istdkbc))
                    {  
                        echo '<div class="alert alert-danger"><strong> Ungültige Datei!</strong><br />';       
                        echo "Die bereitgestellte Datei ist nicht offiziell geeignet. Es ist kein Import von Einzelergebnissen möglich!";                                                     
                        echo '</div><hr />';    
                    }   
                    else if ((!in_array( $kegeldateityp[$entry] , array("E"))) AND ($istdkbc))
                    {  
                        echo '<div class="alert alert-danger"><strong> Ungültige Datei!</strong><br />';       
                        echo "Die bereitgestellte Datei ist nicht offiziell geeignet. Es ist kein Import von Einzelergebnissen möglich!";                                                     
                        echo '</div><hr />';    
                    }  
                    else
                    {
                        // Hier nun die Detailauswertung     
                        $tmp_spielername = array();     
                        $tmp_spielergeboren = array();      
                        $tmp_spielerersatz = array();      
                        $tmp_spielerersatzname = array();
                        $tmp_spielerersatzgeboren = array();
                        
                        if ( $kegeldateityp[$entry] == "A")
                        {
                              include('phpexcel_test.typ_a.php');                                                                       
                        }
                        /*                      
                        if ( $kegeldateityp[$entry] == "B")
                        {
                              include('phpexcel_test.typ_a.php');                                                                       
                        }                       
                        if ( $kegeldateityp[$entry] == "C")
                        {
                              include('phpexcel_test.typ_c.php');                                                                       
                        }
                        */
                        if ( $kegeldateityp[$entry] == "D")
                        {
                              include('phpexcel_test.typ_c.php');                                                                       
                        }
                        /*
                        if ( $kegeldateityp[$entry] == "E")
                        {                                
                              include('phpexcel_test.typ_e.php');                                               
                        }
                        if ( $kegeldateityp[$entry] == "F")
                        {                                 
                              include('phpexcel_test.typ_c.php');      
                        }      
                        if ( $kegeldateityp[$entry] == "G")
                        {                                 
                              include('phpexcel_test.typ_g.php'); // ähnlich E     
                        }      
                        if ( $kegeldateityp[$entry] == "H")
                        {                                 
                              include('phpexcel_test.typ_h.php'); // ähnlich G, ursprünglich B    
                        }
                        */    
                        if ( $kegeldateityp[$entry] == "E")
                        {                              
                            // POKAL ist immer E!
                            include('phpexcel_test.typ_e.php');                                                                       
                        }
                  
                        // Gesamtergebnis ermitteln   
                        $punktergebnis[0]['teamkegel'] = 0;                                                           
                        $punktergebnis[1]['teamkegel'] = 0;         
                        $punktergebnis[0]['teammannschaftspunkte'] = 0;                                                           
                        $punktergebnis[1]['teammannschaftspunkte'] = 0;    
                        $punktergebnis[0]['teamsatzpunkte'] = 0;                                                           
                        $punktergebnis[1]['teamsatzpunkte'] = 0;    
                        if (!isset( $punktergebnis[0]['pokalsvpunkte'] )) $punktergebnis[0]['pokalsvpunkte'] = 0;                                                           
                        if (!isset( $punktergebnis[1]['pokalsvpunkte'] )) $punktergebnis[1]['pokalsvpunkte'] = 0;     
                        $punktergebnis[0]['pokalsatzp'] = 0;                                                           
                        $punktergebnis[1]['pokalsatzp'] = 0;    
                        for ($aspieler=1;$aspieler < 7; $aspieler++) 
                        {
                            // Maximal 6, wenn felder leer sind, diese weglassen
                            if (!empty($tmp_spielername[0][$aspieler]))
                            {
                                $punktergebnis[0]['teamkegel']  = $punktergebnis[0]['teamkegel']   + $punktergebnis[0]['punkte'][$aspieler];     
                                $punktergebnis[1]['teamkegel']  = $punktergebnis[1]['teamkegel']   + $punktergebnis[1]['punkte'][$aspieler];   
                                $punktergebnis[0]['teammannschaftspunkte']  = $punktergebnis[0]['teammannschaftspunkte']   + $punktergebnis[0]['mannschaft'][$aspieler];     
                                $punktergebnis[1]['teammannschaftspunkte']  = $punktergebnis[1]['teammannschaftspunkte']   + $punktergebnis[1]['mannschaft'][$aspieler];    
                                $punktergebnis[0]['teamsatzpunkte']  = $punktergebnis[0]['teamsatzpunkte']   + $punktergebnis[0]['satz'][$aspieler];     
                                $punktergebnis[1]['teamsatzpunkte']  = $punktergebnis[1]['teamsatzpunkte']   + $punktergebnis[1]['satz'][$aspieler];  
                            }     
                        }  
                        $punktergebnis[0]['pokalsatzp'] = $punktergebnis[0]['teamsatzpunkte'];                                                           
                        $punktergebnis[1]['pokalsatzp'] = $punktergebnis[1]['teamsatzpunkte'];    
                        
                        $differenz =  $punktergebnis[0]['teamkegel']  - $punktergebnis[1]['teamkegel'] ;
                        
                        //print_r($tmp_spielerersatz );
                        
                        // Jetzt Daten anzeigen
                        $unbekanntepassnr = array();
                        $spielerzaehler = 0;
                        $tablehtml = '';
                        // Spielerwertung vorerst inaktiv bei anderen Typen
                        if (1 == 1)
                        {
                          $tablehtml .= '<table class="table table-condensed">';
                          if (!empty($tmp_spielername[0]))
                          {
                              foreach( $tmp_spielername[0] AS $aspieler => $heimspieler)
                              {
                                if (!empty( $tmp_spielername[0][$aspieler] ))
                                {
                                $tablehtml .= '<tr>';
                                $tablehtml .= '<td>';
                                $tablehtml .= '<strong>'.$tmp_spielername[0][$aspieler].'</strong>'; 
                                if (!empty($tmp_spielerersatz[0][$aspieler])) 
                                {
                                  $tablehtml .= ' <br />[Auswechslung';
                                  if (!empty($tmp_spielerersatzname[0][$aspieler])) $tablehtml .= ': '.$tmp_spielerersatzname[0][$aspieler]; 
                                  $tablehtml .= ' <span class="badge">'.$tmp_spielerersatz[0][$aspieler].'</span> ';

                                  // ANFANG Ersatzspieler prüfen
                                  $testname = $tmp_spielerersatzname[0][$aspieler];
                                  $testpass =  $tmp_spielerersatz[0][$aspieler];       
                                  $testgeboren =  $tmp_spielerersatzgeboren[0][$aspieler];
                          
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
                                  
                          
                                  if (!empty($testpass))
                                  {
                                            
                                    // Gibt es diese Spieler-ID
                                    $passtutup = rex_sql::factory();
                                    $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr = "'.$testpass.'"';
                                    $passtutup->setQuery( $sql );
                                    $passzahl = $passtutup->getRows(); 
                                                        
                                    if ($passzahl > 0) $tablehtml .= '<span class="badge">OK</span> ';
                                    else 
                                    {                                                                     
                                        $tablehtml .= '<span class="badge" style="background-color: #ddd;">geb. '.substr($testgeboren,3).'</span> ';
                                        //$tablehtml .= '<span class="badge" style="background-color: #ddd;">geb. '.($testgeboren).'</span> ';
                                        if ($booking['addnewplayer'])
                                        {
                                            // Jetzt Spieler neu anlegen   
                                            $spielsql = rex_sql::factory();
                                            $spielsql->setTable(''.rex::getTablePrefix() .'950_spieler');
                                            $spielsql->setValue('spieler', $testname );   
                                            $spielsql->setValue('passnr', $testpass );   
                                            $spielsql->setValue('importid', $booking['importid']);     
                                            $spielsql->setValue('geboren', $testgeboren );            
                                            $spielsql->setValue('geschlecht', $booking['geschlecht'] );       
                                            $spielsql->setValue('stichtagsgeburtsjahr', $testrealjahr );   
                                            $spielsql->insert();  
                                                                                  
                                            $tablehtml .= '<span class="badge">NEU</span> ';
                                            
                                            $neuespielermailtext .= 'Name: '.$testname."\n";
                                            $neuespielermailtext .= 'Spieler-ID: '.$testpass."\n\n";
                                        }
                                        else
                                        {
                                          $tablehtml .= '<span class="badge error">!</span> ';
                                          $unbekanntepassnr[] =  $testpass;
                                        }
                                    }
                                    $spielerzaehler++;
                                  } 
                                  
                                  $tablehtml .= ']';
                                  // ENDE Ersatzspieler prüfen
                                }
                                $tablehtml .= '<br /><span class="badge">'.$tmp_spielernummer[0][$aspieler].'</span> ';      
                                //$tablehtml .= '<span class="badge" style="background-color: #ddd;">geb:'.$tmp_spielergeboren[0][$aspieler].'</span> ';
                                
                                // Spieler pruefen
                                $testname = $tmp_spielername[0][$aspieler];
                                $testpass =  $tmp_spielernummer[0][$aspieler];       
                                $testgeboren =  $tmp_spielergeboren[0][$aspieler];
                                
                                //$tablehtml .= $testgeboren."<br />";
                                
                        
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
                        
                                if (!empty($testpass))
                                {
                                          
                                  // Gibt es diese Spieler-ID
                                  $passtutup = rex_sql::factory();
                                  $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr = "'.$testpass.'"';
                                  $passtutup->setQuery( $sql );
                                  $passzahl = $passtutup->getRows(); 
                                                       
                                  if ($passzahl > 0) $tablehtml .= '<span class="badge">OK</span> ';
                                  else 
                                  {                                                                     
                                      $tablehtml .= '<span class="badge" style="background-color: #ddd;">geb. '.substr($testgeboren,3).'</span> ';
                                      //$tablehtml .= '<span class="badge" style="background-color: #ddd;">geb. '.($testgeboren).'</span> ';
                                      if ($booking['addnewplayer'])
                                      {
                                          // Jetzt Spieler neu anlegen   
                                          $spielsql = rex_sql::factory();
                                          $spielsql->setTable(''.rex::getTablePrefix() .'950_spieler');
                                          $spielsql->setValue('spieler', $testname );   
                                          $spielsql->setValue('passnr', $testpass );   
                                          $spielsql->setValue('importid', $booking['importid']);     
                                          $spielsql->setValue('geboren', $testgeboren );            
                                          $spielsql->setValue('geschlecht', $booking['geschlecht'] );       
                                          $spielsql->setValue('stichtagsgeburtsjahr', $testrealjahr );   
                                          $spielsql->insert();  
                                                                                
                                          $tablehtml .= '<span class="badge">NEU</span> ';
                                          
                                          $neuespielermailtext .= 'Name: '.$testname."\n";
                                          $neuespielermailtext .= 'Spieler-ID: '.$testpass."\n\n";
                                      }
                                      else
                                      {
                                        $tablehtml .= '<span class="badge error">!</span> ';
                                        $unbekanntepassnr[] =  $testpass;
                                      }
                                  }
                                  $spielerzaehler++;
                                }  
                                          
                                // Ende Spieler pruefen

                                $tablehtml .= '</td>';      
                                $tablehtml .= '<td>:</td>';      
                                $tablehtml .= '<td>';
                                $tablehtml .= '<strong>'.$tmp_spielername[1][$aspieler].'</strong>';
                                if (!empty($tmp_spielerersatz[1][$aspieler]))
                                {
                                  $tablehtml .= ' <br />[Auswechslung';
                                  if (!empty($tmp_spielerersatzname[1][$aspieler])) $tablehtml .= ': '.$tmp_spielerersatzname[1][$aspieler]; 
                                  $tablehtml .= ' <span class="badge">'.$tmp_spielerersatz[1][$aspieler].'</span> ';
                                  
                                  // ANFANG Ersatzspieler prüfen
                                  $testname = $tmp_spielerersatzname[1][$aspieler];
                                  $testpass =  $tmp_spielerersatz[1][$aspieler];       
                                  $testgeboren =  $tmp_spielerersatzgeboren[1][$aspieler];
                          
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
                                  
                          
                                  if (!empty($testpass))
                                  {
                                            
                                    // Gibt es diese Spieler-ID
                                    $passtutup = rex_sql::factory();
                                    $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr = "'.$testpass.'"';
                                    $passtutup->setQuery( $sql );
                                    $passzahl = $passtutup->getRows(); 
                                                        
                                    if ($passzahl > 0) $tablehtml .= '<span class="badge">OK</span> ';
                                    else 
                                    {                                                                     
                                        $tablehtml .= '<span class="badge" style="background-color: #ddd;">geb. '.substr($testgeboren,3).'</span> ';
                                        //$tablehtml .= '<span class="badge" style="background-color: #ddd;">geb. '.($testgeboren).'</span> ';
                                        if ($booking['addnewplayer'])
                                        {
                                            // Jetzt Spieler neu anlegen   
                                            $spielsql = rex_sql::factory();
                                            $spielsql->setTable(''.rex::getTablePrefix() .'950_spieler');
                                            $spielsql->setValue('spieler', $testname );   
                                            $spielsql->setValue('passnr', $testpass );   
                                            $spielsql->setValue('importid', $booking['importid']);     
                                            $spielsql->setValue('geboren', $testgeboren );            
                                            $spielsql->setValue('geschlecht', $booking['geschlecht'] );       
                                            $spielsql->setValue('stichtagsgeburtsjahr', $testrealjahr );   
                                            $spielsql->insert();  
                                                                                  
                                            $tablehtml .= '<span class="badge">NEU</span> ';
                                            
                                            $neuespielermailtext .= 'Name: '.$testname."\n";
                                            $neuespielermailtext .= 'Spieler-ID: '.$testpass."\n\n";
                                        }
                                        else
                                        {
                                          $tablehtml .= '<span class="badge error">!</span> ';
                                          $unbekanntepassnr[] =  $testpass;
                                        }
                                    }
                                    $spielerzaehler++;
                                  } 
                                  
                                  $tablehtml .= ']';
                                  // ENDE Ersatzspieler prüfen

                                } 
                                $tablehtml .= '<br /><span class="badge">'.$tmp_spielernummer[1][$aspieler].'</span> ';       
                                
                                // Spieler pruefen
                                $testname = $tmp_spielername[1][$aspieler];
                                $testpass =  $tmp_spielernummer[1][$aspieler];      
                                $testgeboren =  $tmp_spielergeboren[1][$aspieler];
                                                      
                                //$tablehtml .= $testgeboren."<br />";
                        
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
                                
                                
                                if (!empty($testpass))
                                {
                                          
                                  // Gibt es die Spieler-ID
                                  $passtutup = rex_sql::factory();
                                  $sql = 'SELECT id FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr = "'.$testpass.'"';
                                  $passtutup->setQuery( $sql );
                                  $passzahl = $passtutup->getRows(); 
                                                       
                                  if ($passzahl > 0) $tablehtml .= '<span class="badge">OK</span> ';
                                  else 
                                  {                         
                                      $tablehtml .= '<span class="badge" style="background-color: #ddd;">'.substr($testgeboren,3).'</span> ';
                                      if ($booking['addnewplayer'])
                                      {
                                          // Jetzt Spieler neu anlegen   
                                          $spielsql = rex_sql::factory();
                                          $spielsql->setTable(''.rex::getTablePrefix() .'950_spieler');
                                          $spielsql->setValue('spieler', $testname );   
                                          $spielsql->setValue('passnr', $testpass );   
                                          $spielsql->setValue('importid', $booking['importid']);    
                                          $spielsql->setValue('geboren', $testgeboren );      
                                          $spielsql->setValue('geschlecht', $booking['geschlecht'] );             
                                          $spielsql->setValue('stichtagsgeburtsjahr', $testrealjahr );   
                                          $spielsql->insert();  
                                                                                                                                    
                                          $tablehtml .= '<span class="badge">NEU</span> ';       
                                          
                                          $neuespielermailtext .= 'Name: '.$testname."\n";
                                          $neuespielermailtext .= 'Spieler-ID: '.$testpass."\n\n";
                                      }
                                      else
                                      {
                                        $tablehtml .= '<span class="badge error">!</span> ';
                                        $unbekanntepassnr[] =  $testpass;
                                      }
                                  }
                                }
                                // Ende Spieler pruefen
                                $tablehtml .= '</td>';            
                                $tablehtml .= '<td>';    
                                
                                $tablehtml .= '<strong>Kegel: </strong>'.$punktergebnis[0]['punkte'][$aspieler]."&nbsp;:&nbsp;".$punktergebnis[1]['punkte'][$aspieler]."<br />";     
                                $tablehtml .= '<strong>SP: </strong>'.$punktergebnis[0]['satz'][$aspieler]."&nbsp;:&nbsp;".$punktergebnis[1]['satz'][$aspieler]."<br />";       
                                $tablehtml .= '<strong>MP: </strong>'.$punktergebnis[0]['mannschaft'][$aspieler]."&nbsp;:&nbsp;".$punktergebnis[1]['mannschaft'][$aspieler]."<br />";     
                                
                                $tablehtml .= '</td>';      
                                $tablehtml .= '</tr>';
                                }
                              }     
                          }
                        }
                        
                        // Mannschaftspunkte anders berechnen!
                        // nicht mehr $punktergebnis[0]['teammannschaftspunkte']
                        $punktergebnis[0]['teamsatzpunkte'] = $punktergebnis[0]['teammannschaftspunkte'];      
                        $punktergebnis[1]['teamsatzpunkte'] = $punktergebnis[1]['teammannschaftspunkte'];
                        if ( $punktergebnis[0]['teamkegel'] > $punktergebnis[1]['teamkegel'])
                        {
                             $punktergebnis[0]['teamsatzpunkte'] = $punktergebnis[0]['teamsatzpunkte'] + 2;  // Gewinner bekommt +2
                        }   
                        else if ( $punktergebnis[1]['teamkegel'] > $punktergebnis[0]['teamkegel'])
                        {
                             $punktergebnis[1]['teamsatzpunkte'] = $punktergebnis[1]['teamsatzpunkte'] + 2;
                        }
                        else
                        {         
                             $punktergebnis[0]['teamsatzpunkte'] = $punktergebnis[0]['teamsatzpunkte'] + 1;
                             $punktergebnis[1]['teamsatzpunkte'] = $punktergebnis[1]['teamsatzpunkte'] + 1;
                        }
                        
                        // Tabellenpunkte     
                        $punktergebnis[0]['tabellenpunkte'] = 0;
                        $punktergebnis[1]['tabellenpunkte'] = 0;
                        if ( $punktergebnis[0]['teamsatzpunkte'] > $punktergebnis[1]['teamsatzpunkte'])
                        {                        
                            $punktergebnis[0]['tabellenpunkte'] = 2;
                            $punktergebnis[1]['tabellenpunkte'] = 0;
                        }           
                        else if ( $punktergebnis[0]['teamsatzpunkte'] < $punktergebnis[1]['teamsatzpunkte'])
                        {                        
                            $punktergebnis[0]['tabellenpunkte'] = 0;
                            $punktergebnis[1]['tabellenpunkte'] = 2;
                        }        
                        else
                        {                        
                            $punktergebnis[0]['tabellenpunkte'] = 1;
                            $punktergebnis[1]['tabellenpunkte'] = 1;
                        }

                        // Änderung 2022a
                        // altes System zählt nur die Gesamtkegel   
                        $testspnr  = $tmp_spielnr;
                        $testspnrlen = strlen( $testspnr );
                        if ( $testspnrlen == 6 ) {}
                        else if ( $testspnrlen == 5 ) $testspnr = "0".$testspnr;
                        else if ( $testspnrlen == 4 ) $testspnr = "00".$testspnr;
                        else if ( $testspnrlen == 3 ) $testspnr = "000".$testspnr;

                        $tmpfirstnumbers = substr( $testspnr, 0 , strlen($specialspielstring) );
                        if ($tmpfirstnumbers == $specialspielstring) 
                        {
                          $specialansicht = true;
                          //$punktergebnis[0]['teamkegel']."', ";
                          $punktergebnis[0]['teamsatzpunkte'] = 0;
                          $punktergebnis[0]['teammannschaftspunkte'] = 0;
                          $punktergebnis[0]['tabellenpunkte'] = 0;
                          $punktergebnis[0]['pokalsvpunkte'] = 0;
                          $punktergebnis[0]['pokalsatzp'] = 0;

                          //$punktergebnis[1]['teamkegel']."', ";
                          $punktergebnis[1]['teamsatzpunkte'] = 0;
                          $punktergebnis[1]['teammannschaftspunkte'] = 0;
                          $punktergebnis[1]['tabellenpunkte'] = 0;
                          $punktergebnis[1]['pokalsvpunkte'] = 0;
                          $punktergebnis[1]['pokalsatzp'] = 0;
                          
                          if ( $punktergebnis[0]['teamkegel'] > $punktergebnis[1]['teamkegel'])
                          {                        
                              $punktergebnis[0]['tabellenpunkte'] = 2;
                              $punktergebnis[1]['tabellenpunkte'] = 0;
                          }           
                          else if ( $punktergebnis[0]['teamkegel'] < $punktergebnis[1]['teamkegel'])
                          {                        
                              $punktergebnis[0]['tabellenpunkte'] = 0;
                              $punktergebnis[1]['tabellenpunkte'] = 2;
                          }        
                          else
                          {                        
                              $punktergebnis[0]['tabellenpunkte'] = 1;
                              $punktergebnis[1]['tabellenpunkte'] = 1;
                          }
                        }
                        
                        if ($showloginfos) 
                        {
                            $tablehtml .= '<tr><td colspan="4"><br /></td></tr>';    
                            $tablehtml .= '<tr><td colspan="3"><strong style="color: #414A8A;">';
                            $tablehtml .= 'Kegel gesamt: </strong></td><td><strong style="color: #414A8A;">'.$punktergebnis[0]['teamkegel']."&nbsp;:&nbsp;".$punktergebnis[1]['teamkegel']."</strong><br />";   
                            $tablehtml .= '</td></tr>';         
                            $tablehtml .= '<tr><td colspan="3"><strong style="color: #414A8A;">';
                            $tablehtml .= 'MP Spieler: </strong></td><td><strong style="color: #414A8A;">'.$punktergebnis[0]['teammannschaftspunkte']."&nbsp;:&nbsp;".$punktergebnis[1]['teammannschaftspunkte']."</strong><br />";   
                            $tablehtml .= '</td></tr>';                                                                                
                            $tablehtml .= '<tr><td colspan="3"><strong style="color: #414A8A;">';
                            $tablehtml .= 'MP gesamt: </strong></td><td><strong style="color: #414A8A;">'.$punktergebnis[0]['teamsatzpunkte']."&nbsp;:&nbsp;".$punktergebnis[1]['teamsatzpunkte']."</strong><br />";  
                            $tablehtml .= '</td></tr>';         
                            $tablehtml .= '<tr><td colspan="3"><strong style="color: #414A8A;">';
                            $tablehtml .= 'Tabellenpunkte: </strong></td><td><strong style="color: #414A8A;">'.$punktergebnis[0]['tabellenpunkte']."&nbsp;:&nbsp;".$punktergebnis[1]['tabellenpunkte']."</strong><br />"; 
                            $tablehtml .= '</td></tr>';      
                        }
                        
                        //$tablehtml .= '</td></tr>';
                        $tablehtml .= '</table>';
                        
                        //echo '<pre>';
                        //print_r( $tmp_gesamtpunkt );    
                        //print_r( $tmp_satzpunkte ); 
                        //print_r( $punktergebnis ); 
                        //echo '</pre>'; 
                        
                        
                        // Benachrichtigung bei neuen Spielereinträgen
                        if (!in_array($_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' )))
                        {
                          if ($booking['addnewplayer'])
                          {
                            if (!empty($mailrecipientspieler))
                            {
                              if (!empty( $neuespielermailtext ))
                              {    
                                  $mail = new rex_mailer(); 
                                  $mail->CharSet = 'UTF-8';
                                  $mail->isHTML(false);
                                  $mail->From = $realmailsender;
                                  $mail->FromName = $realmailsender;  
                                  $mail->Sender = $realmailsender;    
                                  $mail->AddAddress(  $mailrecipientspieler , $mailrecipientspieler ); 
                                  //$mail->addCC('');  
                                  $mail->Subject = "Neue Spieler durch ".$_SESSION['USR_NAME']." importiert";
                                  $tmpmailtext = "Beim Upload des Spiels ".$tmp_spielnr." durch ".$_SESSION['USR_NAME']." wurden folgende neue Spieler eingetragen: \n\n";   
                                  $tmpmailtext .= "#####################################\n\n";    
                                  $tmpmailtext .= $neuespielermailtext;        
                                  $tmpmailtext .= "#####################################\n\n";          
                                  $tmpmailtext .= "Zeitpunkt: ".date("d.m.Y H.i")." Uhr\n";              
                                  $tmpmailtext .= "Import-ID: ".$booking['importid']."\n\n";
                                  
                                  if (!empty($tmp_spieldatei)) $tmpmailtext .= "Die Excel-Datei kann heruntergeladen werden unter: \n\n". rex::getServer() ."leduploads/".$tmp_spieldatei;
                                  $mail->Body = $tmpmailtext;
                                  if (!empty($tmp_spieldatei)) 
                                  {
                                    $mail->AddAttachment( str_replace( "media", "leduploads", rex_path::media() ). $tmp_spieldatei );
  
                                  }
                                  $mail_status = $mail->Send();    
                                                                         
                                  //echo '<div class="alert alert-danger">Es wurde eine Mail gesendet!</div>'; 
                                  //echo '<pre>'.$tmpmailtext.'</pre>'; 
                              
                              }      
                            }
                          }     
                        }
                        
                        // Jetzt Daten speichern     
                        if ( in_array($booking['step'], array(3))) 
                        {                                                   
                            $tmp_spielnr_official = ($tmp_spielnr);
                            $tmp_spielnr = led_spielnummer($tmp_spielnr);
                            
                            if ($booking['overwrite'])
                            {
                                // alten Import löschen   
                                $existsql = 'DELETE FROM '.rex::getTablePrefix() .'950_spieltabelle WHERE spielnr = "'.$tmp_spielnr.'" AND saisonid = "'. $booking['saison'].'" AND klassenid = "'. $booking['klasse'].'"';
                                $newtutup = rex_sql::factory();
                                $newtutup->setQuery( $existsql );
                                                                                                                                
                                $existsql = 'DELETE FROM '.rex::getTablePrefix() .'950_spielergebnisse WHERE spielnr = "'.$tmp_spielnr.'" AND saisonid = "'. $booking['saison'].'" AND klassenid = "'. $booking['klasse'].'"';
                                $newtutup = rex_sql::factory();
                                $newtutup->setQuery( $existsql );
                                
                                // Optional: Benachrichtigung per EMAIL
                                if (!in_array($_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' )))
                                {
                                    if (!$istdkbc) 
                                    {
                                        $mail = new rex_mailer(); 
                                        $mail->CharSet = 'UTF-8';
                                        $mail->isHTML(false);
                                        $mail->From = $realmailsender;
                                        $mail->FromName = $realmailsender;  
                                        $mail->Sender = $realmailsender;    
                                        $mail->AddAddress(  $mailrecipient , $mailrecipient ); 
                                        //$mail->addCC('');  
                                        $mail->Subject = "Spiel ".$tmp_spielnr." mehrfach durch ".$_SESSION['USR_NAME']." hochgeladen";
                                        $mail->Body = "Spiel ".$tmp_spielnr." mehrfach durch ".$_SESSION['USR_NAME']." hochgeladen. \n\nZeitpunkt: ".date("d.m.Y H.i")." Uhr";
                                        $mail_status = $mail->Send();     
                                    } 
                                }
                            }
                            
                            $existsql = 'SELECT id from '.rex::getTablePrefix() .'950_spieltabelle WHERE spielnr = "'.$tmp_spielnr.'" AND saisonid = "'. $booking['saison'].'" AND klassenid = "'.$booking['klasse'].'"';
                            $newtutup = rex_sql::factory();
                            $newtutup->setQuery( $existsql );
                            $resulttreffer = $newtutup->getRows();     
                            if (empty($resulttreffer))
                            {           
                                $existsql = 'SELECT heimmannschaftid, gastmannschaftid from '.rex::getTablePrefix() .'950_spielplan WHERE spielnr = "'.$tmp_spielnr.'" AND saisonid = "'. $booking['saison'].'" AND klassenid = "'.$booking['klasse'].'"';
                                $datentutup = rex_sql::factory();
                                $datentutup->setQuery( $existsql );
                                $resulttreffer = $datentutup->getRows();    
                                if (!empty($resulttreffer))
                                {             
                                  // Mannschaften auslesen
                                  $heimteamid = $datentutup->getValue("heimmannschaftid", 0); 
                                  $gastteamid = $datentutup->getValue("gastmannschaftid", 0); 
                                  
                                  //$tmpimportnummernid =  date("YmdHis");
                                  $tmpimportnummernid = $booking['importid'];

                                  
                                  // Heimmannschaft eintragen
                                  $existsql = 'INSERT INTO '.rex::getTablePrefix() .'950_spieltabelle (spielnr,saisonid,klassenid,importid,teamid,gegnerid,heimodergast,gesamtkegel,satzpunkte,mannschaftspunkte,tabellenpunkte,pokalsvpunkte,pokalsatzp) VALUES (';
                                  $existsql .= "'".$tmp_spielnr."', ";      
                                  $existsql .= "'".$booking['saison']."', ";    
                                  $existsql .= "'".$booking['klasse']."', ";               
                                  $existsql .= "'".$tmpimportnummernid."', ";
                                  $existsql .= "'".$heimteamid."', ";         
                                  $existsql .= "'".$gastteamid."', ";
                                  $existsql .= "'heim', ";
                                  $existsql .= "'".$punktergebnis[0]['teamkegel']."', ";
                                  $existsql .= "'".$punktergebnis[0]['teamsatzpunkte'] ."', ";
                                  $existsql .= "'".$punktergebnis[0]['teammannschaftspunkte']."', ";     
                                  $existsql .= "'".$punktergebnis[0]['tabellenpunkte']."', ";       
                                  $existsql .= "'".$punktergebnis[0]['pokalsvpunkte']."', ";
                                  $existsql .= "'".$punktergebnis[0]['pokalsatzp']."'";
                                  $existsql .= ")";
                                  $newtutup = rex_sql::factory();
                                  $newtutup->setQuery( $existsql );
                                  
                                  // Gastmannschaft eintragen
                                  $existsql = 'INSERT INTO '.rex::getTablePrefix() .'950_spieltabelle (spielnr,saisonid,klassenid,importid,teamid,gegnerid,heimodergast,gesamtkegel,satzpunkte,mannschaftspunkte,tabellenpunkte,pokalsvpunkte,pokalsatzp) VALUES (';
                                  $existsql .= "'".$tmp_spielnr."', ";   
                                  $existsql .= "'".$booking['saison']."', ";   
                                  $existsql .= "'".$booking['klasse']."', ";                     
                                  $existsql .= "'".$tmpimportnummernid."', ";
                                  $existsql .= "'".$gastteamid."', ";     
                                  $existsql .= "'".$heimteamid."', ";         
                                  $existsql .= "'gast', ";
                                  $existsql .= "'".$punktergebnis[1]['teamkegel']."', ";
                                  $existsql .= "'".$punktergebnis[1]['teamsatzpunkte'] ."', ";
                                  $existsql .= "'".$punktergebnis[1]['teammannschaftspunkte']."', ";     
                                  $existsql .= "'".$punktergebnis[1]['tabellenpunkte']."', ";   
                                  $existsql .= "'".$punktergebnis[1]['pokalsvpunkte']."', ";
                                  $existsql .= "'".$punktergebnis[1]['pokalsatzp']."'";
                                  $existsql .= ")";
                                  $newtutup = rex_sql::factory();
                                  $newtutup->setQuery( $existsql ); 
                                  
                                  // Einzelergebnisse speichern
                                  if (in_array( $kegeldateityp[$entry] , array("A","D", "E")))
                                  {
                                    if (!empty($tmp_spielername[0]))
                                    {
                                        foreach( $tmp_spielername[0] AS $aspieler => $heimspieler)
                                        {
                                        
                                          // Heimspieler
                                          $existsql = 'INSERT INTO '.rex::getTablePrefix() .'950_spielergebnisse (spielnr,saisonid,klassenid,importid,teamid,spielerid,durchgang,kegel,sp,mp,abraum,volle,fehler,heimspiel,inwertung,ersatzspielernummer,ersatzspielername) VALUES (';
                                          $existsql .= "'".$tmp_spielnr."', ";   
                                          $existsql .= "'".$booking['saison']."', ";   
                                          $existsql .= "'".$booking['klasse']."', ";                    
                                          $existsql .= "'".$tmpimportnummernid."', ";
                                          $existsql .= "'".$heimteamid."', ";     
                                          $existsql .= "'".$tmp_spielernummer[0][$aspieler]."', ";   
                                          $existsql .= "'".$aspieler."', ";
                                          $existsql .= "'".$punktergebnis[0]['punkte'][$aspieler]."', ";   
                                          $existsql .= "'".$punktergebnis[0]['satz'][$aspieler]."', ";
                                          $existsql .= "'".$punktergebnis[0]['mannschaft'][$aspieler]."', ";    
                                          $existsql .= "'".$punktergebnis[0]['abraum'][$aspieler]."', ";   
                                          $existsql .= "'".$punktergebnis[0]['volle'][$aspieler]."', ";   
                                          $existsql .= "'".$punktergebnis[0]['fehler'][$aspieler]."', ";   
                                          $existsql .= "'ja', ";                                          
                                          
                                          if (!empty($tmp_spielerersatz[0][$aspieler])) 
                                          {
                                            $existsql .= "'nein' ";   
                                            $existsql .= " , '".$tmp_spielerersatz[0][$aspieler]."'";   
                                            $existsql .= " , '".$tmp_spielerersatzname[0][$aspieler]."'";   
                                          }
                                          else 
                                          {
                                            $existsql .= "'ja' ";      
                                            $existsql .= " , ''";   
                                            $existsql .= " , ''";      
                                          }   
                                          
                                          $existsql .= ")";
                                          $newtutup = rex_sql::factory();
                                          $newtutup->setQuery( $existsql );
                                        
                                          // Gastspieler
                                          $existsql = 'INSERT INTO '.rex::getTablePrefix() .'950_spielergebnisse (spielnr,saisonid,klassenid,importid,teamid,spielerid,durchgang,kegel,sp,mp,abraum,volle,fehler,heimspiel,inwertung,ersatzspielernummer,ersatzspielername) VALUES (';
                                          $existsql .= "'".$tmp_spielnr."', ";   
                                          $existsql .= "'".$booking['saison']."', ";     
                                          $existsql .= "'".$booking['klasse']."', ";                 
                                          $existsql .= "'".$tmpimportnummernid."', ";
                                          $existsql .= "'".$gastteamid."', ";     
                                          $existsql .= "'".$tmp_spielernummer[1][$aspieler]."', ";   
                                          $existsql .= "'".$aspieler."', ";
                                          $existsql .= "'".$punktergebnis[1]['punkte'][$aspieler]."', ";   
                                          $existsql .= "'".$punktergebnis[1]['satz'][$aspieler]."', ";
                                          $existsql .= "'".$punktergebnis[1]['mannschaft'][$aspieler]."', ";    
                                          $existsql .= "'".$punktergebnis[1]['abraum'][$aspieler]."', ";   
                                          $existsql .= "'".$punktergebnis[1]['volle'][$aspieler]."', ";   
                                          $existsql .= "'".$punktergebnis[1]['fehler'][$aspieler]."', ";    
                                          $existsql .= "'nein', ";
                                          
                                          if (!empty($tmp_spielerersatz[1][$aspieler])) 
                                          {
                                            $existsql .= "'nein' ";   
                                            $existsql .= " , '".$tmp_spielerersatz[1][$aspieler]."'";   
                                            $existsql .= " , '".$tmp_spielerersatzname[1][$aspieler]."'";  
                                          }
                                          else 
                                          {
                                            $existsql .= "'ja' ";      
                                            $existsql .= " , ''";   
                                            $existsql .= " , ''";      
                                          }
                                          
                                          $existsql .= ")";
                                          $newtutup = rex_sql::factory();
                                          $newtutup->setQuery( $existsql );
                                        }     
                                    }
                                  }
                                  
                                  echo '<div class="alert alert-success">Das Spiel wurde gespeichert.</div>';
                                  
                                  // Optional: Benachrichtigung per EMAIL
                                  //if ($istdkbc)                                                         
                                  //if (1==1) 
                                  if (!in_array($_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' )))
                                  {
                                      // Empfänger ermitteln  
                                      $existsql = 'SELECT leiteremail, klasse from '.rex::getTablePrefix() .'950_klasse WHERE id = "'.$booking['klasse'].'" ';
                                      $newtutup = rex_sql::factory();
                                      $newtutup->setQuery( $existsql );
                                      $resulttreffer = $newtutup->getRows();   
                                      $spielklassenname = '';
                                      $tmp_spieldatei = '';
                                      
                                      $tmpligamail = '';
                                      if (!empty($resulttreffer))
                                      {
                                         $tmpligamail = trim( $newtutup->getValue("leiteremail", 0) ); 
                                         $spielklassenname = trim( $newtutup->getValue("klasse", 0) ); 
                                      }         
                                      if (empty($tmpligamail)) $tmpligamail = $mailrecipient;
                                      
                                                        
                                      $tmp_spieldatei = '';
                                      $datentutup = rex_sql::factory();
                                      $sql = 'SELECT filename FROM '.rex::getTablePrefix() .'950_uploads WHERE id = "'. $booking['importid'] .'"';
                                      $datentutup->setQuery( $sql );
                                      $datenzahl = $datentutup->getRows();     
                                      if (!empty($datenzahl)) $tmp_spieldatei = $datentutup->getValue("filename", 0);   
                                      
                                      
                                      $mail = new rex_mailer(); 
                                      $mail->CharSet = 'UTF-8';
                                      $mail->isHTML(false);
                                      $mail->From = $realmailsender;
                                      $mail->FromName = $realmailsender;  
                                      $mail->Sender = $realmailsender;    
                                      $mail->AddAddress(  $tmpligamail , $tmpligamail ); 
                                      //$mail->addCC('');  
                                      $mail->Subject = "Spiel ".$tmp_spielnr." wurde durch ".$_SESSION['USR_NAME']." hochgeladen";
                                      $tmpmailtext = "Spiel ".$tmp_spielnr." wurde \n\n";
                                      $tmpmailtext = "für die Liga ".$spielklassenname." durch ".$_SESSION['USR_NAME']." hochgeladen. \n\n";
                                      $tmpmailtext .= "Zeitpunkt: ".date("d.m.Y H.i")." Uhr\n\n";
                                      if (!empty($tmp_spieldatei)) $tmpmailtext .= "Die Excel-Datei kann heruntergeladen werden unter: \n\n". rex::getServer() ."leduploads/".$tmp_spieldatei;
                                      $mail->Body = $tmpmailtext;
                                      if (!empty($tmp_spieldatei)) 
                                      {
                                        $mail->AddAttachment( str_replace( "media", "leduploads", rex_path::media() ). $tmp_spieldatei );

                                      }
                                      $mail_status = $mail->Send();     
                                      
                                      //echo '<pre>'.$tmpmailtext.'</pre>';
                                  }
                                  
                                     
                                }
                                else echo '<div class="alert alert-danger">Die Spielnummer ist nicht im Spielplan!</div>';
                              
                        }
                        else 
                        {
                          // Prüfung, ob schon geprüft
                          $pruefergebnis = 0;
                          $pruefsql = 'SELECT id, geprueft from '.rex::getTablePrefix() .'950_spielplan WHERE spielnr = "'.$tmp_spielnr.'" AND saisonid = "'. $booking['saison'].'" AND klassenid = "'.$booking['klasse'].'"';
                          $prueftutup = rex_sql::factory();
                          $prueftutup->setQuery( $pruefsql );
                          $prueftreffer = $prueftutup->getRows();    
                          //echo $existsql;
                          if (!empty($prueftreffer))
                          {
                            $pruefergebnis = $prueftutup->getValue("geprueft");   
                            echo "ID: ".$prueftutup->getValue("id");  
                          }

                          if ($pruefergebnis == 1)
                          {
                            // wurde schon geprueft, Import abbrechen
                            echo '<div class="alert alert-danger alert-pulsing"><strong style="display: inline-block; font-size: 240%; line-height: 1.2;">Dieses Spiel mit der Spielnummer '.$tmp_spielnr_official.' wurde bereits hochgeladen und vom Staffelleiter abschließend geprüft.<br /><br />Haben Sie eventuell eine falsche Spielnummer eingegeben?</strong><br /><br />Bitte wenden Sie sich an den zuständigen Staffelleiter.</div>';    
                          
                          }
                          else
                          {
                              
                            $importwarnung = true;
                            echo '<div class="alert alert-danger alert-pulsing">Achtung: Es gibt bereits einen erfolgreichen Import!<br /><br /> <strong style="font-size: 240%;">Ist die Spielnummer '.$tmp_spielnr_official.' wirklich richtig?</strong></div>';    
                            ?>  
                            <form class="formgen ledupload clearfix" id="ledupload" action="<?php rex_geturl( rex_article::getCurrentId() ); ?>" method="post" enctype="multipart/form-data">          
                              <?php if ( !rex_addon::get('yrewrite')->isAvailable() ) { ?><input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden"><?php } ?>               
                              <input name="func" value="startupload" type="hidden">    
                              <input name="saison" value="<?php echo $booking['saison']; ?>" type="hidden">
                              <input name="klasse" value="<?php echo $booking['klasse']; ?>" type="hidden">    
                              <input name="step" value="3" type="hidden">    
                              <input name="overwrite" value="1" type="hidden">    
                              <input name="importid" value="<?php echo $booking['importid']; ?>" type="hidden">    
                                  
                              <div class="formblock formsendbtn">         
                                <button type="submit" class="btn formsubmit formgreen">Speichern und alten Import löschen</button>      
                              </div>       
                            </form>
                            <?php  
                          }
                        }
              			 }   
                    }
                }
                
                // Wieder schliessen um Speicherplatz zu sparen 
                $objPHPExcel->disconnectWorksheets();
                unset($objPHPExcel);
                
            }
            else
            {           
                echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Die Excel-Datei konnte nicht gelesen werden!</div>';   
                $error[] = "file";    
            
            }
            
            
            if (empty($error))
            { 
                // Formular für nächsten Schritt anbieten
                if ( in_array($booking['step'], array(2))) 
                {
                    // Bei unbekannten Spieler-ID neu importieren mit Hinweis!
                    if (!empty($unbekanntepassnr))
                    {
                        echo $tablehtml;
                        //echo '<div class="alert alert-danger alert-pulsing"><span class="badge pull-right">!</span> Es sind unbekannte Spieler-ID vorhanden!<br /><br /><strong style="font-size: 150%;">Die rot markierte(n) Spieler-ID ist(sind) fehlerhaft!!!<br />Bitte prüfen Sie die Spieler-ID in der Spielrechtsabfrage und korrigieren danach den Spielbericht.<br /><br /><small>Ist die Spieler-ID richtig, dann markieren Sie das Feld bei "Unbekannte Spieler-ID neu eintragen".</small></strong></div>';     
                        echo '<div class="alert alert-danger alert-pulsing"><span class="badge pull-right">!</span> Es sind unbekannte Spieler-ID vorhanden!<br /><br /><strong style="font-size: 150%;">Die rot markierte(n) Spieler-ID ist(sind) fehlerhaft!!!<br />Bitte prüfen Sie die Spieler-ID in der Spielrechtsabfrage und korrigieren danach den Spielbericht.<br /><br /></strong></div>';     
 
                   } 
                    else if (empty($spielerzaehler)) 
                    {
                        echo '<div class="alert alert-danger"><span class="badge pull-right">!</span> Es wurden keine Spieler gefunden.</div>';    
                    }
                    else if (empty($booking['klasse'])) {}
                    else 
                    {
                        if ($showloginfos) echo $tablehtml;
                        $gotostep3 = true;
                    }
                  if (!$gotostep3)
                  {
                    if (1==2) 
                    { // kein Nachtragen mehr möglich seit 07/2026
                    /*
                    ?>  
                    <form class="formgen ledupload clearfix" id="ledupload" action="<?php rex_geturl( rex_article::getCurrentId() ); ?>" method="post" enctype="multipart/form-data">          
                      <?php if ( !rex_addon::get('yrewrite')->isAvailable() ) { ?><input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden"><?php } ?>   
                      <input name="func" value="startupload" type="hidden">    
                      <input name="saison" value="<?php echo $booking['saison']; ?>" type="hidden"> 
                      <input name="step" value="3" type="hidden">    
                      <input name="importid" value="<?php echo $booking['importid']; ?>" type="hidden">    
                      <?php
                      
                      if (!empty($unbekanntepassnr))
                      {
                      ?>
                      <div class="formblock clearfix">         
                      <p><input type="checkbox" name="addnewplayer" value="1" required />&nbsp;Unbekannte Spieler-ID neu eintragen</p>      
                      </div>      
                      <br /> 
                      <?php
                      }
                      
                      
                      if (!empty($booking['klasse']))
                      {
                          ?>
                          <input name="klasse" value="<?php echo $booking['klasse']; ?>" type="hidden">
                          <?php   
                      }
                      else
                      {
                      ?>
                          <fieldset class="fieldset">
                            <legend>Bitte wählen sie die Spielklasse</legend>
                            <label for="klasse">Spielklasse</label>
                            <select name="klasse" class="form-control" style="width: 100%;" required>
                              <option value="">Bitte wählen...</option>
                              <?php
                                  
                                  // Klassen auslesen
                                  $datentutup = rex_sql::factory();
                                  $sql = 'SELECT id, klasse FROM '.rex::getTablePrefix() .'950_klasse ORDER BY klasse DESC';
                                  $datentutup->setQuery( $sql );
                                  $datenzahl = $datentutup->getRows();     
                                  if (!empty($datenzahl))
                                  {
                                  
                                      for ($spi = 0; $spi<$datenzahl; $spi++) 
                                      { 
                                          if (in_array( $datentutup->getValue("id", $spi) , $tmp_erlaubteklassen)) echo '<option value="'.$datentutup->getValue("id", $spi).'">'.$datentutup->getValue("klasse", $spi).'</option>';     
                                          $datentutup->next( );  
                                      }
                                  } 
                                        
                                        
                              ?>
                            </select>       
                          </fieldset>  
                          <br /><br />
                          <?php
                        }
                        
                        ?>     
                        <div class="formblock formsendbtn">         
                          <button type="submit" class="btn formsubmit formgreen">Unbekannte Spieler-ID neu eintragen und Spiel speichern</button>      
                        </div>       
                      </form>
                      <?php  
                      */
                  }       
                      
                      if (!empty($unbekanntepassnr))
                      {
                        echo '<br /><a class="btn formsubmit" href="'.rex_geturl( rex_article::getCurrentId() ).'" style="display: block; padding: 2rem; width: 100%; background: #e0a800; color: #000; font-size: 1.6rem;"><strong>Abbruch – ändern Sie die Spieler-ID im Spielbericht</strong></a>';
                      }         
                      else echo '<a class="btn btn-link pull-left up3" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a>';
                    }
                
                }
                
                                                                                                                                        
                if (( in_array($booking['step'], array(3))) AND (!$importwarnung))
                {
                    //echo "Saison: ".$booking['saison'].'<br />';
                    //echo "Klasse: ".$booking['klasse'].'<br />';
                    if ((!empty($booking['saison'])) AND (!empty($booking['klasse'])) AND (!empty($tmp_spielnr)))
                    {            
                        $existsql = 'SELECT article_id from '.rex::getTablePrefix() .'article_slice WHERE module_id = '.intval($auswertungsmodulid).' AND value1 = "'. $booking['saison'].'" AND value2 = "'. $booking['klasse'].'"';
                        $newtutup = rex_sql::factory();
                        $newtutup->setQuery( $existsql );
                        $resulttreffer = $newtutup->getRows();     
                        if (!empty($resulttreffer))
                        {             
                          // Mannschaften auslesen
                          $detail_article_id = $newtutup->getValue("article_id", 0);
                          echo '<a class="btn btn-lg btn-warning btn-block" href="'.rex_geturl($detail_article_id, 0, array("spiel" => $tmp_spielnr) ).'" target="_blank" style="width: 100%; text-align: left; white-space: normal; color: #FF0000; background: #FFFF00; "><strong style="display: inline-block; font-size: 3em; line-height: 1.2;">Zur Detailanzeige dieses Spieles Klick<br /><< HIER >></strong><br /><br />Bitte gleichen Sie den Spielbericht mit den nachfolgenden Eintragungen im Ergebnisdienst ab. <br />- Sind die Spielnummer und die Mannschaften richtig? <br />- Entsprechen die Spielernamen dem Eintrag im Spielbericht? <br /><br />Bei Unstimmigkeiten z.B. fehlerhafte Spielerzuordnung oder Ausgabe ohne Namen (o.N.)<br />bitte entsprechende &Auml;nderungen im Excelspielbericht vornehmen und dann noch einmal<br />neu hochladen.</a>';
                          echo '<br /><br /><a class="btn btn-link pull-left up4" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a>';  
                        }
                    } 
                }
            
            }                                                                                                                                                                                                      
            else 
            {
                echo '<a class="btn btn-link pull-left up4" href="'.rex_geturl( rex_article::getCurrentId() ).'">Zurück zum Upload-Formular</a>';
            }
            
            if ($gotostep3) $booking['step'] = 3;
}            


?>