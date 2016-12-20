<?php

class SearchAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        if (!isset($params['search']) || $params['search'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params search'));
            return;
        }
        if (!isset($params['forumid']) || $params['forumid'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params forumid'));
            return;
        }
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            // search_type = 1: Bai viet, 3: Chuyen muc
            // Forumchoice = 17: Can mua, 69: Can ban
            //api: search_doprefs
            $response = $api->callRequest('search_process', [
                'search_type' => '3',
                'query' => $params['search'],
                'forumchoice' => $params['forumid'],
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            if(isset($response['response']) &&  'search' == $response['response']->errormessage){
                echo json_encode(array('code' => 0,
                    'message' => 'search success',
                    'searchid' => $response['show']->searchid));
            }
        }else{
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }


}