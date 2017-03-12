<?php
if ($userName == null || $userName == '') {
    $avata = Yii::app()->theme->baseUrl . '/FileManager/avata.png';
} else {
    if ($userName->avatar == '') {
        $avata = Yii::app()->theme->baseUrl . '/FileManager/avata.png';
    } else {
        $avata = $userName->avatar;
    }
}
?>
<div class="bg_overlay"></div>
<div id="menu_panel">
    <div class="profile">
        <a href="<?php echo Yii::app()->baseUrl . '/profile' ?>"><img width="50px" height="60px"
                                                                      src="<?php echo $avata ?>"/></a>
        <div class="profile-name">
            <?php
            if ($userName != null) {
                ?>
                <a href="<?php echo Yii::app()->baseUrl . '/profile' ?>"><?php echo $userName->username ?></a>
                <!--<p>Coin: <span> <?php // echo $userName->fcoin  ?></span></p>-->
                <p><?php echo $userName->usertitle; ?></p>
            <?php } else { ?>
                <a href="#">Chưa đăng nhập</a>
            <?php } ?>
        </div>
    </div>
    <div class="menu1">
        <ul>
            <li>
                <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/home.png"/>
                <a href="<?php echo Yii::app()->baseUrl . '/post' ?>" class="ui-link">Trang chủ</a>
            </li>
            <li>
                <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/buy.png"/>
                <a href="<?php echo Yii::app()->baseUrl . '/post/buy' ?>" class="ui-link">Đăng tin mua xe</a>
            </li>
            <li>
                <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/sell.png"/>
                <a href="<?php echo Yii::app()->baseUrl . '/post/sell' ?>" class="ui-link">Đăng tin bán xe</a>
            </li>
            <li>
                <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/logout.png"/>
                <a href="<?php echo Yii::app()->baseUrl . '/account/logout' ?>" class="ui-link">Đăng xuất</a>
            </li>
            <li>
                <?php
                if (!Yii::app()->session['user_id']) {
                    ?>
                    <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/login.png"/>
                    <a href="<?php echo Yii::app()->baseUrl . '/account/login' ?>" class="ui-link">Đăng Nhập</a>
                <?php } ?>
            </li>
        </ul>
    </div>
</div>
