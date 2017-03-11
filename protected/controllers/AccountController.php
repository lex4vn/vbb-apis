<?php

class AccountController extends Controller {

    const sessionTimeoutSeconds = 3600;
   protected $apiKey = "pkl";

    /**
     * Declares class-based actions.1
     */
    public function actions() {
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

    public function beforeAction($action) {
        if (strcasecmp($action->id, 'accuracyIndex') != 0 && strcasecmp($action->id, 'accuracy') != 0 && strcasecmp($action->id, 'smsagain') != 0 && strcasecmp($action->id, 'registerWeb') != 0 && strcasecmp($action->id, 'channelHs2') != 0 && strcasecmp($action->id, 'channel1loginface') != 0 && strcasecmp($action->id, 'channelHs1') != 0 && strcasecmp($action->id, 'Channelloginface') != 0 && strcasecmp($action->id, 'channelHs') != 0 && strcasecmp($action->id, 'savepartner') != 0 && strcasecmp($action->id, 'partner') != 0 && strcasecmp($action->id, 'accountNet2e') != 0 && strcasecmp($action->id, 'loginGoogle') != 0 && strcasecmp($action->id, 'loginface') != 0 && strcasecmp($action->id, 'login') != 0 && strcasecmp($action->id, 'register') != 0 && strcasecmp($action->id, 'index')) {
            $sessionKey = isset(Yii::app()->session['session_key']) ? Yii::app()->session['session_key'] : null;
            if ($sessionKey == null) {
                $this->redirect(Yii::app()->homeurl .'site');
            }
            $sessionKey = str_replace(' ', '+', $sessionKey);
            Yii::log("\n SessionKey: " . $sessionKey);
            if (!CUtils::checkAuthSessionKey($sessionKey)) {
                Yii::app()->user->logout();
                Yii::app()->session->clear();
                Yii::app()->session->destroy();
                Yii::app()->user->setFlash('responseToUser', 'Tài khoản Đã bị đăng nhập trên thiêt bị khác');
                $this->redirect(Yii::app()->homeurl .'site');
                return false;
            }
        }
        return parent::beforeAction($action);
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        $this->render('account/index', array());
    }
    
    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

   
    public function actionLogin() {

        if (isset($_POST['submit'])) {

            if (!isset($_POST['username']) || $_POST['username'] == '') {
                Yii::app()->user->setFlash('responseToUser', 'Vui lòng nhập tên đăng nhập!');
                $this->redirect(Yii::app()->homeurl . '/account/login');
            }
            if (!isset($_POST['password']) || $_POST['password'] == '' ) {
                Yii::app()->user->setFlash('responseToUser', 'Vui lòng nhập mật khẩu!');
                $this->redirect(Yii::app()->homeurl . '/account/login');
            }
            $username = $_POST['username'];
            $password = md5($_POST['password']);

            $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
            $uniqueId = uniqid();

            $apiConfig = new ApiConfig(API_KEY, $uniqueId, CLIENT_NAME, CLIENT_VERSION, PLATFORM_NAME, PLATFORM_VERSION);
            $apiConnector = new GuzzleProvider(API_URL);
            $api = new Api($apiConfig, $apiConnector);

            $response = $api->callRequest('api_init', [
                'clientname' => CLIENT_NAME,
                'clientversion' => CLIENT_VERSION,
                'platformname' => PLATFORM_NAME,
                'platformversion' => PLATFORM_VERSION,
                'uniqueid' => $uniqueId]);
            // Get token key
            $accessToken = $response['apiaccesstoken'];

            $apiConfig->setAccessToken($accessToken);
            $api = new Api($apiConfig, $apiConnector);
            if(!$isEmail){
                $response = $api->callRequest('login_login', [
                    'vb_login_username' => $username,
                    'vb_login_md5password' => $password
                ]);
            }else {
                // username null search username  by email.
                $response = $api->callRequest('api_emailsearch', [
                    'fragment' => $username,'api_v'=> '1'
                ]);
                if (count($response) >= 3) {
                    $response = $api->callRequest('login_login', [
                        'vb_login_username' => $response[1],
                        'vb_login_md5password' => $password
                    ]);
                }else{
                    Yii::app()->user->setFlash('responseToUser', 'Email của bạn chưa đăng ký!');
                    $this->redirect(Yii::app()->homeurl . 'account');
                }
            }
            if (isset($response['response'])) {
                if (isset($response['response']->errormessage)) {
                    $result = $response['response']->errormessage[0];
                    if ('badlogin_strikes_passthru' == $result) {
                        Yii::app()->user->setFlash('responseToUser', 'Sai tên đăng nhập hoặc mật khẩu!');
                        $this->redirect(Yii::app()->homeurl . 'account');
                    }
                    if ('strikes' == $result) {
                        Yii::app()->user->setFlash('responseToUser', 'Bạn đã gõ sai qua nhiều, vui lòng đợi 15 phút.');
                        $this->redirect(Yii::app()->homeurl . 'account');
                    }

                    if ('redirect_login' == $result &&  $response['session']->userid !== '0') {
                        $sessionKey = CUtils::generateSessionKey($response['session']->userid,$response['response']->errormessage[1],base64_encode(serialize($apiConfig)));

                        $username = $response['response']->errormessage[1];
                        $user_id = $response['session']->userid;
                        $response = $api->callRequest('member', [
                            'u'=> $user_id,
                            'api_v' => '1'
                        ], ConnectorInterface::METHOD_POST);

                        if(isset($response['response'])){

                            $phonenumber = '';
                            if(isset($response['response']->blocks)) {
                                $fields = $response['response']->blocks->aboutme->block_data->fields->category->fields;
                                foreach ($fields as $field) {
                                    if ($field->profilefield->title == "Phone Number") {
                                        $phonenumber = $field->profilefield->value;
                                    }
                                }
                            }

                            $usertitle = '';
                            $avatar = '';
                            $status = '';
                            if(isset($response['response']->prepared)) {
                                $usertitle = $response['response']->prepared->usertitle;
                                $avatar = str_replace("amp;","",API_URL.$response['response']->prepared->avatarurl);
                                $status = $response['response']->prepared->onlinestatus->onlinestatus == 1 ? "1" : "0";
                            }

                            $user = User::model()->findByPk($user_id);
                            if($user == null){
                                $user = new User();
                                $user->userid = $user_id;
                                $user->username = $username;
                                $user->phonenumber =  $phonenumber;
                                $user->usertitle = $usertitle;
                                $user->avatar = $avatar;
                                $user->status = $status;
                            }else{
                                $user->phonenumber =  $phonenumber;
                                $user->usertitle = $usertitle;
                                //$user->avatar = $avatar;
                                $user->status = $status;
                            }
                            $user->save();

                            Yii::app()->session['user_id'] = $user->userid;
                            $sessionKey = CUtils::generateSessionKey($user->userid);
                            Yii::app()->session['session_key'] = $sessionKey;
                            Yii::app()->user->setState('userSessionTimeout', time() + self::sessionTimeoutSeconds);
                            if (!$this->detect->isMobile() && !$this->detect->isTablet()) {
                                $this->redirect(Yii::app()->homeurl . 'post ');
                            }else{
                                $this->redirect(Yii::app()->homeurl . 'post');
                            }
                        }

                    }
                }
            }else {
                Yii::app()->user->setFlash('responseToUser', 'Hệ thống đang bận, vui lòng quay lại sau');
                $this->redirect(Yii::app()->homeurl . 'account');
            }
        }
        $this->render('account/index');
    }

    public function actionLogout() {
//        unset(Yii::app()->session['user_id']);
        //$this->user_id = '';
        $sessionKey = isset(Yii::app()->session['session_key']) ? Yii::app()->session['session_key'] : null;
        if($sessionKey != null){
            $CUtils = new CUtils();
            $keyDecrypt = $CUtils->decrypt($sessionKey, secret_key);
            Yii::log("\ncheckAuthSessionKey decrypt: " . $keyDecrypt);
            $arrSsKey = explode("|", $keyDecrypt);
            $session = AuthToken::model()->findByPk($arrSsKey[0]);
            $subscriber = Subscriber::model()->findByPk($session->subscriber_id);
            if($subscriber->type == 2){
                $session->expiry_date = time();
                $session->save();
            }
        }
        session_unset();
        session_destroy();
        Yii::app()->session->clear();
        Yii::app()->session->destroy();
        $this->redirect(Yii::app()->homeurl . 'site');
    }

    public static function makeRequestID() {
       $id = rand(100000, 999999);
       return $id;
    }


    public function actionLoginface() {
        if (!isset($_SESSION)) {
            session_start();
        }
        $app_id = "1054223384618565";
        $app_secret = "778ecfdd7c95f1ff723ed0593ad24fea";
        $redirect_uri = urlencode("");

        // Get code value
        $code = $_GET['code'];
        // Get access token info
        $facebook_access_token_uri = "https://graph.facebook.com/oauth/access_token?client_id=$app_id&redirect_uri=$redirect_uri&client_secret=$app_secret&code=$code";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $facebook_access_token_uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);

        curl_close($ch);
        // Get access token
        $aResponse = explode("&", $response);

        $access_token = str_replace('access_token=', '', $aResponse[0]);
        $token = TmpToken::model()->findByAttributes(array("token"=>$access_token));
        if($token == null){
            $token = new TmpToken();
            $token->token = $access_token;
            $token->created = date("Y-m-d H:i:s");
            $token->save();
        }
        // Get user infomation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?access_token=$access_token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        $user = json_decode($response);
        // Log user in
//        $_SESSION['user_login'] = true;
        if ($user) {
            $uid = isset($user->id) ? $user->id : '';
            $fname = isset($user->name) ? $user->name : '';
            $fbirthday = isset($user->birthday) ? $user->birthday : '';
            $fmail = isset($user->email) ? $user->email : '';
            $fgender = isset($user->gender) ? $user->gender : '';
            $access_token = $access_token;
        }
//        echo '<pre>'; print_r($_SESSION);die;
        $checkUser = Subscriber::model()->findByAttributes(array('username' => $uid));
        if (count($checkUser) > 0) {
            $checkUser->firstname = '';
            $checkUser->lastname = $fname;
            $checkUser->password = 'faccebook';
            $checkUser->url_avatar = 'http://graph.facebook.com/' . $uid . '/picture?type=square';
            if (!$checkUser->save()) {
                echo '<pre>';
                print_r($checkUser->getErrors());
            }
            Yii::app()->session['user_id'] = $checkUser->id;
            $sessionKey = CUtils::generateSessionKey($checkUser->id);
            Yii::app()->session['session_key'] = $sessionKey;
            yii::app()->user->setState('userSessionTimeout', time() + self::sessionTimeoutSeconds);
            header("location: https://");
            exit();
        } else {
            $subs = new Subscriber();
            $subs->firstname = '';
            $subs->lastname = $fname;
            $subs->username = $uid;
            $subs->password = 'faccebook';
            $subs->device_type = '';
            $subs->phone_number = '';
            $subs->email = $fmail;
            $subs->type = 1;
            $subs->status = 1;
//            $subs->fcoin = CUtils::isInEvent()? FREE_ONCASH_EVENT : FREE_ONCASH;
            $subs->fcoin = 0;
            $subs->partner_id = 'net2e';
            $subs->url_avatar = 'http://graph.facebook.com/' . $uid . '/picture?type=square';
            $subs->create_date = date('Y-m-d H:i:s');
            if (!$subs->save()) {
                echo '<pre>';
                print_r($subs->getErrors());
            }
            Yii::app()->session['user_id'] = $subs->id;
            $sessionKey = CUtils::generateSessionKey($subs->id);
            Yii::app()->session['session_key'] = $sessionKey;
            yii::app()->user->setState('userSessionTimeout', time() + self::sessionTimeoutSeconds);
            if (isset(Yii::app()->session['user_id'])) {
                header("location:  https://www.pkl.vn/account/type");
                exit();
            } else {
                header("location: https://www.pkl.vn/");
                exit();
            }
        }
    }

    public function actionLoginGoogle() {
        if (!isset($_SESSION)) {
            session_start();
        }
        //    require_once 'src/config.php';
        require_once 'src/Google_Client.php';
        require_once 'src/contrib/Google_PlusService.php';
        require_once 'src/contrib/Google_Oauth2Service.php';
        $client = new Google_Client();
        $client->setScopes(array('https://www.googleapis.com/auth/plus.login', 'https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/plus.me'));
        $client->setApprovalPrompt('auto');
        $service = new Google_Oauth2Service($client);
        if (isset($_GET['type']) && $_GET['type'] == 'google') {
            $authUrl = $client->createAuthUrl();
            header('Location: ' . $authUrl);
        }
        $plus = new Google_PlusService($client);
        $oauth2 = new Google_Oauth2Service($client);
        //unset($_SESSION['access_token']);

        if (isset($_GET['code'])) {
            $client->authenticate(); // Authenticate
            $_SESSION['access_token'] = $client->getAccessToken(); // get the access token here 
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        }

        if (isset($_SESSION['access_token'])) {
            $client->setAccessToken($_SESSION['access_token']);
        }
//	print_r($client->getAccessToken());die;
        if ($client->getAccessToken()) {
            $userProfile = $service->userinfo->get();
            $uid = isset($userProfile['id']) ? $userProfile['id'] : '';
            $Glastname = isset($userProfile['family_name']) ? $userProfile['family_name'] : '';
            $Gfirstname = isset($userProfile['given_name']) ? $userProfile['given_name'] : '';
            $Gmail = isset($userProfile['email']) ? $userProfile['email'] : '';
            $Gpicture = isset($userProfile['picture']) ? $userProfile['picture'] : '';
//		echo "<pre>";print_r($userProfile);die;
            $checkUser = Subscriber::model()->findByAttributes(array('username' => $uid));
            if (count($checkUser) > 0) {
                $checkUser->firstname = $Gfirstname;
                $checkUser->lastname = $Glastname;
                $checkUser->password = 'Google';
                $checkUser->email = $Gmail;
                $checkUser->url_avatar = $Gpicture;
                if (!$checkUser->save()) {
                    echo '<pre>';
                    print_r($checkUser->getErrors());
                }
                Yii::app()->session['user_id'] = $checkUser->id;
                $sessionKey = CUtils::generateSessionKey($checkUser->id);
            } else {
                $subs = new Subscriber();
                $subs->firstname = $Gfirstname;
                $subs->lastname = $Glastname;
                $subs->username = $uid;
                $subs->password = 'Google';
                $subs->device_type = '';
                $subs->phone_number = '';
                $subs->email = $Gmail;
                $subs->type = 1;
                $subs->status = 1;
                $subs->fcoin = 0;
                $subs->url_avatar = $Gpicture;
                $subs->create_date = date('Y-m-d H:i:s');
                if (!$subs->save()) {
                    echo '<pre>';
                    print_r($subs->getErrors());
                }
                Yii::app()->session['user_id'] = $subs->id;
                $sessionKey = CUtils::generateSessionKey($subs->id);
            }
            Yii::app()->session['session_key'] = $sessionKey;
            yii::app()->user->setState('userSessionTimeout', time() + self::sessionTimeoutSeconds);
            header("location: http://pkl.onedu.vn/profile/");
            exit();
        }
        header("location: http://pkl.onedu.vn/account/");
        exit();
    }


}
