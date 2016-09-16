<?php

class ResetPassAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $email = isset($_POST['email']) ? $_POST['email'] : null;
        if ($email == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params email'));
            return;
        }
        //Parameters
        $uniqueId = uniqueId();
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

        // var_dump($response);die(1);

        // Get token key
        $accessToken = $response['apiaccesstoken'];

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);
//vb_login_password - The password of the User. If client use this to login, the plain password may be sniffed during the pass in the network
//vb_login_md5password - The md5 password of the User
//vb_login_md5password_utf - The md5 password (Unicode) of the User
// logintype - Possible value: 'cplogin' or empty. 'cplogin' means that the login will also allow the user to access the AdminCP if they have permission.
        $response = $api->callRequest('login_emailpassword', [
            'email' => $email,
        ]);
     //   var_dump($response);die(1);
        //Thanh cong
        if (isset($response['response'])) {
            if (isset($response['response']->errormessage)) {
                $result = $response['response']->errormessage[0];
                if (!empty($response['response']->errormessage) && 'invalidemail' == $response['response']->errormessage[0]) {
                    echo json_encode(array('code' => 2,'message' => 'Wrong email.'));
                    return;
                }
                if ('redirect_lostpw' == $response['response']->errormessage) {
                    //var_dump($response);
                    echo json_encode(array('code' => 0,
                        'message' => 'Your username and details about how to reset your password have been sent to you by email.',
                    ));
                    return;
                }
            }
        }else{
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }

    }
}
    
