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
        $data = $params['image'];
        Yii::log('avatar_link'.$params['avatarurl']);
        Yii::log('image'.$data);
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
         if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
            $response = $api->callRequest('profile_updateprofilepic',
                ['deleteprofilepic' => true, 'avatarurl' => $params['avatarurl'], 'upload' => $data,'api_v' => '1'], ConnectorInterface::METHOD_POST);
            if(isset($response['response'])){
                 $responsemessage = $response['response'] -> errormessage[0];
                if(strcasecmp ($responsemessage, "redirect_updatethanks") == 0){
                    echo json_encode(array('code' => 0, 'message' => 'Profile picture update successfully'));
                    $user = User::model()->findByPk(Yii::app()->session['user_id']);
                    if($user != null){
                        $user->avatar = $params['avatarurl'];
                        $user->save();
                    }
                } else {
                    echo json_encode(array('code' => 1, 'message' => $responsemessage));
                }
            }else{
                echo json_encode(array('code' => 2, 'message' => 'Forum error'));
                return;
            }
        }
        else {
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
    }
}