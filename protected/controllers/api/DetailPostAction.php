<?php

class DetailPostAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        $threadId = isset($params['threadId']) ? $params['threadId'] : null;
        $page = isset($params['pagenumber']) ? $params['pagenumber'] : 1;

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
                'page' => $page,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_GET);
            //var_dump($response);die();
            $is_first_page = false;
            if (isset($response['response'])) {
                //var_dump($response['response']->postbits);die();
                $totalposts = $response['response']-> totalposts;
                $pagenumber = $response['response']-> pagenumber;
                $perpage = $response['response']-> perpage;
                $postbits = $response['response']->postbits;
               //var_dump($response);die();
                $comments = array();
                foreach($postbits as $postbit){
                    if(!isset($postbit->post)){
                        continue;
                    }
                    //var_dump($postbit);die();
                    $post_item = $postbit->post;
                    //var_dump($post);die();
                    if($page <= 1){
                        $is_first_page = true;
                        //var_dump($post);die();
                        $post = $postbit->post;

                        $bike = '';
                        $price = '';
                        $phone = '';
                        $address = '';
                        $formality = '';
                        $images = array();
                        $status = '';

                        $content = $post_item->message_bbcode;
                        //var_dump($post);die();

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
                        // Remove break line
                        $content_image =preg_replace("/[\n\r]/","",$content);
                        $hasImage = preg_match($regex, $content_image, $result);
                        //var_dump($content);die();
                        if ($hasImage) {
                            //var_dump($result);die();
                            $content = preg_replace($regex, '', $content);
                            if ($result) {
                                //var_dump($result);die();
                                $images_array = explode('http',preg_replace('/\[\/?IMG\]/', '', $result[0]));
                            }
                        }
                        foreach($images_array as $img){
                            if(empty($img)){
                                continue;
                            }
                            $images[] = 'http'.$img;
                        }

                        $regex = '#\[INFOR].*\[\/INFOR]#';
                        $content = preg_replace($regex, '', $content);
                        //var_dump($content);die();
                    }else{
                        // Commments
                        $comment = array(
                            'username' => $post_item->username,
                            'userid' => $post_item->userid,
                            'avatarurl' =>  str_replace("amp;","",API_URL.$post_item->avatarurl),
                            'onlinestatus' => isset($post_item->onlinestatus)?$post_item->onlinestatus->onlinestatus:'',
                            'usertitle' => $post_item->usertitle,
                            'postid' => $post_item->postid,
                            'postdate' => $post_item->postdate,
                            'message' => $post_item->message,
                        );
                        $comments[] = $comment;
                    }
                }
                if($is_first_page){
                    $results = array(
                        'username' => $post->username,
                        'userid' => $post->userid,
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
                        'totalposts' => $totalposts,
                        'pagenumber' => $pagenumber,
                        'perpage' => $perpage,
                        'comments' => $comments
                    );
                }else{
                    $results = array(
                        'totalposts' => $totalposts,
                        'pagenumber' => $pagenumber,
                        'perpage' => $perpage,
                        'comments' => $comments
                    );
                }

                echo json_encode(array('code' => 0,
                    'message' => 'Get detail post success',
                    'detailsthread' => $results
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