<?php
//проверка баланса пользователя
function check_bal($card, $mysqli){
    $sum = (int)$_POST['price'] * (int)$_POST['kol'];
    if($curl = curl_init()){
        curl_setopt($curl, CURLOPT_URL, "http://bartercoin.holding.bz/tasks/check_card.php");
        curl_setopt($curl, CURLOPT_POST, True);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array(
        "sum" => $sum,
        "number" => $card,
        "secret" => "erov74rvue",
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
        $balance = curl_exec($curl);
        curl_close($curl);
    }
    if($balance < $sum){
        return $balance;   
    }
    return "OK";
}
    
//выполнение требования
function order_proc($stock_akcioner, $account, $baseHref, $mysqli){
    $stock_id = (int)$_POST['id'];
 
    $stock = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM stock WHERE id='$stock_id';"));
    
    $merchant_id = '6';
    $secret_word = 'CT0NKVyVaxytwxTI';
    $order_id = $stock['id'];
    if($stock['sum'] == 0){
        $order_amount = $stock['count'] * $stock['price'];
    }elseif($stock['sum'] !=0){
        $order_amount = $stock['sum'];
    }
    $sign = md5($merchant_id.$secret_word.$order_id.(float)$order_amount);
    if((($stock['operation'] == 2 && $stock['order'] == 1) || $stock['operation'] == 1) && $stock['res'] == 1){ 
        $paymentUrl = 'https://bartercoin.holding.bz/do/?stock&shop='.$merchant_id.'&id='.$order_id.'&count='.$stock['count'].'&sum='.$order_amount.'&oper='.$stock['operation'].'&card='.$account['card'].'&comment=Перевод '.(int)$order_amount.' BCR на покупку акций на бирже&secret='.$sign.'&return='.$baseHref.'/stock';
        header('Location:'.$paymentUrl);
        die();
    }elseif(($stock['operation'] == 2 && ($stock['order'] == 2 || $stock['order'] == 3)) && $stock['res'] == 1){
        //обновить кол-во акций
        mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)-".$stock['count']." WHERE `id`=".$account['id']."");
        
        mysqli_query($mysqli,"UPDATE `akcioner` SET `kol_akc`=`kol_akc`+".$stock['count']." WHERE `id`=".$stock_akcioner."");
        //обновить состояние операции
        mysqli_query($mysqli, "UPDATE `stock` SET `res`='2' WHERE id='$stock_id';");
        sms($account['phone'], "Вы подтвердили биржевое требование на продажу ".(int)$stock['count']." акций Миллитари Холдинг". $baseHref);
    }
}

//проверить и убрать крайнее требование из стакана, если >10
function stock_10_gt($operation, $query_sub, $mysqli){
    $query = "SELECT COUNT(DISTINCT price) FROM stock WHERE operation='$operation' AND `order`='2'  AND `res`='2'";
    $orders = mysqli_fetch_array(mysqli_query($mysqli, $query));
    if($orders["COUNT(DISTINCT price)"] > 10){
        $query = "SELECT ". $query_sub ."(price) FROM stock WHERE operation='$operation' AND `order`='2'  AND `res`='2'";
        $query_upd = mysqli_fetch_array(mysqli_query($mysqli, $query));
        $query_upd = $query_upd[$query_sub ."(price)"];
        $query = "UPDATE stock SET `order`='3'  WHERE price='$query_upd' AND `order`='2'";
        mysqli_query($mysqli, $query);
    }
}

//проверить и добавить в стакан, если <10
function stock_10_lt($operation, $query_sub, $mysqli){
    $query = "SELECT COUNT(DISTINCT price) FROM stock WHERE operation='$operation' AND `order`='2' AND `res`='2'";
    $orders_count = mysqli_fetch_array(mysqli_query($mysqli, $query));

    if($orders_count["COUNT(DISTINCT price)"] < 10){
        $query = "SELECT DISTINCT price FROM stock WHERE operation='$operation' AND `res`='2' AND `order`!='1' ORDER BY price ". $query_sub ." LIMIT 10";
        $orders_gl = mysqli_query($mysqli, $query);
        while($row = mysqli_fetch_array($orders_gl)){
            $prise_gl = $row['price'];
            $query = "UPDATE stock SET `order`='2' WHERE operation='$operation' AND price='$prise_gl' AND `order` NOT IN ('1','2')  AND `res` NOT IN ('1','3','4')";// AND `order`!='2'
            mysqli_query($mysqli, $query);
        }
    }
}

//удаление заданий
function order_del($baseHref, $mysqli, $stock_akcioner){
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
    if($stock['res'] == 1){//удалить не подтвержденное требование
        //mysqli_query($mysqli, "DELETE FROM stock WHERE id='$stock_id';");
        $contrs_1 = mysqli_query($mysqli, "SELECT `contrag_id` FROM `contragent` WHERE `stock_id`='".  $stock_id ."';");
        while($contr_1 = mysqli_fetch_array($contrs_1)){
            mysqli_query($mysqli, "UPDATE `stock` SET `order`='2' WHERE `id`='". $contr_1['contrag_id'] ."'");
        }
        mysqli_query($mysqli, "DELETE FROM `stock` WHERE `id`='". $stock_id ."'");
        ?>
        <!-- Modal -->
        <div class="modal" id="myModal_info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Удаление требования на бирже</h4>
              </div>
              <div class="modal-body">
                  <div class="alert alert-success" role="alert">Удаление не подтвержденного требования на покупку(продажу) <?=$stock['count']?> акций BCR на сумму <?=$order_amount?> BCR выполнено.</div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
    <?}elseif($stock['operation'] == 2 && $stock['res'] != 3  && $stock['res'] != 1){//удалить требование на продажу с возвратом акций
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
    <?}elseif($stock['operation'] == 1 && $stock['res'] != 3 && $stock['res'] != 1){//удалить требование на покупку с возвратом средств
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
//удалить неподтвержденные более 5 мин                                  
function order_del_5($mysqli){
    $stocks_1 = mysqli_query($mysqli, "SELECT `id`, `date_order`, `contragent` FROM `stock` WHERE `res`='1';");

	while($stock_1 = mysqli_fetch_array($stocks_1)){
	    if(strtotime($stock_1['date_order']) <= (time() - 60 * 5)){
	        $contrs_1 = mysqli_query($mysqli, "SELECT `contrag_id` FROM `contragent` WHERE `stock_id`='".  $stock_1['id'] ."';");
	        while($contr_1 = mysqli_fetch_array($contrs_1)){
	            mysqli_query($mysqli, "UPDATE `stock` SET `order`='2' WHERE `id`='". $contr_1['contrag_id'] ."'");
	        }
	        mysqli_query($mysqli, "DELETE FROM `stock` WHERE `id`='". $stock_1['id'] ."'");
	    }
	}
}
