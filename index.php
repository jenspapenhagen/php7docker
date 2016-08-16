<?php
declare(strict_types=1);

include 'CurlClient.php';
var_dump($_GET);
echo "\n"."Alle POST: ";
var_dump($_POST);
echo "\n";

//testarea
$curl = New CurlClient("127.0.0.1", "63342");

$hases = array("catz" => "1");
$getStatus=1;
$curl->post("id=112",$hases,$getStatus);


//if( $curl->ServerRunning() !== "200"){
//	echo "something fuckup ".$curl->ServerRunning();
//    die();
//}

//$output = $docker->listdocks();

//echo $output;

