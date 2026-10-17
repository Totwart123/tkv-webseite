<?php
    // Output Listen Ergebnisdienst 
    // Version 20161027

    $booking = array();                                     
    $booking['tabelle']  = rex_request('tabelle', 'int');          
    $booking['tag']  = rex_request('tag', 'int');   
    $booking['spiel']  = rex_request('spiel', 'int');    
    $booking['mannschaft']  = rex_request('mannschaft', 'int');      
    $booking['einzel']  = rex_request('einzel', 'int');              
    $booking['plan']  = rex_request('plan', 'int');     
    $booking['saison']  = rex_request('saison', 'int');   
    $booking['klasse']  = rex_request('klasse', 'int');     
    $booking['top']  = trim(rex_request('top', 'string'));   
    if ($booking['top'] != "alle") $booking['top'] = intval( $booking['top'] );
    
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");   

    //echo 'in Arbeit [CODE: v65f001]';
    //exit;
    
    
    // Ausgabe
?>                                      
<div class="tableitem row clearfix">   
  <div class="col-sm-12">
    <ul>                                                                                                                                    
      <li><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'plan' => 1 )); ?>">OK Ansicht Spielplan</a></li>
      <li><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tabelle' => 1 )); ?>">Ansicht aktueller Tabellenstand</a></li>
      <li><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'tag' => 176 )); ?>">Ansicht einzelner Spieltag</a></li>
      <li><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'spiel' => 176 )); ?>">Ansicht einzelnes Spiel Rositz - Apolda</a></li>    
      <li><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'mannschaft' => 176 )); ?>">Ansicht Mannschaft Rositz</a></li>              
      <li><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'einzel' => 176 )); ?>">Ansicht Einzelschnitte Mannschaft Rositz</a></li>
    </ul>
  </div>
</div>   
<div class="tableitem row clearfix">             

<?php if (!empty( $booking['tabelle'] )) { ?>
  <div class="col-sm-12"><h3>Aktueller Tabellenstand, Datum 32.13.2016</h3></div>
  <div class="col-sm-12"><table class="table table-striped table-hovered" border="0">
  <thead>
  <tr>                   
  <th>Pos.</th>
  <th>Mannschaft</th>
  <th></th>
  <th></th>
  <th></th>
  </tr>      
  </thead>
  <tbody>
  <?php for ($spi = 1; $spi<10; $spi++) { ?>
    <tr>             
    <td><?php echo $spi; ?></td>
    <td><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'mannschaft' => 176 )); ?>">SV Rositz</a></td>
    <td>17 Spiele</td>
    <td>24:10 TP</td>
    <td>86,0 : 50,0 MP</td>
    </tr>    
  <?php } ?>
  </tbody>
  </table></div>
<?php } ?>

<?php if (!empty( $booking['tag'] )) { ?>
  <div class="col-sm-12"><h3>Liga AB, Spieltag 32.13.2016</h3></div><div class="col-sm-12"><table class="table table-striped table-hovered" border="0">
  <thead>
  <tr>
  <th>Sp.Nr.</th>
  <th>Mannschaften</th>
  <th>Punkte</th>
  <th>Ergebnis</th>
  <th>Kommentar/Datei</th>
  </tr>      
  </thead>
  <tbody>
  <?php for ($spi = 0; $spi<10; $spi++) { ?>
    <tr>
    <td>176</td>
    <td><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'spiel' => 176 )); ?>">Rositz - Apolda</a></td>
    <td>7,0 : 1,0</td>
    <td>3284 : 3189</td>
    <td>-</td>
    </tr>    
  <?php } ?>
  </tbody>
  </table></div>
<?php } ?>

<?php if (!empty( $booking['spiel'] )) { ?>
    <div class="col-sm-12"><h3>Liga AB, Spieltag 32.13.2016</h3></div><div class="col-sm-12">
    
  <table class="table" border="0">
  <thead>     
    <tr><th colspan="5">Details zum Spiel</td></tr>                  
  </thead>
  <tbody>
    <tr>
    <td>Spiel-Nummer: 176</td>
    <td><a href="<?php echo rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'spiel' => 176 )); ?>">Rositz - Apolda</a></td>
    <td>7,0 : 1,0</td>
    <td>3284 : 3189</td>
    <td>Sie finden alle Spielerpaarungen in der unteren Tabelle.</td>
    </tr>    
  </tbody>
  </table>
  
  <table class="table table-striped table-hovered" border="0">
    <thead>
    <tr>
    <th>Rositz</th>
    <th></th>
    <th></th>
    <th></th>     
    <th></th>
    <th></th>
    <th></th>   
    <th></th>
    <th>Apolda</th>
    </tr>      
    </thead>
    <tbody>   
    <?php for ($spi = 0; $spi<10; $spi++) { ?>
      <tr>
      <td>Thieme, Andreas</td>
      <td>553</td>
      <td>3,0</td>
      <td>1,0</td>   
      <td>-</td>
      <td>-</td>
      <td>1,0</td>  
      <td>535</td>
      <td>Roth, Frank</td>
      </tr>        
    <?php } ?>
    </tbody>
    </table></div>
<?php } ?>
              

<?php if (!empty( $booking['mannschaft'] )) { ?>
    <div class="col-sm-12"><h3>Mannschaft SV Rositz</h3></div><div class="col-sm-12">
  
  <table class="table table-striped table-hovered" border="0">
    <thead>
    <tr>
    <th>Spieltag</th>
    <th></th>
    <th></th>
    <th>Punkte</th>     
    <th>Ergebnis</th>
    </tr>      
    </thead>
    <tbody>   
    <?php for ($spi = 0; $spi<10; $spi++) { ?>
      <tr>
      <td>1.1.2011</td>
      <td>Apolda</td>
      <td><b>SV Rositz</b></td>
      <td>553 : 535</td>
      <td><span style="color: red">Niederlage (-18)</span></td>
      </tr>        
    <?php } ?>
    </tbody>
    </table></div>
<?php } ?>   
              

<?php if (!empty( $booking['einzel'] )) { ?>
    <div class="col-sm-12"><h3>Einzel-Durchschnitte SV Rositz</h3></div><div class="col-sm-12">
    <p>Hier eine Sortieroption ergänzen...</p>
    <table class="table table-striped table-hovered">
<thead>
<tr>
<th align="right">Pl.</th>
<th>&nbsp;</th>
<th align="left">Name</th>
<th align="left"><a href="#">Mannschaft</a></th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right"><strong>Ausw.</strong></th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right"><a href="#">Heim</a></th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right">&nbsp;</th>
<th align="right"><a href="#">Ges.</a></th>
<th align="right"><a href="#">Akt.</a></th>
</tr>       
</thead>
<tbody>      
    <?php for ($spi = 0; $spi<10; $spi++) { ?>
    <tr>
    <td align="right">1</td>
    <td>&nbsp;</td>
    <td align="left">Max Mustermann</td>
    <td align="left">SV Rositz</td>
    <td align="right">3</td>
    <td align="right">668.7</td>
    <td align="right">368.0</td>
    <td align="right">0.3</td>
    <td align="right"><strong>1036.7</strong></td>
    <td align="right">4</td>
    <td align="right">684.0</td>
    <td align="right">401.0</td>
    <td align="right">1.0</td>
    <td align="right"><strong>1085.0</strong></td>
    <td align="right">7</td>
    <td align="right">677.4</td>
    <td align="right">386.9</td>
    <td align="right">0.7</td>
    <td align="right"><strong>1064.3</strong></td>
    <td align="right">1069</td>
    </tr>      
    <?php } ?>
</tbody>
</table>
<?php } ?>    
              

<?php 

if (!empty( $booking['plan'] )) 
{ 
  include(rex_path::src()."/addons/ergebnisdienst/lib/led.liste.spielplan.php");   
} ?>

<a class="btn btn-info" href="#">Drucken</a>    
<a class="btn btn-info" href="#">Als PDF-Datei speichern</a>

</div>