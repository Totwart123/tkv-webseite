<?php
          // Import Dateityp E, basierend auf C
          /*
          $datentutup = new sql();
          $sql = 'SELECT id, klasse, geschlecht, spielerzahl FROM rex_950_klasse WHERE id = "'.$booking['klasse'].'"';
          $datentutup->setQuery( $sql );
          $datenzahl = $datentutup->getRows();     
          if (!empty($datenzahl))
          {
                 
              //$booking['klasse'] = $datentutup->getValue("id", 0);           
              //$booking['klassename'] = $datentutup->getValue("klasse", 0);     
              //$booking['geschlecht'] = $datentutup->getValue("geschlecht", 0);    
              //$booking['spielerzahl'] = $datentutup->getValue("spielerzahl", 0);    
          }
          print_r( $booking );
          */
  
          $tmp_einzelsheet = 1;   
                                               
          $tmp_gesamtpunkt = array();     
          $tmp_satzpunkte = array();       
          $tmp_abraumpunkte = array();   
          $tmp_vollepunkte = array();   
          $tmp_fehlerpunkte = array();                                                                        
          $punktergebnis = array();       
          $tmp_spielername = array(); 
          $tmp_spielernummer = array();
          $tmp_spielerersatznummer  = array();
          $tmp_spielerersatzgeboren = array();
          
          $standardspielerzahl = 7;
          if ( @$booking['spielerzahl'] == 4)  $standardspielerzahl = 5;
          
          // NEU POKAL
          $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
          $punktergebnis[0]['pokalsvpunkte'] = trim($objPHPExcel->getActiveSheet()->getCell('L56')->getCalculatedValue() );                                                             
          $punktergebnis[1]['pokalsvpunkte'] = trim($objPHPExcel->getActiveSheet()->getCell('N56')->getCalculatedValue() );        
          // ENDE POKAL
              
          for ($aspieler=1;$aspieler < $standardspielerzahl; $aspieler++)
          {                                       
              
              if ( $aspieler == 1) $zellstart = 10;  
              if ( $aspieler == 2) $zellstart = 17;  
              if ( $aspieler == 3) $zellstart = 24;   
              if ( $aspieler == 4) $zellstart = 31;  
              if ( $aspieler == 5) $zellstart = 38;  
              if ( $aspieler == 6) $zellstart = 45;  
              
              $satzzahlmitleerzeile = 6; 
              
              $objPHPExcel->setActiveSheetIndex( $tmp_dkbsheet );
              if (($istdkbc) AND (( $kegeldateityp[$entry] == "E2")))
              {      
                  $tmp_spielername[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('B'.intval( $zellstart + 1) )->getOldCalculatedValue() );                
                  $tmp_spielernummer[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 1) )->getOldCalculatedValue() );   
              }
              else
              {      
                  $tmp_spielername[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('B'.intval( $zellstart + 1) )->getCalculatedValue() );                
                  $tmp_spielernummer[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 1) )->getCalculatedValue() );   
              }   
              //$tmp_spielergeboren[0][$aspieler] = led_passgeboren( PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 2) )->getOldCalculatedValue() ));                
              //$tmp_spielergeboren[0][$aspieler] = led_passgeboren($objPHPExcel->getActiveSheet()->getCell( 'A'.intval( $zellstart + 2) )->getCalculatedValue() );         
              //$tmp_spielergeboren[0][$aspieler] = led_passgeboren($objPHPExcel->getActiveSheet()->getCell( 'A'.intval( $zellstart + 2) )->getCalculatedValue()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14 ) );
              $tmp_spielergeboren[0][$aspieler] = '';
              //$tmp_spielergeboren[0][$aspieler] = led_passgeboren($objPHPExcel->getActiveSheet()->getCell( 'A'.intval( $zellstart + 2) )->getCalculatedValue() );    
              //$tmp_spielergeboren[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell( 'A'.intval( $zellstart + 2) )->getCalculatedValue() );   
              
              $cell = $objPHPExcel->getActiveSheet()->getCell( 'A'.intval( $zellstart + 2) );
              $format = "d.m.Y";
              if (($istdkbc) AND (( $kegeldateityp[$entry] == "E2"))) $InvDate= $cell->getOldCalculatedValue();
              else $InvDate= $cell->getValue();
              if(PHPExcel_Shared_Date::isDateTime($cell)) $InvDate = date($format, PHPExcel_Shared_Date::ExcelToPHP($InvDate)); 
              $tmp_spielergeboren[0][$aspieler]  = $InvDate;
              if (($istdkbc) AND (( $kegeldateityp[$entry] == "E2"))) $tmp_spielergeboren[0][$aspieler] = '';
                    
                            
              if (($istdkbc) AND (( $kegeldateityp[$entry] == "E2")))
              {      
                  $tmp_spielername[1][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('P'.intval( $zellstart + 1) )->getOldCalculatedValue() );                
                  $tmp_spielernummer[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 1) )->getOldCalculatedValue() );     
              }
              else
              {      
                  $tmp_spielername[1][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('P'.intval( $zellstart + 1) )->getCalculatedValue() );                
                  $tmp_spielernummer[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 1) )->getCalculatedValue() );     
              }   
               
              //$tmp_spielergeboren[1][$aspieler] = led_passgeboren( PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 2) )->getOldCalculatedValue() ));  
              //$tmp_spielergeboren[1][$aspieler] = led_passgeboren($objPHPExcel->getActiveSheet()->getCell( 'O'.intval( $zellstart + 2) )->getCalculatedValue() );         
              $tmp_spielergeboren[1][$aspieler] = '';       
              
              $cell = $objPHPExcel->getActiveSheet()->getCell( 'O'.intval( $zellstart + 2) );
              $format = "d.m.Y";
              if (($istdkbc) AND (( $kegeldateityp[$entry] == "E2"))) $InvDate= $cell->getOldCalculatedValue();
              else $InvDate= $cell->getValue();
              if(PHPExcel_Shared_Date::isDateTime($cell)) $InvDate = date($format, intval(PHPExcel_Shared_Date::ExcelToPHP($InvDate))); 
              $tmp_spielergeboren[1][$aspieler]  = $InvDate;    
              if (($istdkbc) AND (( $kegeldateityp[$entry] == "E2"))) $tmp_spielergeboren[1][$aspieler] = '';
                                                 
                      // NEU: wurde ausgewechselt       
                      if (($istdkbc) AND (( $kegeldateityp[$entry] == "E2")))
                      {                                          
                          $tmp_spielerersatz[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 4) )->getOldCalculatedValue() );     
                          $tmp_spielerersatz[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 4) )->getOldCalculatedValue() );  
                          $tmp_spielerersatzname[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('B'.intval( $zellstart + 4) )->getOldCalculatedValue() );     
                          $tmp_spielerersatzname[1][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('P'.intval( $zellstart + 4) )->getOldCalculatedValue() );    
                          $tmp_spielerersatzgeboren[0][$aspieler] = '';     
                          $tmp_spielerersatzgeboren[1][$aspieler] = '';        
                      }
                      else
                      {      
                          $tmp_spielerersatz[0][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('A'.intval( $zellstart + 4) )->getCalculatedValue() );     
                          $tmp_spielerersatz[1][$aspieler] = led_passnummer($objPHPExcel->getActiveSheet()->getCell('O'.intval( $zellstart + 4) )->getCalculatedValue() );    
                          $tmp_spielerersatzname[0][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('B'.intval( $zellstart + 4) )->getCalculatedValue() );     
                          $tmp_spielerersatzname[1][$aspieler] = trim($objPHPExcel->getActiveSheet()->getCell('P'.intval( $zellstart + 4) )->getCalculatedValue() );    

                            $cell = $objPHPExcel->getActiveSheet()->getCell( 'A'.intval( $zellstart + 5) );
                            $format = "d.m.Y";
                            $InvDate= $cell->getValue();
                            if(PHPExcel_Shared_Date::isDateTime($cell)) $InvDate = date($format, PHPExcel_Shared_Date::ExcelToPHP($InvDate)); 
                            $tmp_spielerersatzgeboren[0][$aspieler] = $InvDate;

                            $cell = $objPHPExcel->getActiveSheet()->getCell( 'O'.intval( $zellstart + 5) );
                            $format = "d.m.Y";
                            $InvDate= $cell->getValue();
                            if(PHPExcel_Shared_Date::isDateTime($cell)) $InvDate = date($format, PHPExcel_Shared_Date::ExcelToPHP($InvDate)); 
                            $tmp_spielerersatzgeboren[1][$aspieler] = $InvDate;
                      } 
                      // ENDE: wurde ausgewechselt   
                      
                      //echo "E1: ".$tmp_spielerersatz[0][$aspieler]."<br /><br />";
                      //echo "E2: ".$tmp_spielerersatz[1][$aspieler]."<br /><br />";
              
                     
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
                     if (($istdkbc) AND (( $kegeldateityp[$entry] == "E2")))
                     {            
                        $tmp_abraumpunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('F'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );  
                        $tmp_abraumpunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('T'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );     
                        $tmp_vollepunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('G'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );  
                        $tmp_vollepunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('U'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );   
                        $tmp_fehlerpunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('E'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );  
                        $tmp_fehlerpunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('S'.intval( $zellstart + $asatz) )->getOldCalculatedValue() );   
                     }
                     else
                     {                                                                                                                          
                        $tmp_abraumpunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('F'.intval( $zellstart + $asatz) )->getCalculatedValue() );  
                        $tmp_abraumpunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('T'.intval( $zellstart + $asatz) )->getCalculatedValue() );     
                        $tmp_vollepunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('G'.intval( $zellstart + $asatz) )->getCalculatedValue() );  
                        $tmp_vollepunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('U'.intval( $zellstart + $asatz) )->getCalculatedValue() );   
                        $tmp_fehlerpunkte[0][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('E'.intval( $zellstart + $asatz) )->getCalculatedValue() );  
                        $tmp_fehlerpunkte[1][$asatz] = intval($objPHPExcel->getActiveSheet()->getCell('S'.intval( $zellstart + $asatz) )->getCalculatedValue() );   
                     }                                                                                                        
                    
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
          
          /*
          echo '<pre>';
          print_r ( $tmp_spielergeboren );   
          echo '</pre>';  
          
          echo '<pre>';
          print_r ( $tmp_spielername );   
          echo '</pre>';
                              
          
          echo '<pre>';
          print_r ( $tmp_spielernummer );   
          echo '</pre>';
          */
?>