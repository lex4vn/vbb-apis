<?php

class ListProfileAction extends CAction
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
            $apiConfig = unserialize(base64_decode($params['sessionhash']));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
//            $response = $api->callRequest('newthread_postthread', [
//                'username' => isset($params['username']) ? $params['username'] : '',
//                'message' => $info,
//                'subject' => $params['subject'],
//                'f' => $params['type'] == 1 ? 69 : 17,
//                'api_v' => '1'
//            ], ConnectorInterface::METHOD_POST);
        }
        else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}