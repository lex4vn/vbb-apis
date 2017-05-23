<?php

class LoginAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;

		if (!isset($params['username']) || $params['username'] == '') {
			echo json_encode(array('code' => 5, 'message' => 'Missing params username'));
			return;
		} 
		if (!isset($params['password']) || $params['password'] == '' ) {
				echo json_encode(array('code' => 5, 'message' => 'Missing params password'));
				return;
		}
		$isEmail = filter_var($params['username'], FILTER_VALIDATE_EMAIL);
        $uniqueId = uniqid();

        if(!$isEmail){
            $user = User::model()->findByAttributes(array('username'=>$params['username']));
            if($user & $user->password == $params['password']){
                $sessionKey = CUtils::generateSessionKey($user->userid,$user->username,'12345679');
                echo json_encode(array('code' => 0,
                    'message' => 'Login successful',
                    'sessionhash' => $sessionKey,
                    'result' =>  true,
                    'userid' =>  $user->userid,
                    'username' =>  $user->username,
                ));
                return;
            }
        }

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
                'vb_login_username' => $params['username'],
                'vb_login_md5password' => $params['password']
            ]);

        }else {
            // username null search username  by email.
            $response = $api->callRequest('api_emailsearch', [
                'fragment' => $params['username'],'api_v'=> '1'
            ]);
            if (count($response) >= 3) {
                $response = $api->callRequest('login_login', [
                    'vb_login_username' => $response[1],
                    'vb_login_md5password' => $params['password']
                ]);
            }else{
                echo json_encode(array('code' => 1, 'message' => 'Email is not registered.'));
                return;
            }
        }
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
                if ('redirect_login' == $result &&  $response['session'] ->userid !== '0') {
                    $sessionKey = CUtils::generateSessionKey($response['session']->userid,$response['response']->errormessage[1],base64_encode(serialize($apiConfig)));
                    echo json_encode(array('code' => 0,
                        'message' => 'Login successful',
                        'sessionhash' => $sessionKey,
                        'result' =>  true,
                        'userid' =>  $response['session']->userid,
                        'username' =>  $response['response']->errormessage[1],
                    ));
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
                        $email = '';
                        if(isset($response['response']->prepared)) {
                            $usertitle = $response['response']->prepared->usertitle;
                            $avatar = str_replace("amp;","",API_URL.$response['response']->prepared->avatarurl);
                            $status = $response['response']->prepared->onlinestatus->onlinestatus == 1 ? "1" : "0";
                            $email = $response['response']->prepared->displayemail;
                        }

                        $user = User::model()->findByPk($user_id);
                        if($user == null){
                            $user = new User();
                            $user->userid = $user_id;
                            $user->username = $username;
                            $user->phonenumber =  $phonenumber;
                            $user->password =  $email;
                            $user->usertitle = $usertitle;
                            $user->avatar = $avatar;
                            $user->status = $status;
                        }else{
                            $user->phonenumber =  $phonenumber;
                            $user->usertitle = $usertitle;
                            //$user->avatar = $avatar;
                            $user->password =  $email;
                            $user->status = $status;
                        }
                        $user->save();
                    }

                    return;
                }
            }
        }else{
            echo json_encode(array('code' => 1, 'result' =>  false,'message' => 'Forum error'));
            return;
        }

    }
}
