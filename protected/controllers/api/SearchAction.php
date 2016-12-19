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
                'forumchoice' => '17',
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);

            // TODO
            //words_very_common: tu khoa ngan qua
           var_dump($response);die();

            $searchId = '0';
            if(isset($response['response']) &&  'search' == $response['response']->errormessage){
                $searchId = $response['show']->searchid;
            }
           // var_dump($searchId);die();
            $response = $api->callRequest('search_showresults', [
                'searchid'=> $searchId,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            var_dump($response);die(1);

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