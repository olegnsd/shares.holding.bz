<?php
$card = $_POST['card'];
if($curl = curl_init()){
    curl_setopt($curl, CURLOPT_URL, "http://bartercoin.holding.bz/tasks/check_card.php");
    curl_setopt($curl, CURLOPT_POST, True);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array(
    "card" => $card,
    "secret" => "erov74rvue",
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
    $data1 = curl_exec($curl);
    curl_close($curl);
}
if($data1 != "OK"){
    die("ERR");   
}
die("OK");
