<?/*if($_GET['id']){?><a href="admin" class="btn btn-default"><i class="fa fa-chevron-left"> назад</i></a><hr><?


if($_POST['editphone']){
if(((int)$_GET['id'])==$account['id'])$_POST['is_admin']=1;
$phone=phone($_POST['editphone']);
if((substr($phone,0,1)==7) & strlen($phone)==11){
mysqli_query($mysqli,"UPDATE `akcioner` SET `name`='".mysqli_escape_string($mysqli,$_POST['name'])."',`comment`='".mysqli_escape_string($mysqli,$_POST[comment])."',`phone`='".mysqli_escape_string($mysqli,$phone)."',`qiwi`='".mysqli_escape_string($mysqli,$_POST['qiwi'])."',`is_admin`='".(int)$_POST['is_admin']."' WHERE `id`=".(int)$_GET['id']."");
?><div class="alert alert-success">Изменения сохранены</div><?}else{?><div class="alert alert-danger">Неверный формат телефона</div><?}
}


$data=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='".(int)$_GET['id']."' LIMIT 1;"));
if($data[id]==0){?><div class="alert alert-danger">Акционер не найден</div><?}else{?>
<form method=post>
<div class="form-group">
<label>Имя</label>
<input type="text" class="form-control" name="name" value="<?=htmlspecialchars($data['name']);?>">
</div>
<div class="form-group">
<label>Телефон</label>
<input type="text" class="form-control" name="editphone" value="<?=htmlspecialchars($data['phone']);?>">
</div>
<div class="form-group">
<label>QIWI телефон</label>
<input type="text" class="form-control" name="qiwi" value="<?=htmlspecialchars($data['qiwi']);?>">
</div>
<?if($data['id']!=$account['id']){?><div class="form-group">
<label>Администратор</label>
<?if($data['id']!=$account['id'])?><select class="form-control" name="is_admin">
<option value=0>Нет
<option value=1<?if($data['is_admin']){?> SELECTED<?}?>>Да
</select>
</div><?}?>
<div class="form-group">
<label>Комментарий (видят только админы)</label>
<textarea class="form-control" name="comment"><?=htmlspecialchars($data['comment']);?></textarea>
</div>
<div class="form-group">
<button type="submit" class="btn btn-success">Сохранить</button>
</div>
</form>
<?}
}else*/{
if($_POST['text']){

mysqli_query($mysqli,"INSERT INTO `sms_tasks` (`id`, `blank`, `text`) VALUES (NULL, '".(int)$_POST['blank']."', '".mysqli_escape_string($mysqli, $_POST['text'])."');");
if(mysqli_insert_id($mysqli)){header('Location: '.$baseHref.'admin/sms');die();}else{?><div class="alert alert-danger">Ошибка при записи в базу</div><?}}

if($_GET['delete']){
$data=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `sms_tasks` WHERE id='".(int)$_GET['delete']."' LIMIT 1;"));

mysqli_query($mysqli, "DELETE FROM `sms_tasks` WHERE `id` = ".(int)$_GET['delete']);
header('Location: '.$baseHref.'admin/sms?next='.(int)$_GET['next']);die();


}?>
<form method=post class="row">
<div class="form-group col-md-9 col-xs-12">
<label>СМС</label>
<textarea class="form-control" name="text"></textarea>
</div>
<div class="form-group col-md-3 col-xs-12">

<label><input type="checkbox" value="1" name="blank"> Посылать при 0 акций</label>
</div>
<div class="form-group col-xs-12">
<button type="submit" class="btn btn-success">Добавить задачу на отправку СМС</button>
</div>
</form>
<div class="panel panel-default">
<div class="panel-heading">Обработка задач</div>
<div class="panel-body ajaxcron"><button class="btn btn-default btn-xs" onclick="start();">Начать обработку задач</button></div>
</div>
<script>
function start(){
$('.ajaxcron').text('начинаем...');
$('.ajaxcron').load('/smscron.php');
}
</script>
<? pagination_main('sms_tasks',function($data){global $mysqli;
$status='';
if($data['status']==1)$status="<span class=\"text-success\">Выполнено</span>"; else{
$count1=mysqli_fetch_array(mysqli_query($mysqli,"SELECT COUNT(*) FROM `akcioner`"));
$count2=mysqli_fetch_array(mysqli_query($mysqli,"SELECT COUNT(*) FROM `akcioner` WHERE `id`<=".(int)$data['next']));
$status=$count2['COUNT(*)']." / ".$count1['COUNT(*)'];
}
$blank="Нет";
if($data['blank'])$blank='Да';
return '<tr><td>'.(int)$data['id'].'<td>'.htmlspecialchars($data['date']).'<td>'.htmlspecialchars($data['text']).'<td>'.$blank.'<td>'.$status.'<td><a href="admin/sms?delete='.$data['id'].'&next='.(int)$_GET['next'].'" onclick="return confirm(\'Удалить?\');" class="btn btn-xs btn-block btn-danger"><i class="fa fa-trash"></i></a>';},'<table class="table table-bordered"><thead><th>ID<th>Дата<th>СМС<th>Посылать при 0 акций<th>Статус<th>&nbsp;</thead>%list%</table>%pagination%',100,'admin/sms?next=',$_GET['next'],'`id`');}
