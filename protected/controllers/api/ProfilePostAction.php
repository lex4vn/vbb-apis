<?php

class ProfilePostAction extends CAction
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
                'searchuser'=> Yii::app()->session['user_id'],
                'type'=> array(1),
                'contenttypeid'=> 3,
                'showposts'=> 1,
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