<?php

class ListCommentInPost extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        $post_id = isset($params['post_id']) ? $params['post_id'] : null;
        if ($post_id == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params post_id'));
            return;
        }
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            $response = $api->callRequest('blog_post_comment',  [
                'post_d' => $post_id,], ConnectorInterface::METHOD_POST);
            //var_dump($response);die(1);
            if (isset($response['response'])) {

            }else{
                echo json_encode(array('code' => 1, 'message' => 'Forum error'));
                return;
            }
        }else{
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
    }
}

