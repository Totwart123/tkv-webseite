<?php
    // Ansicht Tabellenstand Ergebnisdienst 
    // Version 20170606 
    if (empty( $booking['tag'] ))
    {
    ?>
      <h3 class="pb">
      Tabellenstand: 
     <?php echo $booking['klassename']; ?>, 
     <?php echo $booking['saisonname']; ?>
      </h3>
      <?php  
    }
    include('led.liste.tabellenstand.php');       
   
?>