<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 10:43 AM
 */
class AddFriendAction extends CAction
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

            $response = $api->callRequest('profile_doaddlist', [
                'userid' => isset($params['userid']) ? $params['userid'] : '',
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);

            // invalidid
            //var_dump($response);die();
            if (isset($response['response']->errormessage)) {
                $mess = $response['response']->errormessage[0];
                if ($mess == 'redirect_addlist_contact') {
                    echo json_encode(array('code' => 0, 'message' => 'Add '.$response['response']->errormessage[1].' to friend list successfull.'));
					//send notification
					$fromuser = Yii::app()->session['username'];
					$message = $fromuser." just sent you a friend request.";
					$recipient = User::model()->findByAttributes(array('userid'=>$params['userid']));
					$tokens = array();
					if(isset($recipient) && isset($recipient->device_token)) {
						$tokens[] = $recipient->device_token;
					}
					CUtils::send_notification($message, $tokens);
                    return;
                }

                if ($mess == 'invalidid') {
                    echo json_encode(array('code' => 1, 'message' => 'Invalid id'));
                    return;
                }

                if ($mess == 'noid') {
                    echo json_encode(array('code' => 1, 'message' => 'Please add user id'));
                    return;
                }
                echo json_encode(array('code' => 1, 'message' => 'Can not add this user.'));
                return;
            }
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
        echo json_encode(array('code' => 2, 'message' => 'Forum error'));
    }
}