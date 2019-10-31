<?ob_start();include('inc/config.php');
include('inc/mysql.php');
include('inc/functions.php');
include('inc/auth.php');
include('inc/header.php');
//$auth=1;include('pages/admin/message.php');die();
if($auth){
$st = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT SUM(`count`) FROM stock WHERE akcion_id='". $account['id'] ."' AND operation=2 AND res=2;"));
$kol_all = $account['kol_akc'] + $st['SUM(`count`)'];
$st_price = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT sum, count FROM stock WHERE res='3' AND deal='0' ORDER BY date_compl DESC LIMIT 1;"));
$st_price = round($st_price['sum']/$st_price['count'], 0);
//$data = CBR_XML_Daily_Ru();//Курс валют ЦБ
//$cours_usd = $data->Valute->USD->Value;
//$cours_eur = $data->Valute->EUR->Value;

if(true){//($kol_all)
?>
<div class="row">
<div class="col-xs-12">
<ul class="nav nav-pills">
  <li<?if($_GET['page']==''){?> class="active"<?}?>><a href="/">Главная</a></li>
  <li<?if($_GET['page']=='stock'){?> class="active"<?}?>><a href="stock">Биржа</a></li>
  <!--li><a href="buy">Покупка акций</a></li-->
  <li<?if($_GET['page']=='send'){?> class="active"<?}?>><a href="send">Передача акций</a></li>
  <li<?if($_GET['page']=='divs'){?> class="active"<?}?>><a href="divs">История дивидендов</a></li>
  <li<?if($_GET['page']=='history'){?> class="active"<?}?>><a href="history">История акций</a></li>
  <li<?if($_GET['page']=='settings'){?> class="active"<?}?>><a href="settings">Реквизиты акционера</a></li>
  <!--li<?if($_GET['page']=='2'){?> class="active"<?}?>><a href="#">Messages</a></li-->
<?if($account['is_admin']){?>  <li<?if($_GET['page']=='admin'){?> class="active"<?}?>><a href="admin">Администрирование</a></li><?}?>
</ul><br>
</div>
<div class="col-md-8 col-xs-12">
<?if($_GET['page']==''){echo(file_get_contents('pages/index'));}
elseif($_GET['page']=='divs'){include('pages/divs.php');}
elseif($_GET['page']=='settings'){include('pages/divr.php');}
elseif($_GET['page']=='stock'){include('pages/stock.php');}
//elseif($_GET['page']=='buy'){include('pages/buy.php');}
elseif($_GET['page']=='send'){include('pages/send.php');}
elseif($_GET['page']=='history'){include('pages/history.php');}
elseif(($_GET['page']=='admin') & ($account['is_admin'])){?><hr><ul class="nav nav-pills">
  <li<?if($_GET['admin']==''){?> class="active"<?}?>><a href="admin">Акционеры</a></li>
  <li<?if($_GET['admin']=='stock'){?> class="active"<?}?>><a href="admin/stock">Биржевые операции</a></li>
  <li<?if($_GET['admin']=='pay'){?> class="active"<?}?>><a href="admin/pay">Выплата дивидендов Киви</a></li>
  <li<?if($_GET['admin']=='div_bcr'){?> class="active"<?}?>><a href="admin/div_bcr">Выплата дивидендов BCR</a></li>
  <li<?if($_GET['admin']=='sms'){?> class="active"<?}?>><a href="admin/sms">Рассылка СМС</a></li>
  <li<?if($_GET['admin']=='message'){?> class="active"<?}?>><a href="admin/message">Сообщение акционерам</a></li>
  <li<?if($_GET['admin']=='polls'){?> class="active"<?}?>><a href="admin/polls">Голосования</a></li>
</ul><br>
<?if($_GET['admin']=='')include('pages/admin/list.php');
elseif($_GET['admin']=='stock')include('pages/admin/stock.php');
elseif($_GET['admin']=='pay')include('pages/admin/pay.php');
elseif($_GET['admin']=='div_bcr')include('pages/admin/div_bcr.php');
elseif($_GET['admin']=='sms')include('pages/admin/sms.php');
elseif($_GET['admin']=='message')include('pages/admin/message.php');
?>

<?

}?>
</div>
<?$em=mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT SUM(`kol_akc`) FROM akcioner LIMIT 1;"));?>
<div class="col-md-4 col-xs-12">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <b style="font-size:40px;"><?=number_format($kol_all,0,'.',' ')?></b>
            <br>акций<br>
            <? if($st['SUM(`count`)'] > 0){?>
               (доступно <?=number_format($account['kol_akc'],0,'.',' ')?>)
                <br>
                (продаются на бирже <?=number_format($st['SUM(`count`)'],0,'.',' ')?>)
            <?}?>
        </div>
    </div>

<div class="panel panel-default"><div class="panel-body text-center"><b style="font-size:40px;"><?=number_format($kol_all/$em['SUM(`kol_akc`)']*100,5,'.',' ')?>%</b><br>ваша доля</div>
<?$perc=$kol_all/$em['SUM(`kol_akc`)']*100;if($perc<1)$perc=1;?><div class="progress" style="margin-bottom: 0;border-top-left-radius: 0;border-top-right-radius: 0;">
  <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" style="width: <?=number_format($perc,5,'.','')?>%">
  </div>
</div>
</div>


<div class="panel panel-default">
    <div class="panel-body text-center">
        <b style="font-size:40px;"><?=number_format($em['SUM(`kol_akc`)'],0,'.',' ')?>
        </b>
        <br>объём эмиссии<br>
            последняя сделка: <b style="font-size:30px;"><?=$st_price?> BCR</b>
    </div>
</div> 

<div class="panel panel-default">
<div class="panel-heading">Ваши дивиденды</div>
<ul class="list-group">
<?
pagination_main('div_log',function($data){
return '<li class="list-group-item"><span class="label label-default pull-right">'.$data['date'].'</span>'.(float)$data['sum'].' руб. '.htmlspecialchars($data['text']).'</li>';},'%list%',10,'admin?next=',$_GET['next'],'`user`='.$account['id']);
pagination_main('div_bcr_log',function($data){
return '<li class="list-group-item"><span class="label label-default pull-right">'.$data['date'].'</span>'.(float)$data['sum'].' BCR '.htmlspecialchars($data['text']).'</li>';},'%list%',10,'admin?next=',$_GET['next'],'`user`='.$account['id']);
?>
    
    <li class="list-group-item"><a href="divs">Подробнее</a></li>
  </ul>
</div>


</div>
</div>

<?}else{?><br><br><div class="alert alert-info">У вас нет акций. Доступ в личный кабинет ограничен</div><br><br><?}}else{
?>

<div class="row">

<div class="col-md-6 col-md-offset-3">
<div class="panel panel-default">
<div class="panel-heading">Вход</div>
<div class="panel-body">

<form method="post"> 
<div class="form-group"> <input id="phone1" type="text" name="phone" class="form-control" placeholder="Номер телефона"> </div>

<div class="row"><div class="col-md-4"><div class="form-group"> <input type="number" name="pin" class="form-control" placeholder="КОД"> </div></div>
<div class="col-md-8" id="btn1"><a class="btn btn-block btn-info submit-button" onclick="if($(this).hasClass('disabled'))return false;$('#btn1 a').addClass('disabled');$('.ajax1').load('ajaxreg.php?btn=1&phone='+encodeURI($('#phone1').val()),function(){$('#btn1 a').removeClass('disabled');});return false;">Забыли код? Выслать новый</a></div>
</div>

<div class="ajax1"></div>
<?if($_POST[phone] | $_POST[pin]){?><div class="alert alert-danger">Введены неверные данные</div><?}?>

<button type="submit" class="btn btn-success submit-button">Войти</button> </form>
</div></div></div>



</div>
<?
}

include('inc/footer.php');echo ob_get_clean();?>
