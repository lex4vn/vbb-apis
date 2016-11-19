<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 11/6/2016
 * Time: 3:36 PM
 */
class DetailCommentAction extends CAction
{
    public function run(){
        header('Content-type: application/json');
        $params = $_GET;
        $threadId = isset($_GET['threadId']) ? $_GET['threadId'] : null;
        if ($threadId == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params thread id'));
            return;
        }
        $pageNumber = isset($_GET['pageNumber']) ? $_GET['pageNumber'] : null;
        if ($pageNumber == null) {
            $pageNumber = 1;
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

        $response = $api->callRequest('showthread', [
            'threadid' => $threadId, 'api_v'=> '1', 'pagenumber' => $pageNumber
        ], ConnectorInterface::METHOD_GET);
        $result = array();
        foreach ( $response["response"]->postbits as $postbit){
            $item = array(
                'username' => $postbit->post->username,
                'avatarurl' => $postbit->post->avatarurl,
                'onlinestatus' => $postbit->post->onlinestatus,
                'usertitle' => $postbit->post->usertitle,
                'message' => $postbit->post->message,
                'postdate' => $postbit->post->postdate
            );
            array_push($result, $item);
        }
// con thieu phan lay email, SĐT, check user hiện tại có phải là người đăng post hay là ban hay ko
        var_dump($result);
        die;
    }
}