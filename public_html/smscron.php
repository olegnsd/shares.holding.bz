<?include('inc/config.php');
include('inc/mysql.php');
include('inc/functions.php');
include('inc/auth.php');

if($auth){if($account['is_admin']){
?>Время запуска: <?=date('Y-m-d H:i:s');?><br><?
$data=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `sms_tasks` WHERE `status`='0' LIMIT 1;"));
if($data['id']>0){?><script>
setTimeout(function(){$('.ajaxcron').load('/smscron.php');},2000);


</script><?
?>Задача <?echo($data['id']);?><br><?
$res=mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE `id`>'".(int)$data['next']."' LIMIT 0,100;");
$bd=false;//переделать проверку на запрос количества
while($row=mysqli_fetch_assoc($res))
    {$bd=true;


if(($data['blank']==1) | ($row['kol_akc']>0)){

//$sum=((float)$data['amount'])*$row['kol_akc'];

$text=$data['text'];
//$text=str_replace('{sum}',number_format($sum,2,'.',' '),$text);
//$text=str_replace('{amount}',number_format((float)$data['amount'],2,'.',' '),$text);
if($sum<1)$sum=1;


///тут делаем дело
  
sms($row['phone'], $text);
		   
		
	


echo('Обработан акционер '.htmlspecialchars($row['name']));



}else{?>У акционера <?=htmlspecialchars($row['name']);?> нет акций<br><?}
mysqli_query($mysqli,"UPDATE `sms_tasks` SET `next` = '".(int)$row['id']."' WHERE `id`=".(int)$data['id'].";");
if(($data['blank']==1) | ($row['kol_akc']>0))die();
}if(!$bd){mysqli_query($mysqli,"UPDATE `sms_tasks` SET `status` = '1' WHERE `id`=".(int)$data['id'].";");
?>Задача окончена<?
}



}else die('Задачи кончились');


die();}}?>Нет доступа<??>
