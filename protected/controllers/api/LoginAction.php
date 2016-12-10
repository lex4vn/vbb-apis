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
                if ('redirect_login' == $result &&  $response['session']->userid !== '0') {
                    echo json_encode(array('code' => 0,
                        'message' => 'Login successful',
                        'sessionhash' => base64_encode(serialize($apiConfig)),
                       // 'sessionhash' =>  $response['session']->dbsessionhash,
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

    }
}
