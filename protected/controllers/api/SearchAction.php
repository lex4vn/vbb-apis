<?php

class SearchAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        if (!isset($params['search']) || $params['search'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params search'));
            return;
        }
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            // search_type = 1: Bai viet, 3: Chuyen muc
            // Forumchoice = 17: Can mua, 69: Can ban
            //api: search_doprefs
            $response = $api->callRequest('search_process', [
                'search_type' => '3',
                'query' => $params['search'],
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            if(isset($response['response']) &&  'search' == $response['response']->errormessage){
                $searchid = $response['show']->searchid;
                $response = $api->callRequest('search_showresults', [
                'searchid'=> $searchid,
                'api_v' => '1'
                ], ConnectorInterface::METHOD_POST);
                if (isset($response['response'])) {
                    $items = array();
                    foreach ($response["response"]->searchbits as $searchbits) {
                        $forumid = (int)str_replace(' ', '', $searchbits->thread->forumid);
                        if(  $forumid != 17 &&  $forumid != 69){
                            continue;
                        }
                        $content = $searchbits->thread->preview;
                        $regex = '#\[BIKE].*\[\/BIKE]#';
                        $hasBike = preg_match($regex, $content, $result);
                        $bike = '';
                        $price = '';
                        $address = '';
                        $formality = '';
                        $image = '';
                        $status = '';
                        $phone = '';
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
                            'threadid' => $searchbits->thread->threadid,
                            'threadtitle' => $searchbits->thread->threadtitle,
                            'postuserid' => $searchbits->thread->postuserid,
                            'postusername' => $searchbits->thread->postusername,
                            'preview' => $content,
                            'price' => $price,
                            'phone' => $phone,
                            'bike' => $bike,
                            'address' => $address,
                            'formality' => $formality,
                            'image' => $image,
                            'status' => $status,
                            'type' => $forumid == 17 ? 2 : 1 ,
                        );
                        array_push($items, $item);
                    }
                    echo json_encode(array('code' => 0,
                        'message' => 'search success',
                        'listThread' => $items
                    ));
                    return;
                 } else {
                    echo json_encode(array('code' => 1, 'message' => 'Forum error'));
                    return;
                }
            }
        }else{
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
    }


}