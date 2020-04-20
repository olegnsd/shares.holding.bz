<h2>Внебиржевая передача акицй</h2><? if ($_GET['to']) { ?>
    <a href="send" class="btn btn-default"><i class="fa fa-chevron-left"> назад</i></a>
    <hr>
    <? $data = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM `akcioner` WHERE phone='" . phone($_GET['to']) . "' LIMIT 1;"));
    if ($data['id'] > 0) {
        if ($data['id'] != $account['id']) {
            $_POST['amount'] = (int)$_POST['amount'];
            if (($_POST['amount'] > 0) & ($_POST['amount'] <= $account['kol_akc'])) {
                $text = "Купля/продажа акций. Сумма в руб: " . (int)$_POST['price'];
                if ($_POST['type'] == 2) $text = 'Дарение акций';
                mysqli_query($mysqli, "INSERT INTO `send_log` (`from`, `to`, `amount`, `text`)
VALUES ('" . (int)$account['id'] . "', '" . (int)$data['id'] . "', '" . (int)$_POST['amount'] . "', '" . mysqli_escape_string($mysqli, $text) . "');");
                mysqli_query($mysqli, "UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)-" . (int)$_POST['amount'] . " WHERE `id`=" . (int)$account['id'] . "");
                mysqli_query($mysqli, "UPDATE `akcioner` SET `kol_akc`=IFNULL(`kol_akc`,0)+" . (int)$_POST['amount'] . " WHERE `id`=" . (int)$data['id'] . "");
                sms($data['phone'], "Вы получили " . (int)$_POST['amount'] . " акций Милитари Холдинг от " . $account['name'] . ". Остаток: " . ((int)$data['kol_akc'] + (int)$_POST['amount']) . " акций " . $baseHref);
                sms($account['phone'], "Вы отправили " . (int)$_POST['amount'] . " акций Милитари Холдинг акционеру " . $data['name'] . ". Остаток: " . ((int)$account['kol_akc'] - (int)$_POST['amount']) . " акций " . $baseHref);
//$account=mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT * FROM users WHERE id='".(int)$account['id']));
                ?>
                <div class="alert alert-success">Акции успешно отправлены</div><?
            } else {
                ?>
                <form method="post">
                    <div class="form-group">
                        <label>Акционер</label>
                        <input type="text" value="<?= htmlspecialchars($data['name']); ?>" readonly
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Количество акций для передачи</label>
                        <input type="number" name="amount" min=0 max=<?= (int)$account['kol_akc']; ?> value="0"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Тип сделки</label>
                        <select name="type" class="form-control"
                                onchange="$('.kol').hide();if($(this).val()==1)$('.kol').show();">
                            <option value=1>Продажа
                            <option value=2>Дарение
                        </select>
                    </div>
                    <div class="form-group kol">
                        <label>Сумма сделки в рублях</label>
                        <input type="number" name="price" value="0" min=0 class="form-control">
                    </div>
                    <div class="form-group">
                        <button type="submit" onclick="return confirm('Передать акции?');" class="btn btn-success">
                            Передать акции
                        </button>
                    </div>
                </form>
            <? }
        } else {
            ?>
            <div class="alert alert-danger">Вы не можете отправить акции себе</div><? }
    } else {
        ?>
        <div class="alert alert-danger">Акционер не найден</div><? } ?>
<? } else { ?>
    <form>
        <div class="form-group">
            <label>Введите номер телефона акционера, которому хотите передать акции</label>
            <input type="text" name="to" class="form-control">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success">Выбрать акционера</button>
        </div>
    </form><? } ?>
