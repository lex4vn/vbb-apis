<?php

class LoginGoogleAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        $accessToken = isset($params['accessToken']) ? $params['accessToken'] : null;

        if ($accessToken == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params accessToken'));
            return;
        }
        $email = isset($params['email']) ? $params['email'] : null;
        if ($email == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params email'));
            return;
        }
        // Get user infomation
//        require_once 'src/Google_Client.php';
//        require_once 'src/contrib/Google_PlusService.php';
//        require_once 'src/contrib/Google_Oauth2Service.php';
//
//        $client = new Google_Client();
//        $client->setScopes(array('https://www.googleapis.com/auth/plus.login', 'https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/plus.me'));
//        $client->setApprovalPrompt('auto');
//
//        $service = new Google_Oauth2Service($client);
        Yii::log("accessToken: $accessToken \n");

//        if (isset($accessToken)) {
//            $client->setAccessToken($accessToken);
//        }

//        if ($client->getAccessToken()) {
//            $userProfile = $service->userinfo->get();
//            $uid = isset($userProfile['id']) ? $userProfile['id'] : '';
//            $Glastname = isset($userProfile['family_name']) ? $userProfile['family_name'] : '';
//            $Gfirstname = isset($userProfile['given_name']) ? $userProfile['given_name'] : '';
//            $Gmail = isset($userProfile['email']) ? $userProfile['email'] : '';
//            $Gpicture = isset($userProfile['picture']) ? $userProfile['picture'] : '';
//        }

        // Init api forum
        $uniqueId = uniqid();
        $apiConfig = new ApiConfig(API_KEY, $uniqueId, CLIENT_NAME, CLIENT_VERSION, PLATFORM_NAME, PLATFORM_VERSION);
        $apiConnector = new GuzzleProvider(API_URL);
        $api = new Api($apiConfig, $apiConnector);

        $response = $api->callRequest('api_init', [
            'clientname' => CLIENT_NAME,
            'clientversion' => CLIENT_VERSION,
            'platformname' => PLATFORM_NAME,
            'platformversion' => PLATFORM_VERSION,
            'uniqueid' => $uniqueId
        ]);
        // Get token key
        $accessToken = $response['apiaccesstoken'];

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);

        $response = $api->callRequest('login_facebook', [
            'fbuserid' => $email,
            'api_v' => '4'
        ], ConnectorInterface::METHOD_POST);
        $error = false;

        if (isset($response['response'])) {
            if (isset($response['response']->errormessage)) {
                $result = $response['response']->errormessage[0];
                //var_dump($result);die();
                if ('redirect_login' == $result && $response['session']->userid !== '0') {
                    $sessionKey = CUtils::generateSessionKey($response['session']->userid, $response['response']->errormessage[1],base64_encode(serialize($apiConfig)));
                    echo json_encode(array('code' => 0,
                        'message' => 'Login successful',
                        'sessionhash' => $sessionKey,
                        'result' => true,
                        'userid' => $response['session']->userid,
                        'username' => $response['response']->errormessage[1],
                    ));
                    return;
                }

                if ('badlogin_facebook' == $result) {
                    echo json_encode(array('code' => 2, 'result' => false, 'message' => 'Please register with google account'));
                    return;
                }

            } else {
                $error = true;
            }
        }

        if ($error) {
            echo json_encode(array('code' => 1, 'result' => false, 'message' => 'Forum error'));
            return;
        }
    }
}