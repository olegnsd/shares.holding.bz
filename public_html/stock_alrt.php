<?php
ini_set('display_errors', 0);

//die('error');

$merchant_id = '6';
$merchant_secret = '6WR5FuIVkERK4UWr';


$sign = md5($merchant_id.$merchant_secret.$_REQUEST['id'].$_REQUEST['sum']);

if ($sign != $_REQUEST['secret']) {
    die('wrong sign');
}

include('inc/config.php');
include('inc/mysql.php');
include('inc/functions.php');

$stock_id = (float)($_REQUEST['id']);

//echo('stock_id: '. $stock_id);
//die();

$stock = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM stock WHERE id='$stock_id' LIMIT 1;"));
$stock_akcioner = mysqli_fetch_array(mysqli_query($mysqli, "SELECT value FROM settings WHERE name='stock_id';"));
$stock_akcioner = $stock_akcioner['value'];
$contragent = mysqli_query($mysqli, "SELECT * FROM contragent WHERE stock_id='$stock_id';");
$akcioner = mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='".$stock['akcion_id']."' LIMIT 1;"));

//отмена сделок с ошибкой зачисления на карту
if(isset($_POST['card_err'])){
	$card_err = json_decode($_POST['card_err'], true);
	
    foreach($card_err['id'] as $key=>$err_id){
		$err_id = (int)$err_id;
        $res = mysqli_query($mysqli, "UPDATE `stock` SET `res`='4' WHERE id='$err_id';");
    }
    //обновить кол-во акций
    $res = mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)-".(int)array_sum($card_err['count'])." WHERE `id`=".$akcioner['id']."");
    
    $res = mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=`kol_akc`+".(int)array_sum($card_err['count'])." WHERE `id`=".$stock_akcioner."");
    
	$res = mysqli_query($mysqli, "UPDATE `stock` SET `count`=IFNULL(`count`,0)-".(int)array_sum($card_err['count']). ", `sum`=IFNULL(`sum`,0)-".(int)array_sum($card_err['sum']). " WHERE id='$stock_id';");
    
    die('YES_ERR');
}

//покупка лимитная
if($stock['operation'] == 1 && ($stock['order'] == 2 || $stock['order'] == 3) && $stock['res'] == 1){
    mysqli_query($mysqli, "UPDATE `stock` SET `res`='2' WHERE id='$stock_id';");
    sms($akcioner['phone'], "Вы подтвердили биржевое требование на покупку ".(float)$stock['count']." акций Милитари Холдинг". $baseHref);
    die('YES12');
}

//покупка рыночная
//echo('stock[operation]: '. $stock['operation'] );
//echo('stock[order]: '. $stock['order'] );
//echo('stock[res]: '. $stock['res'] );
//die();

if($stock['operation'] == 1 && $stock['order'] == 1 && $stock['res'] == 1){
    //выбор карт BCR продавцов акций
    foreach($contragent as $key=>$contr_row){
        $deal = mysqli_fetch_array(mysqli_query($mysqli,"SELECT `id`, `akcion_id`, `count`, `price`, `sum` FROM `stock` WHERE id='".$contr_row['contrag_id']."' LIMIT 1;"));
        $card = mysqli_fetch_array(mysqli_query($mysqli,"SELECT `card` FROM `akcioner` WHERE id='".$deal['akcion_id']."' LIMIT 1;"));
        
        $deals['card'][] = $card['card']; 
        $deals['akcion_id'][] = $deal['akcion_id'];
        $deals['count'][] = $deal['count']; 
        $deals['price'][] = $deal['price'];  
        $deals['id'][] = $deal['id']; 
        //сделка состоялась
        mysqli_query($mysqli, "UPDATE `stock` SET `res`='3', `date_compl`=CURRENT_TIMESTAMP WHERE id='". $contr_row['contrag_id'] ."';");
    }
    mysqli_query($mysqli, "UPDATE `stock` SET `res`='3', `date_compl`=CURRENT_TIMESTAMP WHERE id='$stock_id';");
    //обновить кол-во акций
    mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)+". array_sum($deals['count']) ." WHERE `id`=".$akcioner['id']."");
    
    $res = mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=`kol_akc`-".array_sum($deals['count'])." WHERE `id`=".$stock_akcioner."");
    
    sms($akcioner['phone'], "Вы подтвердили биржевое требование на покупку ".(float)$stock['count']." акций Милитари Холдинг". $baseHref);
    $deals = json_encode($deals);
    $out = json_encode(array(0=>'YES11', 1=>$deals));
    
    die($out);
}
//продажа рыночная + покупка рыночна!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
if($stock['operation'] == 2 & $stock['order'] == 1 && $stock['res'] == 1){
    //выбор карт BCR продавцов акций
    foreach($contragent as $key=>$contr_row){
        $deal = mysqli_fetch_array(mysqli_query($mysqli,"SELECT `id`, `akcion_id`, `count`, `price` FROM `stock` WHERE id='".$contr_row['contrag_id']."' LIMIT 1;"));
        $card = mysqli_fetch_array(mysqli_query($mysqli,"SELECT `card` FROM `akcioner` WHERE id='".$deal['akcion_id']."' LIMIT 1;"));
        
        $deals['card'][] = $card['card'];
        $deals['akcion_id'][] = $deal['akcion_id'];
        $deals['count'][] = $deal['count']; 
        $deals['price'][] = $deal['price']; 
        $deals['id'][] = $deal['id']; 
        //сделка состоялась
        mysqli_query($mysqli, "UPDATE `stock` SET `res`='3', `date_compl`=CURRENT_TIMESTAMP WHERE id='". $contr_row['contrag_id'] ."';");
        //обновить кол-во акций
        mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)+".$deal['count']." WHERE id='".$deal['akcion_id']."'");
        
        $res = mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=`kol_akc`-".$deal['count']." WHERE `id`=".$stock_akcioner."");
    }
    mysqli_query($mysqli, "UPDATE `stock` SET `res`='3', `date_compl`=CURRENT_TIMESTAMP WHERE id='$stock_id';");
    //обновить кол-во акций
    mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)-".array_sum($deals['count'])." WHERE `id`=".$akcioner['id']."");
    
    $res = mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=`kol_akc`+".array_sum($deals['count'])." WHERE `id`=".$stock_akcioner."");
    
    sms($akcioner['phone'], "Вы подтвердили биржевое требование на продажу ".(float)$stock['count']." акций Милитари Холдинг ". $baseHref);
    $deals = json_encode($deals);
    
    $out = json_encode(array(0=>'YES21', 1=>$deals));
    die($out);
}






//if((($_REQUEST['sum']/$price['value'])>0) & (($_REQUEST['sum']/$price['value'])<=$from['kol_akc'])){
//	$text="Покупка акций за BCR. Сумма в руб: ".$_REQUEST['sum'];
//	mysqli_query($mysqli,"INSERT INTO `send_log` (`from`, `to`, `amount`, `text`)
//	VALUES ('".(int)$from['id']."', '".(int)$to['id']."', '".(int)($_REQUEST['sum']/$price['value'])."', '".mysqli_escape_string($mysqli, $text)."');");
//	mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)-".(int)($_REQUEST['sum']/$price['value'])." WHERE `id`=".(int)$from['id']."");
//	mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)+".(int)($_REQUEST['sum']/$price['value'])." WHERE `id`=".(int)$to['id']."");
	
	//sms($account['phone'], "Вы отправили ".(int)$_POST['amount']." акций Милитари Холдинг акционеру ".$data['name'].". Остаток: ".((int)$account['kol_akc']-(int)$_POST['amount'])." акций ".$baseHref);
	//$account=mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT * FROM users WHERE id='".(int)$account['id']));
//}
?>
Ошибка
