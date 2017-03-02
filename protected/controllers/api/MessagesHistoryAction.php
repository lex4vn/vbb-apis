<?php

class MessagesHistoryAction extends CAction
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
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            $response = $api->callRequest('private_messagelist', ['api_v' => '1'], ConnectorInterface::METHOD_GET);
//            var_dump($response);
//            die();

            if (!isset($response['response'])) {
                echo json_encode(array('code' => 10, 'message' => 'You have no messages.'));
                return;
            }

            $html = $response['response']->HTML;
//            var_dump($html);
//            die();
            if ($html->folderid == 0) {

                $messages = array();
                $groups = $html->messagelist_periodgroups;
               // var_dump($groups);die();
                foreach ($groups as $group) {
                    $messagesBits = $group->messagelistbits;
                    $numberMessage = $group->messagesingroup;
                    if($numberMessage > 1) {
                        foreach ($messagesBits as $messagesBit) {

                            //                        if(isset($messagesBit->pm)){
                            //                            continue;
                            //                        }

                            $messages[] = array(
                                'messageid' => $messagesBit->pm->pmid,
                                'sender_id' => $messagesBit->userbit[0]->userid,
                                'sender_name' => $messagesBit->userbit[0]->username,
                                'senddate' => $messagesBit->pm->senddate . ' ' . $messagesBit->pm->sendtime,
                                'statusicon' => $messagesBit->pm->statusicon,
                                'receipt_id' => Yii::app()->session['user_id'],
                                'content' => $messagesBit->pm->title,
                                'avatar_url' => '',
                            );
                            //                        var_dump($messages);
                            //                        die();
                        }
                    }else{
                        $messages[] = array(
                            'messageid' => $messagesBits->pm->pmid,
                            'sender_id' => $messagesBits->userbit[0]->userid,
                            'sender_name' => $messagesBits->userbit[0]->username,
                            'senddate' => date('m/d/Y h:i:s', strtotime($messagesBits->pm->senddate . ' ' . $messagesBits->pm->sendtime)),
                            'statusicon' => $messagesBits->pm->statusicon,
                            'receipt_id' => Yii::app()->session['user_id'],
                            'content' => $messagesBits->pm->title,
                            'avatar_url' => '',
                        );
                    }
                }

//                                        var_dump($messages);
//                        die();
                echo json_encode(array('code' => 0,
                    'message' => 'Get history messages successful',
                    'listmessages' => $messages
                ));
                return;
            }else{
                echo json_encode(array('code' => 10, 'message' => 'You have no messages.'));
                return;
            }
        } else {
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
    }
}

