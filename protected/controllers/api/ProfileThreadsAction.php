<?php

class ProfileThreadsAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        // var_dump($_POST['sessionhash']);die();
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            $response = $api->callRequest('search_finduser', [
                'userid'=> Yii::app()->session['user_id'],
                'contenttype'=> 'vBForum_Thread',
                'starteronly'=> 1,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);

            //var_dump($response);die();
            $searchId = '0';
            if(isset($response['response']) &&  'search' == $response['response']->errormessage){
                $searchId = $response['show']->searchid;
            }
            //var_dump($searchId);die();
            $response = $api->callRequest('search_showresults', [
                'searchid'=> $searchId,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);

            var_dump($response);die();
            // TODO
            if(isset($response['response'])){
                $response['response']->HTML->buddycount;
                $response['response']->HTML->buddylist;

            }else{
                echo json_encode(array('code' => 2, 'message' => 'Forum error'));
                return;
            }
        }
        else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}