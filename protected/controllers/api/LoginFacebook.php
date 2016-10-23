<?php

class LoginFacebook extends  CAction
{
    public function run()
    {
        header('Content-type: application/json');
        //Parameters
        if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        if (!isset($params['access_token']) || $params['access_token'] == '' ) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params username'));
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
            'api_v'=> '1',
            'uniqueid' => $uniqueId]);

        //var_dump($response);die(1);
        //var_dump($post_id);die(1);
        // Get token key
        $accessToken = $response['apiaccesstoken'];
        // var_dump($accessToken);die(1);
        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);


        $access_token = $params['access_token'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?access_token=$access_token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        $user = json_decode($response);
        if($user){
            $f_id = $user->id;
            // search user id and accesstoken in table user
            $response = $api->callRequest('searchFaceBook', ['fid' => $f_id,  'api_v'=> '1'  ]);
            if (isset($response['response'])) {
                $user = json_decode($response);
                $response = $api->callRequest('login_login', [
                    'vb_login_username' => $user -> username,
                    'vb_login_md5password' => $access_token
                ]);

                if (isset($response['response'])) {
                    if (isset($response['response']->errormessage)) {
                        $result = $response['response']->errormessage[0];
                        if ('badlogin_strikes_passthru' == $result) {
                            echo json_encode(array('code' => 2,'result' =>  false,'message' => 'Wrong username or password.'));
                            return;
                        }
                        if ('strikes' == $result) {
                            echo json_encode(array('code' => 2,'result' =>  false,
                                'message' => 'Wrong username or password. You have used up your failed login quota! Please wait 15 minutes before trying again. Don\'t forget that the password is case sensitive.',
                            ));
                            return;
                        }
                        if ('redirect_login' == $result &&  $response['session']->userid !== '0') {
                            echo json_encode(array('code' => 0,
                                'message' => 'Login successful',
                                'sessionhash' =>  $response['session']->dbsessionhash,
                                'result' =>  true,
                                'userid' =>  $response['session']->userid,
                                'username' =>  $response['response']->errormessage[1],
                            ));
                            return;
                        }
                    }
                }else{
                    echo json_encode(array('code' => 1, 'result' =>  false,'message' => 'Forum error'));
                    return;
                }

            }else{
                // register new user by facebook
                $uid = isset($user->id) ? $user->id : '';
                $fname = isset($user->name) ? $user->name : '';
                $fbirthday = isset($user->birthday) ? $user->birthday : '';
                $fmail = isset($user->email) ? $user->email : '';
                $fgender = isset($user->gender) ? $user->gender : '';
                $access_token = $accessToken;
                //
            }
        }else{
            echo json_encode(array('code' => 0, 'message' => 'cannot get information from facebook.'));
            return;
        }
        var_dump($user);
        die(1);
        $idFaceBook = 'EAACEdEose0cBABGLrAWQp2ObXEw3NsnZCeoEQo2HOSpl4QuUwFdklPQfS0PeZCHGqE7a4AksYOnN5FbALuUrpeKKwtGHbcxgKZBnPjZAbvC4aWxYcS6424x1sOoZBxDOOcZBamAz1dh6LvxOewNh1VN8L4omneqHY5WTaOBi9Q1L1SDW3Io8o2';
        $userId = '742194869201205';
        $response = $api->callRequest('login_facebook', ['userid' => $userId , 'api_v'=> '4' ]);
        var_dump($response);
        die(1);
        //Thanh cong
        if (isset($response['response'])) {

        } else {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }
    }
    public function registerUserByFacebook($user, $accessToken, $api){
        $userName = $user ->id;
        $email = $user ->email;
        $fullName = $user ->last_name + " " +  $user -> first_name;
        $password = $accessToken;
        $birthDay = isset($user ->birthday) ? $user ->birthday : "0912345678";
        $userfield = [];
        $userfield["field5"] = $user ->name;
        $userfield["field6"] = $user ->birthday;
        //Nghe nghiep
        $userfield["field7"] = "";
        $userfield["field8"] = "";

        // Xe - vo 2
        $userfield["field9"] = "";
        $userfield["field10"] = "Tổ đội";

        $month = date("m", strtotime($birthDay));

        // Get day
        $day = date("d", strtotime($birthDay));

        // Get year
        $year = date("Y", strtotime($birthDay));

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
            'api_v'=> '1'
        ]);

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