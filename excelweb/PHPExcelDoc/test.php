<?php

error_reporting(E_ALL);
set_time_limit(0);

date_default_timezone_set('Europe/London');

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<title>Kegel Demo</title>

</head>
<body>

<h1>Kegel Demo #01</h1>
<?php

/** Include path **/
set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');

/** PHPExcel_IOFactory */
include 'PHPExcel/IOFactory.php';


$inputFileName = 'hand.xls';
echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),'  to identify the format<br />';
$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);


echo '<hr />';

//$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
//var_dump($sheetData);
/*
function printr($data, $exit = TRUE) {
  if ($data) {
    print '<pre>';
    print_r($data);
    print '</pre>';
  }
  if ($exit) {
    exit;
  }
}     
printr($sheetData);
*/

$objWorksheet = $objPHPExcel->setActiveSheetIndex(2);
echo '<table border=1>' . "\n";
foreach ($objWorksheet->getRowIterator() as $row) {
  echo '<tr>' . "\n";
  $cellIterator = $row->getCellIterator();
  $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells,
                                                     // even if it is not set.
                                                     // By default, only cells
                                                     // that are set will be
                                                     // iterated.
  foreach ($cellIterator as $cell) {
    echo '<td>' . $cell->getValue() . '</td>' . "\n";
  }
  echo '</tr>' . "\n";
}
echo '</table>' . "\n";


?>
<body>
</html>