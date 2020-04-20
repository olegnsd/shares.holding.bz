<?
$data=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='122' LIMIT 1;"));
$max=$data['kol_akc'];
$price=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `settings` WHERE name='buyprice' LIMIT 1;"));
if(((int)$_POST['kol']>0) & ((int)$_POST['kol']<=$max)){
$sum=$price['value']*(int)$_POST['kol'];
$merchant_id = '4';
$secret_word = 'CT0NKVyVaxytwxTI';
$order_id = $account['id'];
$order_amount = $sum;
$sign = md5($merchant_id.$secret_word.$order_id.(float)$order_amount);
$paymentUrl = 'https://bartercoin.holding.bz/do/?pay&shop='.$merchant_id.'&id='.$order_id.'&sum='.$order_amount.'&comment=Покупка '.(int)$_POST['kol'].' акций Милитари Холдинг&secret='.$sign.'&return='.$baseHref;
header('Location:'.$paymentUrl);die();
}?>

<div class="alert alert-info">
	<a href='https://bartercoin.holding.bz/create'>Расчеты через BarterCoin: https://bartercoin.holding.bz/create</a>
</div>

<h2>Купить акции за BCR</h2>

<?

?>
<form method=post>

<div class="form-group">
<label>Количество акций</label>
<input type="number" value="1" min=1 onchange="$('#price').val(<?=$price['value'];?>*$(this).val());" max="<?=$max;?>" class="form-control" name="kol">
</div>
<div class="form-group">
<label>Цена</label>
<input type="number" id="price" readonly value="<?=$price['value'];?>" class="form-control">
</div>

<div class="form-group">
<button type="submit" class="btn btn-success">Купить</button>
</div>
</form>
