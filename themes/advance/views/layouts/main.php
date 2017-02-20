<?php
    $this->widget("application.widgets.Header", array());
?>
<?php
    $requestUrl = Yii::app()->request->getUrl() . '/' . Yii::app()->controller->action->id;
    //$currentUrl = Yii::app()->request->getUrl();
    $currentAction = '/hocde.vn/questionBank/index';
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
    <link rel="icon" href="<?php echo Yii::app()->theme->baseUrl . '/FileManager/favicon.jpg' ?>"/>
    <!-- Bootstrap -->
    <link href="<?php echo Yii::app()->theme->baseUrl ?>/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo Yii::app()->theme->baseUrl ?>/css/bootstrap-select.css" rel="stylesheet"/>
    <link href="<?php echo Yii::app()->theme->baseUrl ?>/css/reset.css" rel="stylesheet"/>
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
    <?php if ($requestUrl != $currentAction) { ?>
        <?php if ($requestUrl != '/questionBank/index') { ?>
            <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/jquery.js"></script>
            <?php
        }
    }
    ?>
    <?php if ($requestUrl == $currentAction) { ?>
        <?php if ($requestUrl != '/hocde.vn/questionBank/index') { ?>
            <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/jquery.js"></script>
            <?php
        }
    }
    ?>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/bootstrap-select.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/Plugin/Fancybox/fancybox.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/jquery.touchSwipe.min.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/site.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/jquery.validate.min.js"></script>

    <!-- required libraries -->
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/socket.io.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/jsencrypt.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/adapter-1.3.0.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/vstack-sdk-1.5-build-20161118.js"></script>

    <script>
        window.fbAsyncInit = function () {
            FB.init({
                appId: '891467350909145',
                xfbml: true,
                version: 'v2.5'
            });
        };

        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
    <script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-79670755-3', 'auto');
        ga('send', 'pageview');

    </script>
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
                    $uid = $this->userName->id;
                } else {
                    $uid = -1;
                }

                ?>
                <div id="divcontainingNotificationWidget"></div>
                <div class="finter">
                    <?php // if ($checkmenu == 'site' || $checkmenu =='') { ?>
                    <a href="javascript:;" data-toggle="modal" data-target="#myModal"><img
                            src="<?php echo Yii::app()->theme->baseUrl ?>/img/finter.png" class="icon-search1"/>
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
    <!--End Main-->
    <!-- End Footer-->
    <?php
        if ($this->userName != NULL) {
            $uid = $this->userName->id;
        } else {
            $uid = -1;
        }
        $this->widget("application.widgets.SearchBox", array());
        $this->widget("application.widgets.NavMenu", array('userName' => $this->userName));
    ?>
</div>
<script>
    $('body a.comment-home, body .like a, .comment a, .name-title a, .name-detail a, body .uploadQuestion a').click(function () {
        var a = <?php echo $uid ?>;
        if (a == -1) {
            if (confirm("Bạn phải đăng nhập để sử dụng")) {
                window.location.href = "<?php echo Yii::app()->baseUrl . '/account/' ?>";
            }
            return false;
        }

    });
</script>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Lọc</h3>
                <div class="classAll" style="text-align: left; font-weight: 100 !important">
                    <?php
//                    $classAll = null;
//                        foreach ($classAll as $classAll):
//                            ?>
<!--                            <input type="checkbox" name="class" id="class" value="--><?php //echo $classAll['id'] ?><!--"/>-->
<!--                            <label>--><?php //echo $classAll['class_name'] ?><!--</label><br/>-->
<!--                            --><?php
//                        endforeach;
                    ?>
                </div>
                <div class="" style="text-align: center">
                    <button type="button" class="btn btn-primary listClass" style="">Đồng ý</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('.listClass').click(function () {
        var array_result = [];
        $('input:checked').each(function () {
            array_result.push($(this).val());
        });
        var text = "";
        for (i = 0; i < array_result.length; i++) {
            if (i == array_result.length - 1) {
                text += array_result[i];
            } else {
                text += array_result[i] + ',';
            }
        }
        if (array_result.length > 0) {
            $.ajax({
                type: 'POST',
                url: "<?php echo Yii::app()->baseUrl . '/site/test'?>",
                data: {'text': text},
                dataType: 'html',
                success: function (html) {
                    window.location.href = '<?php echo Yii::app()->baseUrl . '/site'?>';
                }
            });
        } else
            location.reload();
    });
</script>
</body>
</html>