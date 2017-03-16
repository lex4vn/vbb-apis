<form enctype="multipart/form-data" id="formUpload" action="" method="post" data-ajax="false">
    <div class="form-group">
        <label for="title">Tiêu đề tin</label>
        <input type="text" name="title" id="title" placeholder="Bắt buộc"/>
    </div>

    <div class="form-group">
        <label>Số điện thoại liên lạc</label>
        <input type="text" name="phone" id="phone" placeholder="Bắt buộc"/>
    </div>

    <div class="form-group">
        <label>Mô tả</label>
        <textarea type="text" row="5" name="message" id="message" placeholder="Bắt buộc (không quá 500 kí tự)">
        </textarea>
    </div>

    <div class="form-group">
        <a class="loadgif" style="display: none"><img width="50px"
                                                      src="<?php echo Yii::app()->theme->baseUrl . '/img/load.gif' ?>"/></a>
    </div>
    <div class="question-submit question-submit-question">
        <button type="submit" class="btn btn-primary">Đăng tin</button>
    </div>

</form>

<script>
    $("#formUpload").on('submit', (function (e) {
        var title = $('#title').val();
        var phone = $('#phone').val();
        var description = $('#description').val();
        var uid = <?php Yii::app()->session['user_id'] ?>;

        if (title == null || title == '') {
            alert('Bạn chưa điền tiêu đề tin');
            return false;
        }

        if (phone == null || phone == '' || phone == '-1') {
            alert('Bạn chưa nhập số điện thoại');
            return false;
        }
        if (description == null || description == '') {
            alert('Bạn chưa nhập mô tả');
            return false;
        }
        e.preventDefault();
        showLoad();

        $('.question-submit-question').hide();
//        $('.loadgif').css('display','block');
        $.ajax({
            url: "<?php echo Yii::app()->request->baseUrl . '/post/saveBuy'?>",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {
                hideLoad();
                window.location.replace("<?php echo Yii::app()->homeurl?>");
            },
            error: function () {
            }
        });
    }));

    function showLoad() {
        $('.loadgif').show();
    }
    function hideLoad() {
        $('.loadgif').hide();
    }
</script>