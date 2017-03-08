<?php
//    print_r($this->userName->url_avatar);die;
    if($this->userName == null || $this->userName == ''){
        $avata = Yii::app()->theme->baseUrl .'/FileManager/avata.png';
    }else{
        if($this->userName->url_avatar != ''){
            $avata = $this->userName->url_avatar;
        }else{
            $avata = Yii::app()->theme->baseUrl .'/FileManager/avata.png';
        }
    }
?>
<form enctype="multipart/form-data" id="formUpload" action="" method="post" data-ajax="false">
    <div class="form-group">
        <label for="title">Tiêu đề tin</label>
        <input type="text" name="title" id="title" placeholder="Bắt buộc" />
    </div>

    <div class="form-group">
        <label>Số điện thoại liên lạc</label>
        <input type="text" name="phone" id="phone" placeholder="Bắt buộc" />
    </div>

    <div class="form-group">
        <label>Mô tả</label>
        <textarea type="text" row="5" name="message" id="message" placeholder="Bắt buộc (không quá 500 kí tự)" >
        </textarea>
    </div>

    <div class="form-group">
        <a class="loadgif" style="display: none"><img width="50px" src="<?php echo Yii::app()->theme->baseUrl .'/img/load.gif'?>" /></a>
    </div>
    <div class="question-submit question-submit-question">
        <button type="submit" class="btn btn-primary">Đăng tin</button>
        <!--<input type="button" id="add_more" class="upload" value="Add More Files">-->
    </div>

</form>

<script>
     var abc = 0; //Declaring and defining global increement variable
    $(document).ready(function () {
//To add new input file field dynamically, on click of "Add More Files" button below function will be executed
        $('#add_more').click(function () {
            $(".upload").before($("<div/>", {id: 'filediv', class: 'ui-block-a img-upload1'}).fadeIn('slow').append(
                $("<input/>", {name: 'file[]', type: 'file', id: 'file'})
            ));
        });
//following function will executes on change event of file input to select different file	
        $('body').on('change', '#file', function () {
            if (this.files && this.files[0]) {
                abc += 1; //increementing global variable by 1
                var z = abc - 1;
                var x = $(this).parent().find('#previewimg' + z).remove();
                $(this).before("<div id='abcd" + abc + "' class='abcd'><img id='previewimg" + abc + "' src=''/></div>");
                $('.custom-file-input').css('top','10%');
                var reader = new FileReader();
                reader.onload = imageIsLoaded;
                reader.readAsDataURL(this.files[0]);
                $('#file').show();
//			    $(this).hide();
                $("#abcd" + abc).append($("<img/>", {
                    id: 'img',
                    class: 'delete',
                    src: '<?php echo Yii::app()->theme->baseUrl?>/img/x.png',
                    alt: 'delete'
                }).click(function () {
//                        $('#file').show();            
//                        alert($(this));return false;
                    $(this).parent().remove();

                }));
            }
        });

//To preview image     
        function imageIsLoaded(e) {
            $('#previewimg' + abc).attr('src', e.target.result);
        };

        $('#upload').click(function (e) {
            var name = $(":file").val();
            if (!name) {
                alert("First Image Must Be Selected");
                e.preventDefault();
            }
        });
    });
    $("#formUpload").on('submit',(function(e){
        var title = $('#title').val();
        var subject= $('#subject').val();
        var price= $('#price').val();
        var file= $('#file').val();
        var phone= $('#phone').val();
        var address= $('#address').val();
        var year= $('#year').val();
        var uid= <?php echo 1;//Yii::app()->session['user_id'] ?>;

        if(title == null || title==''){
            alert('Bạn chưa điền tiêu đề tin'); return false;
        }

        if(file == null|| file ==''){
            alert('Bạn chưa chọn ảnh'); return false;
        }

        if(price == null || price=='-1'){
            alert('Bạn chưa nhập giá bán'); return false;
        }
        if(phone == null || phone =='' || phone=='-1'){
            alert('Bạn chưa nhập số điện thoại'); return false;
        }
        if(address == null || address ==''){
            alert('Bạn chưa địa chỉ xem xe'); return false;
        }
        if(year == null || year ==''){
            alert('Bạn chưa chọn năm sản xuất'); return false;
        }
        e.preventDefault();
        showLoad();
        $('.question-submit-question').hide();
//        $('.loadgif').css('display','block');
        $.ajax({
            url: "<?php echo Yii::app()->request->baseUrl . '/post/saveUpload'?>",
            type: "POST",
            data:  new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data){
                hideLoad();
                if(data == 2){
                   alert('Ảnh upload của bạn bị lỗi, Bạn vui lòng upload lại ảnh.');
                    window.location.replace("<?php echo Yii::app()->homeurl . '/post/upload'?>");return false;
                }else if(data == 3){
                   alert('Ảnh upload của bạn không được quá 2M.'); 
                    window.location.replace("<?php echo Yii::app()->homeurl . '/post/upload'?>");return false;
                }else{
                    window.location.replace("<?php echo Yii::app()->homeurl?>");
                }
            },
            error: function(){}
        });
    }));

    function showLoad(){
        $('.loadgif').show();
    }
    function hideLoad(){
        $('.loadgif').hide();
    }
</script>