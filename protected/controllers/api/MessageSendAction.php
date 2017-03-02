<?php

class MessageSendAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;

        if (!isset($params['title']) || $params['title'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params title'));
            return;
        }

        if (!isset($params['message']) || $params['message'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
            return;
        }

        if (!isset($params['recipient']) || $params['recipient'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params recipient'));
            return;
        }

        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $comment = new Chat();
            $comment->from = Yii::app()->session['user_id'];
            $comment->to = 0;
            $comment->touser =$params['recipient']; // username
            $comment->message = $params['message'];
            $comment->read = 0;
            $comment->time = date('Y-m-d H:i:s');


            if($comment->save()){
                echo json_encode(
                    array(
                        'code' => 0,
                        'message' => 'Send message successful',
                        'item' => array(
                            'id' => $comment->id,
                            'time' => $comment->time,
                            'message' => $comment->content,
                            'recipient' => $comment->touser
                        ),
                    )
                );
                return;
            }else{
                echo json_encode(array('code' => 5, 'message' => 'Message failed'));
                return;
            }
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
    }
}

