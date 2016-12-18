<?php

class SearchAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_POST;
        if (!isset($params['txtSearch']) || $params['txtSearch'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Text to search cannot be empty'));
            return;
        }
        $params = $_POST;
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
            $response = $api->callRequest('search_showresults', [
                'fragment' => $params['txtSearch'], 'forumid' => '17', 'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            var_dump($response);
            die(1);
            if (isset($response['userid']) && isset($response['username'])) {
                echo json_encode(array('code' => 0, 'message' => 'Successful', 'userid' => $response['userid'], 'username' => $response['username']));
                return;
            } else {
                echo json_encode(array('code' => 1, 'message' => 'Your email address is not registered.'));
                return;
            }

        }else{
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }


}