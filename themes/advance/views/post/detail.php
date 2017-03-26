<?php
if (Yii::app()->session['user_id']) {
    $user_id = Yii::app()->session['user_id'];
} else {
    $user_id = -1;
}
$can_delete = $user_id == 22671 ? true : false;
$CUtils = new CUtils();
$time = $CUtils->formatTime($post['modify_date']);
$secs = '00';
$t = time();
if ($user['avatar'] == null) {
    $avata = 'http://pkl.vn/vbb-apis/themes/advance/FileManager/avata.png';
} else {
    $avata = $user['avatar'];
}
?>
<div class="web_body">
    <div class="listarticle">
                <?php if($can_delete){ ?>

                    <a href="<?php echo Yii::app()->baseUrl.'/post/delete/'.$post['id'] ?>">
                        Delete
                    </a>

                <?php } ?>
                    <!-- AVATAR                    -->
                    <div class="col-md-3 col-xs-3 avata">
                        <a href="#">
                            <img src="<?php echo $avata ?>"/>
                        </a>
                    </div>

                    <div class="col-md-6 col-xs-6">
                        <div class="article-title">
                            <a href="#"><?php echo $user['username'] ?></a>
                        </div>
                        <div class="user-status">
                            <span><?php echo 'Online'; ?></span>
                            <span><?php echo $post['modify_date'] ?></span>
                        </div>
                        <div class="article-price">
                            <span><?php echo number_format($post['price'],0,'.',',') ?></span>
                            <span><?php echo $post['formality'],'%' ?></span>
                        </div>

                        <div class="article-location">
                            <span class="glyphicon glyphicon-map-marker"></span>
                            <?php echo $post['location'] ?>
                        </div>

                    </div>

                    <div class="col-md-3 col-xs-3">
                        <?php
                        if ($user_id != -1) {
                            ?>
                            <div class="subject-title">
                                <a data-toggle="modal" data-target="#myModal3" href="#"><img
                                        src="<?php echo Yii::app()->theme->baseUrl . '/img/share.png' ?>"
                                        style="width:30px !important"/></a>
                                <a data-toggle="modal" data-target="#myModal4" href="#"><img
                                        src="<?php echo Yii::app()->theme->baseUrl . '/img/add-friend.png' ?>"
                                        style="width:30px !important"/></a>
                            </div>
                            <?php
                        }
                        ?>
                    </div>


            <?php if($post['type'] == 2){ ?>
                <!-- The Gallery as lightbox dialog, should be a child element of the document body -->
                <div id="blueimp-gallery" class="blueimp-gallery">
                    <div class="slides"></div>
                    <h3 class="title"></h3>
                    <a class="prev">‹</a>
                    <a class="next">›</a>
                    <a class="close">×</a>
                    <a class="play-pause"></a>
                    <ol class="indicator"></ol>
                </div>
                <div id="links">
                    <?php
                    $images_name = array();
                    foreach ($images as $image) { ?>
                        <a href="<?php echo IMAGES_PATH.$image['base_url'] ?>" title="<?php echo $post['subject'] ?>">
                            <img src="<?php echo IMAGES_PATH.$image['base_url'] ?>" alt="<?php echo $post['subject'] ?>">
                        </a>
                    <?php  } ?>
                </div>


    <?php } ?>

            <div class="col-md-12">
                <p class="post-content">
                    <?php echo $post['message'] ?>
                </p>
            </div>



    </div>

    <div class="comment-answer-list"></div>

    <div class="col-md-12 comment-footer">
        <div class="row">
            <div class="col-lg-10 col-md-9 col-xs-8">
                <textarea class="form-control comment-text" rows="1" id="comment-text" name="comment-text"
                          placeholder="Viết bình luận"></textarea>
            </div>
            <div class="col-lg-2 col-md-3 col-xs-4 text-right">
                <button type="button" class="btn btn-primary submit-comment-text">Send</button>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function(){
        loadComment();
    });

    $(".checkLike_<?php echo $post['id'] ?>").click(function () {
        var $_this = $(this);
        var status = $_this.attr('status');
        var uid = $_this.attr('uid');
        var question_id = $_this.attr('question_id');
        var like_nume = parseInt($(".num_like_<?php echo $post['id'] ?>").html());
        if (uid == -1) {
            return false;
        }
        if (status == 0) {
            $(".checkLike_<?php echo $post['id'] ?> img").attr('src', "<?php echo Yii::app()->theme->baseUrl . '/FileManager/ic_like.png' ?>");
            $(".num_like_<?php echo $post['id'] ?>").html(like_nume + 1);
            $_this.attr('status', "1");
        } else {
            $(".checkLike_<?php echo $post['id'] ?> img").attr('src', "<?php echo Yii::app()->theme->baseUrl . '/FileManager/like.png' ?>");
            $(".num_like_<?php echo $post['id'] ?>").html(like_nume - 1);
            $_this.attr('status', "0");
        }
        $.ajax({
            type: 'POST',
            url: "<?php echo Yii::app()->baseUrl . '/site/checklike'?>",
            data: {'status': status, 'uid': uid, 'question_id': question_id},
            dataType: 'html',
            success: function (html) {
                return false;
            }
        });
    });
    $(".comment a").click(function () {
        $('.comment-text').fadeIn();
        $('#comment-text').focus();
    });
    $(".submit-comment-text").click(function () {
        var comment_text = $('#comment-text').val();
        <?php echo $user_id == null? 'alert("Vui lòng đăng nhập để nhận xét");':'' ?>

        var uid = <?php echo $user_id ?>;
        var post_id = <?php echo $post['id'] ?>;
        if (comment_text.length < 3 || comment_text.trim().length < 3) {
            $('#comment-text').focus();
            return;
        }
        $.ajax({
            type: 'POST',
            url: "<?php echo Yii::app()->baseUrl . '/post/insertComment'?>",
            data: {'comment_text': comment_text, 'uid': uid, 'post_id': post_id},
            dataType: 'html',
            success: function (html) {
                $('#comment-text').val('');
                $('#comment-text').focus();
                loadComment();
            }
        });
    });
    function loadComment() {
        var post_id = <?php echo $post['id'] ?>;
        $.ajax({
            type: 'POST',
            url: "<?php echo Yii::app()->baseUrl . '/post/loadComment'?>",
            data: {'post_id': post_id},
            dataType: 'html',
            success: function (html) {
                $('.comment-answer-list').html(html);
            }
        });
    }
    $('.close-comment-text').click(function () {
        $('.comment-text').fadeOut();
    });


    $('#gymQuestion_<?php echo $post['id']?>').click(function () {
        var questionId = <?php echo $post['id']?>;
        var user_id = <?php echo $user_id?>;
//        $('.gymQuestion_<?php // echo $post['id']?>').html('bỏ ghim');
        $.ajax({
            type: 'POST',
            url: "<?php echo Yii::app()->baseUrl . '/question/holdQuestion'?>",
            data: {'questionId': questionId, 'user_id': user_id},
            dataType: 'html',
            success: function (html) {
                if (html == 0) {
                    if (confirm("Bạn phải đăng nhập để sử dụng")) {
                        window.location.href = "<?php echo Yii::app()->baseUrl . '/account/'?>";
                    }
                    return;
                }
                if (html == 1) {
                    alert('Câu hỏi đang được trả lời');
                    return;
                }
                if (html == 4) {
                    alert('Bạn vui lòng trả lời câu hỏi bạn đã ghim trước khi bạn muốn ghim câu hỏi này.');
                    return;
                }
//                var msg = {
//                        question_id: questionId, //id cau hoi
//                        type: '15', //dang tra loi: 1, tra loi xong: 2
//                };
//                websocket.send(JSON.stringify(msg));
                countdown("timer_<?php echo $post['id']?>", 0, html);
                location.reload();
//                countdown( "timer_<?php // echo $post['id']?>", 0, html );
            }
        });
    });

    function countdown(elementName, minutes, seconds) {
        var element, hours, endTime, mins, msLeft, time;
        element = document.getElementById(elementName);
        endTime = (+new Date) + 1000 * (60 * minutes + seconds) + 500;
        updateTimer();
//        endTime.setTime(endTime.getTime()+ (1* 60 *7 * 60 * 60 * 1000));
        updateTimer();

        function twoDigits(n) {
            return (n <= 9 ? "0" + n : n);
        }

        function updateTimer() {
            msLeft = endTime - (+new Date)
            if (msLeft <= 0) {
                element.innerHTML = "Hết giờ";
//                location.reload();
                location.href = '<?php echo Yii::app()->baseUrl . '/question/deleteholdQuestion/' . $post['id']?>';
            } else {
                time = new Date(msLeft);
                hours = time.getUTCHours();
                mins = time.getUTCMinutes();
                element.innerHTML = (hours ? hours + ':' : '') + twoDigits(mins) + ':' + twoDigits(time.getUTCSeconds());
                setTimeout(updateTimer, time.getUTCMilliseconds() + 500);
            }
        }

    }
    $('.UngymQuestion_<?php echo $post['id'] ?>').click(function () {
        var questionId = <?php echo $post['id']?>;
        var user_id = <?php echo $user_id?>;
        $.ajax({
            type: 'POST',
            url: "<?php echo Yii::app()->baseUrl . '/question/unholdQuestion'?>",
            data: {'questionId': questionId, 'user_id': user_id},
            dataType: 'html',
            success: function (html) {
                if (html == 0) {
                    if (confirm("Bạn phải đăng nhập để sử dụng")) {
                        window.location.href = "<?php echo Yii::app()->baseUrl . '/account/'?>";
                    }
                    return;
                }
                if (html == 1) {
                    alert('Bạn bỏ ghim thành công');
                    location.reload();
                }
            }
        });
    });
</script>

<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="searchLibrary" style="">
                <h3>Thông báo</h3>
                <p>Bạn muốn tìm kiếm lời giải trong ngân hàng lời giải của Học Dễ</p>
                <a href="<?php echo Yii::app()->baseUrl . '/questionBank' ?>"><img
                        src="<?php echo Yii::app()->theme->baseUrl . '/img/tk.png' ?>"/></a>
            </div>
            <div class="questionTeach" style="">
                <p>Đặt câu hỏi cho giáo viên</p>
                <a href="<?php echo Yii::app()->baseUrl . '/question/upload' ?>"><img
                        src="<?php echo Yii::app()->theme->baseUrl . '/img/datcauhoi.png' ?>"/></a>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript" src="<?php echo Yii::app()->theme->baseUrl ?>/js/blueimp-gallery.min.js"></script>
<script>
    document.getElementById('links').onclick = function (event) {
        event = event || window.event;
        var target = event.target || event.srcElement,
            link = target.src ? target.parentNode : target,
            options = {index: link, event: event},
            links = this.getElementsByTagName('a');
        blueimp.Gallery(links, options);
    };
</script>
<script>
    blueimp.Gallery(
        document.getElementById('links').getElementsByTagName('a'),
        {
            container: '#blueimp-gallery-carousel',
            carousel: true
        }
    );
</script>