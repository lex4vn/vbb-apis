<?php

class GetForum2 extends CAction
{
    public function run()
    {
        Yii::log('GetForum2');
        header('Content-type: application/json');
        $forumid = isset($_GET['forumid']) ? $_GET['forumid'] : null;
        if ($forumid == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params forumid'));
            return;
        }
        $params = $_GET;
        $sessionhash = CUtils::getSessionHash($params['sessionhash']);
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
            //var_dump($post_id);die(1);

            $response = $api->callRequest('forumdisplay', ['forumid' => $forumid, 'api_v'=> '1' ], ConnectorInterface::METHOD_GET);

            //Thanh cong
            if (isset($response['response'])) {
                $result = array();
              //  var_dump($response['response']);
            //    die(1);
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
                        'post_url' => API_URL.'showthread.php?t='.$threadbits->thread->threadid,
                        'bike' =>  $bike,
                        'price' => $price,
                        'phone' => $phone,
                        'location' => $location,
                        'formality' =>$formality,
                        'status' =>$status,
                        'images' => $this->get_string_by_tag($threadbits->thread->preview, '[IMG]', '[/IMG]'),
                        'preview' => $threadbits->thread->preview
                    );
                    array_push($result, $item);
                }
                echo json_encode(array('code' => 0,
                    'message' => 'get detail forum success',
                    'totalpages' => $response["response"]->pagenav->totalpages,
                    'listThread' => $result
                ));
                return;
            } else {
                echo json_encode(array('code' => 1, 'message' => 'Forum error'));
                return;
            }
        } else {
            echo json_encode(array('code' => 2, 'message' => 'User logged out'));
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
    function  get_list_string_by_tag($string, $start, $end){
        $string = ' ' . $string;
        $StringLength =  strlen($string);
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        while($ini <= $StringLength){
            $this->get_value($string, $start,$end,$ini);
        }

        $len = strpos($string, $end, $ini) - $ini;
        $a = substr($string, $ini, $len);
    }
    function get_value($string,$start, $end, $ini){
        $len = strpos($string, $end, $ini) - $ini;
        $a = substr($string, $ini, $len);
        $ini = $len + strlen($start);
        return $a;
    }

}

