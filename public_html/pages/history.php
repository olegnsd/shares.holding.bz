<div class="panel panel-default">
<div class="panel-heading">История акций</div>
<?
pagination_main('send_log',function($data){global $mysqli; global $account;
if($data['from']==$account['id'])$u=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='".(int)$data['to']."' LIMIT 1;")); else $u=mysqli_fetch_array(mysqli_query($mysqli,"SELECT * FROM `akcioner` WHERE id='".(int)$data['from']."' LIMIT 1;"));
if($data['from']==$account['id'])$do="вы передали ".$u['name']; else $do="вы получили от ".$u['name'];
return '<li class="list-group-item"><span class="label label-default pull-right">'.$data['date'].'</span>'.(float)$data['amount'].' акций '.$do.' ('.htmlspecialchars($data['text']).')</li>';},'<ul class="list-group">%list%</ul>
 %pagination%',10,'history?next=',$_GET['next'],'`from`='.$account['id'].' OR `to`='.$account['id']);
?></div>
