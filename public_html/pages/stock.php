<?

//session_start();

require_once('inc/functions_stock.php');

$stock_akcioner = mysqli_fetch_array(mysqli_query($mysqli, "SELECT value FROM settings WHERE name='stock_id';"));
$stock_akcioner = $stock_akcioner['value'];

//схлопывание заявок
//купля
$bid_max = mysqli_fetch_array(mysqli_query($mysqli, "SELECT price FROM stock WHERE `order`!='1' AND `res`='2' AND operation='1' ORDER BY `price` DESC LIMIT 1"));
$bid_max = $bid_max['price'];
//продажа
$ask_min = mysqli_fetch_array(mysqli_query($mysqli, "SELECT price FROM stock WHERE `order`!='1' AND `res`='2' AND operation='2' ORDER BY `price` ASC LIMIT 1"));
$ask_min = $ask_min['price'];
//спред
$spred = round(($ask_min - $bid_max)/$ask_min*100, 1);//(МАХ-MIN)/MAX*100%

if($_GET['sort']){
    if($_SESSION['sort'. (int)$_GET['sort']] == 'ASC'){
        $_SESSION['sort'. (int)$_GET['sort']] = 'DESC';
    }else{
        $_SESSION['sort'. (int)$_GET['sort']] = 'ASC';
    }
    $_SESSION['sorted'] = (int)$_GET['sort'];
}

//проверка карты
if(strlen($account['card']) != 16){?>
    <div class="alert alert-info">
        Вам нужно <a href="/settings" class="text-success">ввести номер карты</a> бартеркоин.<br><br>
        Нет карты, <a href="https://bartercoin.holding.bz/create"  class="text-danger">зарегистрируйтесь на сайте Бартеркоин</a>
    </div>
<?
die();
}

//удалить требование
$stock = mysqli_fetch_array(mysqli_query($mysqli, "SELECT akcion_id FROM stock WHERE id='". (int)$_POST['del_id'] ."';"));
if((int)$_POST['del_id'] > 0 && $stock['akcion_id'] == $account['id']){
    order_del($baseHref, $mysqli, $stock_akcioner);
}

//удалить неподтвержденные более 5 мин
order_del_5($mysqli);

//выполнить требование
$order_1 = mysqli_fetch_array(mysqli_query($mysqli, "SELECT COUNT(*) FROM stock WHERE akcion_id='$account[id]' AND res='1';"));
if((int)$_POST['id']>0 & $order_1['COUNT(*)'] == 1){
    order_proc($stock_akcioner, $account, $baseHref, $mysqli);
}

$max=$account['kol_akc'];
$price=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `settings` WHERE name='buyprice' LIMIT 1;"));
$price = $price['value'];

//проверка требования
$sec_word = "RTbwd53vddw";
$hash1 = md5($sec_word.$account['id']);
if($_POST['hash'] != $hash1 & (int)$_POST['kol']>0 & (int)$_POST['price']>0){
    $err = "ошибка";
}
$pr_max = round($st_price * 1.25, 0);
$pr_min = round($st_price * 0.75, 0);
$pr_max = $pr_max < $ask_min? $ask_min: $pr_max;
$pr_min = $pr_min > $bid_max? $bid_max: $pr_min;
if((int)$_POST['kol'] > 0 & $account['is_admin']!=1 & (((int)$_POST['price'] > $pr_max & $_POST['operation'] == 'ask') | ((int)$_POST['price'] < $pr_min & $_POST['operation'] == 'bid'))){
    $pr_echo = (int)$_POST['price'];
    $err = "Цена акций $pr_echo BCR в Вашей заявке превышает 25% от курса. Допустимый диапазон $pr_min - $pr_max BCR";
}
//проверка счета бартеркоин
if(((int)$_POST['kol']>0) & (int)$_POST['price']>0 & $_POST['operation'] == 'bid'){
    $balance = check_bal($account['card'], $mysqli);
    if($balance != "OK")$err = "Сумма заявки на покупку " . (int)$_POST['kol'] * (int)$_POST['price'] . " BCR превышает баланс на Вашей карте $balance BCR";
}

//проверка кол-ва заявок
if(((int)$_POST['kol']>0) & (int)$_POST['price']>0 & $order_1['COUNT(*)'] >= 1){
    $err = "Есть неподтвержденное требование";
}

if(((int)$_POST['kol']>0) & (int)$_POST['price']>0 & ((int)$_POST['kol']<=$max | $_POST['operation'] == 'bid') & !isset($err)){
    $count = (int)$_POST['kol'];
    $price = (int)$_POST['price'];
    $order_id = $account['id'];
    //1-купить(bid), 2-продать(ask)
    $operation = ($_POST['operation'] == 'bid') ? 1 : (($_POST['operation'] == 'ask') ? 2 : '');
    $oper_contr = ($_POST['operation'] == 'bid') ? 2 : (($_POST['operation'] == 'ask') ? 1 : '');
    $info_treb = ($operation == '1') ? "<b>купить " : (($operation == '2') ? "<b>продать " : '');
    
    //проверка типа требования order(1-рыночный, 2-лимитный, 3-условный)
    if($oper_contr == 1){
        $query_sub = " price>='$price' ORDER BY `price` DESC";
    }elseif($oper_contr == 2){
        $query_sub = " price<='$price' ORDER BY `price` ASC";
    }
    $query = "SELECT * FROM stock WHERE operation='$oper_contr' AND `order`!='1' AND `res`='2' AND" . $query_sub; // AND `akcion_id`!='$order_id'
    $orders = mysqli_query($mysqli, $query);

    while($row[] = mysqli_fetch_array($orders)){$order = 1;}
    $prc_clm = array_column($row, 'price');//колонка с прайсами
    $cnt_clm = array_column($row, 'count');//колонка с кол-м акций
    $contr_id = array_column($row, 'id');//колонка с id требований
    $prc_unq = array_unique($prc_clm);//уникальные значения прайсов
    $prc_unq = array_values($prc_unq);//сделать ключи по порядку
    $prc_clm_cnt = count($prc_clm) - 1;
    $count_rest = $count;
    $count_rest2 = $count;
    $i = 0;

    foreach($prc_clm as $key=>$prc_clm_cur){
        if($prc_unq[$i] == $prc_clm_cur){
            $cnt_tmp = $cnt_tmp + $cnt_clm[$key];
        }
        if($prc_unq[$i] != $prc_clm_cur){
            $contragent['price'][] = $prc_unq[$i];
            $contragent['best'][] = '2';//1-no, 2-yes
            if(($count_rest - $cnt_tmp) > 0){
                $contragent['count'][] = $cnt_tmp;
            }else{
                $contragent['count'][] = $count_rest;
            }
            $count_rest = $count_rest - $cnt_tmp;
            $cnt_tmp = (int)$cnt_clm[$key];
            $i++;
        }
        if($prc_clm_cnt == $key & $count_rest > 0){
            $contragent['price'][] = $prc_unq[$i];
            $contragent['best'][] = '2';//1-no, 2-yes
            if(($count_rest - $cnt_tmp) > 0){
                $contragent['count'][] = $cnt_tmp;
            }else{
                $contragent['count'][] = $count_rest;
            }
        }
        //найти контрагентов по сделке (потом добавить еще после проведения сделки)
        if($count_rest2 > 0){
            $contr_id_b[] = $contr_id[$key]; //id контрагентов, для базы
            if(($count_rest2 - $cnt_clm[$key]) >= 0){
                $count_b[] = $cnt_clm[$key]; //кол-во акций контрагента, для базы
                $sum_b[] = $prc_clm_cur * $cnt_clm[$key]; //сумма покупки
            }elseif(($count_rest2 - $cnt_clm[$key]) < 0){
                $count_b[] = $count_rest2; //кол-во акций контрагента, для базы
                $sum_b[] = $prc_clm_cur * $count_rest2; //сумма покупки
            }
            $prc_clm_b[] = $prc_clm_cur; //price контрагента, для базы 
            $count_rest2 = $count_rest2 - $cnt_clm[$key];
        }
        if($count_rest <= 0)break;
        
    }
    
    $contr_sum = $count;
    if(isset($contragent)){
        $contr_sum = array_sum($contragent['count']);
    }
    if($order != 1){
        if($operation == 1){
            $query_sub = "MIN";//"MIN";//" price>'$price'";
        }elseif($operation == 2){
            $query_sub = "MAX";//"MAX";//" price<'$price'";
        }
        $query = "SELECT ". $query_sub. "(price) FROM stock WHERE operation='$operation' AND `order`='2' AND `res`='2'";
        
        $orders_max_min = mysqli_fetch_array(mysqli_query($mysqli, $query));
        
        $query = "SELECT COUNT(DISTINCT price) FROM stock WHERE operation='$operation' AND `order`='2'  AND `res`='2'";
        $orders_count = mysqli_fetch_array(mysqli_query($mysqli, $query));

        if(($orders_max_min[$query_sub."(price)"] >= $price || $orders_count["COUNT(DISTINCT price)"] < 10) && $query_sub == "MAX" && $orders_max_min[$query_sub."(price)"] != null){
            $order = 2;
        }elseif(($orders_max_min[$query_sub."(price)"] <= $price || $orders_count["COUNT(DISTINCT price)"] < 10) && $query_sub == "MIN" && $orders_max_min[$query_sub."(price)"] != null){
            $order = 2;
        }else{
            $order = 3;
        }
    }
     
    if($order == 1)$order_t = "Рыночное";
    if($order == 2)$order_t = "Отложенное";
    if($order == 3)$order_t = "Условное";
    $info_treb .= $count. " акций по ". $price ." BCR за акцию, всего ". $count * $price . " BCR</b>, определено как ". $order_t;
    if($contr_sum < $count){
        $info_treb_t = " и отложенное";
        $cuntr_sum_d = $count - $contr_sum;
    }
    
    $contragent_b = '';
    if(isset($contr_id_b) & isset($count_b)){
        $contragent_b = json_encode(array(0=>$contr_id_b, 1=>$count_b));
    }

    $res = 1;//не подтвержденная операция
    
    if($order == 1){
        $sum_b = array_sum($sum_b);
        $price_b = 0;
        $coun_b = array_sum($count_b);
    }else{
        $sum_b = 0;
        $price_b = $price;
        $coun_b = $count;
    }
    
    //занести требование в таблицу
    mysqli_query($mysqli,"INSERT INTO `stock` (`akcion_id`, `operation`, `count`, `price`, `sum`, `order`, `res`, `contragent`, `date_order`) VALUES('$order_id', '$operation', '$coun_b', '$price_b', '$sum_b', '$order', '$res', '$contragent_b', CURRENT_TIMESTAMP);");
    sql_err($mysqli, 'INSERT INTO stock');//deal
    
    $last_id = mysqli_insert_id($mysqli);
    foreach($contr_id_b as $key=>$contr_deal){
        mysqli_query($mysqli,"INSERT INTO `contragent` (`stock_id`, `contrag_id`, `count_akc`) VALUES('$last_id', '$contr_deal', '$count_b[$key]');");
        sql_err($mysqli, 'INSERT INTO contragent');

        mysqli_query($mysqli,"UPDATE `stock` SET `deal`='$last_id', `order`='1' WHERE `id`='$contr_deal';");
        sql_err($mysqli, 'UPDATE stock');

        if($count_b[$key] < $cnt_clm[$key]){
            //изменить кол-во акций на количество в сделке
            mysqli_query($mysqli,"UPDATE `stock` SET `count`='$count_b[$key]' WHERE `id`='$contr_deal';");
            sql_err($mysqli, 'UPDATE stock2');
            //скопировать запись с остатком акций и сделать лимитными
            mysqli_query($mysqli, "INSERT INTO `stock` (`akcion_id`, `operation`, `price`, `count`, `order`, `res`, `contragent`, `date_order`, `date_compl`) SELECT `akcion_id`, `operation`, `price`, `count`, `order`, `res`, `contragent`, `date_order`, `date_compl` FROM `stock` WHERE `id`='$contr_deal'; ");
            sql_err($mysqli, 'INSERT INTO stock2');

            $last2_id = mysqli_insert_id($mysqli);
            $d_count = $cnt_clm[$key]-$count_b[$key];
            mysqli_query($mysqli,"UPDATE `stock` SET `count`='$d_count', `deal`='0', `order`='2' WHERE `id`='$last2_id';");
            sql_err($mysqli, 'UPDATE stock3');
        }
    }

}elseif((int)$_POST['kol'] > $max & $_POST['operation'] == 'ask'){
    $err = "У Вас недостаточно акций";
}

//проверить и убрать крайнее требование из стакана, если >10
stock_10_gt(1, "MIN", $mysqli);
stock_10_gt(2, "MAX", $mysqli);

//проверить и добавить в стакан, если <10
stock_10_lt(1, "DESC", $mysqli);
stock_10_lt(2, "ASC", $mysqli);

//собрать стакан
$query = "SELECT DISTINCT price FROM stock WHERE operation='2' AND `order`='2' AND `res`='2' ORDER BY price DESC";
$prices = mysqli_query($mysqli, $query);

$glasses = array();
while($row = mysqli_fetch_array($prices)){
    $query = "SELECT SUM(count) FROM stock WHERE price=". $row['price'] ." AND operation='2' AND `order`='2'  AND `res`='2'";
    $res = mysqli_fetch_array(mysqli_query($mysqli, $query));
    $glasses[] = array(0=>'2', 1=>$row['price'], 2=>$res['SUM(count)']);
}

$query = "SELECT DISTINCT price FROM stock WHERE  operation='1' AND `order`='2' AND `res`='2' ORDER BY price DESC";
$prices = mysqli_query($mysqli, $query);

while($row = mysqli_fetch_array($prices)){
    $query = "SELECT SUM(count) FROM stock WHERE price=". $row['price'] ." AND operation='1' AND `order`='2'  AND `res`='2'";
    $res = mysqli_fetch_array(mysqli_query($mysqli, $query));
    $glasses[] = array(0=>'1', 1=>$row['price'], 2=>$res['SUM(count)']);
}
?>

<h2>Биржа Акций</h2>

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

<? if(isset($order) & !isset($err)){?>
<div class="col-md-12 col-xs-12">
    <div class="panel panel-default">
      <!-- Default panel contents -->
      <div class="panel-heading">
        <div class="alert alert-info">
        Требование <?=$info_treb?> <?=$info_treb_t?><br>
        </div>
      </div>

      <? if($order == 1){?>
      <!-- Table -->
      <div class='table-responsive'>
      <table class="table table-hover table-condensed">
          <tr>
            <th></th>
            <th>Количество</th>
            <th>Цена за акцию, BCR</th>
            <th>Всего, BCR</th>
          </tr>

        <? 
        $sum = array();
        $count_all = array_sum($contragent['count']);
        foreach($contragent['price'] as $key=>$id_contr){?>
          <tr>
            <td></td>
            <td>
                <?=$contragent['count'][$key]?>
            </td>
            <td>
                <?=$contragent['price'][$key];
                if($contragent['best'][$key] == 2){?>
                    <div class='label label-success' role='alert'>Лучшая цена</div>
                <?}?> 
            </td>
            <td>
                <?
                $sum[$key] = $contragent['count'][$key] * $contragent['price'][$key];
                echo($sum[$key]);
                ?> 
            </td>
          </tr>
        <?}
        $sum_all = array_sum($sum);      
        ?>
          <tr>
            <td><b>Всего</b></td>
            <td><b><?=$count_all?></b></td>
            <td></td>
            <td><b><?=$sum_all?></b></td>
          </tr>
      </table>
      </div>
      <?}?>
      <div class="panel-footer">
        <? if(isset($cuntr_sum_d)){?>
            <div class='alert alert-warning' role='alert'>Остаток <?=$cuntr_sum_d?> акций можете затребовать по другой цене</div>
        <?}?>
        <? if(($operation == 2 && $order == 1) || $operation == 1){  
        ?>
        <div class="form-group">
            <form method="post">
                <input type="hidden" name="id" value="<?=$last_id?>">
                <button type="submit" class="btn btn-success">Перейти на страницу завершения операции(действительно 5 мин)</button>
            </form>
        </div>
        <?}elseif($operation == 2){?>
            <form method="post">
                <input type="hidden" name="id" value="<?=$last_id?>">
                <button type="submit" class="btn btn-success">Подтвердите операцию(действительно 5 мин)</button>
            </form>
        <?}?>
        <br>
        <form method="post">
            <input type="hidden" name="del_id" value="<?=$last_id?>">
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal_del" name="butt_issuse" del_id="<?=$last_id?>">Удалить</button>
        </form>
      </div>
    </div>   
</div>
<?}elseif(isset($err)){?>
    <div class="alert alert-danger">
        <?=$err?>
    </div>
<?}?>
    
<?
if($order_1['COUNT(*)'] == 0){?>    
<div class="col-md-4 col-xs-12">
    <div class="panel panel-default">
      <!-- Default panel contents -->
        <div class="panel-heading">
            Карта BCR для операций: <span name='bcr'><?=$account['card']?></span>
            <button class="btn btn-info btn-xs" id="check_card">Проверить</button>
            <div class="" id="check_result"></div>
        </div>
        <form method=post>
			<input type="hidden" name="hash" value="<?=$hash1?>">
            <div class="form-group">
                <label class="text-center">Операция</label>
                <select name="operation" class="form-control">
                    <option value="bid">Купить акции
                    <option value="ask">Продать акции
                </select>
            </div>
            <div class="form-group">
                <label>Количество акций</label>
                <input type="number" id="number" value="1" min=1 max="" class="form-control" name="kol">

            </div>
            <div class="form-group">
                <label>
                    Цена за акцию, BCR<br>
                    Допустимая отложенная <?=$pr_min?> - <?=$pr_max?> BCR
                </label>
                <input name="price" type="number" id="price" value="<?=$ask_min;?>" min="1" max="" class="form-control">
            </div>
            <div class="form-group">
                <label>Общая сумма, BCR</label>
                <div id="price_all" class="form-control"><?=$price;?></div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-success">Выполнить требование</button>
            </div>
        </form>
    </div>
</div>
<?}?>
<div class="col-md-2 col-xs-12"></div>
<div class="col-md-5 col-xs-12">
    <div class="panel panel-default">
      <!-- Default panel contents -->
      <div class="panel-heading">Cтакан котировок акций. Спред: <?=$spred?> %</div>

      <!-- Table -->
      <div class='table-responsive'>
      <table class="table table-hover table-condensed">
          <tr>
            <th>Купля</th>
            <th>Цена, BCR</th>
            <th>Продажа</th>
          </tr>

        <?foreach($glasses as $glass){?>
          <tr <? if($glass['0'] == '1') echo("class='success'");
                if($glass['0'] == '2') echo("class='danger'");?>>
            <td>
                <? if($glass['0'] == '1') echo($glass['2'])?>
            </td>
            <td>
                <?=$glass['1']?>  
            </td>
            <td>
                <? if($glass['0'] == '2') echo($glass['2'])?>  
            </td>
          </tr>
        <?}?>
      </table>
      </div>
    </div>
</div>

<?
for($i = 1; $i <= 11; $i++){
    if($_SESSION['sort'. $i] == 'ASC'){
        $class[] = 'up';
    }else{
        $class[] = 'down';
    }
    
}

$sort_up_dwn_1=' <a href="stock?sort=1"><i class="fa fa-chevron-'. $class[0] .'"></i></a>';
$sort_up_dwn_2=' <a href="stock?sort=2"><i class="fa fa-chevron-'. $class[1] .'"></i></a>';
$sort_up_dwn_3=' <a href="stock?sort=3"><i class="fa fa-chevron-'. $class[2] .'"></i></a>';
$sort_up_dwn_4=' <a href="stock?sort=4"><i class="fa fa-chevron-'. $class[3] .'"></i></a>';
$sort_up_dwn_5=' <a href="stock?sort=5"><i class="fa fa-chevron-'. $class[4] .'"></i></a>';
//$sort_up_dwn_6=' <a href="stock?sort=6"><i class="fa fa-chevron-'. $class[5] .'"></i></a>';
$sort_up_dwn_7=' <a href="stock?sort=7"><i class="fa fa-chevron-'. $class[6] .'"></i></a>';
$sort_up_dwn_8=' <a href="stock?sort=8"><i class="fa fa-chevron-'. $class[7] .'"></i></a>';
//$sort_up_dwn_9=' <a href="admin/stock?sort=9"><i class="fa fa-chevron-'. $class[8] .'"></i></a>';
$sort_up_dwn_10=' <a href="stock?sort=10"><i class="fa fa-chevron-'. $class[9] .'"></i></a>';
$sort_up_dwn_11=' <a href="stock?sort=11"><i class="fa fa-chevron-'. $class[10] .'"></i></a>';

$query = array('ORDER BY id', 'ORDER BY akcion_id', 'ORDER BY operation', 'ORDER BY count', 'ORDER BY price', '', 'ORDER BY `order`', 'ORDER BY res', '', 'ORDER BY date_order', 'ORDER BY date_compl');

$query1 = 'ORDER BY id DESC';
if(isset($_SESSION['sorted'])){
    $query1 = $query[(int)$_SESSION['sorted']-1] ." ". $_SESSION['sort'. (int)$_SESSION['sorted']];
}
if(array_key_exists((int)$_GET['sort']-1, $query)){
    $query1 = $query[(int)$_GET['sort']-1] ." ". $_SESSION['sort'. (int)$_GET['sort']];
}

$akc_orders = mysqli_query($mysqli, "SELECT * FROM stock WHERE akcion_id='$account[id]' ". $query1 .";");
?>

<div class="col-md-12 col-xs-12">
    <div class="panel panel-default">
      <!-- Default panel contents -->
      <div class="panel-heading">
        <div class="alert alert-info">Все Ваши требования</div>
      </div>
      <!-- Table -->
      <div class='table-responsive'>
      <table class="table table-hover table-condensed">
          <tr>
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
              <? if(($akc_order['operation'] == 2 || $akc_order['operation'] == 1) && $akc_order['res'] == 1){ //($akc_order['operation'] == 2 && $akc_order['order'] == 1)
                    ?>
                    <div class="form-group">
                        <form method="post">
                            <input type="hidden" name="id" value="<?=$akc_order['id']?>">
                            <button type="submit" class="btn btn-success">Перейти на страницу<br> завершения операции<br>(действительно 5 мин)</button>
                        </form>
                        <br>
                        <form method="post" id="issuse<?=$akc_order['id']?>">
                            <input type="hidden" name="del_id" value="<?=$akc_order['id']?>">
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal_del" name="butt_issuse" del_id="<?=$akc_order['id']?>">Удалить</button>
                        </form>
                    </div>
                <?}?>
                <? if($akc_order['res'] != 3  && $akc_order['res'] != 1){?>
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
    $('#number').change(function(){
        $('#price_all').html($('#price').val() * $(this).val())
    });
    $('#price').change(function(){
        $('#price_all').html($('#number').val() * $(this).val())
    });
    
jQuery(function($) {

$.mask.definitions['~']='[+-]';
$('span[name=bcr]').mask('9999 9999 9999 9999');
});

$('#check_card').click(function(){
    $.post( "inc/check_card.php", {card:"<?=$account['card']?>"} , function(data){
        if(data == "OK"){
            $("#check_result").html("Карта бартеркоин рабочая");
            $("#check_result").removeClass("alert alert-danger");
            $("#check_result").addClass("alert alert-success");
        }else if(data == "ERR"){
            $("#check_result").html("Карта бартеркоин не рабочая");
            $("#check_result").removeClass("alert alert-success");
            $("#check_result").addClass("alert alert-danger");
        }
    });
});

    $('button[name=butt_issuse]').each(function() {
        $(this).on('click', function () {
            del_id = $(this).attr("del_id");
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
    
    $('select[name=operation]').change(function(){
        if($(this).val() == 'bid')$('input[name=price]').val(<?=$ask_min?>);
        if($(this).val() == 'ask')$('input[name=price]').val(<?=$bid_max?>);
    });
</script>

<?


?>
