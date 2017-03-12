<?php
    $user_id = Yii::app()->session['user_id'];
?>

<div class="tabs">
    <ul>
            <li class="tabs-item" tab_item="1" style="width: 25%;">
                <a href="#">
                <i class="fa icon icon-facebook"></i>
                    <span>Facebook</span>
                </a></li>
            <li class="tabs-item" tab_item="2"  style="width: 25%;">
                <a href="#">
                <i class="fa icon icon-buy"></i>
                    <span>Cần bán</span>
                </a></li>
            <li class="tabs-item" tab_item="3"  style="width: 25%;">
                <a href="#">
                    <i class="fa icon icon-sell"></i>
                    <span>Cần mua</span>
                    </a></li>
            <li class="tabs-item" tab_item="4"  style="width: 25%;">
                <a href="#">
                    <i class="fa icon icon-search"></i>
                    <span>Tìm kiếm</span></a></li>
    </ul>
</div>
<div class ="list-group-item-answer"></div>

<!--<div class="loadItem" style=""><img src="<?php echo Yii::app()->theme->baseUrl .'/img/ajax-loader.gif'?>" /></div>-->
<script>
    $(document).ready(function(){
      loadItem(1,10);
    });

    function loadItem(page,page_size){
        var uid = '<?php echo $user_id ?>';
        var page = page;
        var page_size = page_size;  

        showLoadItem();

        $.ajax({
            type: 'POST',
            url: "<?php echo Yii::app()->baseUrl . '/post/loadItem'?>",
            data: {'uid':uid,'tab_item':tab_item,'page':page, 'page_size':page_size},
            dataType:'html',
            success: function(html){
                hideLoadItem();
                $('.list-group-item-answer').html(html);
            }
        });
    }
    $('.tabs-item').click(function(){
        var $_this=$(this);
        var uid = <?php echo 1;//$user_id ?>;
        var tab_item = $_this.attr('tab_item');
        var page = 0;
        var page_size = 10;
        showLoadItem();
        $.ajax({
            type: 'POST',
            url: "<?php echo Yii::app()->baseUrl . '/post/loadItem'?>",
            data: {'uid':uid,'tab_item':tab_item,'page':page, 'page_size':page_size},
            dataType:'html',
            success: function(html){
                hideLoadItem();
                $('.list-group-item-answer').html(html);
            }
        });
    });
    function showLoadItem(){
        $('.loadItem').show();
    }
    function hideLoadItem(){
        $('.loadItem').hide();
    }
</script>