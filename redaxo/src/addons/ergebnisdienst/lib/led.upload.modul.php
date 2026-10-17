<div class="ledintern clearfix">
<?php
    // Upload-Funktionen Ergebnisdienst 
    // Version 20170606
    
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");         
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.config.php");   
  
  // Hier die Ausführung bei erfolgtem Login
    
    if ($_SESSION['USR']) 
    {
        
        // Variablen prüfen   
        $booking = array();    
        $booking['func']  = rex_request('func', 'string');   
        $booking['showform'] = true;     
        $booking['overwrite'] = false;         
        $shouldoverwrite = rex_request('overwrite', 'int');    
        if ($shouldoverwrite == 1) $booking['overwrite'] = true;       
        $booking['addnewplayer'] = false;         
        $shouldoverwrite = rex_request('addnewplayer', 'int');    
        if ($shouldoverwrite == 1) $booking['addnewplayer'] = true;        
        $booking['step'] = rex_request('step', 'int');     
        $booking['saison'] = rex_request('saison', 'int');   
        $booking['klasse'] = rex_request('klasse', 'int');      
        $booking['importid']  = rex_request('importid', 'string');  
        $booking['geschlecht']  = rex_request('geschlecht', 'string');   
        
        if (empty($booking['step'])) $booking['step'] = 0;   
               
        // Berechtigung des Nutzers prüfen    
        $datentutup = rex_sql::factory();
        $sql = 'SELECT gruppe FROM '.rex::getTablePrefix() .'950_nutzer WHERE	id="'.$_SESSION['USR_ID'].'"';
        $datentutup->setQuery($sql);   
        $datenzahl = $datentutup->getRows();     
        if (!empty($datenzahl)) $booking['usergruppe'] = $datentutup->getValue("gruppe", 0);      
        else $booking['usergruppe'] = "";   
        
        // Aktive Saison auslesen
        $datentutup = rex_sql::factory();
        $sql = 'SELECT id, saison, aktivesaison FROM '.rex::getTablePrefix() .'950_saison WHERE aktivesaison = "ja"';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();     
        if (!empty($datenzahl))
        {
               
            $booking['saison'] = $datentutup->getValue("id", 0);           
            $booking['saisonname'] = $datentutup->getValue("saison", 0);           
        } 
        else 
        {
            $booking['saison'] = 1;           
            $booking['saisonname'] = '';
        }
        
        // Aktive Klasse festlegen 
        /*   
        $datentutup = rex_sql::factory();
        $sql = 'SELECT id, klasse FROM '.rex::getTablePrefix() .'950_klasse WHERE id = 1';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();     
        if (!empty($datenzahl))
        {
               
            $booking['klasse'] = $datentutup->getValue("id", 0);           
            $booking['klassename'] = $datentutup->getValue("klasse", 0);           
        } 
        else 
        {
            $booking['klasse'] = 1;
            $booking['klassename'] = '';
        }
        */
        
        // Fortschrittsleiste
        $steps = array();       
        $steps[0] = "Datei hochladen";    
        $steps[1] = "Daten prüfen";     
        $steps[2] = "Daten speichern";   
        $steps[3] = "Fertig!";
        
        echo '<div class="clearfix">';
        echo '<ol class="steps">';
        foreach( $steps AS $stepkey => $steptext)
        {
            if ($stepkey == $booking['step']) echo '<li class="aktiv">'.$steptext.'</li>';
            else echo '<li>'.$steptext.'</li>';
        }
        echo '</ol>';
        echo '</div>';
                    
        // Pflichtfeld
        if ($booking['func'] == 'startupload')
        {
            include(rex_path::src()."/addons/ergebnisdienst/lib/upload.einzelspiel.func.php");   
            $booking['showform'] = false;     
        } 
        if ($booking['func'] == 'planupload')
        {
            if ($booking['usergruppe'] == "admin")
            {
                include(rex_path::src()."/addons/ergebnisdienst/lib/upload.spielplan.func.php");   
                $booking['showform'] = false;    
            } 
            else echo '<div class="alert alert-danger">Sie haben keine Berechtigung für Änderungen am Spielplan.</div>';
        }    
        if ($booking['func'] == 'spielerupload')
        {
            if ($booking['usergruppe'] == "admin")
            {
                include(rex_path::src()."/addons/ergebnisdienst/lib/upload.spielerliste.func.php");   
                $booking['showform'] = false;    
            } 
            else echo '<div class="alert alert-danger">Sie haben keine Berechtigung für Änderungen an der Spielerliste.</div>';
        }
                    
        // Upload-Formular anzeigen
        if ($booking['showform'])
        {
            
            ?>      
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="false">  
              <div class="panel panel-default">    
                <div class="panel-heading" role="tab" id="headingOne">      
                  <div class="panel-title">        
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Spielbericht hochladen</a>      
                  </div>    
                </div>    
                <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">      
                  <div class="panel-body">
                  
                        <form class="formgen ledupload clearfix" id="ledupload" action="<?php rex_geturl(rex_article::getCurrentId()); ?>" method="post" enctype="multipart/form-data">          
                          <input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden">               
                          <input name="func" value="startupload" type="hidden">
                          <fieldset class="fieldset">
                            
<legend>Das korrekte hochladen des Spielberichtes ist nur mit dem Schalter <font color="#FF0000"><span style="font-size:18pt"> Upload zum TKV </span><br></font> auf dem Spielbericht möglich.</legend>
<?php echo "<p><img src='\hochladen.jpg'/></p>"; ?>            
                              <div class="fileupl">
                              <span class="beschriftung">Excel-Datei auswählen...</span>
                              <span class="upicon">+</span>
                              <label for="datei">Spielbericht (XLSX)</label>
                              <input name="datei" id="datei" type="file" required>
                              <br>
                            </div>     
                          </fieldset>    
                          <p style="align_center">Hinweis: Derzeit wird für die Saison <strong><?php echo $booking['saisonname']; ?></strong> hochgeladen.</p>        
                          <input name="saison" value="<?php echo $booking['saison']; ?>" type="hidden">
                          <input name="klasse" value="<?php echo $booking['klasse']; ?>" type="hidden">    
                          <input name="step" value="1" type="hidden">
                               
                          <div class="formblock formsendbtn">         
                            <button type="submit" class="btn formsubmit formgreen">Spielbericht senden</button>      
                          </div>       
                        </form>
                  
                  </div>    
                </div>  
              </div>          
              <?php    
              
              // Optional: Hier begrenzen auf bestimmte Admins?  
              if ($booking['usergruppe'] == "admin")
              {
              ?>
              <div class="panel panel-default">    
                <div class="panel-heading textlink" role="tab" id="headingTwo">      
                  <div class="panel-title">        
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">Spielplan hochladen</a>      
                  </div>    
                </div>    
                <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">      
                  <div class="panel-body">
                  
                        <form class="formgen ledupload clearfix" id="planupload" action="<?php rex_geturl(rex_article::getCurrentId()); ?>" method="post" enctype="multipart/form-data">          
                          <input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden">               
                          <input name="func" value="planupload" type="hidden"> 
                          <fieldset class="fieldset">
                            <legend>Bitte wählen sie die Spielklasse</legend>
                            <label for="klasse">Spielklasse</label>
                            <select name="klasse" class="form-control" style="width: 100%;" >
                            <?php
                                        
                                        // Saisons auslesen
                                        $datentutup = rex_sql::factory();
                                        $sql = 'SELECT id, klasse FROM '.rex::getTablePrefix() .'950_klasse ORDER BY klasse DESC';
                                        $datentutup->setQuery( $sql );
                                        $datenzahl = $datentutup->getRows();     
                                        if (!empty($datenzahl))
                                        {
                                        
                                            for ($spi = 0; $spi<$datenzahl; $spi++) 
                                            { 
                                                $tmpistaktiv = $datentutup->getValue("id", $spi);
                                                if ($tmpistaktiv == "REX_VALUE[2]") echo '<option value="'.$datentutup->getValue("id", $spi).'" selected=selected>'.$datentutup->getValue("klasse", $spi).'</option>';  
                                                else echo '<option value="'.$datentutup->getValue("id", $spi).'">'.$datentutup->getValue("klasse", $spi).'</option>';     
                                                $datentutup->next( );  
                                            }
                                        } 
                                        
                                        
                            ?>
                            </select>       
                          </fieldset>  
                          <br /><br />
                          <fieldset class="fieldset">
                            <br />
                            <legend>Hochladen des Spielplans (XLSX)</legend>
                            <div class="fileupl">
                              <span class="beschriftung">Excel-Datei auswählen...</span>
                              <span class="upicon">+</span>
                              <label for="datei">Spielplan (XLSX)</label>
                              <input name="datei" id="datei" type="file" required>
                              <br>
                            </div>     
                          </fieldset>  
                          <p style="align_center">Hinweis: Derzeit wird für die Saison <strong><?php echo $booking['saisonname']; ?></strong> hochgeladen.</p>    
                          <br /><br />    
                          <input name="saison" value="<?php echo $booking['saison']; ?>" type="hidden">   
                          <input name="step" value="1" type="hidden">
                          
                          <div class="formblock formsendbtn">         
                            <button type="submit" class="btn formsubmit formgreen">Spielplan senden</button>      
                          </div>       
                        </form>
                        
                  </div>    
                </div>  
              </div> 
              <?php       
              ?>
              <div class="panel panel-default">    
                <div class="panel-heading textlink" role="tab" id="headingThree">      
                  <div class="panel-title">        
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">Spielerliste hochladen</a>      
                  </div>    
                </div>    
                <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">      
                  <div class="panel-body">
                  
                        <form class="formgen ledupload clearfix" id="spielerupload" action="<?php rex_geturl(rex_article::getCurrentId()); ?>" method="post" enctype="multipart/form-data">          
                          <input name="article_id" value="<?php echo rex_article::getCurrentId(); ?>" type="hidden">               
                          <input name="func" value="spielerupload" type="hidden"> 
                          <fieldset class="fieldset">
                            <br />
                            <legend>Hochladen der Spielerliste (CSV)</legend>
                            <div class="fileupl">
                              <span class="beschriftung">CSV-Datei auswählen...</span>
                              <span class="upicon">+</span>
                              <label for="datei">Spielerliste</label>
                              <input name="datei" id="datei" type="file" required>
                              <br>
                            </div>     
                          </fieldset>  
                          <p style="align_center">Hinweis: Anhand der Spielernummer erfolgt die Identifizierung. Vorhandene Einträge werden aktualisiert. Bei jedem Import wird das Spielrecht für alle Spieler gelöscht und neu eingetragen.</p>    
                          <br /><br />    
                          <input name="step" value="1" type="hidden">
                          
                          <div class="formblock formsendbtn">         
                            <button type="submit" class="btn formsubmit formgreen">Spielerliste senden</button>      
                          </div>       
                        </form>
                        
                  </div>    
                </div>  
              </div> 
              <?php  
              }
              
           } 
          
          ?>
        </div>
        <?php            
    }
?>  
</div>