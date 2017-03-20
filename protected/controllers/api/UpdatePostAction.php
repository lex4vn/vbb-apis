<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 10:43 AM
 */
class UpdatePostAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;

        if (!isset($params['sessionhash']) || $params['sessionhash'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params sessionhash'));
            return;
        }

        $postId = isset($params['postid']) ? $params['postid'] : null;

        if ($postId === null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params postid'));
            return;
        }

        $price = isset($params['price']) ? $params['price'] : null;
        $status = isset($params['status']) ? $params['status'] : null;
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {

            $post = Post::model()->findByPk($postId);
            if($post->postuserid === $postId) {
                if($price != null){
                    $post->price = $price;
                }
                if($status != null){
                    $post->status = $status == 'Mới' || $status == 0 ? 0 : 1;
                }

                $post->modify_date = date('Y-m-d H:i:s');
                $post->save();
                echo json_encode(array('code' => 0, 'message' => 'Post successfull.'));
            }else{
                echo json_encode(array('code' => 1, 'message' => 'Không cập nhật được bài của người khác'));
            }

        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
        }
    }

    private function send_notification($sessionhash, $msg)
    {
        $apiConfig = unserialize(base64_decode($sessionhash));
        $api = new Api($apiConfig, new GuzzleProvider(API_URL));
        $response = $api->callRequest('profile_buddylist', [
            'api_v' => '1'
        ], ConnectorInterface::METHOD_POST);
        if (isset($response['response'])) {
            $tokens = array();
            foreach ($response['response']->HTML->buddylist as $buddy) {
                $user = $buddy->user;

                $userid = $user->userid;

                $user = User::model()->findByAttributes(array('userid' => $userid));
                if (isset($user) && isset($user->device_token)) {
                    $tokens[] = $user->device_token;
                }

            }
            CUtils::send_notification($msg, $tokens);
        }

    }
}