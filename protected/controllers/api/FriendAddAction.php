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
            echo json_encode(array('code' => 1, 'message' => 'Can not add this user.'));
            return;
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