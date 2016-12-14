<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 10:43 AM
 */
class NewThreadAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;

        if (!isset($params['type']) || $params['type'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params type'));
            return;
        }
        if (!($params['type'] == 1 || $params['type'] == 2)) {
            echo json_encode(array('code' => 5, 'message' => 'Params type can be 1 or 2. Therein 1 is need to buy. 2 is need to sell.'));
            return;
        }
        if (!isset($params['sessionhash']) || $params['sessionhash'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params sessionhash'));
            return;
        }

        if (!isset($params['message']) || $params['message'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
            return;
        }
        if ((!isset($params['username']) || $params['username'] == '') && $params['type'] == 1){
            echo json_encode(array('code' => 5, 'message' => 'Missing params username'));
            return;
        }
        if (strlen($params['message']) < 10) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message too short, min 10 character'));
            return;
        }

        if (!isset($params['subject']) || $params['subject'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params subject'));
            return;
        }
        if (strlen($params['subject']) > 85) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params subject too long, max 85 character'));
            return;
        }

        $bike = isset($params['bike']) ? $params['bike'] : 'Chưa xác định';
        $price = isset($params['price']) ? $params['price'] : 0;
        $phone = isset($params['phone']) ? $params['phone'] : 'Không có';
        $location = isset($params['location']) ? $params['location'] : 'vui lòng liên hệ';
        $formality = isset($params['formality']) ? $params['formality'] : 'Không xác định';
        $status = isset($params['status']) ? $params['status'] : 'Khác';

        $apiConfig = unserialize(base64_decode(($params['sessionhash'])));
        $api = new Api($apiConfig, new GuzzleProvider(API_URL));
        $info = '[INFOR]';
        $info .= '[BIKE]' . $bike . '[/BIKE]';
        $info .= '[PRICE]' . $price . '[/PRICE]';
        $info .= '[PHONE]' . $phone . '[/PHONE]';
        $info .= '[LOCATION]' . $location . '[/LOCATION]';
        $info .= '[FORMALITY]' . $formality . '[/FORMALITY]';
        $info .= '[STATUS]' . $status . '[/STATUS]';
        $info .= '[/INFOR]';
        $info .= $params['message'];
        if(isset($params['images'])){
            foreach ($params['images'] as $item) {
                $info .= '[IMG]' . $item['image_url'] . '[/IMG]';
            }
        }

        $response = $api->callRequest('newthread_postthread', [
            'username' => isset($params['username'])? $params['username']:'',
            'message' => $info,
            'subject' => $params['subject'],
            'f' => $params['type'] == 1? 69:17,
            'api_v' => '1'
        ], ConnectorInterface::METHOD_POST);
        //var_dump($response);
        if (isset($response['response']->errormessage)) {
            if ($response['response']->errormessage == 'redirect_postthanks') {
                echo json_encode(array('code' => 0, 'message' => 'Post successfull.'));
                return;
            }
            //redirect_duplicatethread
            if ($response['response']->errormessage == 'redirect_duplicatethread') {
                echo json_encode(array('code' => 1, 'message' => 'Post duplicate thread.'));
                return;
            }
            //redirect_postthanks_moderate
            if ($response['response']->errormessage == 'redirect_postthanks_moderate') {
                echo json_encode(array('code' => 0, 'message' => 'Post successfull. Please wait moderate acceptance'));
                return;
            }
        }
        echo json_encode(array('code' => 2, 'message' => 'Forum error'));
    }
}