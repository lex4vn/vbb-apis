<?php

class ProfileThreadsAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        // var_dump($_POST['sessionhash']);die();
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            $response = $api->callRequest('search_finduser', [
                'userid'=> Yii::app()->session['user_id'],
                'contenttype'=> 'vBForum_Thread',
                'starteronly'=> 1,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);

            //var_dump($response);die();
            $searchId = '0';
            if(isset($response['response']) &&  'search' == $response['response']->errormessage){
                $searchId = $response['show']->searchid;
            }
            //var_dump($searchId);die();
            $response = $api->callRequest('search_showresults', [
                'searchid'=> $searchId,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);

           // var_dump($response);die();
            if(isset($response['response'])){
                //var_dump($response['response']);die();
                if(isset($response['response']->errormessage)) {
                    $errormessage = $response['response']->errormessage;
                    if ($errormessage && isset($errormessage[0]) && $errormessage[0] == 'searchnoresults') {
                        echo json_encode(array('code' => 1, 'message' => 'No results'));
                        return;
                    }
                }
                $threads = $response['response']->searchbits;
                //var_dump($threads);die();
                if($threads){
                    $items = array();
                    foreach($threads as $thread){
                        $id = $thread->thread->forumid;
                        if($id != 69 && $id != 17){
                            continue;
                        }
                        $id = $id == 69? 1:2;
                        //var_dump($thread);die();
                        $content = $thread->thread->preview;
                        $regex = '#\[BIKE].*\[\/BIKE]#';
                        $hasBike = preg_match($regex, $content, $result);
                        $bike = '';
                        $phone = '';
                        $price = '';
                        $address = '';
                        $formality = '';
                        $image = '';
                        $status = '';
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
                        $regex = '#\[IMG].*?\[\/IMG]#';
                        $hasImage = preg_match($regex, $content, $result);
                        if ($hasImage) {
                            $content = preg_replace($regex, '', $content);
                            if ($result) {
                                $image = preg_replace('/\[\/?IMG\]/', '', $result[0]);
                            }
                        }

                        $item = array(
                            'threadid' => $thread->thread->threadid,
                            'threadtitle' => $thread->thread->threadtitle,
                            'postuserid' => $thread->thread->postuserid,
                            'postusername' => $thread->thread->postusername,
                            'preview' => $content,
                            'price' => $price,
                            'phone' => $phone,
                            'bike' => $bike,
                            'address' => $address,
                            'formality' => $formality,
                            'image' => $image,
                            'status' => $status,
                            'type' => $id,
                        );
                        array_push($items, $item);
                    }
                    //var_dump($response);die();
                    echo json_encode(array('code' => 0,
                        'message' => 'Get list post successful',
                        'totalpages' => $response["response"]->total,
                        'posts' => $items
                    ));
                    return;
                }else{
                    echo json_encode(array('code' => 1, 'message' => 'No results'));
                    return;
                }

            }else{
                echo json_encode(array('code' => 2, 'message' => 'Forum error'));
                return;
            }
        }
        else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}