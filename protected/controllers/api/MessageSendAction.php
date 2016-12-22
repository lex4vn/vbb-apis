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
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            $response = $api->callRequest('private_insertpm', [
                'recipients' => $params['recipient'],
                'title' => $params['title'],
                //'content' => 'abcdeddadaaa',
                'message' => $params['message'],
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            //var_dump($response);
            //die();
            if (isset($response['response'])) {
                $res = $response['response'];
                if (isset($res->errormessage) && $res->errormessage == 'pm_messagesent') {
                    echo json_encode(array('code' => 0,
                        'message' => 'Send message successful',
                    ));
                    return;
                }
                if (isset($res->postpreview) && isset($res->postpreview->errorlist)) {
                    echo json_encode(array('code' => 2,
                        'message' => 'Please try later. Maybe you send many times.',
                    ));
                    return;
                }
            } else {
                echo json_encode(array('code' => 1, 'message' => 'Forum error'));
                return;
            }
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}

