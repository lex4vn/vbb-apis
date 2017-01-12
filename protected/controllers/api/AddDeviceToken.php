<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 10:43 AM
 */
class AddDeviceToken extends CAction
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

        if (!isset($params['device_token']) || $params['device_token'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params device_token'));
            return;
        }
        if (!isset($params['device_type']) || $params['device_type'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params device_type'));
            return;
        }

        $user = User::model()->findByAttributes(array('userid'=>$params['userid']));
        if($user == null){
            $user = new User();
            $user->userid = $params['userid'];
            $user->device_token = $params['device_token'];
            $user->device_type = $params['device_type'];
        }else{
            $user->device_token = $params['device_token'];
            $user->device_type = $params['device_type'];
        }
        if($user->save()){
            echo json_encode(array('code' => 0, 'message' => 'Add device token successfull.'));
        }else{
            echo json_encode(array('code' => 1, 'message' => 'Cannot add device token.'));
        }

    }
}