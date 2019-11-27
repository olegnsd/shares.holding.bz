<?php 

date_default_timezone_set('Asia/Baghdad');//Europe/Moscow
setlocale(LC_ALL, 'ru_RU.utf8');
$mysql_conf['host']   = "localhost";
$mysql_conf['port']   = 3306;
$mysql_conf['user']   = ""; // Имя пользователя
$mysql_conf['pass']   = ""; // Пароль БД
$mysql_conf['dbname'] = ""; // Имя БД
$configLoaded=TRUE;
$domain=$_SERVER['SERVER_NAME'];//domain.ltd
$folder="/";//начало и конец - "/"!
$baseHref="https://".$domain.$folder;
$bartercoin = "http://bartercoin.holding.bz";

$qiwi_bot_login="79260001026"; // Логин Qiwi
$qiwi_bot_pass=""; // Пароль Qiwi
$qiwi_token="";// Токен Qiwi
