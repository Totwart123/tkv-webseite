<?php
    // Allgemeine Einstellungen und Funktionen Ergebnisdienst 
    // Version 20170614

    // spielfrei ist immer ohne Datum und Uhrzeit
    $sql = rex_sql::factory();
    $sql->setQuery(
        'UPDATE ' . rex::getTablePrefix() . '950_spielplan
            SET datum = :leer, uhrzeit = :leer
          WHERE gastmannschaftid = :mid OR heimmannschaftid = :mid',
        [':leer' => '', ':mid' => 54]
    );

    
    // Allgemeine Funktionen und Einstellungen
    $LED_UPLOADFOLDER = realpath( rex_path::media().'../leduploads');
    $error = array();
    
    // Fuer phpExcelReader
    $allow_url_override = 0; // Set to 0 to not allow changed VIA POST or GET
    $max_rows = 0; //USE 0 for no max
    $max_cols = 50; //USE 0 for no max
    $debug = 0;  //1 for on 0 for off
    $force_nobr = 1;  //Force the info in cells not to wrap unless stated explicitly (newline)
                                                                
    $maxsizeusevalue = 'post_max_size';    
    //$maxsizeusevalue = 'upload_max_filesize';
    
    if (!function_exists("return_bytes")) 
    {
        function return_bytes($val) 
        {
            $val = trim($val);
            $last = strtolower($val[strlen($val)-1]);
            switch($last) 
            {
                // The 'G' modifier is available since PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }
            
            $text = $val." Byte";
            if ($val > 1024) $text = intval($val/1024)." kB";    
            if ($val > (1024*1024)) $text = (intval($val/(1024*1024/10))/10)." MB";
        
            return $text;
        }
    }
    if (!function_exists("led_filename")) 
    {  
        function led_filename($FILENAME, $doSubindexing = true)
        {
          global $LED_UPLOADFOLDER;
          
        
          // ----- neuer filename und extension holen
          $NFILENAME = str_replace(
            array('Ä',  'ä',  'Ö',  'ö',  'Ü',  'ü',  'ß'),
            array('ae', 'ae', 'oe', 'oe', 'ue', 'ue', 'ss'),
            $FILENAME
          );
          $NFILENAME = strtolower($NFILENAME);
          $NFILENAME = str_replace(
            array("a\xec\x88", "o\xec\x88", "u\xec\x88"),
            array('ae', 'oe', 'ue'),
            $NFILENAME
          );
          $NFILENAME = preg_replace('/[^a-zA-Z0-9.\-\+]/','_',$NFILENAME);
          if (strrpos($NFILENAME,'.') != '')
          {
            $NFILE_NAME = substr($NFILENAME,0,strlen($NFILENAME)-(strlen($NFILENAME)-strrpos($NFILENAME,'.')));
            $NFILE_EXT  = substr($NFILENAME,strrpos($NFILENAME,'.'),strlen($NFILENAME)-strrpos($NFILENAME,'.'));
          }else
          {
            $NFILE_NAME = $NFILENAME;
            $NFILE_EXT  = '';
          }
        
          // ---- ext checken - alle scriptendungen rausfiltern WURDE ENTFERNT
        
          //$NFILE_NAME = date('YmdHis').'_'.$NFILE_NAME;
          $NFILE_NAME = $NFILE_NAME.'_'.date('YmdHis');
          $NFILENAME = $NFILE_NAME.$NFILE_EXT;
        
          if($doSubindexing || $FILENAME != $NFILENAME)
          {
            // ----- datei schon vorhanden -> namen aendern -> _1 ..
            if (file_exists($LED_UPLOADFOLDER.'/'.$NFILENAME))
            {
              $cnt = 1;
              while(file_exists($LED_UPLOADFOLDER.'/'.$NFILE_NAME.'_'.$cnt.$NFILE_EXT))
                $cnt++;
        
              $NFILENAME = $NFILE_NAME.'_'.$cnt.$NFILE_EXT;
            }
          }
        
          return $NFILENAME;
        }
    }
    if (!function_exists("led_saveUpload")) 
    {
        function led_saveUpload($FILE, $filename, $realname="")
        {
          global $LED_UPLOADFOLDER;
        
          $FILESIZE = $FILE['size'];
          $FILETYPE = $FILE['type'];
          $NFILENAME = $filename;
          $message = '';
          $dstFile = $LED_UPLOADFOLDER.'/'.$NFILENAME;
          
          @chmod($dstFile, rex::getProperty('fileperm'));
          $size = @getimagesize($dstFile);
      
          $FILESQL = rex_sql::factory();
          $FILESQL->setTable(''.rex::getTablePrefix() .'950_uploads');
          $FILESQL->setValue('filename',$NFILENAME);   
          $FILESQL->setValue('originalname',$realname);
          $FILESQL->setValue('filesize',$FILESIZE);   
          $FILESQL->setValue('uploaderid', $_SESSION['USR_ID']);
          $FILESQL->setValue('uploadername', $_SESSION['USR_NAME']);
          $FILESQL->setValue('changed', date('Y-m-d H:i:s') );
          //$FILESQL->addGlobalCreateFields(1);
          //$FILESQL->addGlobalUpdateFields(1);
          $FILESQL->insert();
    }
  }      
  if (!function_exists("make_alpha_from_numbers")) 
  {
      function make_alpha_from_numbers($number)
      {
      	$numeric = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      	if($number<strlen($numeric))
      	{
      		return $numeric[$number];
      	}
      	else
      	{
      		$dev_by = floor($number/strlen($numeric));
      		return "" . make_alpha_from_numbers($dev_by-1) . make_alpha_from_numbers($number-($dev_by*strlen($numeric)));
      	}
      }   
  }          
  if (!function_exists("make_alphaonly")) 
  {
      function make_alphaonly($text)
      {
      	$sanitized = preg_replace('/[^a-zA-Z0-9\.\,\(\)@#!?ÄÖÜäöüß\-\/ ]/', '', $text);
        return $sanitized;
      }   
  }       
  if (!function_exists("led_datumformat")) 
  {
      function led_datumformat($datuminput)
      {                                 
      	$datumoutput = trim($datuminput);
      	$datumoutput = str_replace('/','.', $datumoutput);        
      	$datumoutput = str_replace(' Uhr','', $datumoutput);
        return $datumoutput;
      }   
  }   
  if (!function_exists("led_importdatumformat")) 
  {
      function led_importdatumformat($datuminput)
      {                                 
      	$datumoutput = trim($datuminput);
      	$datumoutput = explode('/',$datumoutput);
        $newoutput = date("d.m.Y",mktime(0, 0, 0, $datumoutput[1], intval($datumoutput[0]), $datumoutput[2]) - 60*60*24);
        return $newoutput;
      }   
  }        
  if (!function_exists("led_readabledatumformat")) 
  {
      function led_readabledatumformat($datuminput)
      {                                     
        $korrektesdatumformat = explode( '-' , $datuminput );
        $datumoutput =  trim( $korrektesdatumformat[2].".".$korrektesdatumformat[1].".".$korrektesdatumformat[0] );   
        return $datumoutput;
      }   
  }  
  if (!function_exists("led_spielnummer")) 
  {
      function led_spielnummer($nummer)
      {                                 
      	$spieloutput = trim($nummer);
      	$spieloutput = intval($spieloutput);
        $zeichenzahl = strlen($spieloutput);
        
        //if ($zeichenzahl == 0)  $spieloutput = "0000".$spieloutput;   
        //if ($zeichenzahl == 1)  $spieloutput = "000".$spieloutput;
        //if ($zeichenzahl == 2)  $spieloutput = "00".$spieloutput;    
        if (empty($spieloutput)) $spieloutput = "";    
        else if ($zeichenzahl == 1)  $spieloutput = "00000".$spieloutput;     
        else if ($zeichenzahl == 2)  $spieloutput = "0000".$spieloutput;     
        else if ($zeichenzahl == 3)  $spieloutput = "000".$spieloutput;     
        else if ($zeichenzahl == 4)  $spieloutput = "00".$spieloutput;      
        else if ($zeichenzahl == 5)  $spieloutput = "0".$spieloutput;      
        else if ($zeichenzahl == 6)  $spieloutput = $spieloutput;
        else $spieloutput = "";
        
        return $spieloutput;
      }   
  }       
  if (!function_exists("led_passnummer")) 
  {
      function led_passnummer($nummer)
      {                                 
      	$spieloutput = trim($nummer);
        /*                             
        */
        $zeichenzahl = strlen($spieloutput);
        
        if ($zeichenzahl < 1)  $spieloutput = '';                            
        else if ($zeichenzahl == 1)  $spieloutput = '00000'.$spieloutput; 
        else if ($zeichenzahl == 2)  $spieloutput = '0000'.$spieloutput; 
        else if ($zeichenzahl == 3)  $spieloutput = '000'.$spieloutput; 
        else if ($zeichenzahl == 4)  $spieloutput = '00'.$spieloutput; 
        else if ($zeichenzahl == 5)  $spieloutput = '0'.$spieloutput; 
        else $spieloutput = substr( $spieloutput, -6);
        
        
      	$spielzahl = intval($spieloutput);
        if (empty($spielzahl)) $spieloutput = '';  
        
        return $spieloutput;
      }   
  }          
  if (!function_exists("led_passgeboren")) 
  {
      function led_passgeboren($timestempel)
      {                                 
      	$spieloutput = trim($timestempel);
        $zeichenzahl = strlen($spieloutput);
        /*
        if ($zeichenzahl < 4)  $spieloutput = '';          
        else if ($zeichenzahl == 4)  $spieloutput = '00'.$spieloutput; 
        else if ($zeichenzahl == 5)  $spieloutput = '0'.$spieloutput; 
        else $spieloutput = substr( $spieloutput, -6);
        
        
      	$spielzahl = intval($spieloutput);
        if (empty($spielzahl)) $spieloutput = '';  
        */    
        $spieloutput = str_replace( ',', '.' , $spieloutput);
        
        /*
        if ($zeichenzahl == 1)
        {
            $spieloutput = "01.0".$spieloutput.".2000";
        }   
        else if ($zeichenzahl == 2)
        {
            $spieloutput = "01.".$spieloutput.".2000";
        } 
        else if ($zeichenzahl == 4)
        {
            $spieloutput = "01.0".$spieloutput;
            
            $jahreszahl = substr( $spieloutput, -2);
            if ($jahreszahl > 25)  $spieloutput = substr( $spieloutput,0, 6).'19'.$jahreszahl;
            else  $spieloutput = substr( $spieloutput,0, 6).'20'.$jahreszahl;
        }    
        else if ($zeichenzahl == 5)
        {
            $spieloutput = "01.".$spieloutput;       
            
            $jahreszahl = substr( $spieloutput, -2);
            if ($jahreszahl > 25)  $spieloutput = substr( $spieloutput,0, 6).'19'.$jahreszahl;
            else  $spieloutput = substr( $spieloutput,0, 6).'20'.$jahreszahl;
        }
        */
        if ($zeichenzahl < 6)
        {                 
            $spieloutputarray = explode('.', $spieloutput);   
            $spieloutput = "";
            if (!empty( $spieloutputarray[0] ))
            {
               //Monat
               $spdatlaenge = strlen( $spieloutputarray[0] );
               if ( $spdatlaenge == 1)   $spieloutput = "01.0".$spieloutputarray[0];
               else if ( $spdatlaenge == 2)   $spieloutput = "01.".$spieloutputarray[0];
            }    
            if ((!empty( $spieloutputarray[1] )) AND (!empty($spieloutput)))
            {
               // Jahr
               $spdatlaenge = strlen( $spieloutputarray[1] );
               if ( $spdatlaenge == 1)  $spieloutputarray[1] .= "0";
               
               $jahreszahl = trim( $spieloutputarray[1]);
               if ($jahreszahl > 25) $spieloutput .= ".19".$spieloutputarray[1];    
               else $spieloutput .= ".20".$spieloutputarray[1];
            }   
            else if ((empty( $spieloutputarray[1] )) AND (!empty($spieloutput)))
            {
              $spieloutput .= ".2000";
            }
        
        }
        else $spieloutput = date ("d.m.Y", $spieloutput);
        
        return $spieloutput;
        //return $timestempel;
      }   
  }     
  if (!function_exists("led_tofloat")) 
  {
      function led_tofloat($nummer)
      {                                 
      	$spieloutput = str_replace(',' , '.',  $nummer );  
      	//$spieloutput = str_replace('8,5' , 'xx',  $nummer );   
        $spieloutput = floatval( $spieloutput );       
        //$spieloutput = ( $nummer );         
        
        return $spieloutput;
      }   
  }
    
  if (!function_exists("led_altersklasse")) 
  {
      function led_altersklasse($stichtagsgeburtsjahr, $aktivesaison_jahrende) 
      {
        if ((empty($stichtagsgeburtsjahr))) $retval =  '-';
        else 
        {
            $tmpalter = intval( $aktivesaison_jahrende - $stichtagsgeburtsjahr - 1);
            if ( $tmpalter < 10 ) $retval = "u10";
            else if ( $tmpalter < 15 ) $retval = "u14";
            else if ( $tmpalter < 20 ) $retval = "u19";
            else if ( $tmpalter < 24 ) $retval = "u23";
            else if ( $tmpalter < 50 ) $retval = "allgemein";
            else if ( $tmpalter < 60 ) $retval = "Sen A";
            else if ( $tmpalter < 70 ) $retval = "Sen B";
            else $retval = "Sen C";

            //$retval = $retval." // GebJ ".$stichtagsgeburtsjahr." // SaisonEndJahr ".$aktivesaison_jahrende." // Alter ".$tmpalter;
        }
        return $retval;
      }
  }
   
?>