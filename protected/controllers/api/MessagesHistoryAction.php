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
            if ($html->foderid == 0) {
                echo json_encode(array('code' => 10, 'message' => 'You have no messages.'));
                return;
            }
            $messages = array();
            $groups = $html->messagelist_periodgroups;
            foreach ($groups as $group) {
                $messagesBits = $group->messagelistbits;
                foreach ($messagesBits as $messagesBit) {
                    $messages[] = array(
                        'messageid' => $messagesBit->pm->pmid,
                        'sender_id' => $messagesBit->userbit->userid,
                        'sender_name' => $messagesBit->userbit->username,
                        'senddate' => date('d/m/Y h:i:s', strtotime($messagesBit->pm->senddate . ' ' . $messagesBit->pm->sendtime)),
                        'statusicon' => $messagesBit->pm->statusicon,
                        'receipt_id' => Yii::app()->session['user_id'],
                        'content' => $messagesBit->pm->title,
                        'avatar_url' => '',
                    );
                }
            }
            echo json_encode(array('code' => 0,
                'message' => 'Get history messages successful',
                'listmessages' => $messages
            ));
            return;
        } else {
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}

