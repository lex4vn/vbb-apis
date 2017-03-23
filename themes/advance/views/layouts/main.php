<?php
    $this->widget("application.widgets.Header", array());
?>
<?php
    $requestUrl = Yii::app()->request->getUrl() . '/' . Yii::app()->controller->action->id;
    //$currentUrl = Yii::app()->request->getUrl();
?>
<!DOCTYPE HTML>
<html class="ui-mobile">
<head id="Head1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8"/>
    <meta http-equiv="encoding" content="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta content="width=device-width, initial-scale=1" name="viewport" id="viewport"/>
    <title><?php echo $this->titlePage; ?></title>
    <link rel="icon" href="<?php echo Yii::app()->theme->baseUrl . '/FileManager/favicon.png' ?>"/>
    <!-- Bootstrap -->
    <link href="<?php echo Yii::app()->theme->baseUrl ?>/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo Yii::app()->theme->baseUrl ?>/css/bootstrap-select.css" rel="stylesheet"/>
    <link href="<?php echo Yii::app()->theme->baseUrl ?>/css/reset.css" rel="stylesheet"/>
    <link href="<?php echo Yii::app()->theme->baseUrl ?>/css/blueimp-gallery.min.css" rel="stylesheet"/>
    <link href="<?php echo Yii::app()->theme->baseUrl ?>/Plugin/Fancybox/fancybox.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl ?>/css/style.css"/>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <!--[if gte IE 9]>
    <style type="text/css">
        .gradient {
            filter: none;
        }
    </style>
    <![endif]-->

    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/jquery.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/bootstrap-select.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/Plugin/Fancybox/fancybox.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/jquery.touchSwipe.min.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/site.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/jquery.validate.min.js"></script>
</head>
<body>
<div id="tpl-contaiter">
    <!-- Begin Header -->
    <div id="tpl-header">
        <div class="top_header">
            <div class="row">
                <div class="rightmenu col-md-2 col-sm-2 col-xs-2 text-center">
                    <a class="menu-right ui-link" href=""><img
                            src="<?php echo Yii::app()->theme->baseUrl ?>/FileManager/ic_menu_1.png"/></a>
                </div>
                <div class="center col-md-8 col-sm-8 col-xs-8 text-center"
                     style="margin-top: 4px;font-size: 20px;"><?php echo $this->titlePage; ?></div>

                <?php
                if ($this->userName != NULL) {
                    $uid = $this->userName->userid;
                } else {
                    $uid = -1;
                }

                ?>
                <div id="divcontainingNotificationWidget"></div>
                <div class="finter">
                    <?php // if ($checkmenu == 'site' || $checkmenu =='') { ?>
                    <a href="javascript:;" data-toggle="modal" data-target="#myModal"><img
                            src="<?php echo Yii::app()->theme->baseUrl ?>/img/topnav-add.png" class="icon-search1"/>
                    </a>
                    <?php // } ?>
                </div>
            </div>
        </div>
    </div>
    <div id="tpl-main">
        <div class="tpl-main-middle">

            <?php
                echo $content;
            ?>

        </div>
    </div>

    <div class="tabs">
        <ul>
            <li class="tabs-item" tab_item="1" style="width: 25%;">
                <a href="https://www.facebook.com/pklvn/">
                    <i class="fa icon icon-facebook"></i>
                    <span>Facebook</span>
                </a></li>
            <li class="tabs-item tab-sell" tab_item="2"  style="width: 25%;">
                <a href="<?php echo Yii::app()->baseUrl . '/post/sell' ?>">
                    <i class="fa icon icon-buy"></i>
                    <span>Cần bán</span>
                </a></li>
            <li class="tabs-item" tab_item="3"  style="width: 25%;">
                <a href="<?php echo Yii::app()->baseUrl . '/post/buy' ?>">
                    <i class="fa icon icon-sell"></i>
                    <span>Cần mua</span>
                </a></li>
            <li class="tabs-item" tab_item="4"  style="width: 25%;">
                <a href="<?php echo Yii::app()->baseUrl . '/post/search' ?>">
                    <i class="fa icon icon-search"></i>
                    <span>Tìm kiếm</span></a></li>
        </ul>
    </div>
    <!--End Main-->
    <!-- End Footer-->
    <?php
        $this->widget("application.widgets.NavMenu", array('userName' => $this->userName));
    ?>
</div>
<script>
    $('.submit-comment-text, body a.comment-home, body .like a, .comment a, .name-title a').click(function () {
        var a = <?php echo $uid ?>;
        if (a == -1) {
            if (confirm("Bạn phải đăng nhập để sử dụng")) {
                window.location.href = "<?php echo Yii::app()->baseUrl . '/account/' ?>";
            }
            return false;
        }

    });
</script>
</body>
</html>