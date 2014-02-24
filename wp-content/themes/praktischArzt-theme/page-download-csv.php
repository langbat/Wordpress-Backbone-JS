<?php

$path = ABSPATH ."/wp-content/uploads/files/Lastschrift-Bestellnumber ";
$files = explode(',', $_GET['csv']);


$content = '';
foreach ($files as $id){
if (file_exists($path.$id.'.csv')){
    $tmp = explode("\n", file_get_contents($path.$id.'.csv'));
    $header = $tmp[0];
    $content .= "\n".$tmp[1];
}
}
if ($content != ''){
    ob_get_clean();
    header('Content-type: text/csv');
    header('Content-disposition: attachment;filename=Lastschrift-Bestellnumber.csv');
    
    echo $header;
    echo $content;
}
