<?php

class AddCommentAction extends CAction
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

        if (!isset($params['message']) || $params['message'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
            return;
        }

        if (strlen($params['message']) < 10) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message too short, min 10 character'));
            return;
        }

        if (!isset($params['postid']) || $params['postid'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params postid'));
            return;
        }

        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));

        if ($sessionhash) {
            $user = User::model()->findByPk(Yii::app()->session['user_id']);
            $avatarurl = 'noavatar.png';
            if($user != null){
                $avatarurl = $user->avatar;
            }
            $comment = new Comment();
            $comment->user_id = Yii::app()->session['user_id'];
            $comment->post_id = $params['postid'];
            $comment->content = $params['message'];
            $comment->username = Yii::app()->session['username'];
            $comment->avatar = $avatarurl;
            $comment->create_date = date('Y-m-d H:i:s');
            $comment->modify_date = date('Y-m-d H:i:s');

            if($comment->save()){
                $post = Post::model()->findByPk($comment->post_id);
                $post->count_comment += 1;
                if($post->save()){
                    echo json_encode(array('code' => 0, 'message' => 'Post successfull.'));
                    $this->send_notification ($sessionhash, $params['postid']);
                    return;
                }

            }
            Yii::log(json_encode((array)$comment));
            echo json_encode(array('code' => 5, 'message' => 'Comment failed'));
            return;
        } else {
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
        echo json_encode(array('code' => 2, 'message' => 'Forum error'));
    }
    private function send_notification ($sessionhash, $threadId) {
        $apiConfig = unserialize(base64_decode($sessionhash));
        $api = new Api($apiConfig, new GuzzleProvider(API_URL));
        $response = $api->callRequest('showthread', [
            'threadid' => $threadId,
            'api_v' => '1'
        ], ConnectorInterface::METHOD_GET);

        if (isset($response['response'])){
            $tokens = array();
            $post_owner = $response['response']->postbits->post->userid;
            //check you are not post_owner -> do not push notification
            $user_id = Yii::app()->session['user_id'];
            if ($user_id == $post_owner) {
                //return;
            }
            $user = User::model()->findByAttributes(array('userid'=>$post_owner));
            if(isset($user) && isset($user->device_token)) {
                $tokens[] = $user->device_token;
            }
            $post_title = $response['response']->postbits->post->title;
            $user_name = Yii::app()->session['username'];
            $message = $user_name." just commented on your thread '" .$post_title. "'.";
            var_dump($message);
            //CUtils::send_notification($message, $tokens);
        }
    }
}