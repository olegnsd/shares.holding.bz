<?php
session_start();

$stock_akcioner = mysqli_fetch_array(mysqli_query($mysqli, "SELECT value FROM settings WHERE name='stock_id';"));
$stock_akcioner = $stock_akcioner['value'];

if($_GET['sort']){
    if($_SESSION['sort'. (int)$_GET['sort']] == 'ASC'){
        $_SESSION['sort'. (int)$_GET['sort']] = 'DESC';
    }else{
        $_SESSION['sort'. (int)$_GET['sort']] = 'ASC';
    }
    $_SESSION['sorted'] = (int)$_GET['sort'];
}

//запрос баланса карты биржи BCR
    
$curl = curl_init();
$url = array(
    'balance' => '1',
    'shop' => '6'
);

$url = http_build_query($url);
$url = 'http://bartercoin.holding.bz/do/stock_del.php?'. $url;
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$balance_bcr = curl_exec($curl);
curl_close($curl);

$myecho = json_encode($balance_bcr);
`echo " balance_bcr dell: "  $myecho >>/tmp/qaz`;

if(isset($_GET['akc'])){
    $akcioner = mysqli_fetch_row(mysqli_query($mysqli, "SELECT name,kol_akc,wallet,card,email,phone,is_admin,address,comment,seen FROM akcioner WHERE id=". (int)$_GET['akc'] ." LIMIT 1;"));
    
    $myecho = json_encode($akcioner);
    `echo " akcioner: "  $myecho >>/tmp/qaz`;
}

if((int)$_POST['del_id']>0){
    $stock_id = (int)$_POST['del_id'];
    $stock = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM stock WHERE id='$stock_id';"));
    $akcioner = mysqli_fetch_array(mysqli_query($mysqli, "SELECT card FROM akcioner WHERE id=". $stock['akcion_id'] ." LIMIT 1;"));
    
    $merchant_id = '6';
    $secret_word = 'CT0NKVyVaxytwxTI';
    $order_id = $stock['id'];
    if($stock['sum'] == 0){
        $order_amount = $stock['count'] * $stock['price'];
    }elseif($stock['sum'] !=0){
        $order_amount = $stock['sum'];
    }
    $sign = md5($merchant_id.$secret_word.$order_id.(float)$order_amount);
    if($stock['operation'] == 2 && $stock['res'] != 3 && $stock['res'] != 1){//удалить требование на продажу с возвратом акций
        
        $myecho = json_encode($stock_id);
        `echo " stock_id: "  $myecho >>/tmp/qaz`;
        //обновить кол-во акций
        mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)+".$stock['count']." WHERE `id`=".$stock['akcion_id']."");
        
        $res = mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=`kol_akc`-".$stock['count']." WHERE `id`=".$stock_akcioner."");
        
        mysqli_query($mysqli, "DELETE FROM stock WHERE id='$stock_id';");?>
        
        <!-- Modal -->
        <div class="modal" id="myModal_info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Удаление требования на бирже</h4>
              </div>
              <div class="modal-body">
                  <div class="alert alert-success" role="alert">Удаление требования на продажу <?=$stock['count']?> акций BCR на сумму <?=$order_amount?> BCR выполнено. Акции возвращены акционеру</div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
    <?}
    if($stock['operation'] == 1 && $stock['res'] != 3 && $stock['res'] != 1){//удалить требование на покупку с возвратом средств
        
        $myecho = json_encode($stock_id);
        `echo " stock_id: "  $myecho >>/tmp/qaz`;
        
        $curl = curl_init();
        $url = array(
            'shop' => $merchant_id,
            'id' => $order_id,
            'count' => $stock['count'],
            'sum' => $order_amount,
            'oper' => '5',
            'card' => $akcioner['card'],
            'comment' => 'Возврат '.(int)$order_amount.' BCR на покупку акций на бирже',
            'secret' => $sign,
            'return' => $baseHref.'/stock'
        );

        $url = http_build_query($url);
        $url = 'http://bartercoin.holding.bz/do/stock_del.php?'. $url;
        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $data1 = curl_exec($curl);
        curl_close($curl);
        
        $myecho = json_encode($url);
        `echo " url dell: "  $myecho >>/tmp/qaz`;
        $myecho = json_encode($data1);
        `echo " data1 dell: "  $myecho >>/tmp/qaz`;
        
        if($data1 == "RETURN_OK"){
            mysqli_query($mysqli, "DELETE FROM stock WHERE id='$stock_id';");

        }?>
        <!-- Modal -->
        <div class="modal" id="myModal_info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Удаление требования на бирже</h4>
              </div>
              <div class="modal-body">
                <? if($data1 == "RETURN_OK"){?>
                  <div class="alert alert-success" role="alert">Удаление требования на покупку <?=$stock['count']?> акций BCR на сумму <?=$order_amount?> BCR выполнено. BCR возвращены на карту акционера</div>
                <?}elseif($data1 == "RETURN_ERR"){?>
                    <div class="alert alert-danger" role="alert">Удаление требования на покупку <?=$stock['count']?> акций BCR на сумму <?=$order_amount?> BCR не выполнено. Ошибка: BCR не возвращены на карту акционера</div>
                <?}else{?>
                    <div class="alert alert-danger" role="alert">Ошибка удаления</div>
                <?}?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        
    <?}
}

for($i = 1; $i <= 11; $i++){
    if($_SESSION['sort'. $i] == 'ASC'){
        $class[] = 'up';
    }else{
        $class[] = 'down';
    }
    
}

$sort_up_dwn_1=' <a href="admin/stock?sort=1"><i class="fa fa-chevron-'. $class[0] .'"></i></a>';
$sort_up_dwn_2=' <a href="admin/stock?sort=2"><i class="fa fa-chevron-'. $class[1] .'"></i></a>';
$sort_up_dwn_3=' <a href="admin/stock?sort=3"><i class="fa fa-chevron-'. $class[2] .'"></i></a>';
$sort_up_dwn_4=' <a href="admin/stock?sort=4"><i class="fa fa-chevron-'. $class[3] .'"></i></a>';
$sort_up_dwn_5=' <a href="admin/stock?sort=5"><i class="fa fa-chevron-'. $class[4] .'"></i></a>';
//$sort_up_dwn_6=' <a href="admin/stock?sort=6"><i class="fa fa-chevron-'. $class[5] .'"></i></a>';
$sort_up_dwn_7=' <a href="admin/stock?sort=7"><i class="fa fa-chevron-'. $class[6] .'"></i></a>';
$sort_up_dwn_8=' <a href="admin/stock?sort=8"><i class="fa fa-chevron-'. $class[7] .'"></i></a>';
//$sort_up_dwn_9=' <a href="admin/stock?sort=9"><i class="fa fa-chevron-'. $class[8] .'"></i></a>';
$sort_up_dwn_10=' <a href="admin/stock?sort=10"><i class="fa fa-chevron-'. $class[9] .'"></i></a>';
$sort_up_dwn_11=' <a href="admin/stock?sort=11"><i class="fa fa-chevron-'. $class[10] .'"></i></a>';

$query = array('ORDER BY id', 'ORDER BY akcion_id', 'ORDER BY operation', 'ORDER BY count', 'ORDER BY price', '', 'ORDER BY `order`', 'ORDER BY res', '', 'ORDER BY date_order', 'ORDER BY date_compl');

$query1 = 'ORDER BY id DESC';
if(isset($_SESSION['sorted'])){
    $query1 = $query[(int)$_SESSION['sorted']-1] ." ". $_SESSION['sort'. (int)$_SESSION['sorted']];
}
if(array_key_exists((int)$_GET['sort']-1, $query)){
    $query1 = $query[(int)$_GET['sort']-1] ." ". $_SESSION['sort'. (int)$_GET['sort']];
}

$myecho = json_encode($query1);
`echo " query: "  $myecho >>/tmp/qaz`;

//if($query == '')$query = 'ORDER BY id DESC';

$akc_orders = mysqli_query($mysqli, "SELECT * FROM stock ". $query1 .";");

$stock_akciy = mysqli_fetch_array(mysqli_query($mysqli,"SELECT kol_akc FROM `akcioner` WHERE id='$stock_akcioner' LIMIT 1;"));
$stock_akciy = $stock_akciy['kol_akc'];

if(isset($_GET['akc'])){
    $title = array('Имя', 'Акций', 'Кошелек', 'Карта BCR', 'Email', 'Телефон', 'Админ', 'Адрес', 'Коментарий', 'Послед. вход');
?>
    <div class="col-md-12 col-xs-12">
        <div class="panel panel-default">
          <!-- Default panel contents -->
          <div class="panel-heading">
            <div class="alert alert-info">Данные Акционера <a href="<?=$baseHref?>admin/stock" class="btn btn-success btn-xs">Назад</a></div>
          </div>
          <!-- Table -->
          <div class='table-responsive'>
          <table class="table table-hover table-condensed">
              <tr>
                <th></th>
                <th></th>
              </tr>
            <? 
            foreach($akcioner as $key => $field){?>
              <tr>
                <td><?=$title[$key]?></td>
                <td><?=$field?></td>
              </tr>

            <?}
            ?>
          </table>
          </div>
        </div>   
    </div>
    <br>
    <a href="<?=$baseHref?>admin/stock" class="btn btn-success btn-xs">Назад</a>
    <br><br>
<?
    exit;
}
?>

<!-- Modal -->
<div class="modal" id="myModal_del" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Удаление требования на бирже</h4>
      </div>
      <div class="modal-body">
        <label class="alert-link" id="del_info"></label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
        <button type="button" id='send_butt' del_id="" name="send_butt" class="btn btn-danger" >Подтвердить</button>
      </div>
    </div>
  </div>
</div>

<div class="col-md-12 col-xs-12">
    <div class="panel panel-default">
      <!-- Default panel contents -->
      <div class="panel-heading">
        <div class="alert alert-info">Биржевые требования. Баланс на карте биржи BCR: <?=$balance_bcr?>. Акций на продаже: <?=$stock_akciy?> шт.</div>
      </div>
      <!-- Table -->
      <div class='table-responsive'>
      <table class="table table-hover table-condensed">
          <tr>
            <th>id операц</th>
            <th>id акцион</th>
            <th>Операция</th>
            <th>Количество акций</th>
            <th>Цена за акцию, BCR</th>
            <th>Всего, BCR</th>
            <th>Статус</th>
            <th>Состояние</th>
            <th>Действие</th>
            <th>Дата требов.</th>
            <th>Дата сделки</th>
          </tr>
          <tr>
            <td><?=$sort_up_dwn_1?></td>
            <td><?=$sort_up_dwn_2?></td>
            <td><?=$sort_up_dwn_3?></td>
            <td><?=$sort_up_dwn_4?></td>
            <td><?=$sort_up_dwn_5?></td>
            <td><?$sort_up_dwn_6?></td>
            <td><?=$sort_up_dwn_7?></td>
            <td><?=$sort_up_dwn_8?></td>
            <td><?$sort_up_dwn_9?></td>
            <td><?=$sort_up_dwn_10?></td>
            <td><?=$sort_up_dwn_11?></td>
          </tr>

        <? 
        foreach($akc_orders as $akc_order){?>
          <tr <? if($akc_order['res'] == 1)echo("class='warning'");
                 if($akc_order['res'] == 2)echo("class='info'");
                if($akc_order['res'] == 3)echo("class='success'");
                if($akc_order['res'] == 4)echo("class='danger'");
              ?>
              >
            <td><?=$akc_order['id']?></td>
              <td>
                  <a href="<?=$baseHref?>admin/stock?akc=<?=$akc_order['akcion_id']?>"><?=$akc_order['akcion_id']?></a>
              </td>
            <td id="oper<?=$akc_order['id']?>">
              <? if($akc_order['operation'] == 1)echo("Купить");
                if($akc_order['operation'] == 2)echo("Продать");
              ?>
            </td>
            <td id="count<?=$akc_order['id']?>">
                <?=$akc_order['count']?>
            </td>
            <td>
                <? if($akc_order['price'] != 0)echo($akc_order['price']);
					if($akc_order['price'] == 0)echo("средняя:". round($akc_order['sum']/$akc_order['count'], 2));
                ?>
            </td>
            <td id="sum<?=$akc_order['id']?>">
                <? if($akc_order['price'] != 0)echo($akc_order['count'] * $akc_order['price']);
                    if($akc_order['price'] == 0)echo($akc_order['sum']);
                ?>
            </td>
            <td>
                <? if($akc_order['order'] == 1)echo("Рыночная");
                    if($akc_order['order'] == 2)echo("Отложенная");
                    if($akc_order['order'] == 3)echo("Условная");
                ?>  
            </td>
            <td>
                <? if($akc_order['res'] == 1)echo("Не подтверждена");
                    if($akc_order['res'] == 2)echo("Подтверждена");
                    if($akc_order['res'] == 3)echo("Выполнена");
                    if($akc_order['res'] == 4)echo("Ошибка");
                ?>
            </td>
            <td>
              <? if($akc_order['res'] != 3 && $akc_order['res'] != 1){?>
                    <form method="post" id="issuse<?=$akc_order['id']?>">
                        <input type="hidden" name="del_id" value="<?=$akc_order['id']?>">
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal_del" name="butt_issuse" del_id="<?=$akc_order['id']?>">Удалить</button>
                    </form>
                <?}?>
            </td>
            <td><?=$akc_order['date_order']?></td>
            <td>
                <?
                    if(($akc_order['date_compl']) != 0)echo($akc_order['date_compl']);
                ?>
            </td>
          </tr>
        <?}
        ?>
      </table>
      </div>
    </div>   
</div>
<script> 
    $('button[name=butt_issuse]').each(function() {
        $(this).on('click', function () {
            del_id = $(this).attr("del_id");
//            alert(del_id);
            oper = $('#oper'+del_id).html();
            count = $('#count'+del_id).html();
            sum = $('#sum'+del_id).html();
            $('#del_info').html("Удаление: "+oper+" "+count+" акций на сумму "+sum+" BCR");
            $('#send_butt').attr("del_id", del_id);
        });
    });
    $('#send_butt').on('click', function () {
        del_id = $(this).attr("del_id");
        $('#myModal_del').modal('hide');
        $('#issuse'+del_id).submit();
    });
    
    $( document ).ready(function() {
        $('#myModal_info').modal('show');
    });
</script>
