<?php


class UpdateProfilePic extends CAction{

    public function run(){
        header('Content-type: application/json');
		if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
         if (!isset($params['image']) || $params['image'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params image'));
            return;
        }
         if (!isset($params['avatarurl']) || $params['avatarurl'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params avatarurl'));
            return;
        }
       $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
         if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
            $response = $api->callRequest('profile_updateprofilepic', 
                ['deleteprofilepic ' => true, 'avatarurl' => $params['avatarurl'], 'upload' => $params['image'],'api_v' => '1'], ConnectorInterface::METHOD_POST);
            var_dump($response);
            die(1);
            if(isset($response['response'])){
            }else{
                echo json_encode(array('code' => 2, 'message' => 'Forum error'));
                return;
            }
        }
        else {
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}