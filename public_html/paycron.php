<?include('inc/config.php');
include('inc/mysql.php');
include('inc/functions.php');
include('inc/auth.php');

if($auth){if($account['is_admin']){
?>Время запуска: <?=date('Y-m-d H:i:s');?><br><?
$data=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `div_tasks` WHERE `status`='0' LIMIT 1;"));
if($data['id']>0){?><script>
setTimeout(function(){$('.ajaxcron').load('/paycron.php');},2000);


</script><?
?>Задача <?echo($data['id']);?><br><?
$res=mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE `id`>'".(int)$data['next']."' LIMIT 0,100;");
$bd=false;//переделать проверку на запрос количества
while($row=mysqli_fetch_assoc($res))
    {$bd=true;


if(($row['qiwi']!='') & ($row['kol_akc']>0)){

$sum=((float)$data['amount'])*$row['kol_akc'];

$text=$data['text'];
$text=str_replace('{sum}',number_format($sum,2,'.',' '),$text);
$text=str_replace('{amount}',number_format((float)$data['amount'],2,'.',' '),$text);
if($sum<1)$sum=1;


///тут делаем дело
    $qiwi =  $sum;



	if( $curl = curl_init() ) {
		curl_setopt($curl, CURLOPT_URL, 'https://edge.qiwi.com/sinap/api/v2/terms/99/payments');
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");                                                             
		$time = date("d.m.Y H:i:s");

			$linkas_qiwi = $row["qiwi"];
			$s = $sum;
			$sms_send = $text;
			//eval("\$sms_send = \"$sms_send\";");

			$id = 1000 * time();
			
			$json_data = '{"id":"' . $id . '","sum":{"amount":' . str_replace(',','.',(float)$sum) . ',"currency":"643"},"paymentMethod":{"type":"Account","accountId":"643"}, "comment":"' . $sms_send . '","fields":{"account":"' . $row["qiwi"] . '"}}';
                                                                  
			curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);    			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                                                                                                                                      
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($json_data),
			    'Authorization: Bearer ' . $qiwi_token)
			); 
		    
		    $out = curl_exec($curl);
			//sleep(10);

		    if (strpos($out,'Accepted')) {
		    	//отправлено. лог и смс
mysqli_query($mysqli, "INSERT INTO `div_log` (`user`, `sum`, `text`) VALUES ('".(int)$row['id']."', '".str_replace(',','.',(float)$sum)."','".mysqli_escape_string($mysqli,$text)."');");
sms($row['phone'], $text);
		    }else{die('Платёж не прошёл! Акционер '.htmlspecialchars($row['name']));}
		
		// sleep(1);
	}

	curl_close($curl);

	/*sleep(10);
	//обновление счета
	if( $curl = curl_init() ) {

		curl_setopt($curl, CURLOPT_URL, 'https://edge.qiwi.com/funding-sources/v1/accounts/current');
         
		// curl_setopt($curl, CURLOPT_HEADER, TRUE);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);   
                                                                                                                                                                                                 
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Content-Type: application/json',
		    'Authorization: Bearer ' . $qiwi_token)
		); 
	    
	    $out_count = curl_exec($curl);

	}

	curl_close($curl);

	$out_count = json_decode($out_count);

	$out_count = $out_count->accounts[0]->balance->amount;

	// $cur_date = date("d.m.Y H:i:s");

	mysql_query("UPDATE adnins set qWbalans='$out_count' where id='1'");
	mysql_query("UPDATE adnins set qBalLastUp='$time' where id='1'");
	
*/



echo('Обработан акционер '.htmlspecialchars($row['name']));



}else{?>У акционера <?=htmlspecialchars($row['name']);?> нет QIWI или нет акций<br><?}
mysqli_query($mysqli,"UPDATE `div_tasks` SET `next` = '".(int)$row['id']."' WHERE `id`=".(int)$data['id'].";");
if(($row['qiwi']) & ($row['kol_akc']>0))die();
}if(!$bd){mysqli_query($mysqli,"UPDATE `div_tasks` SET `status` = '1' WHERE `id`=".(int)$data['id'].";");
?>Задача окончена<?
}



}else die('Задачи кончились');


die();}}?>Нет доступа<??>
