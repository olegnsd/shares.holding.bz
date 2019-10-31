<?if($_GET['id']){?><a href="admin" class="btn btn-default"><i class="fa fa-chevron-left"> назад</i></a><hr><?


if($_POST['editphone']){
if(((int)$_GET['id'])==$account['id'])$_POST['is_admin']=1;
$phone=phone($_POST['editphone']);
if((substr($phone,0,1)==7) & strlen($phone)==11){
mysqli_query($mysqli,"UPDATE `akcioner` SET `name`='".mysqli_escape_string($mysqli,$_POST['name'])."',`comment`='".mysqli_escape_string($mysqli,$_POST[comment])."',`phone`='".mysqli_escape_string($mysqli,$phone)."',`qiwi`='".mysqli_escape_string($mysqli,$_POST['qiwi'])."',`address`='".mysqli_escape_string($mysqli,$_POST['address'])."',`is_admin`='".(int)$_POST['is_admin']."' WHERE `id`=".(int)$_GET['id']."");
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
<div class="form-group">
<label>Почтовый адрес (с индексом)</label>
<textarea class="form-control" name="address"><?=htmlspecialchars($data['address']);?></textarea>
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
}else{if($_POST['newphone']){
$phone=phone($_POST['newphone']);
if((substr($phone,0,1)==7) & strlen($phone)==11){
mysqli_query($mysqli,"INSERT INTO `akcioner` (`id`, `name`, `phone`) VALUES (NULL, '".mysqli_escape_string($mysqli, $_POST['name'])."', '".mysqli_escape_string($mysqli, $phone)."');");
if(mysqli_insert_id($mysqli)){header('Location: '.$baseHref.'admin?id='.mysqli_insert_id($mysqli));die();}else{?><div class="alert alert-danger">Ошибка при записи в базу</div><?}}else{?><div class="alert alert-danger">Неверный формат телефона</div><?}}

if($_GET['delete']){
$data=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='".(int)$_GET['delete']."' LIMIT 1;"));
if(($data['id']>0) & ($data['kol_akc']==0) & ($data['id']!=$account['id'])){
mysqli_query($mysqli, "DELETE FROM `akcioner` WHERE `akcioner`.`id` = ".(int)$_GET['delete']);
header('Location: '.$baseHref.'admin?next='.(int)$_GET['next']);die();
}

}?>
<form method=post class="row">
<div class="form-group col-md-6 col-xs-12">
<label>Имя</label>
<input type="text" class="form-control" name="name">
</div>
<div class="form-group col-md-6 col-xs-12">
<label>Телефон</label>
<input type="text" class="form-control" name="newphone">
</div>
<div class="form-group col-xs-12">
<button type="submit" class="btn btn-success">Добавить акционера</button>
</div>
</form>
<?
$sort3=' <a href="admin?sort=3"><i class="fa fa-chevron-up"></i></a>';
$sort2=' <a href="admin?sort=2"><i class="fa fa-chevron-up"></i></a>';
if($_GET['sort']==3){$order='`seen` DESC';$sort3=' <a class="text-danger" href="admin?sort=3"><i class="fa fa-chevron-up"></i></a>';}
if($_GET['sort']==2){$order='`kol_akc` DESC';$sort2=' <a class="text-danger" href="admin?sort=2"><i class="fa fa-chevron-up"></i></a>';}
 pagination_main('akcioner',function($data){global $mysqli;global $sort2;global $sort3;
$admin='';
if($data['is_admin'])$admin='<span class="text-danger">(админ)</span>';
$delete='';
if($data['kol_akc']==0)$delete='<a href="admin?delete='.$data['id'].'&next='.(int)$_GET['next'].'" onclick="return confirm(\'Удалить?\');" class="btn btn-xs btn-block btn-danger"><i class="fa fa-trash"></i></a>';
return '<tr><td>'.htmlspecialchars($data['name']).' '.$admin.'<td>'.number_format($data['kol_akc'],0,'.',' ').'<td>'.htmlspecialchars($data['phone']).'<br>'.htmlspecialchars($data['qiwi']).'<td>'.$data['seen'].'<td><a href="admin?id='.$data['id'].'" class="btn btn-xs btn-block btn-default"><i class="fa fa-edit"></i></a>'.$delete;},'<table class="table table-bordered"><thead><th>Имя<th>Кол-во акций'.$sort2.'<th>Телефон<br>QIWI<th>Последний вход'.$sort3.'<th>&nbsp;</thead>%list%</table>%pagination%',100,'admin?sort='.(int)$_GET['sort'].'&next=',$_GET['next'],'`id`',$order);}
