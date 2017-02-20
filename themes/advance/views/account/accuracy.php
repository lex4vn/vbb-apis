<?php
if(!isset($user_id)){
    $this->redirect(Yii::app()->homeurl . 'account');
}
$subscriber = Subscriber::model()->findByPk($user_id);
if ($subscriber == null) {
  $this->redirect(Yii::app()->homeurl . 'account');
}
?>
<?php
    $userid_encrypt = CUtils::encrypt($user_id, "hocde");
?>
<div class="web_body">
    <div class="" style="text-align: center">
        <img src="<?php echo Yii::app()->theme->baseUrl . '/FileManager/avata.png';?>"/>
             <p>Xin chào: <?php echo $subscriber->username?></p>
    </div>
    <form method="post" action="<?php echo $this->createUrl("/account/accuracy"); ?>">
        <div class="form-group">
            <label>Nhập Mã xác thực</label>
        </div>
        <div class="form-group" id = "notifi-email">
            <input type="hidden" class="form-control" name="userId" id="userId" value="<?php echo $userid_encrypt?>">
            <input type="text" class="form-control" name="accuracy" id="accuracy" placeholder="Mã xác thực">
          </div>
        <p style="color: #f00">Bạn vui lòng nhập đúng mã xác thực để kích hoạt tài khoản.</p>
        <?php echo Yii::app()->user->getFlash('responseAccuracy'); ?><br/>
        <button type="submit" class="btn btn-default" name="submit">Đồng ý</button>
        <a href="<?php echo Yii::app()->baseUrl .'/account/smsagain?user_id='.$userid_encrypt?>">Nhận lại mã xác thực</a>
    </form>
</div>