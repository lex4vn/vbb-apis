<?php


class GetForumsByPage extends CAction
{
    public function run(){
        header('Content-type: application/json');
        $forumid = isset($_GET['forumid']) ? $_GET['forumid'] : null;
        if ($forumid == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params forumid'));
            return;
        }
        $pageNumber = isset($_GET['pageNumber']) ? $_GET['pageNumber'] : null;
        if ($pageNumber == null) {
            $pageNumber = 1;
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
            'api_v'=> '1',
            'uniqueid' => $uniqueId]);

        //var_dump($response);die(1);
        //var_dump($post_id);die(1);
        // Get token key
        $accessToken = $response['apiaccesstoken'];
        // var_dump($accessToken);die(1);
        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);
        $response = $api->callRequest('forumdisplay', ['forumid' => $forumid, 'api_v'=> '1', 'pagenumber' => $pageNumber ], ConnectorInterface::METHOD_GET);
        //Thanh cong
        if (isset($response['response'])) {
            $result = array();
            foreach ( $response["response"]->threadbits as $threadbits){
                $bike = $this-> get_string_by_tag($threadbits->thread->preview, '[BIKE]', '[/BIKE]');
                $bike = (isset($bike) && $bike != '') ? $bike : 'chưa xác định';
                $price = $this-> get_string_by_tag($threadbits->thread->preview, '[PRICE]', '[/PRICE]');
                $price = (isset($price) && $price != '') ? $price : 0;
                $phone = $this-> get_string_by_tag($threadbits->thread->preview, '[PHONE]', '[/PHONE]');
                $phone = (isset($phone) && $phone != '') ? $phone : 'Không có';
                $location = $this-> get_string_by_tag($threadbits->thread->preview, '[LOCATION]', '[/LOCATION]');
                $location = (isset($location) && $location != '') ? $location : 'vui lòng liên hệ';
                $formality = $this-> get_string_by_tag($threadbits->thread->preview, '[FORMALITY]', '[/FORMALITY]');
                $formality = (isset($formality) && $formality != '')? $formality : 'Không xác định';
                $status = $this-> get_string_by_tag($threadbits->thread->preview, '[STATUS]', '[/STATUS]');
                $status = (isset($status) && $status != '') ? $status : 'Khác';
                $item = array(
                    'threadid' => $threadbits->thread->threadid,
                    'threadtitle' => $threadbits->thread->threadtitle,
                    'postuserid' => $threadbits->thread->postuserid,
                    'postusername' => $threadbits->thread->postusername,
                    'bike' =>  $bike,
                    'price' => $price,
                    'phone' => $phone,
                    'location' => $location,
                    'formality' =>$formality,
                    'status' =>$status,
                    'images' => $this->get_string_by_tag($threadbits->thread->preview, '[IMG]', '[/IMG]'),
                    'preview'  => $threadbits->thread->preview
                );
                array_push($result, $item);
            }
            echo json_encode(array('code' => 0,
                'message' => 'get detail forum success',
                'listThread' => $result
            ));
            return;
        } else {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }
    }

    function get_string_by_tag($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}