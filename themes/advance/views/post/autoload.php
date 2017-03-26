<?php
//Can ban $type = 2;
for ($i = 0; $i < count($post); $i++){
    $CUtils = new CUtils();
    $time = $CUtils->formatTime($tab_item == 2?$post[$i]['modify_date']:$post[$i]['create_date']);
    $post_id = $post[$i]['id'];
    $subject = $post[$i]['subject'];
    $type = $post[$i]['type'];
?>
 <div class="web_body">
     <div class="listarticle">
         <div class="row ">

             <div class="col-lg-2 col-md-3 col-sm-4 col-xs-4">

                <a class="ava" href="<?php echo Yii::app()->baseUrl.'/post/view/'.$post[$i]['id'] ?>">

                    <?php if($tab_item == 2){ ?>
                <img
                src="<?php echo $post[$i]['thumb'] ?>"
                title="<?php echo $post[$i]['subject']?>"
                alt="<?php echo $post[$i]['subject'] ?>" />
                    <?php }else{ ?>
                        <img class="avata"
                            src="<?php echo $post[$i]['avatar'] ?>"
                            title="<?php echo $post[$i]['subject']?>"
                            alt="<?php echo $post[$i]['subject'] ?>" />
                    <?php } ?>


                </a>
             </div>

             <div class="col-lg-10 col-md-9  col-sm-8 col-xs-8">

                 <div class="article-title">
                     <h2 style="word-wrap:break-word;"><a href="<?php echo Yii::app()->baseUrl.'/post/view/'.$post[$i]['id'] ?>
                     "><?php echo $post[$i]['subject'] ?></a></h2>
                 </div>

                 <?php if($tab_item == 2){ ?>
                 <div class="article-price">
                    <div class="price">
                        <?php echo number_format($post[$i]['price'],0,'.',',') ?>
                     </div>

                     <div class="text-right">
                         <?php echo $post[$i]['formality'] == 1? 'Cũ': 'Mới' ?>
                     </div>
                 </div>

                 <div class="article-location">
                         <span class="glyphicon glyphicon-map-marker"></span>
                         <?php echo $post[$i]['location'] ?>
                 </div>

                 <div class="article-user">
                     <div class="username">
                         <?php echo $post[$i]['postusername'] ?>
                     </div>

                     <div class="text-right">
                         <?php echo $post[$i]['status'] == 0? 'Chưa bán': 'Đã bán' ?>
                     </div>
                 </div>

                 <?php }else{ ?>
                     <div class="article-user">
                         <div class="username">
                             <?php echo $post[$i]['postusername'] ?>
                         </div>

                         <div class="text-right">
                             <?php echo $post[$i]['status'] == 1? 'Online': 'Offline' ?>
                         </div>
                     </div>
                     <div class="article-location">
                         <?php echo $post[$i]['create_date'] ?>
                     </div>
                 <?php } ?>

             </div>

         </div>
     </div>
 </div>
<?php
}
?>
<div style="width: 80%;
    height: 40px;
    text-align: center;
    margin: auto;
    background-color: #B3B3B3;
    line-height: 40px;
    border-radius: 5px;
    margin-top: 10px;
    margin-bottom: 15px;
    ">
    <?php if(isset(Yii::app()->session['tab_item']) && Yii::app()->session['tab_item'] == 1){?>
        <a href="#" class="loadMoreComment1" tab_item="1" number = "<?php echo Yii::app()->session['page'] + 1?>" >Xem thêm</a>
    <?php }else if(isset(Yii::app()->session['tab_item']) && Yii::app()->session['tab_item'] == 2){?>
        <a href="#" class="loadMoreComment2" tab_item="2" number = "<?php echo Yii::app()->session['page'] + 1?>" >Xem thêm</a>
    <?php }else if(isset(Yii::app()->session['tab_item']) && Yii::app()->session['tab_item'] == 3){?>
        <a href="#" class="loadMoreComment3" tab_item="3" number = "<?php echo Yii::app()->session['page'] + 1?>" >Xem thêm</a>
    <?php } else if(isset(Yii::app()->session['tab_item']) && Yii::app()->session['tab_item'] == 4){?>
        <a href="#" class="loadMoreComment4" tab_item="4" number = "<?php echo Yii::app()->session['page'] + 1?>" >Xem thêm</a>
    <?php }else if(isset(Yii::app()->session['tab_item']) && Yii::app()->session['tab_item'] ==5){?>
        <a href="#" class="loadMoreComment5" tab_item="5" number = "<?php echo Yii::app()->session['page'] + 1?>" >Xem thêm</a>
    <?php }else{?>
        <?php
           if($this->userName->type == 1){
        ?>
            <a href="#" class="loadMoreComment1" tab_item="1" number = "<?php echo Yii::app()->session['page'] + 1?>" >Xem thêm</a>
        <?php }else{?>
            <a href="#" class="loadMoreComment3" tab_item="3" number = "<?php echo Yii::app()->session['page'] + 1?>" >Xem thêm</a>
        <?php }?>
    <?php }?>
</div>
<div class="loadItem" style=""><img src="<?php echo Yii::app()->theme->baseUrl .'/img/ajax-loader.gif'?>" /></div>
<script>
    $('.loadMoreComment1, .loadMoreComment2, .loadMoreComment3, .loadMoreComment4, .loadMoreComment5').click(function(){ 
        var number = parseInt($(this).attr('number'));
        var tab_item = $(this).attr('tab_item');
        var total = number;
        loadItem(total,10,tab_item);
    });
    function loadItem(page,page_size,tab_item){ 
        var uid = <?php echo $user_id ?>;
        var page = page;
        var page_size = page_size;
        var tab_item = tab_item;
        showLoadItem();
        $.ajax({
            type: 'POST',
            url: "<?php echo Yii::app()->baseUrl . '/question/loadItem'?>",
            data: {'uid':uid,'tab_item':tab_item,'page':page, 'page_size':page_size},
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

