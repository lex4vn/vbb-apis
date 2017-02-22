<?php

class ProfileAction extends CAction
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
            $response = $api->callRequest('member', [
                'u'=> Yii::app()->session['user_id'],
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            // TODO
            if(isset($response['response'])){
				$fields = $response['response']->blocks->aboutme->block_data->fields->category->fields;
				$phonenumber = '';
				$fullname = '';
				if(isset($fields)){

					foreach($fields as $field) {
						if ($field->profilefield->title == "Họ và Tên") {
							$fullname = $field->profilefield->value;
						} else if ($field->profilefield->title == "Phone Number"){
							$phonenumber = $field->profilefield->value;
						}
					}
				}


				$profile = array(
					'username' => $response['response']->prepared->username,
					'fullname' => $fullname,
					'phonenumber' => $phonenumber,
					'birthday' => date('d/m/Y',strtotime($response['response']->prepared->birthday)),
					'age' => $response['response']->prepared->age,
					'displayemail' => $response['response']->prepared->displayemail,
					'homepage' => $response['response']->prepared->homepage,
					'usertitle' => $response['response']->prepared->usertitle,
					'onlinestatus' => $response['response']->prepared->onlinestatus->onlinestatus == 1 ? "online" : "offline",
					'joindate' => date('d/m/Y',strtotime($response['response']->prepared->joindate)),
					'posts' => $response['response']->prepared->posts,
					'avatarurl' =>str_replace("amp;","",API_URL.$response['response']->prepared->avatarurl)
					);
				echo json_encode($profile);

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