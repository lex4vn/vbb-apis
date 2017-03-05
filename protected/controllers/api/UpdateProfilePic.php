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
        //$data = $params['image'];

        $image_base64 = $params['image'];

        $binary = base64_decode($image_base64);
        header('Content-Type: bitmap; charset=utf-8');

        $file_name = date('YmdHis') . '-' . rand() . '.jpg';
        $path = fopen(IMAGES_SOURCE . '/' . $file_name, 'w+');

        //write file
        fwrite($path, $binary);
        //close file stream
        fclose($path);

        $post_image = new Images();
        $post_image->type = 3;
        $post_image->status = 1;
        //$post_image->width = $item['width'];
        //$post_image->height = $item['height'];
        $post_image->base_url = $file_name;
        if (!$post_image->save()){
            echo json_encode(array('code' => 5, 'message' => 'Cannot upload images file'));
            return;
        }

        //$size = getimagesize(IMAGES_SOURCE.$file_name);
        //$post_image->width = $size[0];
        // $post_image->height = $size[1];
        $post_image->save();

        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $user = User::model()->findByPk(Yii::app()->session['user_id']);
            $user->avatar = IMAGES_PATH .$post_image->base_url;
            if ($user->save()) {
                echo json_encode(array('code' => 0, 'message' => 'Profile picture update successfully'));
                return;
            } else {
                echo json_encode(array('code' => 1, 'message' => 'Image error'));
                return;
            }

        }else {
                echo json_encode(array('code' => 101, 'message' => 'User logged out'));
                return;
            }
//        //Yii::log('avatar_link'.$params['avatarurl']);
//        //Yii::log('image'.$data);
//        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
//         if ($sessionhash) {
//            $apiConfig = unserialize(base64_decode($sessionhash));
//            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
//            $response = $api->callRequest('profile_updateprofilepic',
//                ['deleteprofilepic' => true, 'avatarurl' => $params['avatarurl'], 'upload' => $data,'api_v' => '1'], ConnectorInterface::METHOD_POST);
//            if(isset($response['response'])){
//                 $responsemessage = $response['response'] -> errormessage[0];
//                if(strcasecmp ($responsemessage, "redirect_updatethanks") == 0){
//                    echo json_encode(array('code' => 0, 'message' => 'Profile picture update successfully'));
//                } else {
//                    echo json_encode(array('code' => 1, 'message' => $responsemessage));
//                }
//                $user = User::model()->findByPk(Yii::app()->session['user_id']);
//                if($user != null){
//                    $user->avatar = $params['avatarurl'];
//                    $user->save();
//                    Yii::log('::::UP AVATAR:::'.$params['avatarurl']);
//                    Yii::log('::::UPDATE AVATAR:::'.$user->avatar);
//                }
//            }else{
//                echo json_encode(array('code' => 2, 'message' => 'Forum error'));
//                return;
//            }
//        }
//        else {
//            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
//            return;
//        }
    }
}