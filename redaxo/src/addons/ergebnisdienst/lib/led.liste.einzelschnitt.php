<?php
    // Ansicht Einzelschnitte Ergebnisdienst 
    // Version 20200526 - OPTIMIERT (N+1 Problem behoben)
    
    $prefix = rex::getTablePrefix();
    $sql = rex_sql::factory();
    //$sql->setDebug(); // EINKOMMENTIEREN ZUR FEHLERSUCHE

    // 1. WHERE-Bedingungen für den Spielplan dynamisch aufbauen
    $whereSpielplan = ["splan.saisonid = :saison"];
    $sqlParams = [':saison' => $booking['saison']];

    if (!empty($booking['klasse'])) {
        $whereSpielplan[] = "splan.klassenid = :klasse";
        $sqlParams[':klasse'] = $booking['klasse'];
    }
    if (!empty($booking['tag'])) {
        $whereSpielplan[] = "splan.spieltagnr <= :tag";
        $sqlParams[':tag'] = $booking['tag'];
    }
    if (!empty($booking['mannschaft'])) {
        $whereSpielplan[] = "(splan.heimmannschaftid = :mannid OR splan.gastmannschaftid = :mannid)";
        $sqlParams[':mannid'] = $booking['mannschaft'];
    }

    $spielplanCondition = implode(" AND ", $whereSpielplan);

    // 2. WHERE-Bedingungen für die Ergebnisse
    $whereErgebnis = "erg.saisonid = :saison AND erg.inwertung != 'nein'";
    if (!empty($booking['mannschaft'])) {
        $whereErgebnis .= " AND erg.teamid = :teamid";
        $sqlParams[':teamid'] = $booking['mannschaft']; // Parameter wiederverwenden
    }

    // 3. DER ZENTRALE QUERY: Wir lassen MySQL rechnen!
    // Nutzt bedingte Aggregation (SUM(IF...)), um Heim und Auswärts in einem Durchlauf zu trennen.
    $mainQuery = "
        SELECT 
            erg.spielerid,
            spieler.spieler AS name,
            spieler.geschlecht,
            spieler.stichtagsgeburtsjahr,
            MAX(erg.teamid) AS teamid,
            mann.mannschaft AS mannschaftsname,
            
            COUNT(erg.id) AS spiele_gesamt,
            SUM(erg.kegel) AS gesamt,
            SUM(erg.abraum) AS gesamtabraum,
            SUM(erg.volle) AS gesamtvolle,
            SUM(erg.fehler) AS gesamtfehler,
            
            SUM(IF(erg.heimspiel = 'ja', 1, 0)) AS spiele_heim,
            SUM(IF(erg.heimspiel = 'ja', erg.kegel, 0)) AS heim,
            SUM(IF(erg.heimspiel = 'ja', erg.abraum, 0)) AS heimabraum,
            SUM(IF(erg.heimspiel = 'ja', erg.volle, 0)) AS heimvolle,
            SUM(IF(erg.heimspiel = 'ja', erg.fehler, 0)) AS heimfehler,
            
            SUM(IF(erg.heimspiel = 'nein', 1, 0)) AS spiele_gast,
            SUM(IF(erg.heimspiel = 'nein', erg.kegel, 0)) AS gast,
            SUM(IF(erg.heimspiel = 'nein', erg.abraum, 0)) AS gastabraum,
            SUM(IF(erg.heimspiel = 'nein', erg.volle, 0)) AS gastvolle,
            SUM(IF(erg.heimspiel = 'nein', erg.fehler, 0)) AS gastfehler

        FROM {$prefix}950_spielergebnisse erg
        JOIN {$prefix}950_spieler spieler ON erg.spielerid = spieler.passnr
        LEFT JOIN {$prefix}950_mannschaft mann ON erg.teamid = mann.id
        WHERE {$whereErgebnis}
          AND erg.spielnr IN (SELECT splan.spielnr FROM {$prefix}950_spielplan splan WHERE {$spielplanCondition})
        GROUP BY erg.spielerid
    ";

    // Abfrage ausführen
    $rohdaten = $sql->getArray($mainQuery, $sqlParams);

    // 4. Daten für die Ausgabe aufbereiten (Durchschnitte berechnen & Sortier-Index setzen)
    $sortierteliste = array();

    if (empty( $booking['mannschaft'] )) {
        if (!in_array( $booking['sort'] , array("gesamt","heim","gast","name") )) $booking['sort'] = "gast";
    }

    foreach ($rohdaten as $row) {
        $spielerid = $row['spielerid'];
        
        // Alter berechnen
        $alter = "-";
        $geburtsjahr = intval($row['stichtagsgeburtsjahr']);
        if (!empty($geburtsjahr) && !empty($booking['saisonjahr'])) {
            $alter = intval($booking['saisonjahr'] - $geburtsjahr - 1);
        }

        // Durchschnitte berechnen (vermeide Division durch 0)
        // Werte werden mit 100 multipliziert, da bei der Ausgabe durch 100 geteilt wird.
        // FEHLERBEHEBUNG: Wir speichern hier reine Zahlen (Floats), keine formatierten Strings!
        $dgesamt = $dagesamt = $dvgesamt = $dfgesamt = 0.0;
        if ($row['spiele_gesamt'] > 0) {
            $dgesamt  = ($row['gesamt']       / $row['spiele_gesamt']) * 100;
            $dagesamt = ($row['gesamtabraum'] / $row['spiele_gesamt']) * 100;
            $dvgesamt = ($row['gesamtvolle']  / $row['spiele_gesamt']) * 100;
            $dfgesamt = ($row['gesamtfehler'] / $row['spiele_gesamt']) * 100;
        }

        $dheim = $daheim = $dvheim = $dfheim = 0.0;
        if ($row['spiele_heim'] > 0) {
            $dheim  = ($row['heim']       / $row['spiele_heim']) * 100;
            $daheim = ($row['heimabraum'] / $row['spiele_heim']) * 100;
            $dvheim = ($row['heimvolle']  / $row['spiele_heim']) * 100;
            $dfheim = ($row['heimfehler'] / $row['spiele_heim']) * 100;
        }

        $dgast = $dagast = $dvgast = $dfgast = 0.0;
        if ($row['spiele_gast'] > 0) {
            $dgast  = ($row['gast']       / $row['spiele_gast']) * 100;
            $dagast = ($row['gastabraum'] / $row['spiele_gast']) * 100;
            $dvgast = ($row['gastvolle']  / $row['spiele_gast']) * 100;
            $dfgast = ($row['gastfehler'] / $row['spiele_gast']) * 100;
        }

        // Kein Sortier-Index mehr im Array-Key: wir sammeln die Zeilen als einfache Liste
        // und sortieren sie unten sauber numerisch bzw. nach Name.
        $sortierteliste[] = array(
            "spielerid"  => $spielerid,
            "name"       => $row['name'],   
            "geschlecht" => $row['geschlecht'],   
            "alter"      => $alter,   
            "mannschaft" => $row['mannschaftsname'], 
            "spiele"     => $row['spiele_gesamt'],        
            "dgesamt"    => $dgesamt,
            "dheim"      => $dheim,
            "dgast"      => $dgast,   
            "hspiele"    => $row['spiele_heim'],      
            "gspiele"    => $row['spiele_gast'],     
            "dagesamt"   => $dagesamt,   
            "dvgesamt"   => $dvgesamt,   
            "dfgesamt"   => $dfgesamt,    
            "daheim"     => $daheim,   
            "dvheim"     => $dvheim,   
            "dfheim"     => $dfheim,   
            "dagast"     => $dagast,   
            "dvgast"     => $dvgast,   
            "dfgast"     => $dfgast,
        );   
    }

    // Sortierung anwenden – auf echten Zahlen, nicht auf zusammengebauten Array-Keys.
    if (in_array( $booking['sort'] , array("gesamt","heim","gast") )) {
        // Welches numerische Feld wird als Sortierkriterium genutzt?
        $sortfeld = "dgast";
        if ($booking['sort'] == "gesamt")     $sortfeld = "dgesamt";
        else if ($booking['sort'] == "heim")  $sortfeld = "dheim";

        // Absteigend nach Durchschnitt, bei Gleichstand stabil nach Spielerid
        usort($sortierteliste, function ($a, $b) use ($sortfeld) {
            if ($a[$sortfeld] == $b[$sortfeld]) {
                return strcmp((string) $a['spielerid'], (string) $b['spielerid']);
            }
            return ($a[$sortfeld] < $b[$sortfeld]) ? 1 : -1;
        });
    } else {
        // Aufsteigend nach Name, bei Gleichstand stabil nach Spielerid
        usort($sortierteliste, function ($a, $b) {
            $cmp = strcasecmp($a['name'], $b['name']);
            if ($cmp === 0) {
                return strcmp((string) $a['spielerid'], (string) $b['spielerid']);
            }
            return $cmp;
        });
    }
    
    $satzzahl = count($sortierteliste);  
    
    // --- AUSGABE BEREICH (CSV oder HTML) ---
    if (!empty($satzzahl))
    {                
      if ($booking['csv'] == 'csv')
      {
            $export_def = array();                                
            $export_def['trennzeichen'] = ";";
            $export_def['neuersatz']    = "\n";
            $export_def['feldzeichen'] = '"';      
            $text = "";
            
            // Kopfzeilen
            $field_names = array("Pl","Name","m/w","Alter","Mannschaft","gesamt:Sp","gesamt:V","gesamt:A","gesamt:F","gesamt:G","zuhause:Sp","zuhause:V","zuhause:A","zuhause:F","zuhause:G","auswaerts:Sp","auswaerts:V","auswaerts:A","auswaerts:F","auswaerts:G");
            for ($li=0; $li<count($field_names); $li++) $text .= $export_def['feldzeichen'].preg_replace("/(\015\012)|(\015)|(\012)/"," ", $field_names[$li]).$export_def['feldzeichen'].$export_def['trennzeichen'];
          	$text .= $export_def['neuersatz'];
            
            $listencounter = 0;
            foreach ($sortierteliste AS $tmpindex=>$tmpdurchschnittszeile) 
            {                               
              $listencounter++;
              $field_values = array(
                  $listencounter,
                  $tmpdurchschnittszeile['name'],
                  $tmpdurchschnittszeile['geschlecht'],
                  $tmpdurchschnittszeile['alter'],
                  $tmpdurchschnittszeile['mannschaft'],  
                  $tmpdurchschnittszeile['spiele'],
                  number_format($tmpdurchschnittszeile['dvgesamt']/100 , 1 , ',', ''),
                  number_format($tmpdurchschnittszeile['dagesamt']/100 , 1 , ',', ''),
                  number_format($tmpdurchschnittszeile['dfgesamt']/100 , 1 , ',', ''),
                  number_format($tmpdurchschnittszeile['dgesamt']/100 , 1 , ',', ''),
                  $tmpdurchschnittszeile['hspiele'],
                  number_format($tmpdurchschnittszeile['dvheim']/100 , 1 , ',', ''),
                  number_format($tmpdurchschnittszeile['daheim']/100 , 1 , ',', ''),
                  number_format($tmpdurchschnittszeile['dfheim']/100 , 1 , ',', ''),
                  number_format($tmpdurchschnittszeile['dheim']/100 , 1 , ',', ''),
                  $tmpdurchschnittszeile['gspiele'],
                  number_format($tmpdurchschnittszeile['dvgast']/100 , 1 , ',', ''),  
                  number_format($tmpdurchschnittszeile['dagast']/100 , 1 , ',', ''),
                  number_format($tmpdurchschnittszeile['dfgast']/100 , 1 , ',', ''),
                  number_format($tmpdurchschnittszeile['dgast']/100 , 1 , ',', '')
              );
              
              for ($li=0; $li<count($field_values); $li++) $text .= $export_def['feldzeichen'].preg_replace("/(\015\012)|(\015)|(\012)/"," ", $field_values[$li]).$export_def['feldzeichen'].$export_def['trennzeichen'];
              $text .= $export_def['neuersatz'];
            }
          
            ob_end_clean();
            header("Content-type: text/csv");
            header ('Content-Disposition: inline; filename="einzelschnitte_' . gmdate("Ymd") . '.csv"');
          	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');                         
            echo utf8_decode($text);	
            exit;
      }
      else
      {
      ?>
        <br />
        <h3 class="pb">Einzel-Durchschnitte <?php 
        // Den Namen der gefilterten Mannschaft ausgeben, falls gesetzt
        if (!empty($booking['mannschaft'])) {
            // Suchen den ersten Datensatz, um den Mannschaftsnamen zu holen
            $erstesElement = reset($sortierteliste);
            echo $erstesElement ? $erstesElement['mannschaft'] : "Mannschaft"; 
        } else {
          echo  ' <a class="btn btn-default pull-right" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $booking['tag'], 'mannschaft' => $booking['mannschaft'], 'sort' => $booking['sort'], 'top' => 'alle' )).'">Alle</a>';
          echo  ' <a class="btn btn-default pull-right" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $booking['tag'], 'mannschaft' => $booking['mannschaft'], 'sort' => $booking['sort'], 'top' => '' )).'">Top10</a>';
          echo  ' <a class="btn btn-default pull-right" href="'.rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $booking['tag'], 'mannschaft' => $booking['mannschaft'], 'sort' => $booking['sort'], 'top' => 'alle', 'export' => 'csv' )).'">CSV-Export</a>';
        }
        ?><br /><br />       
            <small>
           <?php if (!empty($booking['klassename'])) echo $booking['klassename'].", "; ?> 
           <?php echo $booking['saisonname']; ?>
           </small>
          </h3>   
        <table class="table table-striped table-hovered table-condensed" border="0">
          <thead>
          
          <tr>                                                                                                                                                                                    
          <th>Pl</th>     
          <th><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $booking['tag'], 'mannschaft' => $booking['mannschaft'], 'sort' => 'name', 'top' => $booking['top'] )); ?>">Name</a></th>       
          <th>m/w</th>    
          <th>Alter</th>     
          <th><br /></th> 
          <th colspan="5" style="text-align: right;"><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $booking['tag'], 'mannschaft' => $booking['mannschaft'], 'sort' => 'gesamt', 'top' => $booking['top'] )); ?>">&Oslash; gesamt</a></th>
          <th colspan="5" style="text-align: right;"><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $booking['tag'], 'mannschaft' => $booking['mannschaft'], 'sort' => 'heim', 'top' => $booking['top'] )); ?>">&Oslash; zuhause</a></th>
          <th colspan="5" style="text-align: right;"><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => $booking['tag'], 'mannschaft' => $booking['mannschaft'], 'sort' => 'gast', 'top' => $booking['top'] )); ?>">&Oslash; ausw&auml;rts</a></th>
          </tr>   
          <tr>
                      
          <th></th>   
          <th></th>       
          <th></th>         
          <th></th>       
          <th></th>  
           
          <th style="text-align: right;">Sp</th>
          <th style="text-align: right;">V</th>
          <th style="text-align: right;">A</th>
          <th style="text-align: right;">F</th>   
          <th style="text-align: right;">G</th>
          
          <th style="text-align: right;">Sp</th>
          <th style="text-align: right;">V</th>
          <th style="text-align: right;">A</th>
          <th style="text-align: right;">F</th>    
          <th style="text-align: right;">G</th>
          
          <th style="text-align: right;">Sp</th>
          <th style="text-align: right;">V</th>
          <th style="text-align: right;">A</th>
          <th style="text-align: right;">F</th> 
          <th style="text-align: right;">G</th>
          </tr>     
          </thead>
          <tbody>   
          <?php   
          
          // Limitierung
          if (!empty( $booking['mannschaft'] )) $booking['top'] = 'alle';
          else if (empty( $booking['top'] )) $booking['top'] = 10; 
          
          // Ist es eine PDF
          if ($booking['pdf']  == "pdf")  $booking['top'] = 10; 
          
          if ($booking['top'] == 'alle') $listenlimit = 9999;
          else $listenlimit = intval( $booking['top'] );
          if ( $listenlimit < 10 )  $listenlimit = 10; 
          
          $listencounter = 0;
          foreach ($sortierteliste AS $tmpindex=>$tmpdurchschnittszeile) 
          {                               
            $listencounter++;
            if ($listencounter > $listenlimit) { break; } // Besser als leerer if-Block!
            
            // Formatierungen und Default-Werte direkt im SQL gelöst, daher hier sauber
            ?>
            <tr>                                                      
            <td><?php echo $listencounter; ?></td>    
            <td><?php echo $tmpdurchschnittszeile['name']; ?></td>   
            <td><?php echo $tmpdurchschnittszeile['geschlecht']; ?></td>   
            <td><?php echo $tmpdurchschnittszeile['alter']; ?></td>    
            <td><?php echo $tmpdurchschnittszeile['mannschaft']; ?></td>   
            <td style="text-align: right;"><?php echo $tmpdurchschnittszeile['spiele']; ?></td>     
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['dvgesamt']/100 , 1 , ',', ''); ?></td>            
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['dagesamt']/100 , 1 , ',', ''); ?></td>                        
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['dfgesamt']/100 , 1 , ',', ''); ?></td>                                             
            <td style="text-align: right;"><strong><?php echo number_format($tmpdurchschnittszeile['dgesamt']/100 , 1 , ',', ''); ?></strong></td>     
            <td style="text-align: right;"><?php echo $tmpdurchschnittszeile['hspiele']; ?></td>    
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['dvheim']/100 , 1 , ',', ''); ?></td>            
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['daheim']/100 , 1 , ',', ''); ?></td>                        
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['dfheim']/100 , 1 , ',', ''); ?></td>                           
            <td style="text-align: right;"><strong><?php echo number_format($tmpdurchschnittszeile['dheim']/100 , 1 , ',', ''); ?></strong></td>    
            <td style="text-align: right;"><?php echo $tmpdurchschnittszeile['gspiele']; ?></td>         
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['dvgast']/100 , 1 , ',', ''); ?></td>            
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['dagast']/100 , 1 , ',', ''); ?></td>                        
            <td style="text-align: right;"><?php echo number_format($tmpdurchschnittszeile['dfgast']/100 , 1 , ',', ''); ?></td>                     
            <td style="text-align: right;"><strong><?php echo number_format($tmpdurchschnittszeile['dgast']/100 , 1 , ',', '');     ?></strong></td>    
            </tr>        
            <?php 
          }
          ?>
          </tbody>
          </table>
      
          <?php     
      }
    }
    
    // ENDE Einzeldurchschnitte
?>