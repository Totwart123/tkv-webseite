<?php

    	  	 // Dompdf laden
           require_once ( "./redaxo/include/addons/ergebnisdienst/vendor/dompdf/autoload.inc.php");   
           use Dompdf\Dompdf;
           use Dompdf\Options;
    		  // Optionen festlegen
    		  $pdf_options = new Options();
    		  $pdf_options->setDpi(100); // legt die Dpi für das Dokument fest
    		  $pdf_options->set('defaultFont', 'Helvetica'); // Standard-Font
    		  // PDF erstellen
    		  $art_pdf_name =  'ergebnisdienst';
    		  header('Content-Type: application/pdf');
    		  $dompdf = new Dompdf($pdf_options);
    		  //$dompdf->loadHtml( $this->getArticle(1) );
          $dompdf->loadHtml( 'Hello World' );
    		  // Hinweis: Anstelle von REX_ARTICLE[] kann auch ein gestaltetes Template REX_TEMPLATE[XX] angegeben werden
    		  $dompdf->setPaper('A4', 'portrait');
    		  $dompdf->render();
    		  $dompdf->stream($art_pdf_name ,array('Attachment'=>false)); // bei true wird Download erzwungen
          
    		  die();                    
          /*
          */  
          ?>