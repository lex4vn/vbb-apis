<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 11/5/2016
 * Time: 2:39 PM
 */
class DetailPostAction extends CAction
{
    public function run()
    {

        //test
        header('Content-type: application/json');
        $params = $_GET;
        $threadId = isset($_GET['threadId']) ? $_GET['threadId'] : null;
        if ($threadId == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params thread id'));
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

        $response = $api->callRequest('showthread', [
            'threadid' => $threadId, 'api_v'=> '1'
        ], ConnectorInterface::METHOD_GET);
        $userId = $response["response"]->postbits[0]->post->userid;

        $result = array(
            'username' => $response["response"]->postbits[0]->post->username,
            'avatarurl' => $response["response"]->postbits[0]->post->avatarurl,
            'onlinestatus' => $response["response"]->postbits[0]->post->onlinestatus,
            'usertitle' => $response["response"]->postbits[0]->post->usertitle,
            'postid' => $response["response"]->postbits[0]->post->postid, //first post id
            'postdate' => $response["response"]->postbits[0]->post->postdate,
            'title' => $response["response"]->postbits[0]->post->title,
            'message' => $response["response"]->postbits[0]->post->message,
        );
        // con thieu phan lay email, SĐT, check user hiện tại có phải là người đăng post hay là ban hay ko
        var_dump($result);
        die;


    }
}