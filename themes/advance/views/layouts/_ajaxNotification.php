<?php
if (isset($uid) && $uid > 0) { ?>
    <div class="notifi">
        <a href="javascript:;" onclick="showNotification();updateNew();"><img
                src="<?php echo Yii::app()->theme->baseUrl ?>/img/thongbao_top.png"
                class="icon-search1"/>
            <span id="countnb"></span>
        </a>
    </div>
    <div class="menu-right-top" style="display: none">
        <div class="user-list" id="show_content">
        </div>
    </div>
<?php } ?>

