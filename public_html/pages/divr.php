
<div class="alert alert-info">
	<a href='https://bartercoin.holding.bz/create'>Расчеты через BarterCoin: https://bartercoin.holding.bz/create</a>
</div>

<h2>Реквизиты акционера</h2>

<? if(isset($_POST['qiwi'])){

    //проверка карты бартеркоин
    $card = $_POST['bcr'];
    $card = preg_replace("/\D{1,}/", "", $card);
    $card = str_replace(' ', '', $card);
    $card = mysqli_escape_string($mysqli, $card);
    
    `echo  "       " >>/tmp/qaz`;
    `echo  "card: "$card >>/tmp/qaz`;
    $myecho = $account['card'];
    `echo  "account[card]: "$myecho >>/tmp/qaz`;
    
    if($account['card'] != $card){
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
            
            $myecho = json_encode($data1);
            `echo " data1: "  $myecho >>/tmp/qaz`;
        }
        if($data1 != "OK"){
            $err = "Ошибка, введенная карта бартеркоин не рабочая";
        }
    }else{
        $card = $account['card'];
    }

    if(!isset($err)){
        mysqli_query($mysqli,"UPDATE `akcioner` SET `qiwi`='".mysqli_escape_string($mysqli,$_POST['qiwi'])."',`card`='$card',`address`='".mysqli_escape_string($mysqli,$_POST['address'])."'  WHERE `id`=".(int)$account['id']."");
        ?><div class="alert alert-success">Изменения сохранены</div>
    <?}else{?>
        <div class="alert alert-danger"><?=$err?></div>
    <?
    }
}


$data=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='".(int)$account['id']."' LIMIT 1;"));
if($data[id]==0){?><div class="alert alert-danger">Акционер не найден</div><?}else{?>
<form method=post>

<div class="form-group">
<label>QIWI (формат 79000000000)</label>
<input type="text" class="form-control" name="qiwi" value="<?=htmlspecialchars($data['qiwi']);?>">
</div>

<div class="form-group">
<label>BCR</label>
<input type="text" class="form-control" name="bcr" value="<?=htmlspecialchars($data['card']);?>">
</div>

<div class="form-group">
<label>Почтовый адрес (с индексом)</label>
<textarea class="form-control" name="address"><?=htmlspecialchars($data['address']);?></textarea>
</div>

<div class="form-group">
<button type="submit" class="btn btn-success">Сохранить</button>
</div>
</form>
<?}?>
<script type="text/javascript">

jQuery(function($) {

$.mask.definitions['~']='[+-]';
//
$('input[name=bcr]').mask('9999 9999 9999 9999');

});</script>
