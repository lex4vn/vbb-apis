<?php

class ListCommentInPost extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $post_id = isset($_GET['post_id']) ? $_GET['post_id'] : null;
        if ($post_id == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params post_id'));
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
        //var_dump($post_id);die(1);
        // Get token key
        $accessToken = $response['apiaccesstoken'];

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);
        $response = $api->callRequest('blog_post_comment',  [
            'post_d' => $post_id,], ConnectorInterface::METHOD_POST);
          //var_dump($response);die(1);
        //Thanh cong
        if (isset($response['response'])) {

        }else{
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }

    }
}

