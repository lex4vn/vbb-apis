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

		if(empty($params['md5_password']) || !isset($params['md5_password'])) {
			echo json_encode(array('code' => 1, 'message' => 'Missing params md5_password'));
			return;
		}
		$md5_password = $params['md5_password'];
		$name = !empty($params['fullname']) ? $params['fullname'] : '';
		
		$phonenumber =  !empty($params['phonenumber']) ? $params['phonenumber'] : '';
       
		
		$sessionhash = CUtils::getSessionHash(($params['sessionhash']));
		if ($sessionhash) {
			$userfield = [];
			if (!empty($name)) {
				$userfield["field5"] = $name;
			}
			
			if (!empty($phonenumber )) {
				$userfield["field8"] = $phonenumber;
			}
			
			$apiConfig = unserialize(base64_decode($sessionhash));
			$api = new Api($apiConfig, new GuzzleProvider(API_URL));
			$response = $api->callRequest('profile_updateprofile', [
                 'userfield' => $userfield
            ]);
			if (!isset($response) || !isset($response['response']) || !isset($response["response"]->errormessage[0])) {
				echo json_encode(array('code' => 1, 'message' => 'Forum error'));
				return;
			}
			
			$responsemessage = $response["response"]->errormessage[0];
			if(strcasecmp ($responsemessage, "redirect_updatethanks") == 0) {
				echo json_encode(array('code' => 0, 'message' => 'Profile update successfully'));
			} else {
				echo json_encode(array('code' => 1, 'message' => $responsemessage));
			}
		} else {
			echo json_encode(array('code' => 10, 'message' => 'User logged out'));
		}
    }
}