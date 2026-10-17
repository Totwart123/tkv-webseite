<?php
    // Ansicht Spielplan Ergebnisdienst 
    // Version 20220616
    // Kennzeichnung Änderung2022a


    $savespieltagsresult = false;     
    $doupdate = rex_request('test', 'string');    
    if ($doupdate == "doit" ) $savespieltagsresult = true;      
    if ($savespieltagsresult) include ( rex_path::src().'/addons/ergebnisdienst/vendor/classes/PHPExcel/IOFactory.php' );    
    /*    
    $atttutup = rex_sql::factory();
    $sql = 'UPDATE '.rex::getTablePrefix() .'950_spielergebnisse SET abraum = "", volle= "", fehler = "" WHERE 1=1';
    $atttutup->setQuery( $sql );                    
    */
    
    // Mannschaftsnamen auslesen
    $mannschaftsnamen = array();
    $manntutup = rex_sql::factory();
    $sql = 'SELECT id, mannschaft FROM '.rex::getTablePrefix() .'950_mannschaft';
    $manntutup->setQuery( $sql );
    $mannzahl = $manntutup->getRows();   
    for ($spi = 0; $spi<$mannzahl; $spi++) 
    { 
        $mannschaftsnamen[ $manntutup->getValue("id", $spi) ] = $manntutup->getValue("mannschaft", $spi);   
        $manntutup->next();
    }   
    
    // Spielansetzungen auslesen   
    if (!empty( $booking['tag'] ))
    {
        
        // Spielauswahl einschränken auf den Tag
        $datentutup = rex_sql::factory();
        $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" AND (spieltagnr = "'.$booking['tag'].'" OR echtdatum = "'.$booking['tag'].'") ORDER BY spieltagnr ASC, spielnr ASC, dateformat ASC';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();     
    } 
    else
    {
        // Alle Ansetzungen auslesen
        $datentutup = rex_sql::factory();
        $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" ORDER BY spieltagnr ASC, spielnr ASC, dateformat ASC';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();       
    }  
     
?>
    <div class="col-sm-12">
      <h3>
      <?php
      //if (!empty( $booking['tag'] )) echo $booking['tag'].'. Spieltag, '.led_readabledatumformat($spieltage[ $booking['tag'] ]).'<br /><br />';
      if (!empty( $booking['tag'] )) echo $booking['tag'].'. Spieltag<br /><br />';
      else echo 'Spielplan';
      ?> 
     <?php echo $booking['klassename']; ?>, 
     <?php echo $booking['saisonname']; ?>
      </h3>
    </div>
    <div class="col-sm-12">
    <?php
    
    if (!empty( $booking['tag'] )) {}
    else 
    {
        
        $spieltagscounter = 0;
        $lastspieltag = '';
        if (!empty($datenzahl))
        {       
          
        ?>
        <div class="container">
           <div class="text-center">
              <ul class="pagination pagination-sm">   
                 <li><a name="jump">Zum Spieltag:</a></li>
                 <?php
                 
                 for ($spi = 0; $spi<$datenzahl; $spi++) 
                 {                           
                    // Spieltag ausgeben
                    $tmpspiel['spieltagnr'] = trim($datentutup->getValue("spieltagnr", $spi));   
                             
                    // Änderung 2022a
                    $tmpspielnummerspecials = trim($datentutup->getValue("spielnr", $spi));    
                    $tmpfirstnumbers = substr( $tmpspielnummerspecials, 0 , strlen($specialspielstring) );
                    if ($tmpfirstnumbers == $specialspielstring) $specialansicht = true;

                    if ($tmpspiel['spieltagnr'] != $spieltagscounter)
                    {                                                                                                                                                                                                                                  
                        $spieltagscounter = $tmpspiel['spieltagnr'];  
                        //echo '<li><a class="jumpto" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId()).'#spieltag_'.$spieltagscounter.'">'.$spieltagscounter.'</a></li>'; 
                        echo '<li><a class="jumpto" href="#spieltag_'.$spieltagscounter.'">'.$spieltagscounter.'</a></li>';
                    } 
                    $datentutup->next();
                 }
                 
                 ?>
              </ul>
           </div>
        </div>
        <?php
        }
    }
    $datentutup->reset();

    // Änderung 2022a
    ?>
    <table class="table table-striped table-hovered table-condensed">
    <thead>
      <tr>
        <th>SpNr</th>
        <?php if (1==1)  { ?><th>Datum</th><?php } ?>
        <?php if (empty( $booking['tag'] ))  { ?><th>Uhrzeit</th><?php } ?>      
        <th>Heim</th>
        <th>-</th>
        <th>Gast</th>                                  
        <th style="text-align: center;">Ergebnis</th>
        <th style="text-align: center;"><?php if ($specialansicht) {} else echo "MP"; ?></th>
        <th style="text-align: center;"><abbr title="Spielbericht"><span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span></abbr></th>   
        <th style="text-align: center;"><abbr title="Live-Ticker"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></abbr></th>       
        <th style="text-align: center;"><abbr title="Live-Stream"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></abbr></th>   
      </tr>
    </thead>
<tbody>                      
    <?php 
    $spieltagscounter = 0;
    $lastspieltag = '';
    if (!empty($datenzahl))
    {       
      for ($spi = 0; $spi<$datenzahl; $spi++) 
      { 
          $tmpspiel = array();                                               
          $tmpspiel['spielid'] = $datentutup->getValue("id", $spi);   
          $tmpspiel['spielnr'] = $datentutup->getValue("spielnr", $spi);   
          $tmpspiel['datum'] = $datentutup->getValue("datum", $spi);    
          $tmpspiel['uhrzeit'] = $datentutup->getValue("uhrzeit", $spi);    
          $tmpspiel['heimid'] = $datentutup->getValue("heimmannschaftid", $spi);    
          $tmpspiel['gastid'] = $datentutup->getValue("gastmannschaftid", $spi);    
          $tmpspiel['dateformat'] = $datentutup->getValue("dateformat", $spi);   
          $tmpspiel['anmerkung'] = trim($datentutup->getValue("anmerkung", $spi));         
          $tmpspiel['spieltagnr'] = trim($datentutup->getValue("spieltagnr", $spi));  
          //$tmpspiel['importid'] = trim($datentutup->getValue("importid", $spi));     
          $tmpspiel['tickerurl'] = trim($datentutup->getValue("tickerurl", $spi));     
          $tmpspiel['streamurl'] = trim($datentutup->getValue("streamurl", $spi));   
          $tmpspiel['importid'] = '';    
          $tmpspiel['geprueft'] = trim($datentutup->getValue("geprueft", $spi));    

          // Änderung 2022a
          $tmpfirstnumbers = substr( $tmpspiel['spielnr'], 0 , strlen($specialspielstring) );
          if ($tmpfirstnumbers == $specialspielstring) $specialansicht = true; 
          
          // Spieltag ausgeben
          if ($tmpspiel['spieltagnr'] != $spieltagscounter)
          {                                                                                                                                                                                                                                  
              $spieltagscounter = $tmpspiel['spieltagnr'];  
              if (empty( $booking['tag'] )) echo '<tr><td colspan=11><strong><a id="spieltag_'.$spieltagscounter.'" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $spieltagscounter )).'">'.$spieltagscounter.'.&nbsp;Spieltag</a></strong></td></tr>';
          }
          
          // Kegel auslesen  HEIM  
          $mptutup = rex_sql::factory();
          $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spieltabelle WHERE spielnr = "'.$tmpspiel['spielnr'] .'" AND saisonid = "'.$booking['saison'].'" AND heimodergast = "heim" AND teamid = "'.$tmpspiel['heimid'].'" ORDER BY spielnr ASC';
          $mptutup->setQuery( $sql );
          $tmptreffer = $mptutup->getRows();                                                                 
          $tmpspiel['heim']['kegel'] = '';                                                                 
          $tmpspiel['heim']['sp'] = '';                                                              
          $tmpspiel['heim']['mp'] = '';                                                              
          $tmpspiel['heim']['tp'] = '';     
          if (!empty($tmptreffer)) 
          {
              $tmpspiel['heim']['kegel'] = $mptutup->getValue("gesamtkegel", 0);                                                                     
              $tmpspiel['heim']['sp'] = $mptutup->getValue("satzpunkte", 0);                                                                    
              $tmpspiel['heim']['mp'] = $mptutup->getValue("mannschaftspunkte", 0);                                                                     
              $tmpspiel['heim']['tp'] = $mptutup->getValue("tabellenpunkte", 0);     
              $tmpspiel['importid'] = $mptutup->getValue("importid", 0);       
          }                        
          // Kegel auslesen  GAST      
          $mptutup = rex_sql::factory();
          $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spieltabelle WHERE spielnr = "'.$tmpspiel['spielnr'] .'" AND saisonid = "'.$booking['saison'].'" AND heimodergast = "gast" AND teamid = "'.$tmpspiel['gastid'].'" ORDER BY spielnr ASC';
          $mptutup->setQuery( $sql );
          $tmptreffer = $mptutup->getRows();   
          $tmpspiel['gast']['kegel'] = '';                                                                     
          $tmpspiel['gast']['sp'] = '';                                                              
          $tmpspiel['gast']['mp'] = '';                                                              
          $tmpspiel['gast']['tp'] = '';
          if (!empty($tmptreffer)) 
          {
              $tmpspiel['gast']['kegel'] = $mptutup->getValue("gesamtkegel", 0);                                                                    
              $tmpspiel['gast']['sp'] = $mptutup->getValue("satzpunkte", 0);                                                                             
              $tmpspiel['gast']['mp'] = $mptutup->getValue("mannschaftspunkte", 0);                                                                                   
              $tmpspiel['gast']['tp'] = $mptutup->getValue("tabellenpunkte", 0);                         
          }
          
          ?>
          <tr>                      
          <td><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'spiel' => $tmpspiel['spielnr'] )); ?>"><?php echo $tmpspiel['spielnr']; ?></a></td>
          <?php if (1==1 )  { ?><td><?php if (( !empty($tmpspiel['datum'])) AND (!in_array($tmpspiel['datum'], array("31.12.1969", "29.11.1999", "00.00.0000")))) echo $tmpspiel['datum']; ?></td><?php } ?>   
          <?php if (empty( $booking['tag'] ))  { ?><td><?php if (( !empty($tmpspiel['uhrzeit'])) AND (!in_array($tmpspiel['uhrzeit'], array("00:00"))))  echo $tmpspiel['uhrzeit']." Uhr"; ?></td><?php } ?>   
          <td><?php echo $mannschaftsnamen[ $tmpspiel['heimid'] ]; ?></td>     
          <td>-</td>
          <td><?php echo $mannschaftsnamen[ $tmpspiel['gastid'] ]; ?></td>      
          <td style="text-align: center;">
            <?php 
            echo $tmpspiel['heim']['kegel']; 
            $tmpergebniszeichen = strlen( $tmpspiel['heim']['kegel'] );
            if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
            echo $tmpspiel['gast']['kegel']; 
            ?>
          </td>
          <td style="text-align: center;">
            <?php 

            if ( $specialansicht) {}
            else
            {
              echo $tmpspiel['heim']['sp']; 
              $tmpergebniszeichen = strlen( $tmpspiel['heim']['sp'] );
              if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
              echo $tmpspiel['gast']['sp']; 

            }
            ?>
          </td>
          
            <?php 
            //  <td style="text-align: center;">
            /*
            echo $tmpspiel['heim']['mp']; 
            $tmpergebniszeichen = strlen( $tmpspiel['heim']['mp'] );
            if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
            echo $tmpspiel['gast']['mp']; 
            */
            //  </td>

            // Änderung 2022a
         //   <td style="text-align: center;">
         /*
            echo $tmpspiel['heim']['tp']; 
            $tmpergebniszeichen = strlen( $tmpspiel['heim']['tp'] );
            if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
            echo $tmpspiel['gast']['tp']; 
            */
          //</td>
          //<td style="text-align: right;"> 
          ?>
          
          <td style="text-align: center;"> 
          <?php
          
          // Hier testweise Anzeige des Spielberichtes  
          $atttutup = rex_sql::factory();
          $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_uploads WHERE id = "'.$tmpspiel['importid'] .'" ';
          $atttutup->setQuery( $sql );
          $attzahl = $atttutup->getRows();  
          if (!empty($attzahl))
          {
            $attfilename = $atttutup->getValue("filename", 0);    
            $attuploadername = $atttutup->getValue("uploadername", 0);  
            
            if (!empty($attfilename))             
            {               
              //if (!empty( $booking['tag'] ))
              if (1==1)
              {
              ?>
              <a class="text-white" href="./leduploads/<?php echo $attfilename; ?>" download title="Spielbericht, hochgeladen von <?php echo $attuploadername; ?>"><span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span></a>
              <?php      
              // Prüfstatus ergänzen
                if ( $tmpspiel['geprueft'] == 1) echo '&nbsp;<abbr title="geprüft"><span class="glyphicon glyphicon-ok-circle" aria-hidden="true" style="color: green;"></span></abbr>';  
                
              }
            }
          }
          ?>
          </td>
          <td style="text-align: center;"> 
          <?php
          
          if (!empty($tmpspiel['tickerurl'])) 
          {
              $tmp_tickerprefix = substr($tmpspiel['tickerurl'], 0, strlen("http") );
              if ($tmp_tickerprefix == "http") echo '<a class="text-white" href="'.$tmpspiel['tickerurl'].'" target="_blank"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>';
              else if (!empty($ticker_urlprefix)) echo '<a class="text-white" href="'.$ticker_urlprefix.$tmpspiel['tickerurl'].'" target="_blank"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>';
          }
          else echo '<br />';
          
          ?> 
          </td>   
          <td style="text-align: center;"> 
          <?php
          
          if (!empty($tmpspiel['streamurl'])) 
          {
              $tmp_streamprefix = substr($tmpspiel['streamurl'], 0, strlen("http") );
              if ($tmp_streamprefix == "http") echo '<a class="text-white" href="'.$tmpspiel['streamurl'].'" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>';
              else if (!empty($ticker_streamurlprefix)) echo '<a class="text-white" href="'.$ticker_streamurlprefix.$tmpspiel['streamurl'].'" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>';
          }
          else echo '<br />';
          
          ?> 
          </td>
          </tr> 
          <?php
          
          // Hier testweise Anzeige des Spielberichtes  
          if ($savespieltagsresult)
          {
          $atttutup = rex_sql::factory();
          $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_uploads WHERE id = "'.$tmpspiel['importid'] .'" ';
          $atttutup->setQuery( $sql );
          $attzahl = $atttutup->getRows();  
          if (!empty($attzahl))
          {
            $attfilename = $atttutup->getValue("filename", 0);    
            $attuploadername = $atttutup->getValue("uploadername", 0);  
            
            if (!empty($attfilename))             
            {               
              if (!empty( $booking['tag'] ))
              {
                // Änderung 2022a fällt weg      
                
                $attdateityp = $atttutup->getValue("csvdateityp", 0);
                //echo $attdateityp;
                
                $inputFileName = $LED_UPLOADFOLDER."/".$attfilename ;
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $sheetList = $objReader->listWorksheetNames($inputFileName);
                                                                            
                    
                // Dateityp speichern
                $entry =  $attfilename;
                $tmp_sheetzahl =  count($sheetList);          
                $tmp_dkbsheet = '-';            
                $tmp_grundsheet = '-';          
                $tmp_datensheet = '-';            
                $tmp_einzelsheet = '-';    
                $tmp_teamsheet = '-';
                $tmp_spielnr = '-';    
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
                        $tmp_spielnr = intval($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );        
                        if (!empty($tmp_spielnr)) 
                        {
                          $tmp_spielnr = ($objPHPExcel->getActiveSheet()->getCell('L8')->getValue() );    
                          $kegeldateityp[$entry] = "E";
                          
                          if ( $tmp_sheetnames[1] == "Dialog")  $kegeldateityp[$entry] = "G";
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
                
                if (empty($kegeldateityp[$entry])) echo '<span style="color: red; font-weight: bold">ERR</span>';    
                else 
                {
                  
                  //echo $kegeldateityp[$entry];    
                  
                  // Hier speichern             
                  /*
                  $atttutup = rex_sql::factory();
                  $sql = 'UPDATE '.rex::getTablePrefix() .'950_uploads SET csvdateityp = "'.$kegeldateityp[$entry].'" WHERE id = "'.$tmpspiel['importid'] .'" ';
                  $atttutup->setQuery( $sql );   
                  */                   
                  //echo '<br />'.$sql;
                  $attdateityp = $kegeldateityp[$entry]; 
                  echo $attdateityp;
                }
                
          // Import Dateityp A
          if ($attdateityp == "A")
          {
                  $objPHPExcel = $objReader->load($inputFileName);
                  $tmp_einzelsheet = 1;   
                  $tmp_teamsheet = 4;
                  $tmp_spielernamesheet = 0;
                                                       
                  $tmp_spielername = array();
                  $tmp_spielerersatz = array();
                  $tmp_gesamtpunkt = array();    
                  $tmp_abraumpunkte = array();   
                  $tmp_vollepunkte = array();   
                  $tmp_fehlerpunkte = array();     
                  $tmp_satzpunkte = array();
                  $punktergebnis = array();
                      
                  for ($aspieler=1;$aspieler < 7; $aspieler++)
                  {         
                      
                      if ( $aspieler == 1) $zellstart = 10;  
                      if ( $aspieler == 2) $zellstart = 17;  
                      if ( $aspieler == 3) $zellstart = 24;   
                      if ( $aspieler == 4) $zellstart = 31;  
                      if ( $aspieler == 5) $zellstart = 38;  
                      if ( $aspieler == 6) $zellstart = 45;  
                      
                      $satzzahlmitleerzeile = 6;
                      
                      $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
                      $tmp_spielername[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('B'.intval( $zellstart + 1) )->getOldCalculatedValue() );                
                      $tmp_spielernummer[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 1) )->getOldCalculatedValue() );     
                       
                      $tmp_spielername[1][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('P'.intval( $zellstart + 1) )->getOldCalculatedValue() );                
                      $tmp_spielernummer[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 1) )->getOldCalculatedValue() );    
                                        
                      $tmp_spielerersatz[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 4) )->getOldCalculatedValue() );     
                      $tmp_spielerersatz[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 4) )->getOldCalculatedValue() );    
                      
                      // ENDE Spieler auslesen
                  
                      
                      if ( $aspieler == 1) $zellstart = 4;  
                      if ( $aspieler == 2) $zellstart = 12;  
                      if ( $aspieler == 3) $zellstart = 20;   
                      if ( $aspieler == 4) $zellstart = 28;  
                      if ( $aspieler == 5) $zellstart = 36;  
                      if ( $aspieler == 6) $zellstart = 44;  
                      
                             
                      for ($asatz=1;$asatz < 5; $asatz++)
                      {  
                            $objPHPExcel->setActiveSheetIndex( $tmp_einzelsheet );
                            $tmp_gesamtpunkt[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('F'.intval( $zellstart + $asatz) )->getValue() );     
                            
                            $objPHPExcel->setActiveSheetIndex( $tmp_einzelsheet );
                            $tmp_gesamtpunkt[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('M'.intval( $zellstart + $asatz) )->getValue() );   
                            
                            // NEU: Einzelschnitte                                                                                                            
                            $tmp_abraumpunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('C'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );  
                            $tmp_abraumpunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('J'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );     
                            $tmp_vollepunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('D'.intval( $zellstart + $asatz) )->getValue() );  
                            $tmp_vollepunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('K'.intval( $zellstart + $asatz) )->getValue() );   
                            $tmp_fehlerpunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('E'.intval( $zellstart + $asatz) )->getValue() );  
                            $tmp_fehlerpunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('L'.intval( $zellstart + $asatz) )->getValue() );        
                                  
                      }
                      
                      // Gesamtauswertung Kegel
                      $punktergebnis[0]['punkte'][$aspieler] = 0;
                      foreach ($tmp_gesamtpunkt[0] AS $testwert)  $punktergebnis[0]['punkte'][$aspieler] = $punktergebnis[0]['punkte'][$aspieler] + $testwert;
                      $punktergebnis[1]['punkte'][$aspieler] = 0;
                      foreach ($tmp_gesamtpunkt[1] AS $testwert)  $punktergebnis[1]['punkte'][$aspieler] = $punktergebnis[1]['punkte'][$aspieler] + $testwert;
                      
                      $punktergebnis[0]['abraum'][$aspieler] = 0;
                      foreach ($tmp_abraumpunkte[0] AS $testwert)  $punktergebnis[0]['abraum'][$aspieler] = $punktergebnis[0]['abraum'][$aspieler] + $testwert;
                      $punktergebnis[1]['abraum'][$aspieler] = 0;
                      foreach ($tmp_abraumpunkte[1] AS $testwert)  $punktergebnis[1]['abraum'][$aspieler] = $punktergebnis[1]['abraum'][$aspieler] + $testwert;     
                      
                      $punktergebnis[0]['volle'][$aspieler] = 0;
                      foreach ($tmp_vollepunkte[0] AS $testwert)  $punktergebnis[0]['volle'][$aspieler] = $punktergebnis[0]['volle'][$aspieler] + $testwert;
                      $punktergebnis[1]['volle'][$aspieler] = 0;
                      foreach ($tmp_vollepunkte[1] AS $testwert)  $punktergebnis[1]['volle'][$aspieler] = $punktergebnis[1]['volle'][$aspieler] + $testwert;
                      
                      $punktergebnis[0]['fehler'][$aspieler] = 0;
                      foreach ($tmp_fehlerpunkte[0] AS $testwert)  $punktergebnis[0]['fehler'][$aspieler] = $punktergebnis[0]['fehler'][$aspieler] + $testwert;
                      $punktergebnis[1]['fehler'][$aspieler] = 0;
                      foreach ($tmp_fehlerpunkte[1] AS $testwert)  $punktergebnis[1]['fehler'][$aspieler] = $punktergebnis[1]['fehler'][$aspieler] + $testwert;
                      
                  }
                }
                // ENDE Datei auslesen TYP A       
                
          // Import Dateityp D
          if ($attdateityp == "D")
          {
                  $objPHPExcel = $objReader->load($inputFileName);
                  $tmp_einzelsheet = 1;   
          $tmp_teamsheet = 4;
                                               
          $tmp_gesamtpunkt = array();     
          $tmp_satzpunkte = array();       
          $tmp_abraumpunkte = array();   
          $tmp_vollepunkte = array();   
          $tmp_fehlerpunkte = array(); 
          $punktergebnis = array();       
          $tmp_spielername = array(); 
          $tmp_spielernummer = array();
              
          for ($aspieler=1;$aspieler < 7; $aspieler++)
          {                                       
              
              if ( $aspieler == 1) $zellstart = 10;  
              if ( $aspieler == 2) $zellstart = 17;  
              if ( $aspieler == 3) $zellstart = 24;   
              if ( $aspieler == 4) $zellstart = 31;  
              if ( $aspieler == 5) $zellstart = 38;  
              if ( $aspieler == 6) $zellstart = 45;  
              
              $satzzahlmitleerzeile = 6; 
              
              $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
              $tmp_spielername[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('B'.intval( $zellstart + 1) )->getCalculatedValue() );                
              $tmp_spielernummer[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 1) )->getCalculatedValue() );     
               
              $tmp_spielername[1][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('P'.intval( $zellstart + 1) )->getCalculatedValue() );                
              $tmp_spielernummer[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 1) )->getCalculatedValue() );      
                                                
                      // NEU: wurde ausgewechselt
                      $tmp_spielerersatz[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 4) )->getOldCalculatedValue() );     
                      $tmp_spielerersatz[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 4) )->getOldCalculatedValue() );    
                      // ENDE: wurde ausgewechselt    
              
                     
              for ($asatz=1;$asatz < $satzzahlmitleerzeile; $asatz++)
              {  
                  // es ist immer eine Leerzeile dabei
                  if ( $asatz != 3)
                  {
                    $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
                    $tmp_gesamtpunkt[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('H'.intval( $zellstart + $asatz) )->getCalculatedValue() );   
                    if (( $asatz > 0) AND (empty($tmp_gesamtpunkt[0][$asatz]))) $tmp_gesamtpunkt[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('I'.intval( $zellstart + $asatz) )->getCalculatedValue() );   
                    
                    $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
                    $tmp_gesamtpunkt[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('V'.intval( $zellstart + $asatz) )->getCalculatedValue() );   
                    
                    
                    // NEU: Einzelschnitte                                                                                                            
                    $tmp_abraumpunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('F'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );  
                    $tmp_abraumpunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('T'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );     
                    $tmp_vollepunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('G'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );  
                    $tmp_vollepunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('U'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );   
                    $tmp_fehlerpunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('E'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );  
                    $tmp_fehlerpunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('S'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );        
                  }
              }
              
              // Gesamtauswertung Kegel
              $punktergebnis[0]['punkte'][$aspieler] = 0;
              foreach ($tmp_gesamtpunkt[0] AS $testwert)  $punktergebnis[0]['punkte'][$aspieler] = $punktergebnis[0]['punkte'][$aspieler] + $testwert;
              $punktergebnis[1]['punkte'][$aspieler] = 0;
              foreach ($tmp_gesamtpunkt[1] AS $testwert)  $punktergebnis[1]['punkte'][$aspieler] = $punktergebnis[1]['punkte'][$aspieler] + $testwert;    
                      
              
              $punktergebnis[0]['abraum'][$aspieler] = 0;
              foreach ($tmp_abraumpunkte[0] AS $testwert)  $punktergebnis[0]['abraum'][$aspieler] = $punktergebnis[0]['abraum'][$aspieler] + $testwert;
              $punktergebnis[1]['abraum'][$aspieler] = 0;
              foreach ($tmp_abraumpunkte[1] AS $testwert)  $punktergebnis[1]['abraum'][$aspieler] = $punktergebnis[1]['abraum'][$aspieler] + $testwert;     
              
              $punktergebnis[0]['volle'][$aspieler] = 0;
              foreach ($tmp_vollepunkte[0] AS $testwert)  $punktergebnis[0]['volle'][$aspieler] = $punktergebnis[0]['volle'][$aspieler] + $testwert;
              $punktergebnis[1]['volle'][$aspieler] = 0;
              foreach ($tmp_vollepunkte[1] AS $testwert)  $punktergebnis[1]['volle'][$aspieler] = $punktergebnis[1]['volle'][$aspieler] + $testwert;
              
              $punktergebnis[0]['fehler'][$aspieler] = 0;
              foreach ($tmp_fehlerpunkte[0] AS $testwert)  $punktergebnis[0]['fehler'][$aspieler] = $punktergebnis[0]['fehler'][$aspieler] + $testwert;
              $punktergebnis[1]['fehler'][$aspieler] = 0;
              foreach ($tmp_fehlerpunkte[1] AS $testwert)  $punktergebnis[1]['fehler'][$aspieler] = $punktergebnis[1]['fehler'][$aspieler] + $testwert;
                    
          }
                }
                // ENDE Datei auslesen TYP A
                
                // Jetzt speichern
                if ( in_array($attdateityp, array( "A","D")))
                {  
                  
                  if (!empty($tmp_spielername[0]))
                  {
                      foreach( $tmp_spielername[0] AS $aspieler => $heimspieler)
                      {
                      
                        // Heimspieler
                        $existsql = 'UPDATE '.rex::getTablePrefix() .'950_spielergebnisse SET abraum = "'.$punktergebnis[0]['abraum'][$aspieler].'", ';   
                        $existsql .= " volle = '".$punktergebnis[0]['volle'][$aspieler]."', ";   
                        $existsql .= " fehler = '".$punktergebnis[0]['fehler'][$aspieler]."', ";   
                        $existsql .= " heimspiel='ja' ";   
                        $existsql .= " WHERE importid = '".$tmpspiel['importid']."' AND spielerid = '".$tmp_spielernummer[0][$aspieler]."'";
                        $newtutup = rex_sql::factory();
                        $newtutup->setQuery( $existsql );
                      
                        // Gastspieler           
                        $existsql = 'UPDATE '.rex::getTablePrefix() .'950_spielergebnisse SET abraum = "'.$punktergebnis[1]['abraum'][$aspieler].'", ';   
                        $existsql .= " volle = '".$punktergebnis[1]['volle'][$aspieler]."', ";   
                        $existsql .= " fehler = '".$punktergebnis[1]['fehler'][$aspieler]."', ";   
                        $existsql .= " heimspiel='nein' ";   
                        $existsql .= ' WHERE importid = "'.$tmpspiel['importid'].'" AND spielerid = "'.$tmp_spielernummer[1][$aspieler].'"';
                        $newtutup = rex_sql::factory();
                        $newtutup->setQuery( $existsql );
                        
                        // Ersatzspieler berücksichtigen             
                        if (!empty($tmp_spielerersatz[0][$aspieler]))
                        {
                          $existsql = 'UPDATE '.rex::getTablePrefix() .'950_spielergebnisse SET inwertung = "nein" ';   
                          $existsql .= " WHERE importid = '".$tmpspiel['importid']."' AND spielerid = '".$tmp_spielernummer[0][$aspieler]."'";
                          $newtutup = rex_sql::factory();
                          $newtutup->setQuery( $existsql );
                        }
                        if (!empty($tmp_spielerersatz[1][$aspieler]))
                        {
                          $existsql = 'UPDATE '.rex::getTablePrefix() .'950_spielergebnisse SET inwertung = "nein" ';   
                          $existsql .= " WHERE importid = '".$tmpspiel['importid']."' AND spielerid = '".$tmp_spielernummer[1][$aspieler]."'";
                          $newtutup = rex_sql::factory();
                          $newtutup->setQuery( $existsql );
                        }
                      }     
                  }
                                                 
                  //echo '</td></tr></tr><td colspan="10">';
                  //echo $existsql; 
                  
                  //echo '<pre>';
                  //print_r($tmp_spielername);
                  //echo "</pre>";    
                  //echo '<pre>';
                  //print_r($punktergebnis);
                  //echo "</pre>";   
                  //echo '<pre>';
                  //print_r($tmp_vollepunkte);
                  //echo "</pre>";
                  
                  // Speichern
                }
                 
              }
            }   
          }
          
          }
          
          ?> 
          </td>
          </tr>        
          <?php 
          if (!empty( $tmpspiel['anmerkung'] ))
          {
          ?>
          <tr>                      
          <td colspan=2><br /></td>    
          <td colspan=1><i>Anmerkung:</i></td>
          <td colspan=<?php if (!empty( $booking['tag'] )) echo '6'; else echo '7'; ?> >
            <?php 
            echo nl2br($tmpspiel['anmerkung']); 
            ?>
          </td>
          </tr>  
          <?php
          }
          $datentutup->next();
      }     
    }
    ?>
</tbody>
</table>
<?php

// Tabellenstand ausgeben
    if (!empty( $booking['tag'] ))
    {
         include('led.liste.tabellenstand.php');       
    }       

// Einzelschnitte ausgeben
    if (!empty( $booking['tag'] ))
    {
         include('led.liste.einzelschnitt.php');       
    }
   
?>