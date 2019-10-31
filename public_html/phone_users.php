<?php
exit;
ini_set('display_errors', 1);
require('inc/config.php');
require('inc/functions.php');
require('inc/mysql.php');
ini_set('display_errors', 1);

echo('phone_akcioner');
    
$result=mysqli_query($mysqli,"SELECT distinct(phone) FROM akcioner WHERE phone!=''");

echo(json_encode($result));

//unlink("phones_akcioner");
//$foptmp = fopen("phones_akcioner", "ab");
foreach($result as $phone){
    if(strlen($phone['phone'])>=11){
//        fwrite($foptmp, $phone['phone']. PHP_EOL);
        echo($phone['phone']);
        echo("<br>");
    }
}
//fclose($foptmp);
