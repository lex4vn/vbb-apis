<?php

class FriendDetailAction extends CAction
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
        if (Yii::app()->session['user_id']) {
            $buddy = Relationship::model()->friendDetail($params['userid']);
            $result = array();
            $item = array(
                'userid' => $buddy['userid'],
                'username' => $buddy['username'],
                'avatarurl' => $buddy['avatar'],
                'fullname' => isset($buddy['fullname']) ? $buddy['fullname'] : $buddy['username'],
                'phonenumber' => $buddy['phonenumber'],
                'onlinestatus' => $buddy['expiry_date'] >= (time() + 900)?'Online' : 'Offline',
                'displayemail' => empty($buddy['email']) ? '' : $buddy['email'],
            );
            array_push($result, $item);
            echo json_encode(array('code' => 0,
                'message' => 'Get profile of friend success',
                'listfriend' => $result
            ));
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out', 'abc' => $sessionhash));
        }
    }
}