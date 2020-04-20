<?
function phone($phone) {
$resPhone = preg_replace("/[^0-9]/", "", $phone);
if (substr($resPhone,0,1) == 8) $resPhone[0] = 7;
return $resPhone;
}
include('ssms_su.php');
function sms($phone,$text){
$email = "hrm-militcorp";
$password = "63ghjz1y";
$r = smsapi_push_msg_nologin($email, $password, $phone, $text, array("sender_name"=>'EasySMS24'));
//echo('r: ' . json_encode($r));
if($r[1])return true; else return false;
}

function usersms($phone,$text,$email,$password){
$r = smsapi_push_msg_nologin($email, $password, $phone, $text, array("sender_name"=>'EasySMS24'));
if($r[1])return true; else return false;
}

 
function pagination_main($table,$func,$template,$num,$url,$getnext,$query='`id`',$order=''){
if($template=='')$template='%list%%pagination%';
global $mysqli;
$result=mysqli_query($mysqli,"SELECT * FROM `".mysqli_escape_string($mysqli,$table)."` WHERE ($query) AND ".pagination_query($getnext,0,$order)." LIMIT ".(int)($num+1).";");
if(!$result | !mysqli_num_rows($result)){$template='<div class="alert alert-warning">Ничего не найдено</div>';}
$k=0;$list='';
while($data=mysqli_fetch_assoc($result)){
if($k<$num){
$list.=$func($data);
}$k++;
if($k==($num+1))$last=$data['id'];
}
$template=str_replace("%list%", $list, $template);
$off=1;if($getnext==-1)$off=2;
$previd1=mysqli_query($mysqli,"SELECT id FROM `".mysqli_escape_string($mysqli,$table)."` WHERE ($query) AND ".pagination_query($getnext,1,$order)." LIMIT ".(int)$off.",".(int)($num)); 
if($previd1)while($previd2=mysqli_fetch_assoc($previd1)){$previd=$previd2;}
$template=str_replace("%pagination%", pagination($url,$previd[id],$last,$getnext), $template);
echo($template);
}


function pagination($url,$previd,$last,$getnext){

if($getnext==-1){
$showFirst=TRUE;$showPrevious=TRUE;
$temp=$last;$last=$first;$first=$temp;
}else{if($previd>0&$getnext>0){$showFirst=TRUE;$showPrevious=TRUE;}
if($last){$showNext=TRUE;$showLast=TRUE;}}
$return='<ul class="pager">
%list% 
</ul>';
if($showFirst){$return1=str_replace('%link%',$url,'<li><a href="%link%">Начало</a>');}
if($showPrevious){$return1.=str_replace('%link%',$url.$previd,'<li><a href="%link%">&larr; Предыдущая</a><li>&nbsp;&nbsp;&nbsp;');}
if($showNext){$return1.=str_replace('%link%',$url.$last,'<li>&nbsp;&nbsp;&nbsp;<li>    <a href="%link%">Следующая &rarr;</a>');}
if($showLast){$return1.=str_replace('%link%',$url.'-1','<li><a href="%link%">Конец</a>');}
return stripcslashes(str_replace('%list%',$return1,$return));
}
function pagination_query($getnext,$rev=FALSE,$order){
if($order)$order=$order.', ';
if(!$rev){if($getnext==-1){
return '`id` ORDER BY '.$order.'`id`';
}else{if($getnext>0)return '`id`<='.(int)$getnext.' ORDER BY '.$order.'`id` DESC';else return '`id` ORDER BY '.$order.'`id` DESC';}
}else{
if($getnext==-1){
return '`id` ORDER BY `id`';
}else{if($getnext>0)return '`id`>='.(int)$getnext.' ORDER BY '.$order.'`id`';else return '`id` ORDER BY '.$order.'`id`';}
}
}

function CBR_XML_Daily_Ru() {
    $json_daily_file =  __DIR__.'/daily.json';//'/home/shares/daily.json';
    if (!is_file($json_daily_file) || filemtime($json_daily_file) < time() - 3600*24) {
        if ($json_daily = file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js')) {
            file_put_contents($json_daily_file, $json_daily);
        }
    }

    return json_decode(file_get_contents($json_daily_file));
}

function count2_count1($count2, $count1) {
    if (!$count1) {
        throw new Exception('Деление на ноль.');
    }
    return $count2/$count1;
}

//вывод ошибок sql
function sql_err($mysqli, $fun){
    $myecho = json_encode(mysqli_error($mysqli), JSON_UNESCAPED_UNICODE);
    if(strlen($myecho) > 5)`echo " $fun : "  $myecho >>/home/shares/tmp/qaz_sql_err`;
    return;
}
?>
