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
        if (!isset($params['status'])) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params status'));
            return;
        }

        $status = isset($params['status']) ? $params['status'] : '0';
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {

            $post = Post::model()->findByPk($postId);
            if($post->postuserid === $postId) {
                $post->status = $status == 'Mới' ? 0 : 1;
                $post->modify_date = date('Y-m-d H:i:s');
                $post->save();
                echo json_encode(array('code' => 0, 'message' => 'Post successfull.'));
            }else{
                echo json_encode(array('code' => 1, 'message' => 'Đã có lỗi khi đăng bài'));
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