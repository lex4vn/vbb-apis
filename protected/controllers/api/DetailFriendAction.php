<?php

class DetailFriendAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
		if (!isset($params['userid']) || $params['userid'] == '') {
			echo json_encode(array('code' => 5, 'message' => 'Missing params userid'));
			return;
		} 
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
			$profile = $api->callRequest('member ', [
			    'userid'=> $params['userid'],
				'api_v' => '1'
			], ConnectorInterface::METHOD_POST);

            if(isset($profile['response'])){
				$result = array();			
				$fields = $profile['response']->blocks->aboutme->block_data->fields->category->fields;
				$phonenumber = '';
				$fullname = '';
				foreach($fields as $field) {
					if ($field->profilefield->title == "Họ và Tên") {
						$fullname = $field->profilefield->value;
					} else if ($field->profilefield->title == "Phone Number"){
						$phonenumber = $field->profilefield->value;
					}
				}
				
				$item = array(
					'username' => $profile['response']->prepared->username,
					'avatarurl' => $profile['response']->prepared->avatarurl,
					'fullname' => $fullname,
					'phonenumber' => $phonenumber,
					'onlinestatus' => $profile['response']->prepared->onlinestatus->onlinestatus == 1 ? "online" : "offline",
					'displayemail' => $profile['response']->prepared->displayemail,
					);
				array_push($result, $item);
               
				echo json_encode(array('code' => 0,
					'message' => 'Get profile of friend success',
					'listfriend' => $result
				));
            }else{
                echo json_encode(array('code' => 2, 'message' => 'Forum error'));
                return;
            }
        }
        else {
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out', 'abc' =>$sessionhash));
            return;
        }
    }
}