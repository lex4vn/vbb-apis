<?php

class SearchUserAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        if (!isset($params['email']) || $params['email'] == '' ) {
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

        // var_dump($response);die(1);

        // Get token key
        $accessToken = $response['apiaccesstoken'];

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);
//vb_login_password - The password of the User. If client use this to login, the plain password may be sniffed during the pass in the network
//vb_login_md5password - The md5 password of the User
//vb_login_md5password_utf - The md5 password (Unicode) of the User
// logintype - Possible value: 'cplogin' or empty. 'cplogin' means that the login will also allow the user to access the AdminCP if they have permission.
        $response = $api->callRequest('api_emailsearch', [
            'fragment' => $params['email'],
        ]);
         // var_dump($response);die(1);
        //Thanh cong
      // array(2) { ["userid"]=> string(5) "21344" ["username"]=> string(9) "thanhseo1" }

        // Sai email
//        array(0) {     }


        if (isset($response['userid']) && isset($response['username'])) {
            echo json_encode(array('code' => 0,'message' => 'Successful','userid' => $response['userid'],'username' => $response['username']));
            return;
        }else{
            echo json_encode(array('code' => 1, 'message' => 'Your email address is not registered.'));
            return;
        }

    }
}