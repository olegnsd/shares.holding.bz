<div class="panel panel-default">
    <div class="panel-heading">Ваши дивиденды</div>
    <?
    pagination_main('div_log', function ($data) {
        return '<li class="list-group-item"><span class="label label-default pull-right">' . $data['date'] . '</span>' . (float)$data['sum'] . ' руб. ' . htmlspecialchars($data['text']) . '</li>';
    }, '<ul class="list-group">%list%</ul>
 %pagination%', 10, 'divs?next=', $_GET['next'], '`user`=' . $account['id']);
    pagination_main('div_bcr_log', function ($data) {
        return '<li class="list-group-item"><span class="label label-default pull-right">' . $data['date'] . '</span>' . (float)$data['sum'] . ' BCR ' . htmlspecialchars($data['text']) . '</li>';
    }, '<ul class="list-group">%list%</ul>
 %pagination%', 10, 'divs?next=', $_GET['next'], '`user`=' . $account['id']);
    ?></div>
