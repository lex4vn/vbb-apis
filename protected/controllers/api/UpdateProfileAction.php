<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hungld
 * Date: 1/7/15
 * Time: 2:18 PM
 * To change this template use File | Settings | File Templates.
 */

class UpdateProfileAction extends CAction{
    public function run(){
        header('Content-type: application/json');
		if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
		
		//$sessionhash = CUtils::getSessionHash(($params['sessionhash']));
		
        if(!isset($params['user_id'])){
            if(!isset($params['user_id'])){
                echo json_encode(array('code' => 1, 'message' => 'Missing params user_id'));
                return;
            }
        }
		if (!Subscriber::model()->exists('id = '. $params['user_id'])){
            echo json_encode(array('code' => 1, 'message' => 'user_id is not exist'));
            return;
        }
		
        $subs = Subscriber::model()->findByPk($params['user_id']);
		var_dump($subs); die();
        if(isset($params['phone_number'])){
           $subs->phone_number = $params['phone_number'];
        }
        if(isset($params['lastname'])){
           $subs->lastname = $params['lastname'];
        }
        if(isset($params['firstname'])){
           $subs->firstname = $params['firstname'];
        }
        if(isset($params['email'])){
           $subs->email = $params['email'];
        }
        if(isset($params['class_id'])){
           $subs->class_id = $params['class_id'];
        }
        if(!$subs->save()){
           echo json_encode(array('code' => 1, 'message' => 'Cannot update profile'));
            return; 
        }else{
//            $subs = (array)$subs;
            if($subs->url_avatar != null){
                if($subs->password == 'faccebook' || $subs->password == 'Google'){
                    $url_avatar = $subs->url_avatar;
                }else{
                    $url_avatar = IPSERVER . $subs->url_avatar;
                }
//                $url_avatar = IPSERVER.$subs->url_avatar;
            }else{
                $url_avatar = '';
            }
            $lastname = !empty($subs->lastname) ? $subs->lastname : '';
            $firstname = !empty($subs->firstname) ? $subs->firstname : '';
            $name = $firstname . ' ' . $lastname;
            $mail = !empty($subs->email) ? $subs->email : "";
            $usingService = ServiceSubscriberMapping::model()->findByAttributes(
                array(
                    'subscriber_id' => $subs->id,
                    'is_active' => 1
                )
            );
			$userfield = [];
			$userfield["field5"] = !empty($name) ? $name : 'Noname';
			
			$userfield["field8"] = !empty($subs->phone_number) ? (string)$subs->phone_number : '';
			
			$response = $api->callRequest('profile_updateprofile', [
                    'userfield' => $userfield
               ]);
			var_dump($response); die();
			$email_result = $api->callRequest('profile_updateprofile', [
                    'currentpassword' => '123123', 
					'newpassword' => $mail
               ]);
			
            return;  
        }
    }
}