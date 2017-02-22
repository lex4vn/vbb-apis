<?php

class SiteController extends Controller
{
    private $pageSize = 10;
    const sessionTimeoutSeconds = 86400;

    /**
     * Declares class-based actions.1
     */
    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    public function beforeAction($action)
    {
        $domain = $_SERVER['HTTP_HOST'];
        if ($domain == 'cho.pkl.bike') {
            $this->redirect('https://www.pkl.vn/vbb-api/');
        }
        // Check only when the user is logged in
        if (!Yii::app()->user->isGuest || Yii::app()->session['user_id']) {
            if (yii::app()->user->getState('userSessionTimeout') < time()) {
                // timeout
                Yii::app()->user->logout();
                Yii::app()->session->clear();
                Yii::app()->session->destroy();
                $this->redirect(Yii::app()->homeurl);
            } else {
                yii::app()->user->setState('userSessionTimeout', time() + self::sessionTimeoutSeconds);
                return true;
            }
        } else {
            return true;
        }
        return parent::beforeAction($action);
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        return;
    }



    /**
     * Displays the contact page
     */
    public function actionContact()
    {
        $model = new ContactForm;
        if (isset($_POST['ContactForm'])) {
            $model->attributes = $_POST['ContactForm'];
            if ($model->validate()) {
                $name = '=?UTF-8?B?' . base64_encode($model->name) . '?=';
                $subject = '=?UTF-8?B?' . base64_encode($model->subject) . '?=';
                $headers = "From: $name <{$model->email}>\r\n" .
                    "Reply-To: {$model->email}\r\n" .
                    "MIME-Version: 1.0\r\n" .
                    "Content-type: text/plain; charset=UTF-8";

                mail(Yii::app()->params['adminEmail'], $subject, $model->body, $headers);
                Yii::app()->user->setFlash('contact', 'Thank you for contacting us. We will respond to you as soon as possible.');
                $this->refresh();
            }
        }
        $this->render('contact', array('model' => $model));
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        $model = new LoginForm;

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login())
                $this->redirect(Yii::app()->user->returnUrl);
        }
        // display the login form
        $this->render('login', array('model' => $model));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionChecklike()
    {
        $status = $_POST['status'];
        $uid = $_POST['uid'];
        $question_id = $_POST['question_id'];
        $checkLike = Like::model()->findByAttributes(
            array(
                'subscriber_id' => $uid,
                'question_id' => $question_id,
            )
        );
        $question = Question::model()->findByPk($question_id);
        if ($status == 0) {
            if(count($checkLike) == 0){
                $like = new Like();
                $like->subscriber_id = $uid;
                $like->question_id = $question_id;
                if (!$like->save()) {
                    echo '<pre>';
                    print_r($like->getErrors());
                }
                $question->count_like += 1;
                $question->save();
            }
        } else {
            $like_del = Like::model()->deleteAllByAttributes(array('subscriber_id' => $uid, 'question_id' => $question_id));
            $question->count_like -= 1;
            $question->save();
        }
        return;
    }
    public function actionResetPassword(){
        $this->layout = '/main2';
        $this->titlePage = 'xác nhận';
        $subId = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        Yii::log("subId:". $subId);
        $subId = base64_decode($subId);
        Yii::log("subId-decode:". $subId);
        if($subId == 0){
            echo 'Tài khoản reset password không tôn tại';die;
        }
        $this->render('site/resetpass', array('subId' => $subId));
    }
    public function actionConfirmPass(){
        $subId = isset($_POST['subsId']) ? $_POST['subsId'] : 0;
        $pass1 = isset($_POST['pass1']) ? $_POST['pass1'] : 0;
        $pass2 = isset($_POST['pass2']) ? $_POST['pass2'] : 0;
        if($subId == 0){
            echo 'Lỗi hệ thống, xin vui long liên hệ admin';die;
        }
//        $subId = base64_decode($subId);
        if(strlen($pass1) < 4){
            echo 'Password từ 4 ký tự trở lên';die;
        }
        if($pass1 != $pass2){
            echo 'Password không trùng nhau';die;
        }
        $subscriber = Subscriber::model()->findByPk($subId);
        if(count($subscriber) == 0){
            echo 'Username không tồn tại';die;
        }
        $subscriber->password = MD5($pass1).'_echat';
        if(!$subscriber->save()){
            echo 1;die;
        }
        echo 0;die;
    }

}
