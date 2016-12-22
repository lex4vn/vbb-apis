<?php

class MessagesAction extends CAction
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
            //var_dump($response);die();
            //Thanh cong
            if (isset($response['response'])) {
                $items = array();
                $html = $response['response']->HTML;

                echo json_encode(array('code' => 0,
                    'message' => 'Get messages successful',
                    'box' => $html->folderid,
                    'total' => $html->totalmessages
                ));
                return;
            } else {
                echo json_encode(array('code' => 1, 'message' => 'Forum error'));
                return;
            }
        }else{
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}

