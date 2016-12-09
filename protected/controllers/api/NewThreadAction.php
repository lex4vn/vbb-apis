<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 10:43 AM
 */
class NewThreadAction extends CAction
{
    public function run()
    {
        //test
        header('Content-type: application/json');
        if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;

//        if (!isset($params['apiaccesstoken']) || $params['apiaccesstoken'] == '') {
//            echo json_encode(array('code' => 5, 'message' => 'Missing params apiaccesstoken'));
//            return;
//        }
        if (!isset($params['type']) || $params['type'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params type'));
            return;
        }
        if (!($params['type'] == 1 || $params['type'] == 2)) {
            echo json_encode(array('code' => 5, 'message' => 'Params type can be 1 or 2. Therein 1 is need to buy. 2 is need to sell.'));
            return;
        }
        if (!isset($params['sessionhash']) || $params['sessionhash'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params sessionhash'));
            return;
        }
        if (!isset($params['username']) || $params['username'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params username'));
            return;
        }
        if (!isset($params['userid']) || $params['userid'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params userid'));
            return;
        }

        if (!isset($params['message']) || $params['message'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
            return;
        }
        if (strlen($params['message']) < 10) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message too short, min 10 character'));
            return;
        }
//        if (!isset($params['title']) || $params['title'] == '') {
//            echo json_encode(array('code' => 5, 'message' => 'Missing params title'));
//            return;
//        }
        if (!isset($params['subject']) || $params['subject'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params subject'));
            return;
        }
        if (strlen($params['subject']) > 85) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params subject too long, max 85 character'));
            return;
        }
        $type = $params['type'];
        $sessionhash = $params['sessionhash'];
      //  $accessToken = $params['apiaccesstoken'];
        $username = $params['username'];
        $userid = $params['userid'];
        $message = $params['message'];
//        $title = $params['title'];
        $subject = $params['subject'];
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
       // f = 17 can ban
        $response = $api->callRequest('login_login', [
        'vb_login_username' => 'ksoft11',
            'vb_login_md5password' => '4297f44b13955235245b2497399d7a93'
        ]);
        //$currentLoggedInUserContext = new FetchCurrentUserInfo();
        //$loginUserContext = new Login('ksoft11', '4297f44b13955235245b2497399d7a93');
       // var_dump($currentLoggedInUserContext);die();
        if($type == 1){
            // Can ban
            $f_id = 69;
        }else{
            $f_id = 17;
        }
        $response = $api->callRequest('newthread_postthread', [
            //'vb_login_username' => 'ksoft11',
            'username' => $username,
            'userid' => $userid,
            'loggedinuser' => $userid,
            //'posthash' => '16a952533527c31b94ac267fe3c31850',
            //'securitytoken' => '1481159437-54655eab0d84752db00938a4aa7a3c53af9e82cb',

            'sessionhash'=>$sessionhash,
            'message'=>$message,
//            'title'=>$title,
            //'postnew'=>true,
            'subject'=>$subject,
            'postminchars'=>10,
            'f' => $f_id, 'api_v'=> '1'
        ], ConnectorInterface::METHOD_POST);
       // var_dump($response);
       // die;

        //postfloodcheck  errorlist errors

        //redirect_postthanks errormessage
       // var_dump($response);die();
        if(isset($response['response']->errormessage)) {
            if ($response['response']->errormessage == 'redirect_postthanks') {
                echo json_encode(array('code' => 0, 'message' => 'Post successfull.'));
                return;
            }
            //redirect_duplicatethread
            if ($response['response']->errormessage == 'redirect_duplicatethread') {
                echo json_encode(array('code' => 0, 'message' => 'Post successfull.'));
                return;
            }
            //redirect_postthanks_moderate
            if ($response['response']->errormessage == 'redirect_postthanks_moderate') {
                echo json_encode(array('code' => 0, 'message' => 'Post successfull. Please wait moderate acceptance'));
                return;
            }
        }
        echo json_encode(array('code' => 2, 'message' => 'Forum error'));
    }
}