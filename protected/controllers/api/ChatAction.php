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

        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            if (!isset($params['type']) || $params['type'] == '') {
                echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
                return;
            }
            $type = $params['type'];
            $number_item = 10;
            $sender = '';
            // Send message
            Yii::log(':::::::::'.$type);
            if ($type == 1) {
                if (!isset($params['recipient']) || $params['recipient'] == '') {
                    echo json_encode(array('code' => 5, 'message' => 'Missing params recipient'));
                    return;
                }
                if (!isset($params['message']) || $params['message'] == '') {
                    echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
                    return;
                }
            } // Get message from someone
            else if ($type == 2) {
                if (!isset($params['recipient']) || $params['recipient'] == '') {
                    echo json_encode(array('code' => 5, 'message' => 'Missing params recipient'));
                    return;
                }
                $sender = $params['recipient'];
            } else {
                // Get message all message
                $number_item = isset($params['type']) && $params['type'] > 10 ? $params['type'] : $number_item;
                $type = 3;
            }


            if ($type == 1) {
                //var_dump(Yii::app()-
//                $recipient_id = 0;
//                $apiConfig = unserialize(base64_decode($sessionhash));
//                $api = new Api($apiConfig, new GuzzleProvider(API_URL));
//                $response = $api->callRequest('api_usersearch', [
//                    'fragment' => $params['recipient'],'api_v'=> '1'
//                ]);
//                //var_dump($response);die();
//                if (count($response) >= 3) {
//                    $recipient_id = $response[0];
//                    $recipient_name = $response[1];
//                }
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
                    return;
                } else {
                    echo json_encode(array('code' => 5, 'message' => 'Message failed'));
                    return;
                }
            } else if ($type == 2) {
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
                //  var_dump($criteria);die();
            } else {
                // Get message all message
                $criteria = new CDbCriteria;
                $criteria->alias = 't';
                $criteria->select = 't.*';
                $criteria->condition = 't.to = 20364 or fromid = 20364';//.Yii::app()->session['user_id'];
                $criteria->group = 'fromid,t.to';
                $criteria->order = 'time desc';

            }
            if ($type == 3) {
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
                    if ($type == 3) {
                        // Get all
                        echo json_encode(array('code' => 0,
                            'message' => 'Get history messages successful',
                            'listmessages' => $messageContents
                        ));
                        return;
                    } else {
                        $user = User::model()->findByAttributes(array('username' => $sender));
                        $avatar = $user == null || $user->avatar == '' ? 'noavatar' : $user->avatar;
                        // Get message from username and current id
                        echo json_encode(array('code' => 0,
                            'message' => 'Get all messages successful',
                            "sender_name" => $sender,
                            "avatar_url" => $avatar,
                            'listmessages' => $messageContents
                        ));
                        return;
                    }

                } else {
                    echo json_encode(array('code' => 10, 'message' => 'You have no messages.'));
                    return;
                }

            } else {
                $messages = Chat::model()->findAll($criteria);

            }
            if (count($messages) > 0) {
//                $user = User::model()->findByAttributes(array('username'=>$sender));
//                $avatar = $user == null || $user->avatar == '' ? 'noavatar':  $user->avatar;
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

                if ($type == 3) {
                    // Get all
                    echo json_encode(array('code' => 0,
                        'message' => 'Get history messages successful',
                        'listmessages' => $messageContents
                    ));
                    return;
                } else {
                    $user = User::model()->findByAttributes(array('username' => $sender));
                    $avatar = $user == null || $user->avatar == '' ? 'noavatar' : $user->avatar;
                    // Get message from username and current id
                    echo json_encode(array('code' => 0,
                        'message' => 'Get all messages successful',
                        "sender_name" => $sender,
                        "avatar_url" => $avatar,
                        'listmessages' => $messageContents
                    ));
                    return;
                }

            } else {
                echo json_encode(array('code' => 10, 'message' => 'You have no messages.'));
                return;
            }
        } else {
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
    }
}

