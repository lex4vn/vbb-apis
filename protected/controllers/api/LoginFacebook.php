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

        if (!isset($params['accessToken']) || $params['accessToken'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params accessToken'));
            return;
        }
        $access_token = $params['accessToken'];

        $email = isset($_POST['email']) ? $_POST['email'] : null;
        if ($email == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params email'));
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
                    'fbuserid' => $email,
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
}