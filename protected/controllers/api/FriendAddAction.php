<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 10:43 AM
 */
class FriendAddAction extends CAction
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

        $userId = Yii::app()->session['user_id'];
        $user = User::model()->findByPk($params['userid']);

        if ($user == null) {
            $isError = false;
            if ($sessionhash) {
                $apiConfig = unserialize(base64_decode($sessionhash));
                $api = new Api($apiConfig, new GuzzleProvider(API_URL));
                $profile = $api->callRequest('member ', [
                    'userid'=> $params['userid'],
                    'api_v' => '1'
                ], ConnectorInterface::METHOD_POST);
                Yii::log('ID friend:'.$params['userid']);
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
                    $usertitle = '';
                    $avatar = '';
                    $status = '';
                    $email = '';
                    if(isset($profile['response']->prepared)) {
                        $usertitle = $profile['response']->prepared->usertitle;
                        $avatar = str_replace("amp;","",API_URL.$profile['response']->prepared->avatarurl);
                        $status = $profile['response']->prepared->onlinestatus->onlinestatus == 1 ? "1" : "0";
                        $email = $profile['response']->prepared->displayemail;
                    }
                    Yii::log('Name friend:'.$profile['response']->prepared->username);
                    $user = User::model()->findByPk($params['userid']);
                    if($user == null){
                        $user = new User();
                        $user->userid = $params['userid'];
                        $user->username = $profile['response']->prepared->username;
                        $user->phonenumber =  $phonenumber;
                        $user->password =  $profile['response']->prepared->displayemail;
                        $user->usertitle = $fullname;
                        $user->avatar = $avatar;
                        $user->status = $status;
                    }else{
                        $user->phonenumber =  $phonenumber;
                        $user->usertitle = $usertitle;
                        //$user->avatar = $avatar;
                        $user->password =  $email;
                        $user->status = $status;
                    }
                    $user->save();

                }else{
                    $isError = true;
                }
            }

            if($isError){
                echo json_encode(array('code' => 1, 'message' => 'Can not add this user.'));
                return;
            }

        }

        if ($userId) {

            if ($userId == $params['userid']) {
                echo json_encode(array('code' => 1, 'message' => 'Can not add you.'));
                return;
            }
            if(Relationship::model()->exists('user_one_id=:id AND user_two_id=:uid AND status = 1',array(':id'=>$userId,':uid'=>$params['userid']))){
                echo json_encode(array('code' => 1, 'message' => 'This is your friend.'));
                return;
            }
            $relationShip = new Relationship();
            $relationShip->user_one_id = $userId;
            $relationShip->user_two_id = $params['userid'];
            $relationShip->status = 1;
            $relationShip->action_user_id = $userId;

            if ($relationShip->save()) {
                echo json_encode(array('code' => 0, 'message' => 'Add ' . $user['username'] . ' to friend list successfull.'));

            } else {
                Yii::log(json_encode((array)$relationShip->getErrors()));
                echo json_encode(array('code' => 1, 'message' => 'Can not add this user.'));
            }
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
        }

    }
}