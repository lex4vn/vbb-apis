<?php

class GetForum extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
//        $forumid = isset($_GET['forumid']) ? $_GET['forumid'] : null;
//        if ($forumid == null) {
//            echo json_encode(array('code' => 5, 'message' => 'Missing params forumid'));
//            return;
//        }
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
            'api_v'=> '1',
            'uniqueid' => $uniqueId]);

        //var_dump($response);die(1);
        //var_dump($post_id);die(1);
        // Get token key
        $accessToken = $response['apiaccesstoken'];
       // var_dump($accessToken);die(1);
        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);
        $response = $api->callRequest('forumdisplay', ['forumid' => '59', 'api_v'=> '1' ], ConnectorInterface::METHOD_GET);
        var_dump($response);
        die(1);
        //Thanh cong
        if (isset($response['response'])) {

        } else {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }

    }
}

