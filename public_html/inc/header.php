<!DOCTYPE html>
<html lang="ru">
  <head><base href="<?=$baseHref;?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Акционеры холдинга</title>

    <!-- Bootstrap -->
<!--
    <link href="css/bootstrap.min.css" rel="stylesheet">
-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
<script src="js/jquery.min.js"></script>
<script src="js/jquery.maskedinput.min.js"></script>

  </head>
  <body>
<div class="container">

<div class="page-header"><?if($auth){?><a href="?logout" class="btn btn-danger pull-right"><i class="fa fa-sign-out"></i> Выйти</a><?}?>
  <h1>Личный кабинет акционера <small>Милитари Холдинг</small></h1>
</div>
