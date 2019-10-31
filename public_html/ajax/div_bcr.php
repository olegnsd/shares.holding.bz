<?php

ini_set('display_errors', 0);
include('../inc/config.php');
include('../inc/mysql.php');
include('../inc/functions.php');
include('../inc/auth.php');

$num = preg_replace("/[^0-9]/", '', $_POST['num']);
$month = $_POST['month'];
$year = $_POST['year'];
$cvc = $_POST['cvc'];
$cod = $_POST['cod'];
$secret = 'hdTK4Ms0a5Myq9';
$token = md5($secret.$num.$month.$year.$cvc);
if(strlen($cod) >= 4){
    $json_data = json_encode(array('num' => $num, 'month' => $month, 'year' => $year, 'cvc' => $cvc, 'token' => $token, 'cod' => $cod));
}else{
    $json_data = json_encode(array('num' => $num, 'month' => $month, 'year' => $year, 'cvc' => $cvc, 'token' => $token));
}

if($curl = curl_init()){
    curl_setopt($curl, CURLOPT_URL, $bartercoin ."/api_pay/check_card.php");
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);    			
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_data))
    ); 
    $out = curl_exec($curl);
    curl_close($curl);
}
$out1 = json_decode($out, true);
if($out1['response_code'] == 4){
    $res = mysqli_query($mysqli,"UPDATE `settings` SET value='$num' WHERE name='card_donor';");
    sql_err($mysqli, 'UPDATE settings'); 
}
die($out);
