<?php
    // Ansicht Spielplan Ergebnisdienst, Version POKAL 
    // Version 20210105
    $savespieltagsresult = false;     
    $doupdate = rex_request('test', 'string');    
    if ($doupdate == "doit" ) $savespieltagsresult = true;      
    if ($savespieltagsresult) include ( rex_path::src().'/addons/ergebnisdienst/vendor/classes/PHPExcel/IOFactory.php' );    
    /*    
    $atttutup = rex_sql::factory();
    $sql = 'UPDATE '.rex::getTablePrefix() .'950_spielergebnisse SET abraum = "", volle= "", fehler = "" WHERE 1=1';
    $atttutup->setQuery( $sql );                    
    */
    
    // Mannschaftsnamen auslesen  und ID für "Freilos und Dummy" sichern
    $mannschaftsnamen = array();
    $ignoreteam_dummy_ID = 0;        
    $ignoreteam_freilos_ID = 0;
    $manntutup = rex_sql::factory();
    $sql = 'SELECT id, mannschaft FROM '.rex::getTablePrefix() .'950_mannschaft';
    $manntutup->setQuery( $sql );
    $mannzahl = $manntutup->getRows();   
    for ($spi = 0; $spi<$mannzahl; $spi++) 
    { 
        $mannschaftsnamen[ $manntutup->getValue("id", $spi) ] = $manntutup->getValue("mannschaft", $spi);   
        $tmptestname = strtolower(trim( $manntutup->getValue("mannschaft", $spi) ));
        if ($tmptestname == "dummy") $ignoreteam_dummy_ID = $manntutup->getValue("id", $spi);
        if ($tmptestname == "freilos") $ignoreteam_freilos_ID = $manntutup->getValue("id", $spi);
        $manntutup->next();
    }   
    
    // Spielansetzungen auslesen   
    if (!empty( $booking['tag'] ))
    {
        
        // Spielauswahl einschränken auf den Tag
        $datentutup = rex_sql::factory();
        $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" AND (spieltagnr = "'.$booking['tag'].'" OR echtdatum = "'.$booking['tag'].'") ';    
        $sql .= ' AND (heimmannschaftid <> "'.$ignoreteam_dummy_ID.'" OR gastmannschaftid <> "'.$ignoreteam_dummy_ID.'") ';          
        //$sql .= ' AND uhrzeit > "" AND uhrzeit > "00:00" ';
        $sql .= ' ORDER BY spieltagnr DESC, spielnr ASC, dateformat ASC';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();   
        
        // Tage ermitteln 
        /*
        $tagetutup = rex_sql::factory();
        $sql = 'SELECT DISTINCT(spieltagnr) FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" AND (spieltagnr = "'.$booking['tag'].'" OR echtdatum = "'.$booking['tag'].'") '; 
        //$sql .= ' AND heimmannschaftid <> "'.$ignoreteam_dummy_ID.'" AND gastmannschaftid <> "'.$ignoreteam_dummy_ID.'" AND heimmannschaftid <> "'.$ignoreteam_freilos_ID.'" AND gastmannschaftid <> "'.$ignoreteam_freilos_ID.'" ';
        $sql .= ' ORDER BY spieltagnr DESC, spielnr ASC, dateformat ASC';
        $tagetutup->setQuery( $sql );
        $tagezahl = $tagetutup->getRows();    
        */  
    } 
    else
    {
        // Alle Ansetzungen auslesen
        $datentutup = rex_sql::factory();
        $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" ';
        $sql .= ' AND (heimmannschaftid <> "'.$ignoreteam_dummy_ID.'" OR gastmannschaftid <> "'.$ignoreteam_dummy_ID.'") ';          
        //$sql .= ' AND uhrzeit > "" AND uhrzeit > "00:00" ';
        $sql .= ' ORDER BY spieltagnr DESC, spielnr ASC, dateformat ASC';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();  
    }       
         
    // Tage ermitteln 
    $tagetutup = rex_sql::factory();
    $sql = 'SELECT DISTINCT(spieltagnr)  FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" ';    
    //$sql .= ' AND heimmannschaftid <> "'.$ignoreteam_dummy_ID.'" AND gastmannschaftid <> "'.$ignoreteam_dummy_ID.'" AND heimmannschaftid <> "'.$ignoreteam_freilos_ID.'" AND gastmannschaftid <> "'.$ignoreteam_freilos_ID.'" ';
    $sql .= ' ORDER BY spieltagnr DESC, spielnr ASC, dateformat ASC';
    $tagetutup->setQuery( $sql );
    $tagezahl = $tagetutup->getRows();    
    
    $spieltaglabel = array();      
    
    $spieltagscounter = 0;
    $lastspieltag = '';
    if (!empty($tagezahl))
    {          
       for ($spi = 0; $spi<$tagezahl; $spi++) 
       {        
          // Spieltag ausgeben
          $tmpspiel['spieltagnr'] = trim($tagetutup->getValue("spieltagnr", $spi));   
          if ($tmpspiel['spieltagnr'] != $spieltagscounter)
          {                                                                                                                                                                                                                                  
              $spieltagscounter++;  
              $spieltaglabel[ $spieltagscounter ] = $spieltagscounter;
              
          } 
          $tagetutup->next();
       }
       krsort($spieltaglabel);
       $tmpspieltagzahl = count($spieltaglabel);
       
       foreach( $spieltaglabel AS $tmpkey=>$tmpvalue )
       {
            if ($tmpkey == $tmpspieltagzahl)  $spieltaglabel[ $tmpkey ] = 'Viertelfinale';        
            if ($tmpkey == ($tmpspieltagzahl-1) )  $spieltaglabel[ $tmpkey ] = 'Achtelfinale';   
            if ($tmpkey <  ($tmpspieltagzahl-1) )  $spieltaglabel[ $tmpkey ] =  $tmpvalue.'. Runde';
       }
    }
     
?>
    <div class="col-sm-12">
      <h3>
      <?php
      //if (!empty( $booking['tag'] )) echo $booking['tag'].'. Spieltag, '.led_readabledatumformat($spieltage[ $booking['tag'] ]).'<br /><br />';
      if (!empty( $booking['tag'] )) echo $spieltaglabel[  $booking['tag'] ].'<br /><br />';
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
                 <?php
                 
                 for ($spi = 0; $spi<$datenzahl; $spi++) 
                 {        
                    // Spieltag ausgeben
                    $tmpspiel['spieltagnr'] = trim($datentutup->getValue("spieltagnr", $spi));   
                    if ($tmpspiel['spieltagnr'] != $spieltagscounter)
                    {                                                                                                                                                                                                                                  
                        $spieltagscounter = $tmpspiel['spieltagnr'];  
                        //echo '<li><a class="jumpto" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId()).'#spieltag_'.$spieltagscounter.'">'.$spieltagscounter.'</a></li>'; 
                        echo '<li><a class="jumpto" href="#spieltag_'.$spieltagscounter.'">'.$spieltaglabel[$spieltagscounter] .'</a></li>';
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
    ?>
    <table class="table table-striped table-hovered table-condensed">
    <thead>
      <tr>
        <th>SpNr</th>
        <?php if (1==1)  { ?><th>Datum</th><?php } ?>
        <?php if (1==1)  { ?><th>Uhrzeit</th><?php } ?>      
        <th>Heim</th>
        <th>-</th>
        <th>Gast</th>                                  
        <?php if (1==2)  { ?><th style="text-align: center;">Ergebnis</th><?php } ?>      
        <th style="text-align: center;">MP</th>
        <th style="text-align: center;">SP</th>    
        <th style="text-align: center;">SV</th> 
        <th style="text-align: center;"><br /></th>   
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
          $tmpspiel['importid'] = '';      
          
          // Spieltag ausgeben
          if ($tmpspiel['spieltagnr'] != $spieltagscounter)
          {                                                                                                                                                                                                                                  
              $spieltagscounter = $tmpspiel['spieltagnr'];  
              if (empty( $booking['tag'] )) echo '<tr><td colspan=8><strong><a id="spieltag_'.$spieltagscounter.'" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $spieltagscounter )).'">'.$spieltaglabel[$spieltagscounter].'&nbsp;</a></strong></td></tr>';
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
          $tmpspiel['heim']['sv'] = '';                                                              
          $tmpspiel['heim']['psp'] = '';   
          if (!empty($tmptreffer)) 
          {
              $tmpspiel['heim']['kegel'] = $mptutup->getValue("gesamtkegel", 0);                                                                     
              $tmpspiel['heim']['sp'] = $mptutup->getValue("satzpunkte", 0);                                                                    
              $tmpspiel['heim']['mp'] = (float)$mptutup->getValue("mannschaftspunkte", 0)*1;                                                                     
              $tmpspiel['heim']['tp'] = $mptutup->getValue("tabellenpunkte", 0);                                                                        
              $tmpspiel['heim']['sv'] = (float)$mptutup->getValue("pokalsvpunkte", 0)*1;                                                                          
              $tmpspiel['heim']['psp'] = (float)$mptutup->getValue("pokalsatzp", 0)*1;     
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
          $tmpspiel['gast']['sv'] = '';                                                            
          $tmpspiel['gast']['psp'] = '';
          if (!empty($tmptreffer)) 
          {
              $tmpspiel['gast']['kegel'] = $mptutup->getValue("gesamtkegel", 0);                                                                    
              $tmpspiel['gast']['sp'] = $mptutup->getValue("satzpunkte", 0);                                                                             
              $tmpspiel['gast']['mp'] = (float)$mptutup->getValue("mannschaftspunkte", 0)*1;                                                                                   
              $tmpspiel['gast']['tp'] = $mptutup->getValue("tabellenpunkte", 0);                                                                                   
              $tmpspiel['gast']['sv'] = (float)$mptutup->getValue("pokalsvpunkte", 0)*1;                                                                                  
              $tmpspiel['gast']['psp'] = (float)$mptutup->getValue("pokalsatzp", 0)*1;                            
          }
          
          // Sieger ermitteln
          $sieglevel = 0;
          $heimsieg = false;        
          $gastsieg = false;
          if ( $tmpspiel['heim']['sp'] < $tmpspiel['gast']['sp'])  { $heimsieg = false; $gastsieg = true; $sieglevel = 1;}
          else if ( $tmpspiel['heim']['sp'] > $tmpspiel['gast']['sp'])  { $heimsieg = true; $gastsieg = false; $sieglevel = 1;}
          else if ( $tmpspiel['heim']['psp'] < $tmpspiel['gast']['psp'])  { $heimsieg = false; $gastsieg = true; $sieglevel = 2;}             
          else if ( $tmpspiel['heim']['psp'] > $tmpspiel['gast']['psp']) { $heimsieg = true; $gastsieg = false; $sieglevel = 2;}
          else if ( $tmpspiel['heim']['sv'] < $tmpspiel['gast']['sv'])  { $heimsieg = false; $gastsieg = true; $sieglevel = 3;}               
          else if ( $tmpspiel['heim']['sv'] > $tmpspiel['gast']['sv'])  { $heimsieg = true; $gastsieg = false; $sieglevel = 3;}
          
          ?>
          <tr>                      
          <td><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'spiel' => $tmpspiel['spielnr'] )); ?>"><?php echo $tmpspiel['spielnr']; ?></a></td>
          <?php if (1==1 )  { ?><td><?php if (( !empty($tmpspiel['datum'])) AND (!in_array($tmpspiel['datum'], array("31.12.1969", "29.11.1999", "00.00.0000")))) echo $tmpspiel['datum']; ?></td><?php } ?>   
          <?php if (1==1 )  { ?><td><?php if (( !empty($tmpspiel['uhrzeit'])) AND (!in_array($tmpspiel['uhrzeit'], array("00:00"))))  echo $tmpspiel['uhrzeit']." Uhr"; ?></td><?php } ?>   
          <td><?php 
          
          if ($heimsieg) echo '<strong>'.$mannschaftsnamen[ $tmpspiel['heimid'] ].'</strong>'; 
          else   echo $mannschaftsnamen[ $tmpspiel['heimid'] ]; 
          
          ?></td>     
          <td>-</td>
          <td><?php
          
          if ($gastsieg) echo '<strong>'.$mannschaftsnamen[ $tmpspiel['gastid'] ].'</strong>'; 
          else echo $mannschaftsnamen[ $tmpspiel['gastid'] ]; 
          
          ?></td>    
          <?php if (1==2 )  { ?>  
          <td style="text-align: center;">
            <?php 
            echo $tmpspiel['heim']['kegel']; 
            $tmpergebniszeichen = strlen( $tmpspiel['heim']['kegel'] );
            if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
            echo $tmpspiel['gast']['kegel']; 
            ?>
          </td>
          <?php } ?>   
          <td style="text-align: center;">
            <?php 
            
            if ($sieglevel > 0)
            {
              echo $tmpspiel['heim']['sp']; 
              $tmpergebniszeichen = strlen( $tmpspiel['heim']['sp'] );
              if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
              echo $tmpspiel['gast']['sp'];    
            }       
            else echo '<br />';
            ?>
          </td>
          
          <td style="text-align: center;">
            <?php        
            if ($sieglevel > 1)
            {
              echo $tmpspiel['heim']['psp']; 
              $tmpergebniszeichen = strlen( $tmpspiel['heim']['psp'] );
              if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
              echo $tmpspiel['gast']['psp'];     
            }       
            else echo '<br />';
            //echo '['.$sieglevel.']';
            ?>
          </td>       
          
          <td style="text-align: center;">
            <?php    
            if ($sieglevel > 2)
            {
              echo $tmpspiel['heim']['sv']; 
              $tmpergebniszeichen = strlen( $tmpspiel['heim']['sv'] );
              if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
              echo $tmpspiel['gast']['sv'];    
            }
            else echo '<br />';
            ?>
          </td>
          <td><br /></td>
          </tr>        
          <?php 
          if (!empty( $tmpspiel['anmerkung'] ))
          {
          ?>
          <tr>                      
          <td colspan=2><br /></td>    
          <td colspan=1><i>Anmerkung:</i></td>
          <td colspan=5 >
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

// Tabellenstand ausgeben entfällt    

// Einzelschnitte ausgeben entfällt
   
?>