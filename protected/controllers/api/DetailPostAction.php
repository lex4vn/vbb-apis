<?php

class DetailPostAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        $threadId = isset($params['threadId']) ? $params['threadId'] : null;

        if ($threadId == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params threadId'));
            return;
        }
        $sessionhash = CUtils::getSessionHash($params['sessionhash']);
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
            $response = $api->callRequest('showthread', [
                'threadid' => $threadId,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_GET);
           // var_dump($response);die();
            if (isset($response['response'])) {
                //var_dump($response['response']->postbits);die();

                $postbits = $response['response']->postbits;
                //var_dump($postbits);die();
                $post = $postbits->post;

                //var_dump($post);die();
                $content = $post->message_bbcode;
                //var_dump($message);die();
                $bike = '';
                $price = '';
                $address = '';
                $formality = '';
                $images = array();
                $status = '';

                $regex = '#\[BIKE].*\[\/BIKE]#';
                $hasBike = preg_match($regex, $content, $result);
                if ($hasBike) {
                    $content = preg_replace($regex, '', $content);
                    if ($result) {
                        $bike = preg_replace('/\[\/?BIKE\]/', '', $result[0]);
                    }
                }

                $regex = '#\[PRICE].*\[\/PRICE]#';
                $hasPrice = preg_match($regex, $content, $result);
                if ($hasPrice) {
                    $content = preg_replace($regex, '', $content);
                    if ($result) {
                        $price = preg_replace('/\[\/?PRICE\]/', '', $result[0]);
                    }
                }

                $regex = '#\[LOCATION].*\[\/LOCATION]#';
                $hasLocation = preg_match($regex, $content, $result);
                if ($hasLocation) {
                    $content = preg_replace($regex, '', $content);
                    if ($result) {
                        $address = preg_replace('/\[\/?LOCATION\]/', '', $result[0]);
                    }
                }
                // Hinh thuc
                $regex = '#\[FORMALITY].*\[\/FORMALITY]#';
                $hasFormality = preg_match($regex, $content, $result);
                if ($hasFormality) {
                    $content = preg_replace($regex, '', $content);
                    if ($result) {
                        $formality = preg_replace('/\[\/?FORMALITY\]/', '', $result[0]);
                    }
                }
                // Phone
                $regex = '#\[PHONE].*\[\/PHONE]#';
                $hasPhone = preg_match($regex, $content, $result);
                if ($hasPhone) {
                    $content = preg_replace($regex, '', $content);
                    if ($result) {
                        $phone = preg_replace('/\[\/?PHONE\]/', '', $result[0]);
                    }
                }
                // Trang thai
                $regex = '#\[STATUS].*\[\/STATUS]#';
                $hasStatus = preg_match($regex, $content, $result);
                if ($hasStatus) {
                    $content = preg_replace($regex, '', $content);
                    if ($result) {
                        $status = preg_replace('/\[\/?STATUS\]/', '', $result[0]);
                    }
                }

                // image
                $regex = '#\[IMG].*\[\/IMG]#';
                $hasImage = preg_match($regex, $content, $result);
                if ($hasImage) {
                    $content = preg_replace($regex, '', $content);
                    if ($result) {
                        $images_array = explode('http://',preg_replace('/\[\/?IMG\]/', '', $result[0]));
                    }
                }
                foreach($images_array as $img){
                    if(empty($img)){
                        continue;
                    }
                    $images[] = 'http://'.$img;
                }

                $regex = '#\[INFOR].*\[\/INFOR]#';
                $content = preg_replace($regex, '', $content);
                $result = array(
                    'username' => $post->username,
                    'avatarurl' =>  str_replace("amp;","",API_URL.$post->avatarurl),
                    'onlinestatus' => isset($post->onlinestatus)?$post->onlinestatus->onlinestatus:'',
                    'usertitle' => $post->usertitle,
                    'postid' => $post->postid,
                    'postdate' => $post->postdate,
                    'title' => $post->title,
                    'bike' => $bike,
                    'price' => $price,
                    'phone' => $phone,
                    'address' => $address,
                    'formality' => $formality,
                    'status' => $status,
                    'images' => $images,
                    'message' => $content,
                    'ismypost' => isset($post->userid)? $post->userid == Yii::app()->session['user_id']: false,
                );
                echo json_encode(array('code' => 0,
                    'message' => 'Get detail post success',
                    'detailsthread' => $result
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

//    function get_string_by_tag($string, $start, $end)
//    {
//        $string = ' ' . $string;
//        $ini = strpos($string, $start);
//        if ($ini == 0) return '';
//        $ini += strlen($start);
//        $len = strpos($string, $end, $ini) - $ini;
//        return substr($string, $ini, $len);
//    }
}