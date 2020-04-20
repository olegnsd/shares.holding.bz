<?php

ini_set('display_errors', 0);

if(!isset($_POST['stat']) || $_POST['stat'] != 'kj54n9gub9249'){
    die();
}

include('inc/config.php');
include('inc/mysql.php');

$query = "SELECT COUNT(id) FROM `akcioner`";

//$myecho = json_encode($query);
//`echo " query_task: "  $myecho >>/tmp/qaz`;

$shares_stat = mysqli_query($mysqli, $query);
$shares_stat = mysqli_fetch_assoc($shares_stat);
$shares_akcioner = $shares_stat['COUNT(id)'];

$kol_akc=mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT SUM(`kol_akc`) FROM akcioner LIMIT 1;"));
$kol_akc = $kol_akc['SUM(`kol_akc`)'];
$st_price = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT sum, count FROM stock WHERE res='3' AND deal='0' ORDER BY date_compl DESC LIMIT 1;"));
$st_price = round($st_price['sum']/$st_price['count'], 0);
$shares_capital = $kol_akc * $st_price;

$shares_stat = array(
	'shares_akcioner' => $shares_akcioner,
    'shares_capital' => $shares_capital
	);
echo(json_encode($shares_stat));
