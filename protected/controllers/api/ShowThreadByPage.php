<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 10:14 PM
 */
class ShowThreadByPage extends CAction
{
    public function run(){
        header('Content-type: application/json');
        $params = $_GET;
        $threadId = isset($_GET['threadId']) ? $_GET['threadId'] : null;
        if ($threadId == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params threadId'));
            return;
        }
        $pageNumber = isset($_GET['pageNumber']) ? $_GET['pageNumber'] : null;
        //var_dump($pageNumber);
        if ($pageNumber == null) {
            $pageNumber = 1;
        }

        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            $response = $api->callRequest('showthread', [
                'threadid' => $threadId,
                'pagenumber' => $pageNumber,
                'api_v'=> '1'
            ], ConnectorInterface::METHOD_GET);
            //var_dump($response);die();
        }else{
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}