<?php
$card = $card_donor;
if($curl = curl_init()){
    curl_setopt($curl, CURLOPT_URL, "http://bartercoin.holding.bz/tasks/check_card.php");
    curl_setopt($curl, CURLOPT_POST, True);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array(
    "number" => $card,
    "sum" => "1",
    "secret" => "erov74rvue",
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
    $data1 = curl_exec($curl);
    curl_close($curl);
    $bal = $data1;

}
