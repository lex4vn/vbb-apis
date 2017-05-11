<?php

class ChatAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;

        if (!isset($params['type']) || $params['type'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params type'));
            return;
        }

        $type = $params['type'];
        Yii::log(':::::::::' . $type);
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {

            if ($type == 1) {
                $this->message();
            } elseif ($type == 2) {
                $this->conversation();
            } elseif ($type == 3) {
                $this->conversations();
            } elseif ($type == 4) {
                $this->deleteConversation();
            } else {
                echo json_encode(array('code' => 1, 'message' => 'Sai tham số type.'));
            }

        } else {
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
        }
    }

    private function deleteConversation()
    {
        // Xoa message
        if (!isset($params['receipt_id']) || $params['receipt_id'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params receipt_id'));
            return;
        }
        $message = Chat::model()->findAllByAttributes($params['from_user_id']);
        if ($message) {
            Yii::log($message['fromid']);
            if ($message['fromid'] != Yii::app()->session['user_id'] && $message['to'] != Yii::app()->session['user_id']) {
                echo json_encode(array('code' => 1, 'message' => 'Cannot delete message'));
                return;
            }
            $message->status_from = 0;
        } else {
            echo json_encode(array('code' => 102, 'message' => 'message_id not exist'));
            return;
        }
        if ($message->save()) {
            echo json_encode(
                array(
                    'code' => 0,
                    'message' => 'Delete message successful',
                )
            );
            return;
        } else {
            echo json_encode(
                array(
                    'code' => 1,
                    'message' => 'Cannot delete message',
                )
            );
            return;
        }
    }

    private function conversations()
    {
        $number_item = 10;
        $sender = '';
        // Get message all message
        $userid = Yii::app()->session['user_id'];
        $criteria = new CDbCriteria;
        $criteria->alias = 't';
        $criteria->select = 't.*';
        $criteria->condition = '(t.to = ' . $userid . ' and status_to = 1) or (status_from =1 and fromid = ' . $userid;
        //.Yii::app()->session['user_id'];
        $criteria->group = 'fromid,t.to';
        $criteria->order = 'time desc';

        $messages = Chat::model()->findAll($criteria);

        if (count($messages) > 0) {
//                $user = User::model()->findByAttributes(array('username'=>$sender));
//                $avatar = $user == null || $user->avatar == '' ? 'noavatar':  $user->avatar;
            $messageContents = array();
            $historyGroup = array();
            foreach ($messages as $mess) {
                if ($mess->fromuser == Yii::app()->session['user_id']) {
                    $historyGroup[]['from'] = $mess->touser;
                } else {
                    $historyGroup[]['from'] = $mess->fromuser;
                }
                $messageContents[] = array(
                    'sender_id' => $mess->fromid,
                    'sender_name' => $mess->fromuser,
                    'senddate' => $mess->time,
                    'read' => $mess->read,
                    'receipt_id' => $mess->to,
                    'receipt_name' => $mess->touser,
                    'content' => $mess->message,
                    'avatar_url' => '',
                );
            }
            //var_dump($historyGroup);die;
//            if ($type == 3) {
            // Get all
            echo json_encode(array('code' => 0,
                'message' => 'Get history messages successful',
                'listmessages' => $messageContents
            ));
//                return;
//            } else {
//                $user = User::model()->findByAttributes(array('username' => $sender));
//                $avatar = $user == null || $user->avatar == '' ? 'noavatar' : $user->avatar;
//                // Get message from username and current id
//                echo json_encode(array('code' => 0,
//                    'message' => 'Get all messages successful',
//                    'sender_name' => $sender,
//                    'avatar_url' => $avatar,
//                    'listmessages' => $messageContents
//                ));
//            }

        } else {
            echo json_encode(array('code' => 10, 'message' => 'You have no messages.'));
        }
    }

    private function conversation()
    {
        if (!isset($params['recipient']) || $params['recipient'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params recipient'));
            return;
        }
        $sender = $params['recipient'];

        // Get history
        $criteria = new CDbCriteria;
        $criteria->alias = 't';
        $criteria->select = 't.*';
        $criteria->condition = '(fromid = :userid and touser = :sender) or (t.to = :touserid and fromuser = :fromuser)';
        $criteria->params = array(
            ':userid' => Yii::app()->session['user_id'],
            ':sender' => $sender,
            ':touserid' => Yii::app()->session['user_id'],
            ':fromuser' => $sender);
        $criteria->order = 'time desc';

        $messages = Chat::model()->findAll($criteria);

        $messageContents = array();
        foreach ($messages as $mess) {
            $messageContents[] = array(
                'sender_id' => $mess->fromid,
                'sender_name' => $mess->fromuser,
                'senddate' => $mess->time,
                'read' => $mess->read,
                'receipt_id' => $mess->to,
                'receipt_name' => $mess->touser,
                'content' => $mess->message,
                'avatar_url' => '',
            );
        }
        $user = User::model()->findByAttributes(array('username' => $sender));
        $avatar = $user == null || $user->avatar == '' ? 'noavatar' : $user->avatar;
        // Get message from username and current id
        echo json_encode(array('code' => 0,
            'message' => 'Get all messages successful',
            "sender_name" => $sender,
            "avatar_url" => $avatar,
            'listmessages' => $messageContents
        ));
    }

    private function message()
    {
        if (!isset($params['recipient']) || $params['recipient'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params recipient'));
            return;
        }
        if (!isset($params['message']) || $params['message'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
            return;
        }
        $user = User::model()->findByAttributes(array('userid' => $params['recipient']));
        if (!$user) {
            $user = User::model()->findByAttributes(array('username' => $params['recipient']));
        }
        Yii::log($params['recipient']);
        if (!$user) {
            echo json_encode(array('code' => 5, 'message' => 'Message failed'));
            return;
        }
        $comment = new Chat();
        $comment->fromid = Yii::app()->session['user_id'];
        $comment->fromuser = Yii::app()->session['username'];
        $comment->to = $user->userid;
        $comment->touser = $user->username; // username
        $comment->message = $params['message'];
        $comment->read = 0;
        $comment->time = date('Y-m-d H:i:s');


        //Yii::log(json_encode((array)$comment));
        if ($comment->save()) {
            echo json_encode(
                array(
                    'code' => 0,
                    'message' => 'Send message successful',
                    'item' => array(
                        'id' => $comment->id,
                        'time' => $comment->time,
                        'message' => $comment->message,
                        'recipient' => $comment->touser
                    ),
                )
            );

            //send notification
            $fromuser = Yii::app()->session['username'];
            $message = "New message from " . $fromuser;
            //$recipient = User::model()->findByAttributes(array('userid'=>$recipient_id));
            $tokens = array();
            if (isset($user->device_token)) {
                $tokens[] = $user->device_token;
            }
            CUtils::send_notification($message, $tokens);
        } else {
            echo json_encode(array('code' => 5, 'message' => 'Message failed'));
        }
    }
}

