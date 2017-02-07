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
       // var_dump($_POST['sessionhash']);die();
		$userid = isset($params['userid']) ? $params['userid'] : null;
		if ($userid == null) {
			echo json_encode(array('code' => 5, 'message' => 'Missing params userid'));
			return;
		}
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
		//var_dump($sessionhash);die();
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

			$profile = $api->callRequest('member ', [
				'userid'=> $userid,
				'api_v' => '1'
			], ConnectorInterface::METHOD_POST);

			// TODO update
			var_dump($profile);die();
			if(isset($profile['response'])) {
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
					'userid' => $user->userid,
					'username' => $user->username,
					'avatarurl' => $profile['response']->prepared->avatarurl,
					'fullname' => $fullname,
					'phonenumber' => $phonenumber,
					'onlinestatus' => $profile['response']->prepared->onlinestatus->onlinestatus == 1 ? "online" : "offline",
					'displayemail' => $profile['response']->prepared->displayemail,
				);
				array_push($result, $item);
			}

			//var_dump($profile);die();
        }
        else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out', 'abc' =>$sessionhash));
            return;
        }
    }
}