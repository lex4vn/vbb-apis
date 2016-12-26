<?php

class LoginFacebook extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        //Parameters
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        if (!isset($params['fb_access_token']) || $params['fb_access_token'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params fb_access_token'));
            return;
        }
        $uniqueId = uniqid();
        $content = '';

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
        // var_dump($accessToken);die(1);
        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);

        $access_token = $params['fb_access_token'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?access_token=$access_token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        $user = json_decode($response);
        //var_dump($response);die();
        if ($user) {
            if (isset($user->error)) {
                echo json_encode(array('code' => 4, 'result' => false, 'message' => 'Error validating access token: Session has expired.'));
                return;
            }
            if (isset($user->id)) {
                $response = $api->callRequest('login_facebook', [
                    'fbuserid' => $user->id,
                    'api_v' => '4'
                ], ConnectorInterface::METHOD_POST);
                $error = false;
                if (isset($response['response'])) {
                    if (isset($response['response']->errormessage)) {
                        $result = $response['response']->errormessage[0];
                        //var_dump($result);die();
                        if ('redirect_login' == $result && $response['session']->userid !== '0') {
                            $sessionKey = CUtils::generateSessionKey($response['session']->userid,base64_encode(serialize($apiConfig)));
                            echo json_encode(array('code' => 0,
                                'message' => 'Login successful',
                                'sessionhash' =>$sessionKey,
                                'result' => true,
                                'userid' => $response['session']->userid,
                                'username' => $response['response']->errormessage[1],
                            ));
                            return;
                        }

                        if ('badlogin_facebook' == $result) {
                            echo json_encode(array('code' => 2, 'result' => false, 'message' => 'Please register with facebook account'));
                            return;
                            // register new user by facebook
                            //$this->registerUserByFacebook($user,$accessToken,$api);
                        }

                    }else{
                        $error = true;
                    }
                }

                if($error) {
                    echo json_encode(array('code' => 1, 'result' => false, 'message' => 'Forum error'));
                    return;
                }

            } else {
                echo json_encode(array('code' => 3, 'result' => false, 'message' => 'Please check app facebook with access token.'));
                return;
            }
        }
    }

    public function registerUserByFacebook($user, $accessToken, $api)
    {
        //var_dump($user);die();
        $fullName = $user->name;
        $userName = isset($user->id) ? $user->id : null;
        $email = isset($user->email) ? $user->email : null;
        $birthday = isset($user->birthday) ? $user->birthday : null;
        if ($userName == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing username'));
            return;
        }

        if ($email == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing email'));
            return;
        }

        if ($fullName == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing fullname'));
            return;
        }

        if ($birthday == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing birthday'));
            return;
        }
        $password = 'faccebook';

        $phoneNumber = "0912345678";
        $job = 'Nhân viên';
        $wife = 'Vợ 2';

        $userfield = [];
        $userfield["field5"] = $fullName;
        $userfield["field6"] = $birthday;
        //Nghe nghiep
        $userfield["field7"] = $job;
        $userfield["field8"] = $phoneNumber;

        // Xe - vo 2
        $userfield["field9"] = $wife;
        $userfield["field10"] = "Tổ đội";

        $month = date("m", strtotime($birthday));

        // Get day
        $day = date("d", strtotime($birthday));

        // Get year
        $year = date("Y", strtotime($birthday));

        $birthdate = $day . '/' . $month . '/' . $year;

        $response = $api->callRequest('register_addmember', [
            'agree' => '1',
            'username' => $userName,
            'email' => $email,
            'emailconfirm' => $email,
            'password_md5' => $password,
            'passwordconfirm_md5' => $password,
            'month' => $month,
            'day' => $day,
            'year' => $year,
            'birthdate' => $birthdate,
            'timezoneoptions' => TIME_ZONE_7,
            'userfield' => $userfield,
            'api_v' => '1'
        ]);
        //var_dump($response);die();
        if (!isset($response)) {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }

        if (!isset($response['response'])) {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }

        if (isset($response['response'])) {
            if (isset($response['response']->errormessage)) {
                $result = $response['response']->errormessage[0];
                if ('registration_complete' == $result) {
                    echo json_encode(array('code' => 0,
                        'accessToken' => $accessToken,
                        'message' => 'Register successful',
                        'username' => $userName,
                        'email' => $email,
                        'birthdate' => $birthdate,
                        'fullName' => $fullName,
                    ));
                    return;
                }
            } else {
                $errorList = $response['response']->errorlist;
                if (!empty($errorList)) {
                    $messsage = '';
                    foreach ($errorList as $e) {
                        if ('usernametaken' == $e[0]) {
                            echo json_encode(array('code' => 2, 'message' => 'Tên tài khoản đã được sử dụng'));
                            return;
                        }
                        if ('emailtaken' == $e[0]) {
                            echo json_encode(array('code' => 2, 'message' => 'Email đã được sử dụng'));
                            return;
                        }

                        if ('regexincorrect' == $e[0]) {
                            echo json_encode(array('code' => 2, 'message' => 'Vui lòng nhập họ và tên'));
                            return;
                        }
                        if ('usernametoolong' == $e[0]) {
                            echo json_encode(array('code' => 2, 'message' => 'Tên tài khoản quá dài'));
                            return;
                        }
                    }
                    echo json_encode(array('code' => 2, 'message' => $messsage));
                    return;
                }
            }
        }
    }

}