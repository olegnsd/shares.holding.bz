<?if(!$auth)die();
if($_POST['text']){
#file_put_contents('/home/wizardgrp/web/shares.holding.bz/public_html/pages/index',$_POST['text']);
file_put_contents('pages/index',$_POST['text']);

//$fp = fopen('pages/index', 'w');
//fwrite($fp, $_POST['text']);
//fclose($fp);
}
?><form method=post>
<div class="form-group">
<label>Сообщение акционерам в формате HTML на главной</label>
<textarea class="form-control" style="height:370px;" name="text"><?=htmlspecialchars(file_get_contents('pages/index'));?></textarea>                                                         
</div>
<div class="form-group">
<button type="submit" class="btn btn-success">Сохранить</button>
</div>
</form>
