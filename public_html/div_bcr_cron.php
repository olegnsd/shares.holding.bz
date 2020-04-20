<?php
include('inc/config.php');
include('inc/mysql.php');
include('inc/functions.php');
include('inc/auth.php');

ini_set('display_errors', 0);
ini_set('error_reporting', 0);
ini_set('display_startup_errors', 0);
$sql='select sum/count  as price from stock   order by date_compl desc limit 1;';
$res=mysqli_query($mysqli,$sql);
$row=mysqli_fetch_assoc($res);
$price=(int)$row['price'];
//file_put_contents('/home/shares/tmp/qaz',var_dump($row));
//file_put_contents('/home/shares/tmp/qaz',$price);
//
//exit;



if($auth){if($account['is_admin']){
    $task_id = (int)$_GET['task'];
    if($_GET['first'] == 1){
        mysqli_query($mysqli,"UPDATE `div_bcr_tasks` SET `next` = '0', `status`='0' WHERE `id`='$task_id';");
    }
?>
    Время запуска: <?=date('Y-m-d H:i:s');?><br><?
    $data=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `div_bcr_tasks` WHERE `status`='0' AND `id`='$task_id';"));
    sql_err($mysqli, 'SELECT * FROM div_bcr_tasks');
    
    if($data['id']>0){
        ?>Задача <?echo($data['id']);?><br><?
        $sql="SELECT a.* FROM akcioner AS a WHERE   a.id>'".(int)$data['next']."' AND (a.card!='none' OR a.card!=NULL) AND (a.kol_akc!=NULL OR a.kol_akc!=0) AND a.id<> ALL (SELECT user FROM div_bcr_log WHERE task_id = '".$data['id']."') LIMIT 0,100;";
//        $sql="SELECT a.* FROM akcioner AS a WHERE   a.id>'".(int)$data['next']."' AND (a.card=1000210746274516) AND (a.kol_akc!=NULL OR a.kol_akc!=0) AND a.id<> ALL (SELECT user FROM div_bcr_log WHERE task_id = '".$data['id']."') LIMIT 0,100;";
//        file_put_contents('/home/shares/tmp/qaz',$sql);
//        exit
        $res=mysqli_query($mysqli,$sql);
        sql_err($mysqli, 'SELECT * FROM akcioner');
       
        $card_donor = mysqli_fetch_array(mysqli_query($mysqli,"SELECT value FROM `settings` WHERE name='card_donor'"));
        $card_donor = $card_donor['value'];
                      
        $bd=false;//переделать проверку на запрос количества
        while($row=mysqli_fetch_assoc($res)){
            $bd=true;
            mysqli_query($mysqli,"UPDATE `div_bcr_tasks` SET `next` = '".(int)$row['id']."' WHERE `id`=".(int)$data['id'].";");
//            file_put_contents('/home/shares/tmp/qaz',$row,FILE_APPEND);

            sql_err($mysqli, 'UPDATE div_bcr_tasks');
//            file_put_contents('/home/shares/tmp/qaz','\n'+$row['kol_akc']+'\n',FILE_APPEND);

            if((strlen($row['card'])>=16) && ($row['kol_akc']>0)){
                $sum=ceil(((float)$data['amount'])*$row['kol_akc']);
                $kol_akc=$row['kol_akc'];
                $text=$data['text'];

                $text=str_replace('{sum}',number_format($sum,2,'.',' '),$text);
                $text=str_replace('{amount}',number_format((float)$data['amount'],2,'.',' '),$text);
                file_put_contents('/home/shares/tmp/qaz',$price,FILE_APPEND);
                $text=str_replace('{packetPrice}',$kol_akc*$price,$text);

                if($sum<1)$sum=1;

                ///тут делаем дело
                $qiwi =  $sum;

                if( $curl = curl_init() ) {
                    curl_setopt($curl, CURLOPT_URL, $bartercoin.'/api_pay/api_bcr_div.php');
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");                                                             
                    $time = date("d.m.Y H:i:s");

                    $linkas_qiwi = $row["qiwi"];
                    $s = $sum;
                    $sms_send = $text;
                    //eval("\$sms_send = \"$sms_send\";");

                    $id = 1000 * time();

                    $secret = 'GRT753j#%6rys';
                    $salt = 'apfk95';
                    $json_data['card1'] = $card_donor;
                    $json_data['card2'] = preg_replace("/\D{1,}/", '', $row["card"]);
                    $json_data['sum'] = $sum;
                    $json_data['comment'] = $sms_send;
                    $token = md5($json_data['card1'] . $secret. $json_data['card2']. $json_data['sum']. $salt);
                    $json_data['token'] = $token;
                    $json_data = json_encode($json_data, JSON_UNESCAPED_UNICODE);

                    curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);    			
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                                                                                                                                      
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($json_data)
                    )); 

                    $out = curl_exec($curl);

                    if (strpos($out,'Accepted')) {
                        //отправлено. лог и смс
                        file_put_contents('/home/shares/tmp/qaz',$text);

                        mysqli_query($mysqli, "INSERT INTO `div_bcr_log` (`task_id`, `user`, `sum`, `text`) VALUES ('".$data['id']."', '".(int)$row['id']."', '".str_replace(',','.',(float)$sum)."','".mysqli_escape_string($mysqli,$text)."');");
                        sms($row['phone'], $text);
                        sql_err($mysqli, 'INSERT INTO div_bcr_log');
                        echo('Обработан акционер '.htmlspecialchars($row['name']));
                    }else{
                        $out = json_decode($out, true);
                        echo('Платёж не прошёл! Акционер '.htmlspecialchars($row['name']). ' '. $out['error_message']);
                    }
                }

                curl_close($curl);

            }else{
                ?>У акционера <?=htmlspecialchars($row['name']);?> нет карты BCR или нет акций<br><?
            }
            ?><script>
                setTimeout(function(){$('.ajaxcron'+<?=$task_id?>).load('/div_bcr_cron.php?task='+<?=$task_id?>);},2000);
            </script><?
            die();
        }
        if(!$bd){
            mysqli_query($mysqli,"UPDATE `div_bcr_tasks` SET `status` = '1' WHERE `id`=".(int)$data['id'].";");
            ?>Задача окончена<?
        }
    }else die('Задачи кончились');

    die();
}}?>Нет доступа<??>
