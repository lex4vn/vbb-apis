<?php
    $user_id = Yii::app()->session['user_id'];
?>

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
            data: {'uid':uid,'tab_item':2,'page':page, 'page_size':page_size},
            dataType:'html',
            success: function(html){
                hideLoadItem();
                $('.list-group-item-answer').html(html);
            }
        });
    }

    function showLoadItem(){
        $('.loadItem').show();
    }
    function hideLoadItem(){
        $('.loadItem').hide();
    }
</script>