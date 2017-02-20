<style>
    .radio-type input{height: auto !important; margin-right: 10px;}
    .error{color: #f00}
</style>
<div class="web_body" style=" display: flex;">
    <div class="col-md-12">
        <h1>Đăng ký</h1>
        <div class="content">
            <div class="block-login">
                <h1>Đăng ký tài khoản HOCDE ngay</h1>
                <p>Để nhanh chóng có câu trả lời cho mọi bài tập và giải đáp mọi thắc mắc qua ngân hàng lời giải cùng đội ngũ giáo viên online uy tín. </p>
                <br/>
                <div class="box-search box-login">
                    <form id="login-form" action="<?php echo $this->createUrl("/account/registerWeb"); ?>" method="post">
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <input type="text" class="form-control" name="lastname" id="lastname" placeholder="Họ">
                            </div>
                            <div class="form-group col-xs-6">
                                <input type="text" class="form-control" name="firtname" id="firtname" placeholder="Tên">
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="username" id="username" placeholder="Tên đăng nhập">
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-6 col-md-6">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Mật khẩu">
                            </div>
                            <div class="form-group col-xs-6 col-md-6">
                                <input type="password" class="form-control" name="password_confirm" id="password_confirm" placeholder="Nhập lại mật khẩu">
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Số điện thoại">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="email" id="email" placeholder="Email">
                        </div>
                         <div class="row radio-type">
                            <div class="form-group col-xs-6 col-md-6 ">
                                <input type="radio" name="type_account" checked id="type_account" value="1">
                                <label>Học sinh</label>
                            </div>
                            <div class="form-group col-xs-6 col-md-6">
                                <input type="radio" name="type_account" id="type_account1" value="2">
                                <label>Giáo viên</label>
                            </div>
                        </div>
                        <br/>
                        <?php
                        $errors = Yii::app()->user->hasFlash('responseToUser');
                        if (isset($errors)) {
                            ?>
                            <?php echo Yii::app()->user->getFlash('responseToUser'); ?><br/>
                            <?php
                        }
                        ?>
                        <div class="row text-center"><div class="other-login-txt">Lưu ý: nhập đúng số điện thoại để nhận mã xác thực tài khoản</div></div>
                        <div class="form-group">
                            <button type="submit" name="submit"  class="login form-control" style="padding: 0 !important"> Đăng ký</button>
                        </div>
                        <div class="row" style="margin: 2px;"><div class="light-hight-left"></div>
                        
                    </form>
                </div><!-- end box-search -->
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#login-form').validate({ // initialize the plugin
            rules: {
                username: {
                    required: true,
                },
                lastname: {
                    required: true,
                },
                firtname: {
                    required: true,
                },
                email: {
                    required: true,
                    email: true
                },
                mobile: {
                    required: true,
                    minlength: 9
                },
                password: {
                    required: true,
                    minlength: 6
                },
                password_confirm: {
                    required: true,
                    minlength: 6,
                    equalTo: "#password"
                }
            },
            messages: {
                username: {
                    required : 'Tên đăng nhập không được để trống.'
                },
                email: {
                    required : 'Email không được để trống.'
                },
                lastname: {
                    required : 'Vui lòng nhập tên của bạn.'
                },
                firtname: {
                    required : 'Vui lòng nhập họ của bạn.'
                },
                mobile: {
                    required : 'Vui lòng nhập số điện thoại của bạn.',
                    minlength: 'Có vẻ như số điện thoại chưa chính xác'
                },
                password: {
                    required : 'Bạn chưa nhập mật khẩu.',
                    minlength: 'Mật khẩu của bạn quá ngắn. Phải từ 6 ký tự trở lên.'
                },
                password_confirm: {
                    required : 'Mật khẩu nhập lại chưa đúng.',
                    minlength: 'Mật khẩu không khớp nhau.'
                }
            }

        });

    });
</script>