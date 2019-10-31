<?php 
if($_POST['amount']){
if((float)$_POST['amount']>0){
mysqli_query($mysqli,"INSERT INTO `div_bcr_tasks` (`id`, `amount`, `text`) VALUES (NULL, '".str_replace(',','.',(float)$_POST['amount'])."', '".mysqli_escape_string($mysqli, $_POST['text'])."');");
if(mysqli_insert_id($mysqli)){header('Location: '.$baseHref.'admin/div_bcr');die();}else{?><div class="alert alert-danger">Ошибка при записи в базу</div><?}}}

$card_donor = mysqli_fetch_array(mysqli_query($mysqli,"SELECT value FROM `settings` WHERE name='card_donor'"));
$card_donor = $card_donor['value'];
require_once('./inc/check_bal.php');
$sum_div = $bal/10000;
$sum_div = floor($sum_div)/100;
$sum_div = number_format($sum_div, 2, '.', '');
?>
<form method=post onsubmit="return confirm('Подтвердите');" class="row">
<div class="form-group col-md-3 col-xs-12">
<label>Сумма на акцию</label>
<input type="number" value="<?=$sum_div?>" step="0.01" class="form-control" name="amount">
</div>
<div class="form-group col-md-9 col-xs-12">
<label>СМС</label>
<textarea class="form-control" name="text">Вам выплачены дивиденды в размере {sum} BCR по {amount} BCR за акцию. </textarea>
</div>
<div class="form-group col-xs-12">
<button type="submit" class="btn btn-success">Добавить задачу на отправку BCR дивидендов</button>
</div>
</form>
<form method=post class="row"  id="savecard1">
    <div class="form-group col-md-3 col-xs-12">
	    <label>Карта донор</label>
	    <label>Баланс: <?=$bal?> BCR</label>
	    <input autofocus="" type="text" class="form-control cardnum" value="<?=$card_donor?>" placeholder="Номер карты" name="num" required="">
	    <div class="row">
	        <div class="col-sm-5 col-xs-12"><input type="text" class="form-control cardnum" placeholder="Месяц" name="month" required=""  value=""></div>
	        <div class="col-sm-2  col-xs-12 text-center" style="line-height: 34px;font-size: 20px;">/</div>
	        <div class="col-sm-5  col-xs-12"><input type="text" class="form-control cardnum" placeholder="Год" name="year" required="" value=""></div>
	    </div>
	    <div class="row">
	        <div class="col-sm-5 col-xs-12"><input type="text" class="form-control cardnum" placeholder="CVC" name="cvc" required="" value=""></div>
	    </div>
	    <div class="row" id="card_donor">
	        <div class="col-sm-7  col-xs-12"><button type="button" name="card_donor" class="btn btn-success">Сохранить карту</button></div>
	    </div>
    </div>
</form>
<div class="panel panel-default">
<div class="panel-heading">Обработка задач</div>
</div>
<script>
function start(data_id){
$('.ajaxcron'+data_id).text('начинаем...');
$('.ajaxcron'+data_id).load('/div_bcr_cron.php?first=1&task='+data_id);
}
jQuery(function($) {
    $.mask.definitions['~']='[+-]';
    $('#savecard1 [name=num]').mask('9999 9999 9999 9999',{completed:function(){$('#savecard1 [name=month]').focus();}});
    $('#savecard1 [name=month]').mask('99',{completed:function(){$('#savecard1 [name=year]').focus();}});
    $('#savecard1 [name=year]').mask('99',{completed:function(){$('#savecard1 [name=cvc]').focus();}});
    $('#savecard1 [name=cvc]').mask('999',{completed:function(){$('#savecard1 button[type=button]').focus();}});
});
    
$('form button[name=card_donor]').click(send_card);
    
function send_card() {
    $.post("ajax/div_bcr.php", $( "form[id=savecard1]" ).serialize())
        .done(function(data) {
            var card = jQuery.parseJSON(data);
            var error_message = card['error_message'];
            var cod_sms = '<input type="hidden" name="new_sms" value=0><input type="hidden" name="delta_sms" value=30><div class="col-sm-12  col-xs-12"><label>Введите код из СМС<br><button type="button" id="new_sms" class="btn btn-info" disabled>Выслать код повторно</button><span class="label label-info" role="alert"><ii id="delta_sms">30</ii> сек.</span></label><input type="text" class="form-control cardnum" name="cod" placeholder="код" required></div>';
            if(card['response_code'] == 1){
                $('#card_donor').html(cod_sms+'<div class="col-sm-7  col-xs-12"><button type="button" name="card_donor" class="btn btn-success">Передать</button></div>');
                sms_timer();
            }else if(card['response_code'] == 7){
                $('#card_donor').html('<div class="col-sm-12  col-xs-12"><div class="alert alert-danger" role="alert">'+error_message+'</div></div>'+cod_sms+'<div class="col-sm-7  col-xs-12"><button type="button" name="card_donor" class="btn btn-success">Передать</button></div>');
                sms_timer();
            }else if(card['response_code'] == 4){
                $('#card_donor').html('<div class="col-sm-12  col-xs-12"><div class="alert alert-success" role="alert">Карта сохранена</div></div><div class="col-sm-7  col-xs-12"><button type="button" name="card_donor" class="btn btn-success">Сохранить карту</button></div>');
            }else{
                $('#card_donor').html('<div class="col-sm-12  col-xs-12"><div class="alert alert-danger" role="alert">'+error_message+'</div></div><div class="col-sm-7  col-xs-12"><button type="button" name="card_donor" class="btn btn-success">Сохранить карту</button></div>');
            }
            $('form button[name=card_donor]').click(send_card);
            $('#new_sms').click(function() {
                $('input[name=new_sms]').val('1');
                $('input[name=cod]').removeAttr('required');
                send_card();
            });
        });
}
function sms_timer(){
    var delta_sms = 30;
    let timerId = setInterval(function(){
        $('#delta_sms').html(delta_sms);
        if(delta_sms == 0){
            $('#new_sms').removeAttr('disabled');
            $('#delta_sms').parent().html('');
            delta_sms = 30;
            clearInterval(timerId);
        }else{
            delta_sms--;
            $('input[name=delta_sms]').val(delta_sms);
        }
    },1000);
}

</script>

<? pagination_main('div_bcr_tasks',function($data){global $mysqli;
$status='';
$count1=mysqli_fetch_array(mysqli_query($mysqli,"SELECT COUNT(*) FROM akcioner WHERE (card!='none' OR card!=NULL) AND (kol_akc!=NULL OR kol_akc!=0)"));
//$count2=mysqli_fetch_array(mysqli_query($mysqli,"SELECT COUNT(*) FROM `akcioner` WHERE `id`<=".(int)$data['next']));
$count2=mysqli_fetch_array(mysqli_query($mysqli,"SELECT COUNT(*) FROM div_bcr_log WHERE task_id = '".$data['id']."';"));
$status=$count2['COUNT(*)']." / ".$count1['COUNT(*)'];

if($count2['COUNT(*)']==$count1['COUNT(*)'])$status.="<br><span class=\"text-success\">Выполнено</span>"; else{
    $status = $status . '<div class="panel-body ajaxcron'.$data['id'].'"><button class="btn btn-default btn-xs" onclick="start('.$data['id'].');">Запустить</button></div>';
}
return '<tr><td>'.(int)$data['id'].'<td>'.number_format($data['amount'],2,'.',' ').' BCR/акция<td>'.htmlspecialchars($data['date']).'<td>'.htmlspecialchars($data['text']).'<td>'.$status;},'<table class="table table-bordered"><thead><th>ID<th>Сумма<th>Дата<th>Комментарий<th>Статус</thead>%list%</table>%pagination%',100,'admin/div_bcr?next=',$_GET['next'],'`id`');
