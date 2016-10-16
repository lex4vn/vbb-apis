<?php

class ResetPassAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $email = isset($_GET['email']) ? $_GET['email'] : null;
        if ($email == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params email'));
            return;
        }
        //Parameters
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

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);
        $response = $api->callRequest('login_emailpassword', [
            'email' => $email,
        ]);
        if (isset($response['response'])) {
            if (isset($response['response']->errormessage)) {
                $result = $response['response']->errormessage[0];
                if (!empty($response['response']->errormessage) && 'invalidemail' == $response['response']->errormessage[0]) {
                    echo json_encode(array('code' => 2,
                        'result' => false,
                        'message' => 'Wrong email.'));
                    return;
                }
                if ('redirect_lostpw' == $response['response']->errormessage) {
                    echo json_encode(array('code' => 0,
                        'result' => true,
                        'message' => 'Your username and details about how to reset your password have been sent to you by email.',
                    ));
                    return;
                }
            }
        } else {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }

    }
}
    
