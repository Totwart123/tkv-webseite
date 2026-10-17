<?php
          // Import Dateityp A
          
                  $tmp_einzelsheet = 1;   
                  $tmp_teamsheet = 4;
                  $tmp_spielernamesheet = 0;
                                                       
                  $tmp_gesamtpunkt = array();     
                  $tmp_satzpunkte = array();    
                  $tmp_abraumpunkte = array();   
                  $tmp_vollepunkte = array();   
                  $tmp_fehlerpunkte = array();     
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
                      //$tmp_spielergeboren[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 2) )->getOldCalculatedValue() );                
                      $tmp_spielergeboren[0][$aspieler] = led_passgeboren( PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 2) )->getOldCalculatedValue() ));                
                        
                      $tmp_spielername[1][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('P'.intval( $zellstart + 1) )->getOldCalculatedValue() );                
                      $tmp_spielernummer[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 1) )->getOldCalculatedValue() );    
                      $tmp_spielergeboren[1][$aspieler] = led_passgeboren( PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 2) )->getOldCalculatedValue() ));  
                                                
                      // NEU: wurde ausgewechselt
                      $tmp_spielerersatz[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 4) )->getOldCalculatedValue() );     
                      $tmp_spielerersatz[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 4) )->getOldCalculatedValue() );     
                      $tmp_spielerersatzname[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('B'.intval( $zellstart + 4) )->getOldCalculatedValue() );     
                      $tmp_spielerersatzname[1][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('P'.intval( $zellstart + 4) )->getOldCalculatedValue() );       
                      // ENDE: wurde ausgewechselt
                      
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
                            
                            if ( $tmp_gesamtpunkt[0][$asatz] == $tmp_gesamtpunkt[1][$asatz] )
                            {
                            
                              $tmp_satzpunkte[0][$asatz] = 0.5;      
                              $tmp_satzpunkte[1][$asatz] = 0.5;
                            }
                            else if ( $tmp_gesamtpunkt[0][$asatz] > $tmp_gesamtpunkt[1][$asatz] )
                            {     
                              $tmp_satzpunkte[0][$asatz] = 1.0;      
                              $tmp_satzpunkte[1][$asatz] = 0.0;
                            }
                            else
                            {    
                              $tmp_satzpunkte[0][$asatz] = 0.0;      
                              $tmp_satzpunkte[1][$asatz] = 1.0;
                            }       
                            
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
                            
                      // Gesamtauswertung Satz-Punkte
                      $punktergebnis[0]['satz'][$aspieler] = 0;
                      foreach ($tmp_satzpunkte[0] AS $testwert)  $punktergebnis[0]['satz'][$aspieler] = $punktergebnis[0]['satz'][$aspieler] + $testwert;
                      $punktergebnis[1]['satz'][$aspieler] = 0;
                      foreach ($tmp_satzpunkte[1] AS $testwert)  $punktergebnis[1]['satz'][$aspieler] = $punktergebnis[1]['satz'][$aspieler] + $testwert;
                            
                      // Gesamtauswertung Mannschaftspunkte-Punkte
                      $punktergebnis[0]['mannschaft'][$aspieler] = 0;    
                      $punktergebnis[1]['mannschaft'][$aspieler] = 0;
                      if ( $punktergebnis[0]['satz'][$aspieler] == $punktergebnis[1]['satz'][$aspieler] )
                      {    
                          // Punkteteilung nur bei Gleichstand
                          if ( $punktergebnis[0]['punkte'][$aspieler] == $punktergebnis[1]['punkte'][$aspieler] )
                          {
                              $punktergebnis[0]['mannschaft'][$aspieler] = 0.5;    
                              $punktergebnis[1]['mannschaft'][$aspieler] = 0.5;         
                          }
                          else if ( $punktergebnis[0]['punkte'][$aspieler] > $punktergebnis[1]['punkte'][$aspieler] )
                          {    
                              $punktergebnis[0]['mannschaft'][$aspieler] = 1.0;    
                              $punktergebnis[1]['mannschaft'][$aspieler] = 0.0;
                          } 
                          else
                          {    
                              $punktergebnis[0]['mannschaft'][$aspieler] = 0.0;    
                              $punktergebnis[1]['mannschaft'][$aspieler] = 1.0;
                          }
                      }   
                      else if ( $punktergebnis[0]['satz'][$aspieler] > $punktergebnis[1]['satz'][$aspieler] )
                      {    
                          $punktergebnis[0]['mannschaft'][$aspieler] = 1.0;    
                          $punktergebnis[1]['mannschaft'][$aspieler] = 0.0;
                      } 
                      else
                      {    
                          $punktergebnis[0]['mannschaft'][$aspieler] = 0.0;    
                          $punktergebnis[1]['mannschaft'][$aspieler] = 1.0;
                      }
                      
                  }
                  
                  //print_r ( $tmp_spielergeboren );
                  
                  ?>