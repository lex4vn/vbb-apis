<?php

class ListProfileBuddyAction extends CAction
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
		//var_dump($sessionhash);die();
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
            $response = $api->callRequest('profile_buddylist', [
               // 'userid'=> 123,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);

			//var_dump($response);die();
            if(isset($response['response'])){
				$result = array();
				$buddycount = $response['response']->HTML->buddycount;
				//var_dump($buddycount);die();
				if($buddycount == 0){
					echo json_encode(array('code' => 3, 'message' => 'List friend is empty.'));
					return;
				}
				foreach ($response['response']->HTML->buddylist as $buddy){
					$user = $buddy->user;
					$userid = $user->userid;
					$api = new Api($apiConfig, new GuzzleProvider(API_URL));
					$profile = $api->callRequest('member ', [
					    'userid'=> $userid,
						'api_v' => '1'
					], ConnectorInterface::METHOD_POST);
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
							'username' => $user->username,
							'avatarurl' => $user->avatarurl,
							'fullname' => $fullname,
							'phonenumber' => $phonenumber,
							'onlinestatus' => $profile['response']->prepared->onlinestatus->onlinestatus == 1 ? "online" : "offline",
							'displayemail' => $profile['response']->prepared->displayemail,
						);
						 array_push($result, $item);
					} 
               
				}
				echo json_encode($result);
            }else{
                echo json_encode(array('code' => 2, 'message' => 'Forum error'));
                return;
            }
        }
        else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out', 'abc' =>$sessionhash));
            return;
        }
    }
}