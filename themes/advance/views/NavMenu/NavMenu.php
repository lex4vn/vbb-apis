<?php
if ($userName == null || $userName == '') {
    $avata = Yii::app()->theme->baseUrl . '/FileManager/avata.png';
} else {
    if ($userName->url_avatar != '') {
        if ($userName->password == 'faccebook' || $userName->password == 'Google') {
            $avata = $userName->url_avatar;
        } else {
            $avata = IPSERVER . $userName->url_avatar;
        }
    } else {
        $avata = Yii::app()->theme->baseUrl . '/img/giaovien.png';
    }
}
?>
<div class="bg_overlay"></div>
<div id="menu_panel">
    <?php
    if ($userName != null && $userName->status == 3) {
        ?>
        <div class="profile">
            <a href="<?php echo Yii::app()->baseUrl . '/profile' ?>"><img width="50px" height="60px" src="<?php echo $avata ?>" /></a>
            <a href="<?php echo Yii::app()->baseUrl . '/profile' ?>"><?php echo $userName->lastname . ' ' . $userName->firstname ?></a>
            <p>Tài khoản: <?php if ($userName->type == 1) {
    } elseif ($userName->type == 2) {
        echo '';
    } ?></p>
        </div>
        <div class="menu1">
            <ul>
                <li>
                    <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/home.png" />
                    <a href="<?php echo Yii::app()->baseUrl . '/' ?>" class="ui-link">Trang chủ</a>
                </li>
                <li>
    <?php
    if (Yii::app()->session['user_id']) {
        ?>
                        <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/logout.png" />
                        <a href="<?php echo Yii::app()->baseUrl . '/account/logout' ?>" class="ui-link">Đăng xuất</a>
                    <?php } else { ?>
                        <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/login.png" />
                        <a href="<?php echo Yii::app()->baseUrl . '/account/login' ?>" class="ui-link">Đăng Nhập</a>
                        <!--<a href="https://facebook.com/dialog/oauth?client_id=1054223384618565&redirect_uri=http://hocde.onedu.vn/account/loginface" class="ui-link">Đăng Nhập fb</a>-->
        <?php } ?>
                </li>
            </ul>
        </div>
    <?php
} else if ($userName != null && $userName->status == 4) {
    ?>
        <div class="profile">
            <a href="<?php echo Yii::app()->baseUrl . '/profile' ?>"><img width="50px" height="60px" src="<?php echo $avata ?>" /></a>
            <a href="<?php echo Yii::app()->baseUrl . '/profile' ?>"><?php echo $userName->lastname . ' ' . $userName->firstname ?></a>
            <p>Tài khoản: <?php if ($userName->type == 1) {
        echo 'Học sinh';
    } elseif ($userName->type == 2) {
        echo 'Giáo viên';
    } ?></p>
        </div>
        <div class="menu1">
            <ul>
                <li>
                    <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/home.png" />
                    <a href="<?php echo Yii::app()->baseUrl . '/' ?>" class="ui-link">Trang chủ</a>
                </li>
                <li>
    <?php
    if (Yii::app()->session['user_id']) {
        ?>
                        <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/logout.png" />
                        <a href="<?php echo Yii::app()->baseUrl . '/account/logout' ?>" class="ui-link">Đăng xuất</a>
        <?php } else { ?>
                        <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/login.png" />
                        <a href="<?php echo Yii::app()->baseUrl . '/account/login' ?>" class="ui-link">Đăng Nhập</a>
                        <!--<a href="https://facebook.com/dialog/oauth?client_id=1054223384618565&redirect_uri=http://hocde.onedu.vn/account/loginface" class="ui-link">Đăng Nhập fb</a>-->
    <?php } ?>
                </li>
            </ul>
        </div>
    <?php
} else {
    ?>
        <div class="profile">
            <a href="<?php echo Yii::app()->baseUrl . '/profile' ?>"><img width="50px" height="60px" src="<?php echo $avata ?>" /></a>
            <div class="profile-name">
                <?php
                if ($userName != null) {
                    ?>
                    <a href="<?php echo Yii::app()->baseUrl . '/profile' ?>"><?php echo $userName->lastname . ' ' . $userName->firstname ?></a>
                    <!--<p>Coin: <span> <?php // echo $userName->fcoin  ?></span></p>-->
                    <p>Tài khoản: <?php if ($userName->type == 1) {
                        echo 'Học sinh';
                    } elseif ($userName->type == 2) {
                        echo 'Giáo viên';
                    } ?></p>
    <?php } else { ?>
                    <a href="#">Chưa đăng nhập</a>
                    <?php } ?>
            </div>
        </div>
        <div class="menu1">
            <ul>
                <li>
                    <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/home.png" />
                    <a href="<?php echo Yii::app()->baseUrl . '/' ?>" class="ui-link">Trang chủ</a>
                </li>
                <li>
                    <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/buy.png" />
                    <a href="<?php echo Yii::app()->baseUrl . '/post/buy' ?>" class="ui-link">Đăng tin mua xe</a>
                </li>
                <li>
                    <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/sell.png" />
                    <a href="<?php echo Yii::app()->baseUrl . '/post/sell' ?>" class="ui-link">Đăng tin bán xe</a>
                </li>

                <?php if ($userName != null) { ?>
                <li>
                    <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/caidat.png" />
                    <a href="<?php echo Yii::app()->baseUrl . '/setting/group' ?>" class="ui-link">Cài đặt</a>
                </li>
                <?php } ?>

                <li>
                    <?php
                    if (!Yii::app()->session['user_id']) {
                    ?>
                       <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/login.png" />
                        <a href="<?php echo Yii::app()->baseUrl . '/account/login' ?>" class="ui-link">Đăng Nhập</a>
                    <?php } ?>
                </li>
            </ul>
        </div>
<?php } ?>
</div>
