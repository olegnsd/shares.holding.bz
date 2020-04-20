<?


$merchant_id = '4';
$merchant_secret = '6WR5FuIVkERK4UWr';


$sign = md5($merchant_id.$merchant_secret.$_REQUEST['id'].$_REQUEST['sum']);

if ($sign != $_REQUEST['secret']) {
    die('wrong sign');
}

include('inc/config.php');
include('inc/mysql.php');
include('inc/functions.php');

$from=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='122' LIMIT 1;"));
$to=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='".(int)$_REQUEST['id']."' LIMIT 1;"));
if($from==0)die('Ошибка');if($to==0)die('Ошибка');
$max=$from['kol_akc'];
$price=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `settings` WHERE name='buyprice' LIMIT 1;"));


if((($_REQUEST['sum']/$price['value'])>0) & (($_REQUEST['sum']/$price['value'])<=$from['kol_akc'])){
$text="Покупка акций за BCR. Сумма в руб: ".$_REQUEST['sum'];
mysqli_query($mysqli,"INSERT INTO `send_log` (`from`, `to`, `amount`, `text`)
VALUES ('".(int)$from['id']."', '".(int)$to['id']."', '".(int)($_REQUEST['sum']/$price['value'])."', '".mysqli_escape_string($mysqli, $text)."');");
mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)-".(int)($_REQUEST['sum']/$price['value'])." WHERE `id`=".(int)$from['id']."");
mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)+".(int)($_REQUEST['sum']/$price['value'])." WHERE `id`=".(int)$to['id']."");
sms($to['phone'], "Вы купили ".(int)$_POST['amount']." акций Милитари Холдинг. Остаток: ".(int)$to['kol_akc']+(int)($_REQUEST['sum']/$price['value'])." акций ".$baseHref);
//sms($account['phone'], "Вы отправили ".(int)$_POST['amount']." акций Милитари Холдинг акционеру ".$data['name'].". Остаток: ".((int)$account['kol_akc']-(int)$_POST['amount'])." акций ".$baseHref);
//$account=mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT * FROM users WHERE id='".(int)$account['id']));
die('YES');?><?
}
?>Ошибка
