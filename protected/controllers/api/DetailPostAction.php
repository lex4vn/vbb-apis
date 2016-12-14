<?php

class DetailPostAction extends CAction
{
    public function run()
    {

        //test
        header('Content-type: application/json');
        $params = $_GET;
        $threadId = isset($_GET['threadId']) ? $_GET['threadId'] : null;
        if ($threadId == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params thread id'));
            return;
        }
        $userId = isset($_GET['userid']) ? $_GET['userid'] : null;
        if ($userId == null) {
            echo json_encode(array('code' => 5, 'message' => 'you need login'));
            return;
        }
        //Parameters
        $uniqueId = uniqid();
        $content = '';

        $apiConfig = new ApiConfig(API_KEY, $uniqueId, CLIENT_NAME, CLIENT_VERSION, PLATFORM_NAME, PLATFORM_VERSION);
        $apiConnector = new GuzzleProvider(API_URL);
        $api = new Api($apiConfig, $apiConnector);

        $response = $api->callRequest('api_init', [
            'clientname' => CLIENT_NAME,
            'clientversion' => CLIENT_VERSION,
            'platformname' => PLATFORM_NAME,
            'platformversion' => PLATFORM_VERSION,
            'uniqueid' => $uniqueId]);

        // var_dump($response);die(1);

        // Get token key
        $accessToken = $response['apiaccesstoken'];

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);

        $response = $api->callRequest('showthread', [
            'threadid' => $threadId, 'api_v'=> '1'
        ], ConnectorInterface::METHOD_GET);
        if (isset($response['response'])) {
            $bike = $this-> get_string_by_tag( $response["response"]->postbits->post->message_bbcode, '[BIKE]', '[/BIKE]');
            $bike = (isset($bike) && $bike != '') ? $bike : 'chưa xác định';
            $price = $this-> get_string_by_tag( $response["response"]->postbits->post->message_bbcode, '[PRICE]', '[/PRICE]');
            $price = (isset($price) && $price != '') ? $price : 0;
            $phone = $this-> get_string_by_tag( $response["response"]->postbits->post->message_bbcode, '[PHONE]', '[/PHONE]');
            $phone = (isset($phone) && $phone != '') ? $phone : 'Không có';
            $location = $this-> get_string_by_tag( $response["response"]->postbits->post->message_bbcode, '[LOCATION]', '[/LOCATION]');
            $location = (isset($location) && $location != '') ? $location : 'vui lòng liên hệ';
            $formality = $this-> get_string_by_tag( $response["response"]->postbits->post->message_bbcode, '[FORMALITY]', '[/FORMALITY]');
            $formality = (isset($formality) && $formality != '')? $formality : 'Không xác định';
            $status = $this-> get_string_by_tag( $response["response"]->postbits->post->message_bbcode, '[STATUS]', '[/STATUS]');
            $status = (isset($status) && $status != '') ? $status : 'Khác';
            $result = array(
                'username' => $response["response"]->postbits->post->username,
                'avatarurl' => $response["response"]->postbits->post->avatarurl,
                'onlinestatus' => $response["response"]->postbits->post->onlinestatus,
                'usertitle' => $response["response"]->postbits->post->usertitle,
                'postid' => $response["response"]->postbits->post->postid,
                'postdate' => $response["response"]->postbits->post->postdate,
                'title' => $response["response"]->postbits->post->title,
                'bike' =>  $bike,
                'price' => $price,
                'phone' => $phone,
                'location' => $location,
                'formality' =>$formality,
                'status' =>$status,
                'image' => $this->get_string_by_tag( $response["response"]->postbits->post->message_bbcode, '[IMG]', '[/IMG]'),
                'message' => $response["response"]->postbits->post->message,
                'ismypost' => $response["response"]->postbits->post->userid == $userId,
            );
            echo json_encode(array('code' => 0,
                'message' => 'get detail post success',
                'detailsthread' => $result
            ));
            return;
        } else {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }
    }
    function  get_string_by_tag($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}