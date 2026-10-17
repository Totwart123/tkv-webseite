<?php
    // Ansicht Spielplan Ergebnisdienst 
    // Version 20170713
    if (empty($booking['mannschaft'])) echo '<div class="alert alert-danger">Es wurde keine Mannschaft ausgewählt!</div>';
    else
    {
    
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
    
    // Alle Ansetzungen auslesen
    $datentutup = rex_sql::factory();
    $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'.$booking['klasse'] .'" AND saisonid = "'.$booking['saison'] .'" AND ( heimmannschaftid="'.$booking['mannschaft'].'" OR gastmannschaftid="'.$booking['mannschaft'].'") ORDER BY spieltagnr ASC, spielnr ASC, dateformat ASC';
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();    
     
?>           
    <?php

// falls keine Einzelschnitte gewünscht sind        
if (empty($booking['sort']))
{
    ?>
    <div class="col-sm-12">
      <h3>
      <?php
      echo 'Mannschaft: '.$mannschaftsnamen[ $booking['mannschaft'] ].'<br /><br />';
      ?> 
      <small>
     <?php echo $booking['klassename']; ?>, 
     <?php echo $booking['saisonname']; ?>
     </small>
      </h3>
    </div>
    <div class="col-sm-12">
    <table class="table table-striped table-hovered table-condensed">
    <thead>
      <tr>
        <th>SpNr</th>
        <th>Datum</th>
        <th>Heim</th>
        <th>-</th>
        <th>Gast</th>                                     
        <th style="text-align: center;">MP</th>
        <th style="text-align: center;">Ergebnis</th>
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
              $tmpspiel['heim']['kegel'] = (int) ( $mptutup->getValue("gesamtkegel", 0) );                                                                     
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
              $tmpspiel['gast']['kegel'] = (int) ( $mptutup->getValue("gesamtkegel", 0) );                                                                    
              $tmpspiel['gast']['sp'] = $mptutup->getValue("satzpunkte", 0);                                                                             
              $tmpspiel['gast']['mp'] = $mptutup->getValue("mannschaftspunkte", 0);                                                                                   
              $tmpspiel['gast']['tp'] = $mptutup->getValue("tabellenpunkte", 0);                         
          }
          
          ?>
          <tr>                      
          <td><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'spiel' => $tmpspiel['spielnr'] )); ?>"><?php echo $tmpspiel['spielnr']; ?></a></td>
          <td><?php echo $tmpspiel['datum']; ?></td>
          <td><?php 
          
          if ( $tmpspiel['heimid'] == $booking['mannschaft']) echo '<strong>';
          echo $mannschaftsnamen[ $tmpspiel['heimid'] ];    
          if ( $tmpspiel['heimid'] == $booking['mannschaft']) echo '</strong>';
          
          ?></td>     
          <td>-</td>
          <td><?php 
          
          
          if ( $tmpspiel['gastid'] == $booking['mannschaft']) echo '<strong>';
          echo $mannschaftsnamen[ $tmpspiel['gastid'] ]; 
          if ( $tmpspiel['gastid'] == $booking['mannschaft']) echo '</strong>';
          ?></td>                            
          <td style="text-align: center;">
            <?php    
            
            echo $tmpspiel['heim']['sp']; 
            $tmpergebniszeichen = strlen( $tmpspiel['heim']['sp'] );
            if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
            echo $tmpspiel['gast']['sp']; 
            
            ?>
          </td> 
          <td style="text-align: center;">
            <?php 
            
            if ((empty( $tmpspiel['heim']['kegel'] )) AND (empty( $tmpspiel['gast']['kegel'] )) )  {}
            else
            {
                
                if ((( $tmpspiel['heimid'] == $booking['mannschaft']) AND (  $tmpspiel['heim']['kegel'] < $tmpspiel['gast']['kegel'] )) )
                {
                  // Heimspiel verloren
                  echo '<span style="color: red;">';
                }
                else if ((( $tmpspiel['gastid'] == $booking['mannschaft']) AND (  $tmpspiel['heim']['kegel'] > $tmpspiel['gast']['kegel'] )) )    
                {
                  // Gast verloren
                  echo '<span style="color: red;">';
                }
                else if ( $tmpspiel['heim']['kegel'] == $tmpspiel['gast']['kegel'] )
                {      
                  // unentschieden
                  echo '<span>';
                }
                else echo '<span style="color: green;">';
                
                echo $tmpspiel['heim']['kegel']; 
                $tmpergebniszeichen = strlen( $tmpspiel['heim']['kegel'] );
                if (!empty( $tmpergebniszeichen )) echo "&nbsp;:&nbsp;";
                echo $tmpspiel['gast']['kegel']; 
                
                echo '</span>';      
            }
            ?>
          </td>
          </tr>        
          <?php 
          if (!empty( $tmpspiel['anmerkung'] ))
          {
          ?>
          <tr>                      
          <td colspan=1><br /></td>    
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
}
    
    // Hier  die Einzeldurchschnitte  
    include('led.liste.einzelschnitt.php');       
    
}
   
?>