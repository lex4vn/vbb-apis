<?php

class FriendListAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;

        $page = isset($params['page_number']) ? $params['page_number'] : 1;
        $limit = isset($params['page_size']) ? $params['page_size'] : 10;
        $offset = ($page - 1) * $limit;
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));

        if (Yii::app()->session['user_id']) {

            $result = array();
            $friends = Relationship::model()->friendList(Yii::app()->session['user_id'], $limit, $offset);

            if (count($friends) == 0) {
                echo json_encode(array('code' => 3, 'message' => 'List friend is empty.'));
                return;
            }

            foreach ($friends as $buddy) {
                $result[] = array(
                    'userid' => $buddy['userid'],
                    'username' => $buddy['username'],
                    'avatarurl' => $buddy['avatar'],
                    'fullname' => isset($buddy['fullname']) ? $buddy['fullname'] : $buddy['username'],
                    'phonenumber' => $buddy['phonenumber'],
                    'onlinestatus' => $buddy['expiry_date'] >= (time() + 900)?'Online' : 'Offline',
                    'displayemail' => empty($buddy['email']) ? '' : $buddy['email'],
                );
                //Yii::log($buddy['expiry_date'].(time() + 900));
                //array_push($result, $item);

            }
            echo json_encode(array('code' => 0,
                'message' => 'Get list friend success',
                'listfriend' => $result
            ));
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out', 'abc' => $sessionhash));
            return;
        }
    }
}