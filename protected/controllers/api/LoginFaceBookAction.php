<?php

class LoginFaceBookAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $accessToken = isset($_GET['accessToken']) ? $_GET['accessToken'] : null;
        if ($accessToken == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params accessToken'));
            return;
        }
        // Get user infomation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?access_token=$accessToken");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        $user = json_decode($response);
        Yii::log("accessToken: $accessToken \n");

        if ($user) {
            $uid = isset($user->id) ? $user->id : '';
            $fname = isset($user->name) ? $user->name : '';
            $fbirthday = isset($user->birthday) ? $user->birthday : '';
            $fmail = isset($user->email) ? $user->email : '';
            $fgender = isset($user->gender) ? $user->gender : '';
            $access_token = $accessToken;
        }

        $checkUser = Subscriber::model()->findByAttributes(array('username' => $uid));
        $totalFree = 0;
        if ($checkUser != null) {
            $checkUser->firstname = '';
            $checkUser->lastname = $fname;
            $checkUser->password = 'faccebook';
            $checkUser->url_avatar = 'http://graph.facebook.com/' . $uid . '/picture?type=square';
            if (!$checkUser->save()) {
                echo '<pre>';
                print_r($checkUser->getErrors());
            }
            $avata = $checkUser->url_avatar;
            $type = $checkUser->type;
            $loginFirst = 1;

            $code = 0;
            $message = 'Bạn đăng nhập thành công';

            Yii::app()->session['user_id'] = $checkUser->id;
            $sessionKey = CUtils::generateSessionKey($checkUser->id);
        } else {
            $subs = new Subscriber();
            $subs->firstname = '';
            $subs->lastname = $fname;
            $subs->username = $uid;
            $subs->password = 'faccebook';
            $subs->phone_number = '';
            $subs->email = $fmail;
            $subs->type = 1;
            $subs->status = 1;
            $subs->url_avatar = 'http://graph.facebook.com/' . $uid . '/picture?type=square';
            $subs->create_date = date('Y-m-d H:i:s');
            if (!$subs->save()) {
                echo '<pre>';
                print_r($subs->getErrors());
            }
            $code = 0;
            $message = 'Bạn đăng nhập thành công';
            $avata = $subs->url_avatar;
            $fcoin = $subs->fcoin;
            $loginFirst = 0;
            Yii::app()->session['user_id'] = $subs->id;
            $sessionKey = CUtils::generateSessionKey($subs->id);
        }
        Yii::app()->session['session_key'] = $sessionKey;
        echo json_encode(array(
            'code' => $code,
            'sessionkey' => $sessionKey,
            'message' => $message,
            'item' => array(
                'id' => Yii::app()->session['user_id'],
                'username' => !empty($uid) ? $uid : 'Noname',
                'user_name' => !empty($uid) ? $uid : 'Noname',
                'url_avatar' => $avata,
                'lastname' => !empty($fname) ? $fname : '',
                'firstname' => '',
                'loginFirst' => $loginFirst,
                'type' => !empty($type) ? $type : '',
                'versionApp' => '1.0'
            ),
        ));
    }
}