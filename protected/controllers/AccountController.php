<?php

class AccountController extends Controller {

    const sessionTimeoutSeconds = 3600;
   protected $apiKey = "hocde";

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
    public function actionCard(){
        $this->titlePage = 'Nạp thẻ | Học Dễ';
        $action = Yii::app()->controller->action->id;
        $issuer = isset($_POST['issuer']) ? $_POST['issuer'] : null;
        $cardSerial = isset($_POST['cardSerial']) ? $_POST['cardSerial'] : null;
        $cardCode = isset($_POST['cardCode']) ? $_POST['cardCode'] : null;
//		$subId = isset($_POST['subId']) ? $_POST['subId'] : null;
        $subId = isset(Yii::app()->session['user_id']) ? Yii::app()->session['user_id'] : null;
        $html = HtmlContent::model()->findAll();
        $type = 1;
        $responseMessage = "";
        if ($cardSerial == null || $cardCode == null || $issuer == null) {
            $this->render('account/use_card', array('action' => $action, 'html' => $html,'type'=>$type));
            return;
        } else {
//                    echo $this->userName->partner_id;die;
            Yii::log("-------------------------------------------------------------------------$cardSerial---------$cardSerial---------$cardSerial---------");
//			echo $issuer."|".$cardSerial."|".$cardCode;
            //get subscriber -- ktra co trong he thong chua
            $subscriber = Subscriber::model()->findByPk($subId);
            if ($subscriber == null) {
                $responseMessage = ContentResponse::getErrorMessageWap(SUB_NOT_EXIST, "subId");
                $this->render('account/use_card', array('action' => $action));
                return;
            }
            if ($subscriber->partner_id == null) {
                $partner_id = 'net2e';
            } else {
                $partner_id = $subscriber->partner_id;
            }
            //ktra xem seria + cardcode da dung dinh dang chua
            if (!$this->checkCardCode($issuer, $cardCode, $cardSerial))
                return;

            //check nap sai qua 3 lan -> block 5phut
            $transaction = $subscriber->newTransaction(PURCHASE_TYPE_NEW, $cardCode, $cardSerial, $issuer, $partner_id);
            //Nap the cua don vi phat hanh hocde
//                             kenh hoc de
            $voucherPayment = new VoucherPayment();
            $res = $voucherPayment->payment($issuer, $cardSerial, $cardCode, $transaction->id, $subscriber->username);
            $resultoObj = ContentResponse::parserResult($res);
            // ket qua
            if ($resultoObj->status == CARD_OK) {
//                $this->checkLoopUseCard($subscriber->id, false); // check nap the sai qua 3 lan
                $transaction->status = 1;
                $subscriber->fcoin += intval($resultoObj->amount) / 100;
                $subscriber->save();
                //update telco check active the
                $generate_acitive = GenerateCardActive::model()->findByAttributes(array('subscriber_id' => $subscriber->id, 'status' => 1, "telco" => 0));
                if ($generate_acitive != null) {
                    $generate_acitive->telco = 1;
                    $generate_acitive->status = 2;
                    $generate_acitive->save();
                }
                //
                $responseMessage = "Bạn nạp thẻ thành công";
                //                            Yii::app()->user->setFlash('result', "Bạn nạp thẻ thành công");
            } else {
                $responseMessage = ContentResponse::getErrorMessageWapNet2E($resultoObj->status);
            }
            $transaction->error_code = $resultoObj->description;
            $transaction->card_seria = $resultoObj->cardSerial;
            $transaction->description = $resultoObj->cardCode;
            $transaction->cost = $resultoObj->amount;
            $transaction->oncash = (intval($resultoObj->amount)) / 100;
            $transaction->save();
        }
        $this->render('account/use_card', array('message' => $responseMessage, 'action' => $action, 'html' => $html,'type'=>$type));
    }
    public function actionUseCard() {
        $this->titlePage = 'Nạp thẻ | Học Dễ';
        $action = Yii::app()->controller->action->id;
        $issuer = isset($_POST['issuer']) ? $_POST['issuer'] : null;
        $cardSerial = isset($_POST['cardSerial']) ? $_POST['cardSerial'] : null;
        $cardCode = isset($_POST['cardCode']) ? $_POST['cardCode'] : null;
//		$subId = isset($_POST['subId']) ? $_POST['subId'] : null;
        $subId = isset(Yii::app()->session['user_id']) ? Yii::app()->session['user_id'] : null;
        $html = HtmlContent::model()->findAll();
        $type = 1;
        $responseMessage = "";
        if ($cardSerial == null || $cardCode == null || $issuer == null) {
            $this->render('account/use_card', array('action' => $action, 'html' => $html,'type'=>$type));
            return;
        } else {
//                    echo $this->userName->partner_id;die;
            Yii::log("-------------------------------------------------------------------------$cardSerial---------$cardSerial---------$cardSerial---------");
//			echo $issuer."|".$cardSerial."|".$cardCode;
            //get subscriber -- ktra co trong he thong chua
            $subscriber = Subscriber::model()->findByPk($subId);
            if ($subscriber == null) {
                $responseMessage = ContentResponse::getErrorMessageWap(SUB_NOT_EXIST, "subId");
                $this->render('account/use_card', array('action' => $action));
                return;
            }
            if ($subscriber->partner_id == null) {
                $partner_id = 'net2e';
            } else {
                $partner_id = $subscriber->partner_id;
            }
            //ktra xem seria + cardcode da dung dinh dang chua
            if (!$this->checkCardCode($issuer, $cardCode, $cardSerial))
                return;

            //check nap sai qua 3 lan -> block 5phut
            $transaction = $subscriber->newTransaction(PURCHASE_TYPE_NEW, $cardCode, $cardSerial, $issuer, $partner_id);
            //Nap the cua don vi phat hanh hocde
            if (strtoupper($issuer) === HOCDE || strtoupper($issuer) === CODE40) {
                if (strtoupper($issuer) === HOCDE) {
                    $resultoObj = $this->useCardHocde($cardSerial, $cardCode, $subscriber->id, $transaction);
                } else {
                    $resultoObj = $this->useCardHocdeCode40($cardSerial, $cardCode, $subscriber->id);
                }
            } else if (strtoupper($issuer) === ONCASH) {
                $net2EPayment = new Net2EPayment();
                $res = $net2EPayment->paymentNet2E($issuer, $cardSerial, $cardCode, $transaction->id, $subscriber->username);
                $resultoObj = ContentResponse::parserResultNet2e($res);
            } else { //Nap the cua cac don vi mobile 
//                             kenh hoc de
//                            $voucherPayment = new VoucherPayment();
//                            $res = $voucherPayment->payment($issuer, $cardSerial, $cardCode, $transaction->id);
//                            $resultoObj = ContentResponse::parserResult($res);
                // kenh net2E
                $net2EPayment = new Net2EPayment();
                $res = $net2EPayment->paymentNet2E($issuer, $cardSerial, $cardCode, $transaction->id, $subscriber->username);
                $resultoObj = ContentResponse::parserResultNet2e($res);
            }
//                    if($resultoObj->errorCode == CARD_OK){ // kenh hocde
            if (strtoupper($issuer) === HOCDE || strtoupper($issuer) === CODE40) {
                if (strtoupper($issuer) === HOCDE) {
                    if ($resultoObj->errorCode == CARD_OK) {
                        $transaction->status = 1;
                        $subscriber->fcoin += intval($resultoObj->amount) / 100;
                        $subscriber->save();
                        /* them bang generate_card_active
                         * auth Hungld
                         * create date 2016-09-05
                         */
                        $generate_acitive = GenerateCardActive::model()->findByAttributes(array('subscriber_id' => $subscriber->id, 'status' => 1));
                        if ($generate_acitive == null) {
                            $generate_acitive = new GenerateCardActive();
                            $generate_acitive->subscriber_id = $subscriber->id;
                            $generate_acitive->number = 1;
                            $generate_acitive->status = 1;
                            $generate_acitive->start_date = date("Y-m-d H:i:s");
                            $generate_acitive->end_date = date("Y-m-d 23:59:59", time() + 30 * 60 * 60 * 24);
                            $generate_acitive->create_date = date("Y-m-d H:i:s");
                            $generate_acitive->save();
                        }
                        /*
                         * END
                         */
                        $responseMessage = "Bạn nạp thẻ thành công";
                    } else {
                        $responseMessage = ContentResponse::getErrorMessageWap($resultoObj->errorCode);
                    }
                    $transaction->error_code = $resultoObj->errorCode;
                    $transaction->description = $resultoObj->errorMessage;
                    $transaction->cost = $resultoObj->amount;
                    $transaction->oncash = (intval($resultoObj->amount)) / 100;
                    $transaction->save();
                } else {
                    $oncard = 0;
                    if ($resultoObj->errorCode == CARD_OK) {
                        $transaction->status = 1;
                        $responseMessage = "Bạn nạp thẻ thành công";
                        if ($resultoObj->amount == 40) {
                            MapCode::model()->activeCode40($subscriber->id);
                            $oncard = 2000;
                        }
                        $subscriber->fcoin += $oncard;
                        $subscriber->save();
                        //update telco check active the
                        $generate_acitive = GenerateCardActive::model()->findByAttributes(array('subscriber_id' => $subscriber->id, 'status' => 1, "telco" => 0));
                        if ($generate_acitive != null) {
                            $generate_acitive->telco = 1;
                            $generate_acitive->save();
                        }
                    } else {
                        $responseMessage = ContentResponse::getErrorMessageWap($resultoObj->errorCode);
                    }
                    $transaction->error_code = $resultoObj->errorCode;
                    $transaction->description = $resultoObj->errorMessage;
                    $transaction->cost = 0;
                    $transaction->oncash = $oncard;
                    $transaction->save();
                }
            } else {
                if ($resultoObj->returnCode == CARD_ONCASH_OK) { // kenh net2e
//                            $this->checkLoopUseCard($subscriber->id, false);
                    $transaction->status = 1;
                    $subscriber->fcoin += intval($resultoObj->cardPrice) / 100;
                    $subscriber->save();
                    //update telco check active the
                    $generate_acitive = GenerateCardActive::model()->findByAttributes(array('subscriber_id' => $subscriber->id, 'status' => 1, "telco" => 0));
                    if ($generate_acitive != null) {
                        $generate_acitive->telco = 1;
                        $generate_acitive->status = 2;
                        $generate_acitive->save();
                    }
                    //
                    $responseMessage = "Bạn nạp thẻ thành công";
                    //                            Yii::app()->user->setFlash('result', "Bạn nạp thẻ thành công");
                } else {
                    $responseMessage = ContentResponse::getErrorMessageWapNet2E($resultoObj->returnCode);
                }
                $transaction->error_code = $resultoObj->returnCode;
                $transaction->card_seria = $resultoObj->returnSerial;
                $transaction->description = $resultoObj->returnDescription;
                $transaction->cost = $resultoObj->cardPrice;
                $transaction->oncash = (intval($resultoObj->cardPrice)) / 100;
                $transaction->save();
            }
        }
        $this->render('account/use_card', array('message' => $responseMessage, 'action' => $action, 'html' => $html,'type'=>$type));
    }

    public function checkCardCode($issuer, $cardCode, $cardSerial) {
        switch (strtoupper($issuer)) {
            case VT:
                if (strlen($cardSerial) > 15 || strlen($cardSerial) < 11) {
                    ContentResponse::getErrorMessageWap(CARD_SERIA_INVALID, "cardSerial");
                    return false;
                }
                if (strlen($cardCode) > 15 || strlen($cardCode) < 13 || !preg_match('/^[0-9]*$/', $cardCode)) {
                    ContentResponse::getErrorMessageWap(CARD_SERIA_CODE_INVALID, "cardCode");
                    return false;
                }
                break;
            case MOBI:
                if (strlen($cardSerial) > 15 || strlen($cardSerial) < 9) {
                    ContentResponse::getErrorMessageWap(CARD_SERIA_INVALID, "cardSerial");
                    return false;
                }
                if ((strlen($cardCode) != 12 && strlen($cardCode) != 14 && strlen($cardCode) != 15) || !preg_match('/^[0-9]*$/', $cardCode)) {
                    ContentResponse::getErrorMessageWap(CARD_SERIA_CODE_INVALID, "cardCode");
                    return false;
                }
                break;
            case VINA:
                if (strlen($cardSerial) > 15 || strlen($cardSerial) < 8) {
                    ContentResponse::getErrorMessageWap(CARD_SERIA_INVALID, "cardSerial");
                    return false;
                }
                if ((strlen($cardCode) != 12 && strlen($cardCode) != 14) || !preg_match('/^[0-9]*$/', $cardCode)) {
                    ContentResponse::getErrorMessageWap(CARD_SERIA_CODE_INVALID, "cardCode");
                    return false;
                }
                break;
            case HOCDE:
                if ((strlen($cardCode) != 14 && strlen($cardCode) != 15)) {
                    ContentResponse::getErrorMessageWap(CARD_SERIA_CODE_INVALID, "cardCode");
                    return false;
                }
                if (strlen($cardSerial) != 14 && strlen($cardSerial) != 15) {
                    ContentResponse::getErrorMessageWap(CARD_SERIA_INVALID, "cardSerial");
                    return false;
                }
                break;
        }
        return true;
    }

    public function checkLoopUseCard($subscriber_id, $error = true) {
        $today = date("Y-m-d");
        $blocktime = 360; //s
        $str_today = strtotime($today);
        Yii::log("Check Loop Use Card today: $str_today\n");
        if ($error) {
            if (CUtils::hasCookie($subscriber_id)) {
                $value = explode("|", CUtils::getCookie($subscriber_id));
                Yii::log("cookie str_today:\n");
                if ($str_today == $value[1]) {
                    $time_use = intval($value[0]) + 1;
                    $cookieTmp = $time_use . "|" . $value[1];
                    CUtils::setCookie($subscriber_id, $cookieTmp, $blocktime);
                    if ($time_use > 3)
                        return false;
                } else {
                    Yii::log("Check Loop Use Card remove cooke:\n");
                    CUtils::removeCookie($subscriber_id);
                    return true;
                }
            } else {
                Yii::log("Check Loop Use Card set cookie sub_id: $subscriber_id | str_today: $str_today| block: $blocktime\n");
                CUtils::setCookie($subscriber_id, "1|$str_today", $blocktime);
                return true;
            }
        } else {
            Yii::log("Check Loop Use Card remove cooke:\n");
            CUtils::removeCookie($subscriber_id);
            return true;
        }
    }

    protected function useCardHocde($cardSeria, $cardCode, $subscriberId, $transaction) {
        $checkCard = GenerateCard::model()->findByAttributes(array('card_seria' => $cardSeria, 'status' => 1));
        $resultoObj = ContentResponse::getResultCode();
        if ($checkCard != null) {
            if ($checkCard->type == 1) {//type, 1: bt, 2:khuyen mai
                if ($checkCard->card_code === $cardCode) {
                    $checkCard->status = 2;
                    if ($checkCard->save()) {
                        $resultoObj->amount = $checkCard->amount;
                        $resultoObj->errorCode = CARD_OK;
                        $resultoObj->errorMessage = 'Kiem tra thanh cong';
                    } else {
                        $resultoObj->errorCode = CARD_NOT_OK;
                        $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
                    }
                }
            } else {
                Yii::log("\n partner the: " . $checkCard->partner);
//                        if($checkCard->partner == 'net2e'){
//                            $resultoObj = $this->checkPartnerNet2e($checkCard, $cardCode, $resultoObj, $subscriberId);
//                        }else 
                if ($checkCard->partner == 'long') {
                    Yii::log("\n partner the: " . $checkCard->partner);
                    $resultoObj = $this->checkPartnerLong($checkCard, $cardCode, $resultoObj, $subscriberId);
                } else if ($checkCard->partner == 'telesale') {//partner:telesale
                    Yii::log("\n partner the: " . $checkCard->partner);
                    $resultoObj = $this->checkPartnerTelesale($checkCard, $cardCode, $resultoObj, $subscriberId, $transaction);
                } else if ($checkCard->partner == 'trinhhoan') {//partner:telesale
                    Yii::log("\n partner the: " . $checkCard->partner);
                    $resultoObj = $this->checkPartnerTrinhhoan($checkCard, $cardCode, $resultoObj, $subscriberId, $transaction);
                }else{
                    if ($checkCard->card_code === $cardCode) {
                        $checkCard->status = 2;
                        if ($checkCard->save()) {
                            $resultoObj->amount = $checkCard->amount;
                            $resultoObj->errorCode = CARD_OK;
                            $resultoObj->errorMessage = 'Kiem tra thanh cong';
                        } else {
                            $resultoObj->errorCode = CARD_NOT_OK;
                            $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
                        }
                    }
                }
            }
        } else {
            $resultoObj->errorCode = CARD_NOT_OK;
            $resultoObj->errorMessage = 'Ma so nap tien khong ton tai hoac da duoc su dung';
        }
        return $resultoObj;
    }

    protected function useCardHocdeCode40($cardSeria, $cardCode, $subscriberId) {
        $checkCard = GenerateCodeMonth::model()->findByAttributes(array('card_seria' => $cardSeria, 'status' => 1));
        $resultoObj = ContentResponse::getResultCode();
        if ($checkCard != null) {
            if ($checkCard->card_code === $cardCode) {
                $checkCard->status = 2;
                if ($checkCard->save()) {
                    if ($checkCard->type == 1) {
                        $total = 40;
                    } else {
                        $total = 0;
                    }
                    $resultoObj->amount = $total;
                    $resultoObj->errorCode = CARD_OK;
                    $resultoObj->errorMessage = 'Kiem tra the code40 thanh cong';
                } else {
                    $resultoObj->errorCode = CARD_NOT_OK;
                    $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
                }
            }
        } else {
            $resultoObj->errorCode = CARD_NOT_OK;
            $resultoObj->errorMessage = 'Ma so nap the khong ton tai hoac da duoc su dung';
        }
        return $resultoObj;
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

    /**
     * Displays the contact page
     */
    public function actionContact() {
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

    public function actionLogin() {
        if (isset($_POST['submit'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $partner =  isset($_POST['partner']) ? $_POST['partner'] : null;
            $pass = MD5($password) . '_echat';
//            if ("net2e_web" == $partner ) {
//                $user = Subscriber::model()->findByAttributes(array('username' => $username, 'partner_id' => "net2e"));
//            } else {
                $user = Subscriber::model()->findByAttributes(array('username' => $username));
//            }
//                $user1 = Subscriber::model()->findByAttributes(array('username'=>$username, 'status'=>5));
            if ($user != null && $user->status == 5) {
                Yii::app()->user->setFlash('responseToUser', 'Tài khoản của bạn đang tạm khóa, xin vui lòng gọi đường dây nóng (04 4450 8388) để biết thêm chi tiết');
                $this->redirect(Yii::app()->homeurl . 'account');
            } else if ($user != null && $user->status == 2) {
                Yii::app()->user->setFlash('responseToUser', 'Tài khoản của bạn chưa được kich hoạt!');
                $userid_encrypt = CUtils::encrypt($user->id, $this->apiKey);
                return $this->redirect(Yii::app()->homeurl . 'account/accuracyIndex?user_id='.$userid_encrypt.'&response=200');
//                $this->redirect(Yii::app()->homeurl . 'account');
            } else if ($user == null) {
                Yii::app()->user->setFlash('responseToUser', 'Tài khoản hoặc mật khẩu của bạn chưa đúng!');
                $this->redirect(Yii::app()->homeurl . 'account');
            }
            if ($user->password == $pass) {
                Yii::app()->session['user_id'] = $user->id;
                $sessionKey = CUtils::generateSessionKey($user->id);
                Yii::app()->session['session_key'] = $sessionKey;
                Yii::app()->user->setState('userSessionTimeout', time() + self::sessionTimeoutSeconds);
                if (!$this->detect->isMobile() && !$this->detect->isTablet()) {
                     $this->redirect(Yii::app()->homeurl . 'question/list ');
                }else{
                    $this->redirect(Yii::app()->homeurl . 'site');
                }
            } else {
                Yii::app()->user->setFlash('responseToUser', 'Tài khoản hoặc mật khẩu của bạn chưa đúng!');
                $this->redirect(Yii::app()->homeurl . 'account');
            }
        } else {
            $this->redirect(Yii::app()->homeurl . 'account');
        }
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

    public function actionRegisterWeb() {
        if (isset($_POST['submit'])) {
            $firtname = $_POST['firtname'];
            $lastname = $_POST['lastname'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];
            $mobile = $_POST['mobile'];
            $email = $_POST['email'];
            $type = (!isset($_POST['type_account'])) ? 1 : $_POST['type_account'];
            $partnerid = 'net2e';
            Yii::log("--username:--------$username---------");
            if ($firtname == '') {
                Yii::app()->user->setFlash('responseToUser', 'Tài khoản của bạn không được bỏ trống tên.');
                return $this->render('account/register');
            }
            if ($username == '' || strlen($username) < 6) {
                Yii::app()->user->setFlash('responseToUser', 'Tài khoản của bạn không hợp lệ, tài khoản từ 6 ký tự trở lên.');
                return $this->render('account/register');
            }
            if ($password == '' || strlen($password) < 6) {
                Yii::app()->user->setFlash('responseToUser', 'Mật khẩu của bạn không hợp lệ, mật khẩu từ 6 ký tự trở lên.');
                return $this->render('account/register');
            }
            if ($password != $password_confirm) {
                Yii::app()->user->setFlash('responseToUser', 'Mật khẩu xác nhận không trùng.');
                return $this->render('account/register');
            }
            $password = MD5($_POST['password']) . '_echat';
            $subscriber = Subscriber::model()->findByAttributes(array('username' => $username));
            if ($subscriber != null) {
                Yii::app()->user->setFlash('responseToUser', 'Tài khoản của bạn đã tồn tại.');
                return $this->render('account/register');
            }
            if($mobile != '01266625777' && $mobile != '0989869555' && $mobile != '0989869555'){
                $subscriberMobile = Subscriber::model()->findByAttributes(array('phone_number' => $mobile));
                if ($subscriberMobile != null) {
                    Yii::app()->user->setFlash('responseToUser', 'Số điện thoại đã tồn tại.');
                    return $this->render('account/register');
                }
            }
//            $subscriberEmail = Subscriber::model()->findByAttributes(array('email' => $email));
//            if ($subscriberEmail != null) {
//                Yii::app()->user->setFlash('responseToUser', 'Email của bạn đã tồn tại.');
//                return $this->render('account/register');
//            }
            $subs = new Subscriber();
            $subs->firstname = $firtname;
            $subs->lastname = $lastname;
            $subs->username = $username;
            $subs->password = $password;
            $subs->phone_number = $mobile;
            $subs->email = $email;
            $subs->type = $type;
            $freeOncash = CUtils::isInEvent()? FREE_ONCASH_EVENT : FREE_ONCASH;
            if ($type == 1) {
                $subs->status = 2;
                $subs->fcoin = 0; // khuyen mai 25 oncash cho lan tao tai khoan tu 5/5/16
//                $subs->fcoin = $freeOncash; // khuyen mai 25 oncash cho lan tao tai khoan tu 5/5/16
            } else {
                $subs->fcoin = 0;
                $subs->status = 2;
            }
            $subs->partner_id = $partnerid;
            $subs->create_date = date('Y-m-d H:i:s');
            if ($subs->save()) {
                $user_id = $subs->primaryKey;
                $total = 0;
                $messege = self::makeRequestID();
                $apiService = new APIService();
                $response = $apiService->smsService($mobile, $messege, $user_id, 1);
                $userid_encrypt = CUtils::encrypt($user_id, $this->apiKey);
                Yii::log(Yii::app()->homeurl . 'account/accuracyIndex?user_id='.$subs->id.'&response='.$response);
                return $this->redirect(Yii::app()->homeurl . 'account/accuracyIndex?user_id='.$userid_encrypt.'&response=200');
           } else {
                Yii::app()->user->setFlash('responseToUser', 'Có lỗi xảy ra trong quá trình đăng ký');
                $this->redirect(Yii::app()->homeurl . '/account/register');
            }
        } else {
            $this->render('account/register');
        }
    }

    public function actionRegister() {
        $this->titlePage = "Đăng ký | Học Dễ";
        $firtname = $_POST['firtname'];
        $lastname = $_POST['lastname'];
        $username = $_POST['username'];
        $password = MD5($_POST['password']) . '_echat';
        $mobile = $_POST['mobile'];
        $email = $_POST['email'];
        $type = $_POST['type'];
        $partnerid = isset($_POST['partnerid']) ? $_POST['partnerid'] : 'net2e';
        Yii::log("--username:--------$username---------");
        Yii::log("--Register1:--------$partnerid---------");
        if (trim(strtolower($partnerid)) == '' || empty($partnerid)) {
            $partnerid = 'net2e';
        }
        $subscriber = Subscriber::model()->findByAttributes(array('username' => $username));
        if (count($subscriber) > 0) {
            echo '1';
            die;
        }
        $subscriberEmail = Subscriber::model()->findByAttributes(array('email' => $email));
        if ($subscriberEmail != null) {
            echo '2';
            die;
        }
        $subscriberMobile = Subscriber::model()->findByAttributes(array('phone_number' => $mobile));
        if ($subscriberMobile != null) {
            echo '3';
            die;
        }
        $partnerid = strtolower($partnerid);
        $subs = new Subscriber();
        $subs->firstname = $firtname;
        $subs->lastname = $lastname;
        $subs->username = $username;
        $subs->password = $password;
        $subs->phone_number = $mobile;
        $subs->email = $email;
        $subs->type = $type;
        $freeOncash = CUtils::isInEvent()? FREE_ONCASH_EVENT : FREE_ONCASH;
        if ($type == 1) {
            $subs->status = 2;
//            $subs->fcoin = $freeOncash;
            $subs->fcoin = 0;
        } else {
            $subs->fcoin = 0;
            $subs->status = 4;
        }
        $subs->partner_id = $partnerid;
        $subs->create_date = date('Y-m-d H:i:s');
        if ($subs->save()) {
            $messege = self::makeRequestID();
            $apiService = new APIService();
            $response = $apiService->smsService($mobile, $messege, $user_id, 1);
            $this->render('account/accuracy', array(
                'user_id' => $subs->id,
                'response'=>$response
            ));
//            $sessionKey = CUtils::generateSessionKey($user_id);
//            Yii::app()->session['session_key'] = $sessionKey;
//            Yii::app()->session['user_id'] = $user_id;
//            Yii::app()->user->setState('userSessionTimeout', time() + self::sessionTimeoutSeconds);
            
        } else {
            Yii::app()->user->setFlash('responseToUser', 'Có lỗi xảy ra trong quá trình đăng ký');
            $this->redirect(Yii::app()->homeurl . 'account');
        }
    }
    public function actionAccuracyIndex(){
        $userId = isset($_REQUEST['user_id']) ? $_REQUEST['user_id']: 0;
        $response = isset($_REQUEST['response']) ? $_REQUEST['response'] : 200;
        $userId = str_replace(' ', '+', $userId);
        Yii::log("encrypt".$userId);
        $userId = CUtils::decrypt($userId, $this->apiKey);
        Yii::log("decrypt".$userId);
        $userId = (int)$userId;
        if(!is_numeric($userId)){
            $this->redirect(Yii::app()->homeurl . 'account');
        }
        if (!Subscriber::model()->exists('id = ' .$userId)) {
           $this->redirect(Yii::app()->homeurl . 'account');
        }
        $this->render('account/accuracy', array(
            'user_id' => $userId,
            'response'=>$response
        ));
    }
    public function actionAccuracy(){
        $time = time();
        $accuracy_p = $_POST['accuracy'];
        $userId = $_POST['userId'];
        $userId = CUtils::decrypt($userId, $this->apiKey);
        $userId = (int)$userId;
        if(!is_numeric($userId)){
            $this->redirect(Yii::app()->homeurl . 'account');
        }
        if (!Subscriber::model()->exists('id = ' .$userId)) {
           $this->redirect(Yii::app()->homeurl . 'account');
        }
        $subscriber = Subscriber::model()->findByPk($userId);
//        $accuracy = Accuracy::model()->findByAttributes(array('subscriber_id'=>$userId,'status'=>1, 'type'=>1),"$time <= endate");
        $accuracy = Accuracy::model()->findBySql("select * from accuracy where subscriber_id = $userId and status = 1 and type = 1 and endate >= '$time' order by id desc limit 1");
        $userid_encrypt = CUtils::encrypt($subscriber->id, $this->apiKey);
        if($accuracy == null){
            Yii::app()->user->setFlash('responseAccuracy', 'Mã xác thực không hợp lệ');
             return $this->redirect(Yii::app()->homeurl . 'account/accuracyIndex?user_id='.$userid_encrypt.'&response=200');
        }
        if($accuracy->message != $accuracy_p){
            Yii::app()->user->setFlash('responseAccuracy', 'Mã xác thực không hợp lệ');
             return $this->redirect(Yii::app()->homeurl . 'account/accuracyIndex?user_id='.$userid_encrypt.'&response=200');
        }
        $accuracy->status = 3;
        $accuracy->save();
        $freeOncash = CUtils::isInEvent()? FREE_ONCASH_EVENT : FREE_ONCASH;
        if($subscriber->type == 1){
//            if($subscriber->create_date > "2016-11-01 00:00:00"){
//                $subscriber->fcoin = $freeOncash;// khuyen mai 25 oncash cho lan tao tai khoan tu 5/5/16
//            }else{
                $subscriber->fcoin += $freeOncash;
//            }
            $subscriber->status = 1;
        }else{
           if($subscriber->create_date > "2016-11-01 00:00:00"){
                $subscriber->status = 4;
            }
        }
        $subscriber->accuracy = 1;
        if($subscriber->save()){
            $sessionKey = CUtils::generateSessionKey($userId);
            Yii::app()->session['session_key'] = $sessionKey;
            Yii::app()->session['user_id'] = $userId;
            Yii::app()->user->setState('userSessionTimeout', time() + self::sessionTimeoutSeconds);
        }
        $this->redirect(Yii::app()->homeurl . 'site');
    }
    public function actionSmsagain() {
        $userId = isset($_REQUEST['user_id'])? $_REQUEST['user_id']: 0;
        $userId = CUtils::decrypt($userId, $this->apiKey);
        $userId = (int)$userId;
        if(!is_numeric($userId)){
            $this->redirect(Yii::app()->homeurl . 'account');
        }
        $subscriber = Subscriber::model()->findByPk($userId);
         if ($subscriber == null) {
           $this->redirect(Yii::app()->homeurl . 'account');
        }
        $messege = self::makeRequestID();
        $apiService = new APIService();
        $response = $apiService->smsService($subscriber->phone_number, $messege, $subscriber->id, 1);
        $userid_encrypt = CUtils::encrypt($subscriber->id, $this->apiKey);
        return $this->redirect(Yii::app()->homeurl . 'account/accuracyIndex?user_id='.$userid_encrypt.'&response=200');
//        $this->render('account/accuracy', array(
//            'user_id' => $userId,
//            'response'=>$response
//        ));
    }
    public static function makeRequestID() {
       $id = rand(100000, 999999);
       return $id;
    }
    public function actionService() {
        $this->titlePage = 'Tài khoản';
        $usingService = $this->checkUserService();
        $action = Yii::app()->controller->action->id;
        $this->render('account/service', array('usingService' => $usingService, 'action' => $action));
    }

    public function actionCancelService() {
        $this->titlePage = 'Tài khoản';
        $service_id = isset($_REQUEST['service']) ? $_REQUEST['service'] : 0;
        if ($service_id == 0) {
            try {
                $service_id = isset($_REQUEST['id']) ? base64_decode($_REQUEST['id']) : 0;
            } catch (Exception $e) {
                // do nothing
            }
        }
        $service = Service::model()->findByPk($service_id);
        if (count($service) == 0) {
            $this->redirect(Yii::app()->homeurl . '/account/service/');
        }
        $usingService = $this->checkUserService($service_id);
        if (count($usingService) == 0) {
            $this->redirect(Yii::app()->homeurl . '/account/service/');
        }
        $subscriber = Subscriber::model()->findByPk(Yii::app()->session['user_id']);
        $transaction = $subscriber->newTransactionService(3, $service, $subscriber);
        $result = $this->cancelUsing($subscriber, $service);
        if ($result == SUCCEED) {
            $transaction->status = 1;
            $transaction->cost = 0;
//            $subscriber->fcoin -= $service['price'];
//            $subscriber->save();
            $transaction->save();
            $responseMessage = "Bạn đã hủy thành công";
        } else {
            $responseMessage = "Lỗi hệ thống, xin vui lòng liên hệ Admin";
        }
        $this->render('account/service', array(
            'responseMessage' => $responseMessage,
            'usingService' => $this->checkUserService(),
        ));
    }

    public function actionRegisterService() {
        $this->titlePage = 'Tài khoản | Học Dễ';
        $service_id = isset($_REQUEST['service']) ? $_REQUEST['service'] : 0;
        if ($service_id == 0) {
            try {
                $service_id = isset($_REQUEST['id']) ? base64_decode($_REQUEST['id']) : 0;
            } catch (Exception $e) {
                // do nothing
            }
        }
        $service = Service::model()->findByPk($service_id);
        if (count($service) == 0) {
            $this->redirect(Yii::app()->homeurl . '/account/service/');
        }
        if ($service_id > 0 && isset(Yii::app()->session['user_id'])) {
            $subscriber = Subscriber::model()->findByPk(Yii::app()->session['user_id']);
            $using = $this->checkUserService();
            if (count($using) == 0) {
                $transaction = $subscriber->newTransactionService(PURCHASE_TYPE_NEW, $service, $subscriber);
                $result = $this->chargUsing($subscriber, $service);
                if ($result == SUCCEED) {
                    $transaction->status = 1;
                    $transaction->save();
                    $subscriber->fcoin -= $service['price'];
                    $subscriber->save();
                    $responseMessage = "Bạn đã đăng ký thành công";
                } else if ($result == FAIL_MONEY) {
                    $responseMessage = "Tài khoản của bạn không đủ, bạn vui lòng nạp thêm ONCASH để đăng ký gói cước.";
                } else {
                    $responseMessage = "Lỗi hệ thống, xin vui lòng liên hệ Admin";
                }
            } else {
                $responseMessage = "Bạn đã đăng ký gói cước";
            }
        } else {
            $responseMessage = "Gói cước không hợp lệ";
        }
        $this->render('account/service', array(
            'responseMessage' => $responseMessage,
            'usingService' => $this->checkUserService(),
        ));
    }

    private function checkUserService($service_id = null) {
        $time = date('Y-m-d H:i:s');
        $criteria = new CDbCriteria;
        if ($service_id == null) {
            $criteria->addCondition("is_active = 1 and expiry_date > '$time'");
        } else {
            $criteria->addCondition("is_active = 1 and service_id = $service_id and expiry_date > '$time'");
        }
        $criteria->compare('subscriber_id', Yii::app()->session['user_id']);
        $usingService = ServiceSubscriberMapping::model()->findAll($criteria);
        return $usingService;
    }

    private function chargUsing($subscriber, $service) {
        if ($subscriber->fcoin >= $service['price']) {
            $endtime = time() + $service['using_days'] * 24 * 60 * 60;
            $using = new ServiceSubscriberMapping;
            $using->subscriber_id = $subscriber->id;
            $using->service_id = $service['id'];
            $using->is_active = 1;
            $using->expiry_date = date('Y-m-d H:i:s', $endtime);
            $using->create_date = date('Y-m-d H:i:s');
            if (!$using->save()) {
                echo '<pre>';
                print_r($using->getErrors());
                return 2;
            }
            return 0;
        } else {
            return 1;
        }
    }

    private function cancelUsing($subscriber, $service) {
        $using = ServiceSubscriberMapping::model()->findByAttributes(array('service_id' => $service['id'], 'subscriber_id' => $subscriber->id, 'is_active' => 1));
        $using->is_active = 0;
        if (!$using->save()) {
            echo '<pre>';
            print_r($using->getErrors());
            return 2;
        }
        return 0;
    }

    public function actionLoginface() {
        if (!isset($_SESSION)) {
            session_start();
        }
        $app_id = "1054223384618565";
        $app_secret = "778ecfdd7c95f1ff723ed0593ad24fea";
        $redirect_uri = urlencode("https://www.hocde.vn/account/loginface");

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
            header("location: https://www.hocde.vn/profile");
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
                header("location:  https://www.hocde.vn/account/type");
                exit();
            } else {
                header("location: https://www.hocde.vn/");
                exit();
            }
        }
    }

    public function actionChannelloginface() {
        if (!isset($_SESSION)) {
            session_start();
        }
        $app_id = "1054223384618565";
        $app_secret = "778ecfdd7c95f1ff723ed0593ad24fea";
        $redirect_uri = urlencode("https://www.hocde.vn/account/channelloginface");

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
        if ($checkUser != null) {
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
            header("location: https://www.hocde.vn/profile");
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
            $subs->fcoin = 0;
            $subs->partner_id = 'hs';
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
                header("location:  https://www.hocde.vn/account/type");
                exit();
            } else {
                header("location: https://www.hocde.vn/");
                exit();
            }
        }
    }

    public function actionChannel1loginface() {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (isset($_GET['par'])) {
            $partner = $_GET['par'];
        } else {
            $partner = 'hs1';
        }
        $app_id = "1054223384618565";
        $app_secret = "778ecfdd7c95f1ff723ed0593ad24fea";
        $redirect_uri = urlencode("https://www.hocde.vn/account/channel1loginface?par=$partner");

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
        if (isset($_GET['par'])) {
            $partner = $_GET['par'];
        } else {
            $partner = 'hs1';
        }
        $checkUser = Subscriber::model()->findByAttributes(array('username' => $uid));
        if ($checkUser != null) {
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
            header("location: https://www.hocde.vn/profile");
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
            $subs->fcoin = 0;
            $subs->partner_id = $partner;
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
                header("location:  https://www.hocde.vn/account/type");
                exit();
            } else {
                header("location: https://www.hocde.vn/");
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
            header("location: http://hocde.onedu.vn/profile/");
            exit();
        }
        header("location: http://hocde.onedu.vn/account/");
        exit();
//	print_r($client->getAccessToken());die;
//	echo '<a href="'.$client->createAuthUrl().'"><img src="images/glogin.png" alt=""/></a>';
//	echo $_SESSION['access_token'];die;
    }

    public function actionHistoryService() {
        $this->titlePage = 'Lịch sử mua gói';
        if (!Yii::app()->session['user_id']) {
            $this->redirect(Yii::app()->homeurl . '/site');
        }
        $subscriber = Subscriber::model()->findByPk(Yii::app()->session['user_id']);
        if ($subscriber == null || count($subscriber) == 0) {
            $this->redirect(Yii::app()->homeurl . '/site');
        }
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $page_size = 20;
        if (isset($_GET['page'])) {
            $offset = $page_size * $page;
        } else {
            $offset = 0;
        }
        $action = Yii::app()->controller->action->id;
        $startTime = date('Y-m-d H:i:s', (time() - 90 * 24 * 60 * 60));
        $endTime = date('Y-m-d H:i:s', time());
        $query = "select service_id, status, cost, create_date, purchase_type from subscriber_transaction_service where subscriber_id = $subscriber->id and create_date between '$startTime' and '$endTime' limit $offset, $page_size";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        $this->render('account/historyService', array('result' => $result, 'action' => $action));
    }

    public function actionHistoryCard() {
        $this->titlePage = 'Lịch sử nạp thẻ';
        if (!Yii::app()->session['user_id']) {
            $this->redirect(Yii::app()->homeurl . '/site');
        }
        $subscriber = Subscriber::model()->findByPk(Yii::app()->session['user_id']);
        if ($subscriber == null || count($subscriber) == 0) {
            $this->redirect(Yii::app()->homeurl . '/site');
        }
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $page_size = 20;
        if (isset($_GET['page'])) {
            $offset = $page_size * $page;
        } else {
            $offset = 0;
        }
        $type = 2;
        $action = Yii::app()->controller->action->id;
        $startTime = date('Y-m-d H:i:s', (time() - 90 * 24 * 60 * 60));
        $endTime = date('Y-m-d H:i:s', time());
        $query = "select issuer, status, cost, create_date from subscriber_transaction where subscriber_id = $subscriber->id and create_date between '$startTime' and '$endTime' limit $offset, $page_size";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        $this->render('account/historyCard', array('result' => $result, 'action' => $action,'type'=>$type));
    }

    public function actionAccountNet2e() {
        $this->layout = 'main1';
        $this->render('account/accountNet2e');
    }

    public function checkPartnerNet2e($checkCard, $resultoObj, $subscriberId) {
        if ($checkCard->partner == 'net2e') {
            $checkPromition = CheckPromotion::model()->findAllByAttributes(array('subscriber_id' => $subscriberId, 'km1' => 1));
            $km = 1;
        }
        Yii::log("\n km nap the: " . $km);
        Yii::log("\n subsId nap the: " . $subscriberId);
        if ($checkCard->card_code === $cardCode) {
            if (count($checkPromition) == 0) {
                $checkCard->status = 2;
                $checkPromition = new CheckPromotion();
                $checkPromition->subscriber_id = $subscriberId;
                $checkPromition->km1 = $km; //1: net2e, 2:danglai
                $checkPromition->created_date = date('Y-m-d H:i:s');
                $checkPromition->save();
                if ($checkCard->save()) {
                    $resultoObj->amount = $checkCard->amount;
                    $resultoObj->errorCode = CARD_OK;
                    $resultoObj->errorMessage = 'Kiem tra thanh cong';
                } else {
                    $resultoObj->errorCode = CARD_NOT_OK;
                    $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
                }
            } else {
                $resultoObj->errorCode = INACTIVECARD;
                $resultoObj->errorMessage = 'Moi tai khoan chi duoc nap toi da 1 the khuyen mai!';
            }
        } else {
            $resultoObj->errorCode = INACTIVECARD;
            $resultoObj->errorMessage = 'Moi tai khoan chi duoc nap toi da 1 the khuyen mai!';
        }
        return $resultoObj;
    }

    public function checkPartnerLong($checkCard, $cardCode, $resultoObj, $subscriberId) {
        Yii::log("\n partner the: " . $checkCard->partner . '-----------' . $subscriberId);
        $checkPromition = CheckPromotion::model()->findByAttributes(array('subscriber_id' => $subscriberId, 'km1' => 3));
        if ($checkCard->card_code === $cardCode) {
            if ($checkPromition == null) {
                $checkCard->status = 2;
                $checkPromition = new CheckPromotion();
                $checkPromition->subscriber_id = $subscriberId;
                $checkPromition->km1 = 3; //1: net2e, 3:duylong
                $checkPromition->created_date = date('Y-m-d H:i:s');
                $checkPromition->save();
                if ($checkCard->save()) {
                    $resultoObj->amount = $checkCard->amount;
                    $resultoObj->errorCode = CARD_OK;
                    $resultoObj->errorMessage = 'Kiem tra thanh cong';
                } else {
                    $resultoObj->errorCode = CARD_NOT_OK;
                    $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
                }
            } else {
                $resultoObj->errorCode = INACTIVECARD;
                $resultoObj->errorMessage = 'Moi tai khoan chi duoc nap toi da 1 the khuyen mai!';
            }
        } else {
            $resultoObj->errorCode = CARD_NOT_OK;
            $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
        }
        return $resultoObj;
    }
    public function checkPartnerTelesale($checkCard, $cardCode, $resultoObj, $subscriberId,$transaction) {
        Yii::log("\n partner the: " . $checkCard->partner . '-----------' . $subscriberId);
//        $checkPromition = CheckPromotion::model()->findByAttributes(array('subscriber_id' => $subscriberId, 'km1' => 3));
        if ($checkCard->card_code === $cardCode) {
//            if ($checkPromition == null) {
                $transaction->partner_id = "telesale";
                $checkCard->status = 2;
                $checkPromition = CheckPromotion::model()->findByAttributes(array('subscriber_id' => $subscriberId, 'km1' => 4));
                if($checkPromition == null){
                    $checkPromition = new CheckPromotion();
                }
                $checkPromition->subscriber_id = $subscriberId;
                $checkPromition->km1 = 4; //1: net2e, 3:duylong, 4:Telesale
                $checkPromition->created_date = date('Y-m-d H:i:s');
                if(intval($checkCard->amount) == 400000){
                    $time = date("Y-m-d 23:59:59", time()+60*60*24*90);
                }else{
                    $time = date("Y-m-d 23:59:59", time()+60*60*24*12*30);
                }
                $checkPromition->end_date = $time;
                $checkPromition->save();
                if ($checkCard->save()) {
                    $resultoObj->amount = $checkCard->amount;
                    $resultoObj->errorCode = CARD_OK;
                    $resultoObj->errorMessage = 'Kiem tra thanh cong';
                } else {
                    $resultoObj->errorCode = CARD_NOT_OK;
                    $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
                }
//            } else {
//                $resultoObj->errorCode = INACTIVECARD;
//                $resultoObj->errorMessage = 'Moi tai khoan chi duoc nap toi da 1 the khuyen mai!';
//            }
        } else {
            $resultoObj->errorCode = CARD_NOT_OK;
            $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
        }
        return $resultoObj;
    }
    public function checkPartnerTrinhhoan($checkCard, $cardCode, $resultoObj, $subscriberId,$transaction) {
        Yii::log("\n partner the: " . $checkCard->partner . '-----------' . $subscriberId);
//        $checkPromition = CheckPromotion::model()->findByAttributes(array('subscriber_id' => $subscriberId, 'km1' => 3));
        if ($checkCard->card_code === $cardCode) {
//            if ($checkPromition == null) {
                $transaction->partner_id = "telesale";
                $checkCard->status = 2;
                $checkPromition = CheckPromotion::model()->findByAttributes(array('subscriber_id' => $subscriberId, 'km1' => 5));
                if($checkPromition == null){
                    $checkPromition = new CheckPromotion();
                }
                $checkPromition->subscriber_id = $subscriberId;
                $checkPromition->km1 = 5; //1: net2e, 3:duylong, 4:Telesale, 5:trinhhoan
                $checkPromition->created_date = date('Y-m-d H:i:s');
                $time = date("Y-m-d 23:59:59", time()+60*60*24*6*30);
                $checkPromition->end_date = $time;
                $checkPromition->save();
                if ($checkCard->save()) {
                    $resultoObj->amount = $checkCard->amount;
                    $resultoObj->errorCode = CARD_OK;
                    $resultoObj->errorMessage = 'Kiem tra thanh cong';
                } else {
                    $resultoObj->errorCode = CARD_NOT_OK;
                    $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
                }
//            } else {
//                $resultoObj->errorCode = INACTIVECARD;
//                $resultoObj->errorMessage = 'Moi tai khoan chi duoc nap toi da 1 the khuyen mai!';
//            }
        } else {
            $resultoObj->errorCode = CARD_NOT_OK;
            $resultoObj->errorMessage = 'Loi he thong! xin vui long nap lai!';
        }
        return $resultoObj;
    }

    public function actionType() {
        $this->titlePage = 'Chọn loại tài khoản';
        $this->render('account/type', array());
    }

    public function actionSaveType() {
        $subId = isset($_POST['subId']) ? $_POST['subId'] : null;
        $type = $_POST['type'];
        $email = $_POST['email'];
        $subscriber = Subscriber::model()->findByPk($subId);
        if ($type == 1) {
            $this->TypeHS($subscriber, $type, $email);
        } else {
            $this->TypeGV($subscriber, $type, $email);
        }
        echo '1';
        die;
    }

    public function TypeHS($subscriber, $type, $email) {
        $subscriber->type = $type;
        $subscriber->status = 1;
//        $subscriber->fcoin = 25;// khuyen mai
        $subscriber->save();
        return $subscriber;
    }

    public function TypGV($subscriber, $type, $email) {
        $subscriber->type = $type;
        $subscriber->status = 4;
        if ($subscriber->fcoin > 0) {
            $subscriber->fcoin -= 25;
        }
        $subscriber->save();
        return $subscriber;
    }

    public function actionChannelHs() {
        $this->layout = 'main1';
        $this->render('account/channelhs', array());
    }

    public function actionGroup() {
        $this->render('account/group', array());
    }

    public function actionChannelHs1() {
        $this->layout = 'main1';
        $this->render('account/channelhs1', array('par' => 'hs1',));
    }

    public function actionChannelHs2() {
        $partner = isset($_GET['hs']) ? $_GET['hs'] : '1';
        $par = 'hs' . $partner;
        $this->layout = 'main1';
        $this->render('account/channelhs1', array(
            'par' => $par,
        ));
    }

}
