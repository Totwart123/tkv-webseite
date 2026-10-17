<?php
    // Ansicht Spielplan Ergebnisdienst 
    // Version 20220616
    // Kennzeichnung Änderung2022a
    
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
    if (!empty( $booking['spiel'] ))
    {
        
        // Spielauswahl einschränken auf den Tag
        $datentutup = rex_sql::factory();
        $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" AND (spielnr = "'.$booking['spiel'].'") ORDER BY spieltagnr ASC, spielnr ASC, dateformat ASC LIMIT 0,1';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();      
        
          // Änderung 2022a
          $tmpfirstnumbers = substr( $booking['spiel'], 0 , strlen($specialspielstring) );
          if ($tmpfirstnumbers == $specialspielstring) $specialansicht = true;

        // ist es ein Turnier?
        $esisteinturnier = false;
        if ($datenzahl > 0)
        {
          $testmannschaftsid = $datentutup->getValue("heimmannschaftid", 0);
          if ( $testmannschaftsid == $turniermannschaftsid) $esisteinturnier = true;
        }

     
?>
    <div class="col-sm-12">      
      <h3>
      <?php
      echo 'Details zum Spiel<br />';
      ?> 
      <br />
     <?php echo $booking['klassename']; ?>, 
     <?php echo $booking['saisonname']; ?>
      </h3>
    </div>
    <div class="col-sm-12">
    <table class="table table-striped table-hovered table-condensed">
    <thead>
      <tr>
        <th>SpNr</th>
        <?php if (empty( $booking['tag'] ))  { ?><th>Datum</th><?php } ?>
        <?php if (empty( $booking['tag'] ))  { ?><th>Uhrzeit</th><?php } ?>      
        <th>Heim</th>
        <th>-</th>
        <th>Gast</th>                                  
        <th style="text-align: center;">Ergebnis</th>
        <th style="text-align: center;"><?php if ($specialansicht) {} else echo "MP"; ?></th>
        <th style="text-align: center;"><?php if ($specialansicht) {} else echo "TP"; ?></th>       
        <th style="text-align: center;">Spielbericht</th> 
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
          
          $tmpspiel['spielbericht'] = trim($datentutup->getValue("spielbericht", $spi));   
          $tmpspiel['geprueft'] = trim($datentutup->getValue("geprueft", $spi));   
          
          // Spieltag ausgeben
          if ($tmpspiel['spieltagnr'] != $spieltagscounter)
          {                                                                                                                                                                                                                                  
              $spieltagscounter = $tmpspiel['spieltagnr'];  
              //if (empty( $booking['tag'] )) echo '<tr><td colspan=10><strong><a href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $spieltagscounter )).'">'.$spieltagscounter.'.&nbsp;Spieltag</a></strong></td></tr>';
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
          $tmpspiel['importid'] = '';
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
          <td><?php if (empty( @$spielid )) { ?><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'spiel' => $tmpspiel['spielnr'] )); ?>"><?php echo $tmpspiel['spielnr']; ?></a><?php } else echo $tmpspiel['spielnr']; ?></td>
          <?php if (empty( $booking['tag'] ))  { ?><td><?php if ( $tmpspiel['datum'] != "31.12.1969") echo $tmpspiel['datum']; ?></td><?php } ?>   
          <?php if (empty( $booking['tag'] ))  { ?><td><?php if (!empty($tmpspiel['uhrzeit'])) echo $tmpspiel['uhrzeit']." Uhr"; ?></td><?php } ?>   
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
            // Änderung 2022a
            if ($specialansicht) {} 
            else 
            {
              echo $tmpspiel['heim']['sp']; 
              $tmpergebniszeichen = strlen( $tmpspiel['heim']['sp'] );
              if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
              echo $tmpspiel['gast']['sp']; 

            }
            ?>
          </td>
          <td style="text-align: center;">
            <?php 
            /*
            echo $tmpspiel['heim']['mp']; 
            $tmpergebniszeichen = strlen( $tmpspiel['heim']['mp'] );
            if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
            echo $tmpspiel['gast']['mp']; 
            */
            ?>
         
            <?php 
            //  <td style="text-align: center;">
            // Änderung 2022a
            if ($specialansicht) {} 
            else 
            {
              echo $tmpspiel['heim']['tp']; 
              $tmpergebniszeichen = strlen( $tmpspiel['heim']['tp'] );
              if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
              echo $tmpspiel['gast']['tp']; 
            }
            
            // </td> 
            ?>  
          </td>
                 
          <td style="text-align: center;">
            <?php
            
            // Anzeige des Spielberichtes  
            $atttutup = rex_sql::factory();
            $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_uploads WHERE id = "'.$tmpspiel['importid'] .'" ';
            $atttutup->setQuery( $sql );
            $attzahl = $atttutup->getRows();  
            if (!empty($attzahl))
            {
              $attfilename = $atttutup->getValue("filename", $spi);    
              $attuploadername = $atttutup->getValue("uploadername", $spi);  
              
              if (!empty($attfilename))             
              {
                ?>
                <a href="./leduploads/<?php echo $attfilename; ?>" download title="Spielbericht, hochgeladen von <?php echo $attuploadername; ?>"><span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span></a>
                <?php 
              // Prüfstatus ergänzen
              if ( $tmpspiel['geprueft'] == 1) echo '&nbsp;<abbr title="geprüft"><span class="glyphicon glyphicon-ok-circle" aria-hidden="true" style="color: green;"></span></abbr>';  
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
          if (!empty($tmpspiel['spielbericht']))
          {
          ?>
          <tr>           
          <td colspan=3><br /></td>              
          <td colspan=<?php if (!empty( $booking['tag'] )) echo '6'; else echo '7'; ?> >
            <a href="./media/<?php  echo ($tmpspiel['spielbericht']); ?>" title="Spielbericht anzeigen" class="btn btn-info btn-lg" style="color: #fff; margin: 1rem 0;">Spielbericht anzeigen (PDF)</a>
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
<br />
<?php
  //Hier die Satzwertung   
    
    // Einzelsätze auslesen    
    $heimsatztutup = rex_sql::factory();
    $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielergebnisse WHERE saisonid = "'.$booking['saison'] .'" AND (spielnr = "'.$booking['spiel'].'") AND teamid="'.$tmpspiel['heimid'].'" ORDER BY durchgang ASC';
    $heimsatztutup->setQuery( $sql );
    $satzzahl = $heimsatztutup->getRows();  
    
    //echo $sql;
    
    if (!empty($satzzahl))
    {

     // Änderung 2022a 
?> 
  <table class="table table-striped table-hovered" border="0">
    <thead>
    <tr>
    <th><?php echo $mannschaftsnamen[ $tmpspiel['heimid'] ]; ?></th>
    <th>Kegel</th>
    <th><?php if ($specialansicht) {} else echo "MP"; ?></th>
    <th><?php if ($specialansicht) {} else echo "SaP"; ?></th>     
    <th></th>
    <th><?php if ($specialansicht) {} else echo "SaP"; ?></th>
    <th><?php if ($specialansicht) {} else echo "MP"; ?></th>   
    <th>Kegel</th>
    <th style="text-align: right;"><?php echo $mannschaftsnamen[ $tmpspiel['gastid'] ]; ?></th>
    </tr>      
    </thead>
    <tbody>   
    <?php       
    
    for ($spi = 0; $spi< $satzzahl; $spi++) 
    {      
    
      // Gegenspieler
      $gastsatztutup = rex_sql::factory();
      $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielergebnisse WHERE saisonid = "'.$booking['saison'] .'" AND (spielnr = "'.$booking['spiel'].'") AND teamid="'.$tmpspiel['gastid'].'" AND durchgang="'.$heimsatztutup->getValue("durchgang", $spi).'" ORDER BY durchgang ASC';
      $gastsatztutup->setQuery( $sql );
      $gastsatzzahl = $gastsatztutup->getRows();                           
    
      // Spielername
      $tmpspielername = 'o.N.';
      $spielertutup = rex_sql::factory();
      $sql = 'SELECT id, spieler FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr="'.$heimsatztutup->getValue("spielerid", $spi).'"';
      $spielertutup->setQuery( $sql );
      $mannzahl = $spielertutup->getRows();   
      if (!empty($mannzahl)) $tmpspielername = $spielertutup->getValue("spieler", 0);     
    
      // Gast-Spielername
      $gastspielername = 'o.N.';
      if (!empty($gastsatzzahl))
      {
          $spielertutup = rex_sql::factory();
          $sql = 'SELECT id, spieler FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr="'.$gastsatztutup->getValue("spielerid", 0).'"';
          $spielertutup->setQuery( $sql );
          $mannzahl = $spielertutup->getRows();   
          if (!empty($mannzahl)) $gastspielername = $spielertutup->getValue("spieler", 0); 
      }  
      
      ?>
      <tr>
      <td><?php 
      echo $tmpspielername; 
      // NEU: Auswechslung anzeigen
      $tmp_inwertung = $heimsatztutup->getValue("inwertung", $spi);
      if ( $tmp_inwertung == "nein") 
      {
          /*
          $tmp_ersatzname = $heimsatztutup->getValue("ersatzspielername", $spi);
          if (!empty($tmp_ersatzname)) echo ' / '.$tmp_ersatzname;
          else echo ' [Auswechslung]';
          */
          $tmpersatzspielername = $heimsatztutup->getValue("ersatzspielername", $spi);
          $ersatzspielernummer = $heimsatztutup->getValue("ersatzspielernummer", $spi);
          if (!empty($ersatzspielernummer))
          {
              $ersatzspielertutup = rex_sql::factory();
              $sql = 'SELECT id, spieler FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr="'.$ersatzspielernummer.'"';
              $ersatzspielertutup->setQuery( $sql );
              $ersatzmannzahl = $ersatzspielertutup->getRows();   
              if (!empty($ersatzmannzahl)) $tmpersatzspielername = $ersatzspielertutup->getValue("spieler", 0);  
          }
          if (!empty($tmpersatzspielername)) echo ' / '.$tmpersatzspielername;
          else echo ' [Auswechslung]';
      }
      ?></td>
      <td><?php echo $heimsatztutup->getValue("kegel", $spi); ?></td>    
      <td><?php if ($specialansicht) {} else echo $heimsatztutup->getValue("mp", $spi); ?></td>   
      <td><?php if ($specialansicht) {} else echo $heimsatztutup->getValue("sp", $spi); ?></td>
      <td><?php if ($specialansicht) { echo "-"; } else echo "-"; ?></td>
      <?php
      if (!empty($gastsatzzahl))
      {
      ?>                                                              
      <td><?php if ($specialansicht) {} else echo $gastsatztutup->getValue("sp", 0); ?></td>   
      <td><?php if ($specialansicht) {} else echo $gastsatztutup->getValue("mp", 0); ?></td>   
      <td><?php echo $gastsatztutup->getValue("kegel", 0); ?></td>   
      <td style="text-align: right;"><?php 
      echo $gastspielername;    
      // NEU: Auswechslung anzeigen
      $tmp_inwertung = $gastsatztutup->getValue("inwertung", 0);
      if ( $tmp_inwertung == "nein") 
      {
          /*
          $tmp_ersatzname = $gastsatztutup->getValue("ersatzspielername", $spi);
          if (!empty($tmp_ersatzname)) echo ' / '.$tmp_ersatzname;
          else echo ' [Auswechslung]';
          */
          $tmpersatzspielername = $gastsatztutup->getValue("ersatzspielername", $spi);
          $ersatzspielernummer = $gastsatztutup->getValue("ersatzspielernummer", $spi);
          if (!empty($ersatzspielernummer))
          {
              $ersatzspielertutup = rex_sql::factory();
              $sql = 'SELECT id, spieler FROM '.rex::getTablePrefix() .'950_spieler WHERE passnr="'.$ersatzspielernummer.'"';
              $ersatzspielertutup->setQuery( $sql );
              $ersatzmannzahl = $ersatzspielertutup->getRows();   
              if (!empty($ersatzmannzahl)) $tmpersatzspielername = $ersatzspielertutup->getValue("spieler", 0);  
          }
          if (!empty($tmpersatzspielername)) echo ' / '.$tmpersatzspielername;
          else echo ' [Auswechslung]';
      }  
      
      ?></td>
      <?php
      }
      else echo '<td>-</td><td>-</td><td>-</td><td>-</td>';
      ?>
      </tr>        
    <?php 
    $heimsatztutup->next();
    } 
    ?>
    </tbody>
    </table>

<?php   
    }    
    
    // Jetzt Turnier anzeigen
    if ($esisteinturnier)
    {

      echo '<h2>Ergebnisse des Turniers</h2>'; 

      // Manschaften auflisten
        $teamliste = array();
        if (!is_array( $spielfreieteams )) $spielfreieteams = array();
        $teamtutup = rex_sql::factory();

        //Änderung 2022a
        $sql = 'SELECT spielnr, heimmannschaftid, gastmannschaftid FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'"';
        $teamtutup->setQuery( $sql );
        $teamzahl = $teamtutup->getRows();   
        for ($spi = 0; $spi<$teamzahl; $spi++) 
        { 
            $tmp_id = $teamtutup->getValue("heimmannschaftid", $spi);
            if (!in_array( $tmp_id , $spielfreieteams ))
            {
                if ((!in_array( $tmp_id , $teamliste )) AND (!empty($tmp_id))) $teamliste[] = $tmp_id;  
            }    
            $tmp_id = $teamtutup->getValue("gastmannschaftid", $spi);      
            if (!in_array( $tmp_id , $spielfreieteams ))
            {
                if ((!in_array( $tmp_id , $teamliste )) AND (!empty($tmp_id))) $teamliste[] = $tmp_id;  
            }
            
            // Änderung 2022a
            $tmpfirstnumbers = substr( $teamtutup->getValue("spielnr", $spi), 0 , strlen($specialspielstring) );
            if ($tmpfirstnumbers == $specialspielstring) $specialansicht = true;
            $teamtutup->next( );
        }

      echo '<table class="table table-striped">';
      echo '<thead><tr><th>Mannschaft</th><th>Kegel</th><th>Punkte</th></tr></thead>';
      echo '<tbody>';

      $teamnummern = array();
      $ergebnisse = array();

      $teamtutup = rex_sql::factory();
      $sql = 'SELECT teamid, tp, kegel FROM '.rex::getTablePrefix() .'950_turnierdaten WHERE saisonid = "'. $booking['saison'] .'" AND spielnr="'.$booking['spiel'].'" ORDER BY tp DESC';
      $teamtutup->setQuery( $sql );
      $teamzahl = $teamtutup->getRows();   
      for ($spi = 0; $spi<$teamzahl; $spi++) 
      { 
          $ergebnisse[ $teamtutup->getValue("teamid", $spi) ] = array( "tp" => $teamtutup->getValue("tp", $spi), "kegel" => $teamtutup->getValue("kegel", $spi));
          $teamtutup->next( );
      } 

      $teamcounter = 1;
      foreach( $ergebnisse AS $curteam=>$curresult)
      {
          if ( (!empty($curteam)) AND ( $curteam != $turniermannschaftsid))
          {
              if (!isset( $ergebnisse[ $curteam ] )) $ergebnisse[ $curteam ] = array( "tp" => '', "kegel" => '');
              
              echo '<tr>';
              echo '<td>'.$mannschaftsnamen[ $curteam ].'</td>';
              echo '<td>'.$ergebnisse[ $curteam ]['kegel'].' Kegel</td>';
              echo '<td>'.$ergebnisse[ $curteam ]['tp'].' Punkte</td>';
              echo '</tr>';
              $teamnummern[] = $curteam;
              $teamcounter++;
          }
      }
      echo '</tbody>';
      echo '</table>';
    }
    }
   
?>