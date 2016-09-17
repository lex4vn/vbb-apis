<?php

class LoginAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_POST;
        if (!isset($params['username']) || $params['username'] == '' ) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params username'));
            return;
        }
        if (!isset($params['password'])  || $params['password'] == '' ) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params password'));
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
        $response = $api->callRequest('login_login', [
            'vb_login_username' => $params['username'],
            'vb_login_password' => $params['password']
        ]);
       //  var_dump($response);die(1);
        //Thanh cong
        //array(2) { ["session"]=> object(stdClass)#43 (2) {
        // ["dbsessionhash"]=> string(32) "4214b0a1f30fbebb95e57cc3c12a6c93" ["userid"]=> int(21342) }
        // ["response"]=> object(stdClass)#50 (1) { ["errormessage"]=> array(2) { [0]=> string(14) "redirect_login" [1]=> string(5) "sWa10" } } }

        // Sai pass
        //array(2) { ["session"]=> object(stdClass)#43 (2) {
        // ["dbsessionhash"]=> string(32) "cad6cb765af7f03c4b0edd364eb13359" ["userid"]=> string(1) "0" }
        // ["response"]=> object(stdClass)#50 (1) { ["errormessage"]=> array(4) { [0]=> string(25) "badlogin_strikes_passthru" [1]=> string(18) "http://pkl.vn/test" [2]=> string(49) "s=cad6cb765af7f03c4b0edd364eb13359&api=1&" [3]=> int(1) } } }


       // array(2) { ["session"]=> object(stdClass)#43 (2) {
        //
        // ["dbsessionhash"]=> string(32) "955fd073816ddf01059e87369ae90e42"
        // ["userid"]=> string(1) "0" } ["response"]=> object(stdClass)#50 (1) { ["errormessage"]=> array(3) { [0]=> string(7) "strikes" [1]=> string(18) "http://pkl.vn/test" [2]=> string(49) "s=955fd073816ddf01059e87369ae90e42&api=1&" } } }
        //Wrong username or password. You have used up your failed login quota! Please wait 15 minutes before trying again. Don't forget that the password is case sensitive. Forgotten your password? Click here!

        var_dump($response);die(1);
        // var_dump($response['response']);die(1);
        //  if(!isset($response->response->errorlist)){

        if (isset($response['response'])) {
            if (isset($response['response']->errormessage)) {
                $result = $response['response']->errormessage[0];
                if ('badlogin_strikes_passthru' == $result) {
                    //var_dump($response);
                    echo json_encode(array('code' => 2,'message' => 'Wrong username or password.'));
                    return;
                }
                if ('strikes' == $result) {
                    //var_dump($response);
                    echo json_encode(array('code' => 2,
                        'message' => 'Wrong username or password. You have used up your failed login quota! Please wait 15 minutes before trying again. Don\'t forget that the password is case sensitive.',
                    ));
                    return;
                }
                if ('redirect_login' == $result &&  $response['session']->userid !== '0') {
                    //var_dump($response);
                    echo json_encode(array('code' => 0,
                        'message' => 'Login successful',
                        'sessionhash' =>  $response['session']->dbsessionhash,
                        'userid' =>  $response['session']->userid,
                        'username' =>  $response['response']->errormessage[1],
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
