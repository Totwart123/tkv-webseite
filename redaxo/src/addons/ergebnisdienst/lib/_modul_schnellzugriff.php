<?php
// Bei Bedarf hier Variablen übergeben
$zielid= "REX_LINK[id=1 output=id]";      
$zielurl= "REX_LINK[id=1 output=url]";
$spielid = rex_request('spielid', 'string');    

if (empty($spielid ))
{
    // Formular anzeigen
    ?>
    <form action="<?php echo $zielurl; ?>" style="margin: 1rem 0; padding: 0 1rem; border: 1px solid #ddd; background: rgba(0,0,0,0.05); border-top: 0;">
      <input type="hidden" name="article_id" value="<?php echo $zielid; ?>">
      <table class="table">
        <tr>
        <td><label for="spielid" style="display: block; line-height: 1.1; padding: 5px 0;">Schnellzugriff auf eine Spielnummer der aktuellen Saison:</label></td>
        <td><input type="text" class="form-control" name="spielid" placeholder="Spielnr." required style="display: block; width: 100%; border: 1px solid #ddd; border-radius: 5px; line-height: 34px; padding-left: 1rem; background: #fff;" /></td>
        <td><button type="submit" class="btn btn-success btn-block" style="display: block; width: 100%; ">Los</button></td>     
        </tr>               
      </table>
    </form>
    <?php
}
else
{
    // Detailansicht       
    if(rex::isBackend()) echo 'Hier erscheint die Detailansicht.';
    else include(rex_path::src() ."/addons/ergebnisdienst/lib/led.ergebnisse.modul.php");
}

?>