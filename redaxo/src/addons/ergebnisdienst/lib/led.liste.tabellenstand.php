<?php
    // Ansicht Tabellenstand Ergebnisdienst 
    // Version 20220616
    //echo  '<div class="alert alert-info">'.$booking['klasse']." vs ".$booking['saison']."</div>";

    // Kennzeichnung: Änderung 2022a
    
    // Mannschaftsnamen auslesen
    if (!@is_array($mannschaftsnamen))
    {
        $mannschaftsnamen = array();
        $manntutup = rex_sql::factory();
        $sql = 'SELECT id, mannschaft FROM '.rex::getTablePrefix() .'950_mannschaft';
        $manntutup->setQuery( $sql );
        $mannzahl = $manntutup->getRows();   
        for ($spi = 0; $spi<$mannzahl; $spi++) 
        { 
            $mannschaftsnamen[ $manntutup->getValue("id", $spi) ] = $manntutup->getValue("mannschaft", $spi);   
            $manntutup->next( );
        }   
    }     
    
    // Spielfreie Mannschaften auslesen     
    //$spielfreieteams = array();
    if (!@is_array($spielfreieteams))
    {
        $spielfreieteams = array();
        $manntutup = rex_sql::factory();
        $sql = 'SELECT id, mannschaft FROM '.rex::getTablePrefix() .'950_mannschaft';
        $manntutup->setQuery( $sql );
        $mannzahl = $manntutup->getRows();   
        for ($spi = 0; $spi<$mannzahl; $spi++) 
        { 
            $tmp_testname = trim(strtolower( $manntutup->getValue("mannschaft", $spi) ));
            $tmp_testnameid = $manntutup->getValue("id", $spi);
            if ($tmp_testname == "spielfrei") $spielfreieteams[] =  $manntutup->getValue("id", $spi);
            if (empty($tmp_testname)) $spielfreieteams[] =  $manntutup->getValue("id", $spi);
            if (empty($tmp_testnameid)) $spielfreieteams[] =  $manntutup->getValue("id", $spi);
            if ($tmp_testnameid == $turniermannschaftsid) $spielfreieteams[] =  $manntutup->getValue("id", $spi);
            //echo $tmp_testname."<br />";
            $manntutup->next( );
        }   
    }
    //print_r( $spielfreieteams );

    if (empty( $booking['tag'] ))  $booking['tag'] = 999;
    
        // Manschaften auflisten
        $teamliste = array();
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
    
        ?>
        
        <br />
        <?php 
        //if ($booking['tag'] != 999 ) echo '<h3>Tabellenstand, '.$booking['tag'].'. Spieltag'.', '.led_readabledatumformat($spieltage[ $booking['tag'] ]).'</h3>'; 
        if ($booking['tag'] != 999 ) echo '<h3 class="pb">Tabellenstand, '.$booking['tag'].'. Spieltag</h3>'; 
        // Änderung 2022a
        ?>
        <table class="table table-striped table-hovered" border="0">
        <thead>
        <tr>                   
        <th>Pos.</th>
        <th>Mannschaft</th>
        <th>SP</th>
        <th>TP</th>
        <th><?php if ($specialansicht) { echo "Kegel"; } else echo "MP"; ?></th>    
        <?php if (1==2) { ?><th>SP</th><?php } ?>   
        </tr>      
        </thead>
        <tbody>
        <?php 
        
        // Auszuwertende Spiele
        $turnierspielwertung = false;
        $turnierspielliste = array();
        $spieleliste = array();
        $spieletutup = rex_sql::factory();
        $sql = 'SELECT spielnr, heimmannschaftid, gastmannschaftid FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" AND  spieltagnr <= '.$booking['tag'].'';
        $spieletutup->setQuery( $sql );
        $spielezahl = $spieletutup->getRows();   
        for ($spi = 0; $spi<$spielezahl; $spi++) 
        { 
            $tmp_spielid = $spieletutup->getValue("spielnr", $spi);     
            $tmp_teamid = $spieletutup->getValue("heimmannschaftid", $spi);
            if (!in_array( $tmp_teamid , $spielfreieteams ))
            {
                $tmp_teamid = $spieletutup->getValue("gastmannschaftid", $spi);   
                if (!in_array( $tmp_teamid , $spielfreieteams ))
                {       
                    if (!in_array( $tmp_spielid , $spieleliste )) $spieleliste[] = $tmp_spielid;  
                }  
            }
            if ($tmp_teamid == $turniermannschaftsid)
            {
                $turnierspielwertung = true;
                $turnierspielliste[] = $spieletutup->getValue("spielnr", $spi);     
            }
            $spieletutup->next( );
        }
        $lesbarspieleliste = '"'.implode ( '","', $spieleliste ).'"';
                
        // Jetzt mit Turnierwertungen
        $turnierergebnisse = array();
        if ($turnierspielwertung)
        {
            // jetzt jedes Spiel auslesen
            foreach($turnierspielliste AS $tmpturnierspielnr)
            {
                
    
                $teamtutup = rex_sql::factory();
                $sql = 'SELECT teamid, tp, kegel FROM '.rex::getTablePrefix() .'950_turnierdaten WHERE saisonid = "'. $booking['saison'] .'" AND spielnr="'.$tmpturnierspielnr.'"';
                $teamtutup->setQuery( $sql );
                $teamzahl = $teamtutup->getRows();   
                for ($spi = 0; $spi<$teamzahl; $spi++) 
                { 
                    $turnierergebnisse[ $tmpturnierspielnr ][ $teamtutup->getValue("teamid", $spi) ] = array( "tp" => $teamtutup->getValue("tp", $spi), "kegel" => $teamtutup->getValue("kegel", $spi));
                    $teamtutup->next( );
                }

            }
            //echo '<pre>';
            //print_r( $turnierergebnisse );
            //echo '</pre>';
        }
        
                
        // alten Stand loeschen     
        $mptutup = rex_sql::factory();
        $sql = 'DELETE FROM '.rex::getTablePrefix() .'950_tabellenstand WHERE saisonid = "'.$booking['saison'] .'" AND klassenid = "'.$booking['klasse'] .'" AND spieltagnr = "'.$booking['tag'].'"';
        $mptutup->setQuery( $sql );
        
        // Wertung Teams
        $teamzahl = count($teamliste);
        $tmp_position = 1;
        foreach ( $teamliste AS $einzelteam) 
        {    
                // Ergebnisse auslesen
                $tmpspiel = array();    
                $tmpknownspielnr = array(); 
                $tmpspielcounter = 0;                                                              
                $tmpspiel['kegel'] = intval(0);                                                                   
                $tmpspiel['sp'] = led_tofloat(0);                                                                         
                $tmpspiel['tp'] = led_tofloat(0);                                                                             
                $tmpspiel['mp'] = led_tofloat(0);                       
                $mptutup = rex_sql::factory();
                $sql = 'SELECT  * FROM '.rex::getTablePrefix() .'950_spieltabelle WHERE teamid = "'.$einzelteam.'" AND spielnr IN ('.$lesbarspieleliste.')  AND saisonid = "'.$booking['saison'] .'" ORDER BY id DESC';
                $mptutup->setQuery( $sql );
                $tmpspiel['spielanzahl'] = $mptutup->getRows();    
                if (!empty( $tmpspiel['spielanzahl']  )) 
                {                                                                 
                    for ($spi = 0; $spi<$tmpspiel['spielanzahl']; $spi++) 
                    {                                                                                  
                        $tmpthisspielnr = $mptutup->getValue("spielnr", $spi);
                        if (!in_array( $tmpthisspielnr , $tmpknownspielnr))
                        {
                            $tmpknownspielnr[] = $tmpthisspielnr;
                            $tmpspiel['mp'] = led_tofloat($tmpspiel['sp']) + led_tofloat( $mptutup->getValue("mannschaftspunkte", $spi));    
                            $tmpspiel['sp'] = led_tofloat($tmpspiel['sp']) + led_tofloat( $mptutup->getValue("satzpunkte", $spi));                                                                          
                            $tmpspiel['tp'] = led_tofloat($tmpspiel['tp']) + led_tofloat( $mptutup->getValue("tabellenpunkte", $spi));                                                                  
                            $tmpspiel['kegel'] = intval($tmpspiel['kegel']) + intval( $mptutup->getValue("gesamtkegel", $spi)); 
                            $tmpspielcounter++;   
                        }
                        $mptutup->next( );
                    }    
                }     
                $tmpspiel['spielanzahl'] = $tmpspielcounter;    
                $tmp_gegnerspiel = array();                                                             
                $tmp_gegnerspiel['kegel'] = intval(0);                                                                             
                $tmp_gegnerspiel['sp'] = led_tofloat(0);                                                                          
                $tmp_gegnerspiel['tp'] = led_tofloat(0);                                                                               
                $tmp_gegnerspiel['mp'] = led_tofloat(0);                             
                $mptutup = rex_sql::factory();
                $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spieltabelle WHERE gegnerid = "'.$einzelteam.'" AND spielnr IN ('.$lesbarspieleliste.')  AND saisonid = "'.$booking['saison'] .'" ORDER BY id DESC ';
                
                $mptutup->setQuery( $sql );
                $tmp_gegnerspiel['spielanzahl'] = $mptutup->getRows();    
                if (!empty( $tmp_gegnerspiel['spielanzahl']  )) 
                {                                                                 
                    for ($spi = 0; $spi<$tmp_gegnerspiel['spielanzahl']; $spi++) 
                    {                                                                                  
                        $tmp_gegnerspiel['mp'] = led_tofloat($tmp_gegnerspiel['sp']) + led_tofloat( $mptutup->getValue("mannschaftspunkte", $spi)); 
                        $tmp_gegnerspiel['sp'] = led_tofloat($tmp_gegnerspiel['sp']) + led_tofloat( $mptutup->getValue("satzpunkte", $spi));                                                                        
                        $tmp_gegnerspiel['tp'] = led_tofloat($tmp_gegnerspiel['tp']) + led_tofloat( $mptutup->getValue("tabellenpunkte", $spi));                                                                 
                        $tmp_gegnerspiel['kegel'] = intval($tmp_gegnerspiel['kegel']) + intval( $mptutup->getValue("gesamtkegel", $spi)); 
                        $mptutup->next( );
                    }    
                } 
                
                // Jetzt mit Turnierwertungen
                if ($turnierspielwertung)
                {
                    //echo '<br />mit Turnierwertung<br />';
                    //print_r( $turnierspielliste );
                    //$tmp_gegnerspiel['tabellenpunkte'] =  $tmp_gegnerspiel['tabellenpunkte'] + $turnierergebnisse[ $einzelteam ]['tp'];
                    foreach($turnierspielliste AS $tmpturnierspielnr)
                    {
                        $tmpspiel['tp'] =  $tmpspiel['tp'] + $turnierergebnisse[ $tmpturnierspielnr ][ $einzelteam ]['tp'];
                        $tmpspiel['kegel'] =  $tmpspiel['kegel'] + $turnierergebnisse[ $tmpturnierspielnr ][ $einzelteam ]['kegel'];
                    }
                }
                
                // neue Werte eintragen
                $spielsql = rex_sql::factory();
                $spielsql->setTable(''.rex::getTablePrefix() .'950_tabellenstand');                     
                $spielsql->setValue('saisonid', $booking['saison'] );
                $spielsql->setValue('klassenid', $booking['klasse'] );  
                $spielsql->setValue('spieltagnr', $booking['tag'] );                                            
                $spielsql->setValue('teamid', $einzelteam );                                                    
                $spielsql->setValue('spielanzahl', $tmpspiel['spielanzahl'] );                                      
                $spielsql->setValue('satzpunkte', str_replace(',' , '.',  $tmpspiel['sp'])*10 );                                       
                $spielsql->setValue('mannschaftspunkte', str_replace(',' , '.',  $tmpspiel['mp'] )*10 );                                       
                $spielsql->setValue('tabellenpunkte', str_replace(',' , '.',  $tmpspiel['tp'] )*10 );                                       
                $spielsql->setValue('gegnersatzpunkte', str_replace(',' , '.',  $tmp_gegnerspiel['sp'] )*10 );                                       
                $spielsql->setValue('gegnermannschaftspunkte', str_replace(',' , '.',  $tmp_gegnerspiel['mp'] )*10 );                              
                $spielsql->setValue('gegnertabellenpunkte', str_replace(',' , '.',  $tmp_gegnerspiel['tp'] )*10 );                               
                $spielsql->setValue('kegel', $tmpspiel['kegel'] );     
                $spielsql->insert();  
                /*        
                $mptutup = rex_sql::factory();
                $sql = 'INSERT INTO '.rex::getTablePrefix() .'950_tabellenstand (saisonid, klassenid, spieltagnr, teamid, spielanzahl, satzpunkte, mannschaftspunkte, tabellenpunkte, gegnersatzpunkte, gegnermannschaftspunkte, gegnertabellenpunkte ) ';
                $sql .= ' VALUES ("'.$booking['saison'].'","'.$booking['klasse'].'","'.$booking['tag'].'","'.$einzelteam.'","'.$tmpspiel['spielanzahl'].'","'.str_replace(',' , '.',  $tmpspiel['sp']).'","'.str_replace(',' , '.',  $tmpspiel['mp']).'","'.$tmpspiel['tp'].'","'.$tmp_gegnerspiel['sp'].'","'.$tmp_gegnerspiel['mp'].'","'.$tmp_gegnerspiel['tp'].'")';
                $mptutup->setQuery( $sql );
                echo $sql;            
                */                     
                
                
                if (1==2)
                {                                                               
                    ?>
                    <tr>             
                    <td><?php echo $tmp_position; ?></td>
                    <td><?php echo $mannschaftsnamen[ $einzelteam ]; ?></td>
                    <td><?php 
                    $tmpspielzahl = $tmpspiel['spielanzahl'];
                    echo $tmpspielzahl ; 
                    if ($tmpspielzahl == 1) echo ' Spiel'; 
                    else echo ' Spiele'; 
                    
                    ?></td>
                    <td><?php echo $tmpspiel['tp']; ?> : <?php echo $tmp_gegnerspiel['tp'] ; ?></td>
                    <?php if (1==2) { ?><td><?php echo $tmpspiel['mp']; ?> : <?php echo $tmp_gegnerspiel['mp']; ?></td><?php } ?>      
                    <td><?php echo $tmpspiel['sp']; ?> : <?php echo $tmp_gegnerspiel['sp']; ?></td>    
                    </tr>    
                    <?php 
                    $tmp_position++;
                }
            }
            /*
            //Tabellenstände werden ermittelt//
            OK 1.  Anzahl der positiven Tabellenpunkte in absteigender Reihenfolge //
            OK 2.  Anzahl der negativen Tabellenpunkte in aufsteigender Reihenfolge //
            OK 3.  Anzahl der positiven Mannschaftspunkte in absteigender Reihenfolge//
            */
            
            // Jetzt sortiert aus SQL auslesen    
            $tabtutup = rex_sql::factory();
            //$sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_tabellenstand WHERE saisonid = "'.$booking['saison'] .'" AND klassenid = "'.$booking['klasse'] .'" AND spieltagnr = "'.$booking['tag'].'" ORDER BY tabellenpunkte DESC, gegnertabellenpunkte ASC, mannschaftspunkte DESC, satzpunkte ASC, gegnersatzpunkte DESC ';     
            $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_tabellenstand WHERE saisonid = "'.$booking['saison'] .'" AND klassenid = "'.$booking['klasse'] .'" AND spieltagnr = "'.$booking['tag'].'" ORDER BY tabellenpunkte DESC, gegnertabellenpunkte ASC, satzpunkte DESC, gegnersatzpunkte ASC ';     
            //echo $sql;
            $tabtutup->setQuery( $sql );
            $tabtreffer = $tabtutup->getRows();    
            if (!empty( $tabtreffer )) 
            {                                                                 
                $zwischentabellen_array = $endtabellen_array = array();
                $strittige_wertungen = $zuvergleichendespiele = $echtzuvergleichendespiele = $mannschaftsvergleich = $lastspiel =  array();
                for ($spi = 0; $spi<$tabtreffer; $spi++) 
                {                                                                                  
                    
                    $zwischentabellen_array[$spi] = array( 
                                                "teamid" => $tabtutup->getValue("teamid", $spi),  
                                                "spielanzahl" => $tabtutup->getValue("spielanzahl", $spi),     
                                                "tabellenpunkte" => $tabtutup->getValue("tabellenpunkte", $spi),      
                                                "gegnertabellenpunkte" => $tabtutup->getValue("gegnertabellenpunkte", $spi),
                                                "mannschaftspunkte" => $tabtutup->getValue("mannschaftspunkte", $spi),
                                                "gegnermannschaftspunkte" => $tabtutup->getValue("gegnermannschaftspunkte", $spi),       
                                                "satzpunkte" => $tabtutup->getValue("satzpunkte", $spi),
                                                "gegnersatzpunkte" => $tabtutup->getValue("gegnersatzpunkte", $spi) ,
                                                "kegel" => $tabtutup->getValue("kegel", $spi)
                                              ); 
                                              
                    // Gibt es Mannschaften mit gleichen Werten
                    $tmp_wertungs_id =  $tabtutup->getValue("tabellenpunkte", $spi)."_".$tabtutup->getValue("gegnertabellenpunkte", $spi)."_".$tabtutup->getValue("satzpunkte", $spi)."_".$tabtutup->getValue("gegnersatzpunkte", $spi);
                    //$tmp_wertungs_id =  $tabtutup->getValue("tabellenpunkte", $spi)."_".$tabtutup->getValue("gegnertabellenpunkte", $spi);      
                    $zwischentabellen_array[$spi]['wertungsid'] = $tmp_wertungs_id;
                      
                    if (!in_array( $tmp_wertungs_id, $strittige_wertungen )) 
                    {
                        $strittige_wertungen[] = $tmp_wertungs_id;
                    }
                    else
                    {                                                                                                                              
                        // falls noch nicht: vorheriges Spiel
                        if (!empty( @$lastspiel[$tmp_wertungs_id] ))
                        {
                            if (is_array($zuvergleichendespiele))
                            {
                                if (!in_array( $lastspiel[$tmp_wertungs_id], $zuvergleichendespiele )) $zuvergleichendespiele[] =  $lastspiel[$tmp_wertungs_id];
                            }
                            if (is_array( @$mannschaftsvergleich[ $tmp_wertungs_id ] ))
                            {
                                if (!@in_array( $lastspiel[$tmp_wertungs_id], @$mannschaftsvergleich[ $tmp_wertungs_id ] )) $mannschaftsvergleich[ $tmp_wertungs_id ][] = $lastspiel[$tmp_wertungs_id];      
                            }
                        }
                        
                        // jetziges Spiel
                        if (!in_array( $spi, $zuvergleichendespiele )) $zuvergleichendespiele[] =  $spi;
                        if (isset($mannschaftsvergleich[ $tmp_wertungs_id ]))
                        {
                            if (is_array( $mannschaftsvergleich[ $tmp_wertungs_id ] ))
                            {
                              if (!in_array( $spi, $mannschaftsvergleich[ $tmp_wertungs_id ] )) $mannschaftsvergleich[ $tmp_wertungs_id ][] = $spi;
                            }
                        }
                    }
                    $lastspiel[$tmp_wertungs_id] = $spi;
                    $tabtutup->next( );
                }          
                // Zwischenstand ermitteln  
                $echtetabposition = 1;
                $showhinweisaufmanuellewertung = false;   
                $bereitsgewertetespiele = array();
                foreach( $zwischentabellen_array AS $spi => $tabellenwertung) 
                {
                  
                  if (!in_array( $spi , $bereitsgewertetespiele ))
                  {
                      if (in_array($spi, $zuvergleichendespiele))
                      {
                          // Punktgleiche Mannschaften vergleichen    
                          /*
                          //Abschlusstabelle://
                          //Bei Gleichheit der Tabellenpunkte (TP) und der Mannschaftspunkte (MP) zwischen zwei oder mehr Mannschaften richtet sich die Platzierung nach dem direkten Vergleich der betroffenen Mannschaften, und zwar//
                          4.  die Tabellenpunkte in absteigender Form//
                          5.  die Mannschaftspunkte in absteigender Form//
                          6.  die Satzpunkte in absteigender Form und//
                          7.  der im Durchschnitt bei allen Spielen ohne Einbeziehung des gegenseitigen Spiels der zu wertenden Mannschaften erreichten Anzahl an Kegel in absteigender Folge.
                          */  
                          $tmpgleichegegner = 0;  
                          if (isset($mannschaftsvergleich[ $tabellenwertung['wertungsid'] ])) $tmpgleichegegner = count ( $mannschaftsvergleich[ $tabellenwertung['wertungsid'] ] );
                          if ($tmpgleichegegner == 2)
                          {
                            // GENAU 2 Sind punktgleich    
                            $tmpspiel = array();                                                             
                            $tmpspiel['kegel'] = led_tofloat(0);                                                                             
                            $tmpspiel['sp'] = led_tofloat(0);                                                                          
                            $tmpspiel['tp'] = led_tofloat(0);                                                                               
                            $tmpspiel['mp'] = led_tofloat(0);   
                            $tmpspiel['gesamtkegel'] = intval(0);                                 
                            $mptutup = rex_sql::factory();
                            $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spieltabelle WHERE teamid = "'.$zwischentabellen_array[ $spi]['teamid'].'" AND gegnerid = "'.$zwischentabellen_array[ $spi+1 ]['teamid'].'"  AND spielnr IN ('.$lesbarspieleliste.') AND saisonid = "'.$booking['saison'] .'" ORDER BY id DESC ';
                            //echo $sql;
                            $mptutup->setQuery( $sql );
                            $tmpspiel['spielanzahl'] = $mptutup->getRows();    
                            if (!empty( $tmpspiel['spielanzahl']  )) 
                            {                                                                 
                                for ($xpi = 0; $xpi<$tmpspiel['spielanzahl']; $xpi++) 
                                {                                                                                  
                                    $tmpspiel['mp'] = led_tofloat($tmpspiel['sp']) + led_tofloat( $mptutup->getValue("mannschaftspunkte", $xpi)); 
                                    $tmpspiel['sp'] = led_tofloat($tmpspiel['sp']) + led_tofloat( $mptutup->getValue("satzpunkte", $xpi));                                                                        
                                    $tmpspiel['tp'] = led_tofloat($tmpspiel['tp']) + led_tofloat( $mptutup->getValue("tabellenpunkte", $xpi));                                                                
                                    $tmpspiel['gesamtkegel'] = intval($tmpspiel['gesamtkegel']) + intval( $mptutup->getValue("gesamtkegel", $xpi));  
                                    $mptutup->next( );
                                }    
                            }   
                            //print_r ( $tmpspiel );  
                             
                            $tmp_gegnerspiel = array();                                                             
                            $tmp_gegnerspiel['kegel'] = led_tofloat(0);                                                                             
                            $tmp_gegnerspiel['sp'] = led_tofloat(0);                                                                          
                            $tmp_gegnerspiel['tp'] = led_tofloat(0);                                                                               
                            $tmp_gegnerspiel['mp'] = led_tofloat(0);                                                                                
                            $tmp_gegnerspiel['gesamtkegel'] = intval(0);                           
                            $mptutup = rex_sql::factory();
                            $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spieltabelle WHERE teamid = "'.$zwischentabellen_array[ $spi+1]['teamid'].'" AND gegnerid = "'.$zwischentabellen_array[ $spi ]['teamid'].'"  AND spielnr IN ('.$lesbarspieleliste.') AND saisonid = "'.$booking['saison'] .'" ORDER BY id DESC ';
                            //echo $sql;
                            $mptutup->setQuery( $sql );
                            $tmp_gegnerspiel['spielanzahl'] = $mptutup->getRows();    
                            if (!empty( $tmp_gegnerspiel['spielanzahl']  )) 
                            {                                                                 
                                for ($xpi = 0; $xpi<$tmp_gegnerspiel['spielanzahl']; $xpi++) 
                                {                                                                                  
                                    $tmp_gegnerspiel['mp'] = led_tofloat($tmp_gegnerspiel['sp']) + led_tofloat( $mptutup->getValue("mannschaftspunkte", $xpi)); 
                                    $tmp_gegnerspiel['sp'] = led_tofloat($tmp_gegnerspiel['sp']) + led_tofloat( $mptutup->getValue("satzpunkte", $xpi));                                                                        
                                    $tmp_gegnerspiel['tp'] = led_tofloat($tmp_gegnerspiel['tp']) + led_tofloat( $mptutup->getValue("tabellenpunkte", $xpi));                                                                    
                                    $tmp_gegnerspiel['gesamtkegel'] = intval($tmp_gegnerspiel['gesamtkegel']) + intval( $mptutup->getValue("gesamtkegel", $xpi));  
                                    $mptutup->next( );
                                }    
                            }   
                            //print_r ( $tmp_gegnerspiel );  
                            
                            // Vergleich der Teams
                            $teameinsgewinnt = true;  
                            /*           
                            */
                            if ( $tmp_gegnerspiel['tp'] > $tmpspiel['tp'] ) { $teameinsgewinnt = false;  /*echo 'TP entscheiden';*/ }
                            else if ( $tmp_gegnerspiel['tp'] == $tmpspiel['tp'] )
                            {
                                if ( $tmp_gegnerspiel['mp'] > $tmpspiel['mp'] ) { $teameinsgewinnt = false;  /*echo 'MP entscheiden';*/ }
                                else if ( $tmp_gegnerspiel['mp'] == $tmpspiel['mp'] )
                                {
                                    if ( $tmp_gegnerspiel['sp'] > $tmpspiel['sp'] ) { $teameinsgewinnt = false;  /*echo 'SP entscheiden';*/ }
                                    else if ( $tmp_gegnerspiel['sp'] == $tmpspiel['sp'] )
                                    {
                                        // Durchschnitt bei allen Spielen ohne Einbeziehung des gegenseitigen Spiels der zu wertenden Mannschaften erreichten Anzahl an Kegel in absteigender Folge.
                                        if ( (!empty( $zwischentabellen_array[ $spi]['spielanzahl'] )) AND (!empty( $zwischentabellen_array[ $spi+1]['spielanzahl'] ) ) )
                                        {
                                            //echo 'Kegel entscheiden<br />';                                                                    
                                            //echo 'Kegel gesamt: '.$zwischentabellen_array[ $spi]['kegel'].':'.$zwischentabellen_array[ $spi+1]['kegel'].'<br />';
                                            //echo 'Kegel gegeneinander: '.$tmpspiel['gesamtkegel'].':'.$tmp_gegnerspiel['gesamtkegel'].'<br />';
                                            $tmpspiel['kegelschnitt'] =  led_tofloat( ($zwischentabellen_array[ $spi]['kegel'] -  $tmpspiel['gesamtkegel'] )/ $zwischentabellen_array[ $spi]['spielanzahl'] );  
                                            $tmp_gegnerspiel['kegelschnitt'] =  led_tofloat( ($zwischentabellen_array[ $spi+1]['kegel'] -  $tmp_gegnerspiel['gesamtkegel'] )/ $zwischentabellen_array[ $spi+1]['spielanzahl'] );        
                                            //echo 'Kegel Differenz im Durchschnitt pro Spiel: '.$tmpspiel['kegelschnitt'].':'.$tmp_gegnerspiel['kegelschnitt'].'<br />';  
                                            if ( $tmp_gegnerspiel['kegelschnitt'] > $tmpspiel['kegelschnitt'] ) { $teameinsgewinnt = false;  /*echo 'Kegel entscheiden';*/ } 
                                        }
                                    }
                                }
                            }            
                            /*
                            */   
                              
                            // Spiele umtauschen?   
                            if (!$teameinsgewinnt)
                            {                       
                                $endtabellen_array[ $echtetabposition ] = $zwischentabellen_array[ $spi+1 ];  
                                //$echtzuvergleichendespiele[] = $echtetabposition;    
                                $bereitsgewertetespiele[] =  $spi+1;
                                $echtetabposition++;
                                                   
                                $endtabellen_array[ $echtetabposition ] = $zwischentabellen_array[ $spi ];  
                                //$echtzuvergleichendespiele[] = $echtetabposition;    
                                $bereitsgewertetespiele[] =  $spi;
                                $echtetabposition++;
                            }
                            else
                            {                       
                                $endtabellen_array[ $echtetabposition ] = $zwischentabellen_array[ $spi ];  
                                //$echtzuvergleichendespiele[] = $echtetabposition;    
                                $bereitsgewertetespiele[] =  $spi;
                                $echtetabposition++;
                                                   
                                $endtabellen_array[ $echtetabposition ] = $zwischentabellen_array[ $spi+1 ];  
                                //$echtzuvergleichendespiele[] = $echtetabposition;    
                                $bereitsgewertetespiele[] =  $spi+1;
                                $echtetabposition++;
                            }
                            /*   
                            // TODO: mehr als 2 sind punktgleich...
                            // wäre eine schleife zur Sortierung, 1. Team mit allen anderen Vergleichen, das unterste bleibt unten, dann wieder von vorn bis zum vorletzten usw. 
                            */
                            
                          }    
                          else
                          {     
                            // TODO: mehr als 2 sind punktgleich, ist mathematisch nicht wirklich lösbar...
                            $showhinweisaufmanuellewertung = true;
                            $endtabellen_array[ $echtetabposition ] = $zwischentabellen_array[ $spi ];
                            $bereitsgewertetespiele[] =  $spi;
                            $echtzuvergleichendespiele[] = $echtetabposition;    
                            $echtetabposition++;
                          }          
                      }
                      else
                      {
                          // kein Vergleich nötig
                          $endtabellen_array[ $echtetabposition ] = $zwischentabellen_array[ $spi ];
                          $bereitsgewertetespiele[] =  $spi;
                          $echtetabposition++;
                      }     
                  }
                }
                
                // Sortieren nach Position   
                ksort($endtabellen_array);
                
                // Jetzt der Endstand
                $poscounter = 1;
                foreach( $endtabellen_array AS $spi => $tabellenwertung) 
                {            
                    ?>
                    <tr>             
                    <td><?php echo ($poscounter); ?><?php if (($showhinweisaufmanuellewertung) AND (in_array($spi, $echtzuvergleichendespiele))) echo '*'; ?></td>
                    <td><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'mannschaft' => $tabellenwertung["teamid"] )); ?>"><?php echo $mannschaftsnamen[ $tabellenwertung["teamid"] ]; ?></a></td>
                    <td><?php 
                    $tmpspielzahl = $tabellenwertung["spielanzahl"];
                    echo $tmpspielzahl ; 
                    // Änderung 2022a
                    //if ($tmpspielzahl == 1) echo ' Spiel'; 
                    //else echo ' Spiele'; 
                    
                    //if (in_array($spi, $echtzuvergleichendespiele)) echo " VGL!";
                    
                    ?></td>
                    <td><?php echo intval(number_format( $tabellenwertung["tabellenpunkte"]/10 , 1, ",", "")); ?> : <?php echo intval(number_format( $tabellenwertung["gegnertabellenpunkte"]/10 , 1, ",", "")); ?></td>     
                    <?php if (1==2) { ?><td><?php echo number_format( $tabellenwertung["mannschaftspunkte"]/10 , 1, ",", ""); ?> : <?php echo number_format( $tabellenwertung["gegnermannschaftspunkte"]/10 , 1, ",", ""); ?></td><?php } ?>      
                    <td><?php 

                    // Änderung 2022a
                    if ($specialansicht) { echo $tabellenwertung["kegel"]; } 
                    else echo number_format( $tabellenwertung["satzpunkte"] /10 , 1, ",", ""); 
                    
                    ?>
                        
                        <?php 
                            // Änderung 2022a
                            //echo ' : ';
                            //echo number_format( $tabellenwertung["gegnersatzpunkte"]/10 , 1, ",", ""); 
                        ?></td>      
                    </tr>    
                    <?php 
                    $poscounter++;
                }    
            } 
          
                
        ?>
        </tbody>
        </table>
        <?php
   
?>