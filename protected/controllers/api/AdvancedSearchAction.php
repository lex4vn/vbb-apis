<?php

class AdvancedSearchAction extends CAction
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
        if (!isset($params['price_min']) || $params['price_min'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params price_min'));
            return;
        }
        if (!isset($params['price_max']) || $params['price_max'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params price_max'));
            return;
        }
        if ($params['price_max'] < $params['price_min']) {
            echo json_encode(array('code' => 5, 'message' => 'Param price_max must greater than price_min'));
            return;
        }
        if (!isset($params['bike']) || $params['bike'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params bike'));
            return;
        }
        if (!isset($params['brand']) || $params['brand'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params brand'));
            return;
        }
        //var_dump($params);die();
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            // search_type = 1: Bai viet, 3: Chuyen muc
            // Forumchoice = 17: Can mua, 69: Can ban
            //api: search_doprefs
            $response = $api->callRequest('search_process', [
                'search_type' => '1',
                'query' => $params['brand'],
                'forumchoice' => $params['type'] == 1 ? 69 : 17,
                'titleonly' => '0',
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            //var_dump($response);die();
            $searchid = 0;
            if (isset($response['response']) && 'search' == $response['response']->errormessage) {
                $searchid = $response['show']->searchid;
            } else {
                echo json_encode(array('code' => 1, 'message' => 'No results'));
                return;
            }
            //var_dump($searchid);die();
            $response = $api->callRequest('search_showresults', [
                'searchid' => $searchid,
                //'pagenumber' => $pageNumber,
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            //var_dump($response);die();
            $items = array();
            if (isset($response['response'])) {
                foreach ($response["response"]->searchbits as $searchbits) {
                    if(!isset($searchbits->thread))
                        continue;
                    //var_dump($searchbits->thread);die();
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
                    $regex = '#\[INFOR].*\[\/INFOR]#';
                    $content = preg_replace($regex, '', $content);
                    //var_dump($status.$bike.$price);
                    //die();
                    if (trim($bike) != $params['bike'] || intval($price) > $params['price_max'] || intval($price) < $params['price_min']) {
                        continue;
                    }
                    //var_dump($content);die();
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
                    );
                    //var_dump($item);die();

                    array_push($items, $item);
                }
               // var_dump($items);
               // die();
                if ($items) {
                    echo json_encode(array('code' => 0,
                        'message' => 'Search successful',
                        // 'totalpages' => $response["response"]->pagenav->totalpages,
                        'listThread' => $items
                    ));
                    return;
                } else {
                    echo json_encode(array('code' => 1, 'message' => 'No results'));
                    return;
                }


            } else {
                echo json_encode(array('code' => 1, 'message' => 'Forum error'));
                return;
            }
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }

    }
}