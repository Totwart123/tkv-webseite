<?php
    // Output Listen Ergebnisdienst 
    // Version 20220616
    // Kennzeichnung Änderung2022a
    
    // optional $zeigebereich     
    //echo '<div class="alert alert-info">'.$zeigeklasse." vs ".$zeigebereich."</div>";    
    
    // Änderung 2022a
    //$specialansicht = false;
    //$specialspielstring = "60";

    $booking = array();                                     
    $booking['tabelle']  = rex_request('tabelle', 'int');          
    $booking['tag']  = rex_request('tag', 'int');   
    $booking['spiel']  = rex_request('spiel', 'string');    
    $booking['mannschaft']  = rex_request('mannschaft', 'int');      
    $booking['einzel']  = rex_request('einzel', 'int');              
    $booking['plan']  = rex_request('plan', 'int');     
    //$booking['saison']  = rex_request('saison', 'int');   
    $booking['klasse']  = rex_request('klasse', 'int');    
    $booking['sort']  = rex_request('sort', 'string');   
    if (!empty($zeigebereich)) $booking['saison']  = $zeigebereich;
    else $booking['saison']  = 1;        
    if (!empty($zeigeklasse))  $booking['klasse']  = $zeigeklasse;
    else $booking['klasse']  = 1;   
    if (empty($booking['tabelle'])) 
    {
      if (empty($booking['spiel']))  
      {
          if (empty($booking['mannschaft']))  
          {
            $booking['plan'] = 1;   
          } 
      } 
    }       
    //echo '<div class="alert alert-info">'.$booking['klasse']." vs ".$booking['saison']."</div>";
    
    $booking['top']  = trim(rex_request('top', 'string'));   
    if ($booking['top'] != "alle") $booking['top'] = intval( $booking['top'] );
    
    
    //$booking['pdf']  = rex_request('pdf', 'string');    
    $booking['pdf']  = '';     
    $booking['csv']  = rex_request('export', 'string');      
    
    //if ($listenversion == "pokal") $booking['tag'] = 0;  
     
    
    // Grundfunktionen
    include( rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");   
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.config.php");   
    //echo '<div class="alert alert-info">'.$booking['klasse']." vs ".$booking['saison']."</div>";   
    
    // Einzelzugriff auf ein Spiel der aktuellen Saison
    $spielid = rex_request('spielid', 'string');    
    if (!empty($spielid ))
    {
        $booking['spiel'] = led_spielnummer( $spielid );
        $booking['plan'] = 0;
         
        // Saison auslesen
        $datentutup = rex_sql::factory();
        $datentutup->setDebug(false);
        $sql = 'SELECT id, saison, aktivesaison FROM rex_950_saison WHERE aktivesaison = "ja" ORDER BY saison DESC';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();     
        if (!empty($datenzahl)) $booking['saison'] = $datentutup->getValue("id"); 
          
        // Klasse auswählen      
        $datentutup = rex_sql::factory();
        $datentutup->setDebug(false);
        $sql = 'SELECT klassenid FROM rex_950_spielplan WHERE spielnr = "'.$booking['spiel'].'" AND saisonid = "'.$booking['saison'].'"';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();        
        if (!empty($datenzahl)) $booking['klasse'] = $datentutup->getValue("klassenid"); 
    }  
    
    // Saison und klasse auslesen
    $booking['saisonname'] = $booking['saisonspieltage'] = '';
    $datentutup = rex_sql::factory();
    $sql = 'SELECT saison, spieltage, saisonjahr FROM '.rex::getTablePrefix() .'950_saison WHERE id = "'.$booking['saison'].'"';
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();     
    if (!empty($datenzahl)) 
    {
        $booking['saisonname'] = $datentutup->getValue("saison", 0);     
        $booking['saisonspieltage'] = trim($datentutup->getValue("spieltage", 0));         
        $booking['saisonjahr'] = trim($datentutup->getValue("saisonjahr", 0)); 
    }  
    
    //echo '<div class="alert alert-info">'.$booking['klasse']." vs ".$booking['saison']."</div>";
    
    $booking['klassename'] = '';
    $datentutup = rex_sql::factory();
    $sql = 'SELECT klasse FROM '.rex::getTablePrefix() .'950_klasse WHERE id = "'.$booking['klasse'].'"';
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();     
    if (!empty($datenzahl)) $booking['klassename'] = $datentutup->getValue("klasse", 0);   
    
    // Spieltage neu eintragen und danach sortieren   
    $datentutup = rex_sql::factory();
    $sql = 'SELECT id, datum, echtdatum FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" ORDER BY spielnr ASC';
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();    
    $spieltage = array();
      
    for ($spi = 0; $spi<$datenzahl; $spi++) 
    {                                                                     
        $tmpspielid = ( $datentutup->getValue("id", $spi) );  
        $tmpspieldatum = trim( $datentutup->getValue("datum", $spi) );    
        $tmpechtspieldatum = trim( $datentutup->getValue("echtdatum", $spi) );  
        
        // Datum korrekt eintragen
        $pieces = explode('.', $tmpspieldatum);
        $formatteddate = '';                     
        if (!empty(@$pieces[2])) 
        {
            $formatteddate = ( $pieces[2].'-'.$pieces[1].'-'.$pieces[0] );    
            $formatteddatetime = strtotime( $formatteddate );   
            if (date('w', $formatteddatetime) == 0)  
            {
              if (!in_array( $formatteddate, $spieltage )) $spieltage[] =  $formatteddate;
            }                              
            $changetutup = rex_sql::factory();
            $sql = 'UPDATE '.rex::getTablePrefix() .'950_spielplan SET dateformat = "'.$formatteddate .'" WHERE id="'.$tmpspielid.'"';
            $changetutup->setQuery( $sql );  
        }           
        
        // tatsächliches Spiel-Datum korrekt eintragen
        /*
        $pieces = explode('.', $tmpechtspieldatum);
        $formatteddate = '';                     
        if (!empty(@$pieces[2])) 
        {
            $formatteddate = ( $pieces[2].'-'.$pieces[1].'-'.$pieces[0] );    
            $formatteddatetime = strtotime( $formatteddate );                            
            $changetutup = rex_sql::factory();
            $sql = 'UPDATE '.rex::getTablePrefix() .'950_spielplan SET echtdateformat = "'.$formatteddate .'" WHERE id="'.$tmpspielid.'"';
            $changetutup->setQuery( $sql );
        }  
        */
        $datentutup->next();        
    }
    /*
    echo count($spieltage).' Spieltage: <pre>';
    //echo print_r( $spieltage );
    sort($spieltage);
    echo implode( ", " , $spieltage );
    echo '</pre>';
    */         
    //echo '<div class="alert alert-info">'.$booking['klasse']." vs ".$booking['saison']."</div>";
    
    // Gibt es offiziell eingetragene Spieltage?
    $spieltage = array();    
    $spieltagscounter = '';
    if (!empty($booking['saisonspieltage']))   
    {
        $tmp_alletage = explode( ",", $booking['saisonspieltage']);
        $tmp_tageszahl = count ( $tmp_alletage );
        if (!empty($tmp_tageszahl))
        {
            
            foreach($tmp_alletage AS $meintag)
            {
                $meintag = trim( $meintag );    
                //$meintag = str_replace( "-", "", $meintag );          
                if (!in_array( $meintag, $spieltage )) 
                {
                  $spieltagscounter++;
                  $spieltage[ $spieltagscounter ] =  $meintag;
                }
            }     
        }
    }
    else
    {
    
        // Alle Ansetzungen auslesen
        $datumtutup = rex_sql::factory();
        $sql = 'SELECT DISTINCT(spieltagnr) FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" ORDER BY spieltagnr ASC, spielnr ASC, dateformat ASC';
        $datumtutup->setQuery( $sql );
        $datumzahl = $datumtutup->getRows();   
        $spieltagscounter =  $datumzahl;
    }  
    
    
    //sort($spieltage);
    /*
    echo count($spieltage).' Spieltage: <pre>';
    echo print_r( $spieltage );
    //echo implode( ", " , $spieltage );
    echo '</pre>';
    */          
    //echo '<div class="alert alert-info">'.$booking['klasse']." vs ".$booking['saison']."</div>";
                                
    echo '<div class="navibuttons clearfix"><div class="btn-group" role="group">';   
    if (!empty($spielid ))
    {
        // kein Menü bei Detailansicht
        echo '<br /><br />';
    }
    else if (!empty( $booking['tag'] )) 
    {
        // Pagination bei Tagesansicht
        //$gesamtspieltage = count($spieltage);      
        $gesamtspieltage = $spieltagscounter;                                                                                                  
        echo '<a class="btn btn-default" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( )).'">Spielplan</a>&nbsp;';
        if ($booking['tag'] > 1) echo '<a class="btn btn-info" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => ($booking['tag'] - 1) )).'">&laquo;</a>&nbsp;';   
        if ($booking['tag'] < $gesamtspieltage) echo '<a class="btn btn-info" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => ($booking['tag'] + 1) )).'">&raquo;</a>&nbsp;';    
    }          
    else if (!empty( $booking['mannschaft'] )) 
    {
        // keine Pagination bei Mannschaftsansicht                                                      
        echo '<a class="btn btn-default" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( )).'">Spielplan</a>&nbsp;';
        if ($listenversion == "pokal") {}
        else
        {
          echo '<a class="btn btn-default" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tabelle' => 1 )).'">Tabellenstand</a>&nbsp;';        
          if (!empty($booking['sort'])) echo '<a class="btn btn-info" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'mannschaft' => $booking['mannschaft'] )).'">Mannschaft</a>&nbsp;';     
        }
    }     
    else
    {     
        // Pagination bei Gesamt-Spielplan                                                                                                                               
        if (!empty( $booking['tabelle'] )) 
        {                                                                                                                                                                                            
          echo '<a class="btn btn-default" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( )).'">Spielplan</a>&nbsp;';
          if ($listenversion == "pokal") {}
          else echo '<a class="btn btn-info" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tabelle' => 1 )).'">Tabellenstand</a>&nbsp;';   
        }
        else 
        {                                                                                                                                                                            
          echo '<a class="btn btn-info" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( )).'">Spielplan</a>&nbsp;';
          if ($listenversion == "pokal") {}
          else echo '<a class="btn btn-default" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tabelle' => 1 )).'">Tabellenstand</a>&nbsp;';    
          //echo '<a class="btn btn-default" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tabelle' => 1 )).'">Zum Spieltag</a>&nbsp;';   
        }         
        
        //NEU: PDF-Datei mit Infos
        if (!empty($abschlussdatei))
        {
            if (empty($abschlussbeschriftung)) $abschlussbeschriftung = "Abschlusstabelle";    
            echo '<a class="btn btn-lg btn-default" style="color: #fff; background: #000; border-color: #000; font-weight: bold;" href="./media/'.$abschlussdatei.'" target="_blank">'.$abschlussbeschriftung.'</a>&nbsp;';   
        }      
        
              //NEU: PDF-Datei mit Infos
        if (!empty($kontaktdatei))
        {
            if (empty($kontaktbeschriftung)) $kontaktbeschriftung = "Kontaktdaten"; 
            $filename = './media/'.$kontaktdatei;  
            $kontaktdatum = '';
            if (file_exists($filename)) 
            {
                $kontaktdatum = date ("d.m.Y", filemtime($filename));
                echo '<a class="btn btn-default" href="'.$filename.'" target="_blank">'.$kontaktbeschriftung;
                echo ' <span class="badge" style="background: #31b0d5;">'.$kontaktdatum.'</span>';
                echo '</a>&nbsp;';  
            }    
        }    
        
        //NEU: PDF-Datei mit Infos
        if (!empty($infodatei))
        {
            if (empty($infobeschriftung)) $infobeschriftung = "Mitteilung";  
            $filename = './media/'.$infodatei;  
            $infodatum = '';
            if (file_exists($filename)) 
            {
                $infodatum = date ("d.m.Y", filemtime($filename));
                echo '<a class="btn btn-default" href="'.$filename.'" target="_blank">'.$infobeschriftung;
                echo ' <span class="badge" style="background: #31b0d5;">'.$infodatum.'</span>';
                echo '</a>&nbsp;';  
            } 
        }  
    }                                                                                               
    if (1==2) echo '&nbsp;<a class="btn btn-default pull-right makeprint" href="#">Druckansicht</a>';     
    // PDF-Export kann im Backend (Ergebnis-Dienst > Einstellungen) deaktiviert
    // werden. Steuerung ueber eine Flag-Datei im Addon-Data-Verzeichnis.
    $pdf_export_enabled = !is_file(rex_path::addonData('ergebnisdienst', 'pdf_export_disabled.flag'));
    if (!empty($spielid ) || !$pdf_export_enabled) {}
    else
    {
        // PDF-Export per POST ausloesen: Suchmaschinen/Crawler folgen keinen
        // POST-Aktionen, dadurch wird getpdf.php nicht mehr als Link abgeklappert.
        $pdf_params = array(
            'article_id' => rex_article::getCurrentId(),
            'tabelle'    => $booking['tabelle'],
            'tag'        => $booking['tag'],
            'spiel'      => $booking['spiel'],
            'mannschaft' => $booking['mannschaft'],
            'plan'       => $booking['plan'],
            'einzel'     => $booking['einzel'],
            'sort'       => $booking['sort'],
            'pdf'        => 'pdf',
        );
        echo '&nbsp;<form method="post" action="getpdf.php" class="pull-right" style="display:inline; margin:0;">';
        foreach ($pdf_params as $pdf_key => $pdf_value)
        {
            echo '<input type="hidden" name="'.rex_escape($pdf_key).'" value="'.rex_escape($pdf_value).'">';
        }
        echo '<button type="submit" class="btn btn-default">PDF</button>';
        echo '</form>';
    }
    echo '</div></div>';
    
                                      
    //echo '<div class="alert alert-info">'.$booking['klasse']." vs ".$booking['saison']."</div>";
    

    // Spielplan nach Spieltagen ausgeben   
    echo '<div id="echtergebnisliste" class="clearfix">';  
    
    if (!empty( $booking['plan'] )) 
    { 
      if ($listenversion == "pokal") include(rex_path::src()."/addons/ergebnisdienst/lib/led.pokal.spielplan.php");   
      else include(rex_path::src()."/addons/ergebnisdienst/lib/led.liste.spielplan.php");   
    }   
    // aktuelle Tabelle ausgeben
    if (!empty( $booking['tabelle'] )) 
    { 
      include(rex_path::src()."/addons/ergebnisdienst/lib/led.liste.tabelle.php");   
    }      
    // Einzelspiel ausgeben
    if (!empty( $booking['spiel'] )) 
    { 
      //echo 'in Arbeit [CODE: v65f004]';
      
      include(rex_path::src()."/addons/ergebnisdienst/lib/led.liste.spiel.php");   

    }  
    // Mannschaftsdaten ausgeben
    if (!empty( $booking['mannschaft'] )) 
    { 
      include(rex_path::src()."/addons/ergebnisdienst/lib/led.liste.mannschaft.php");   
    } 
    
    echo '</div>';
?>

</div>