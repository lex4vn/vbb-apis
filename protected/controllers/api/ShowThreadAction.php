<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 9:21 AM
 */
class ShowThreadAction extends CAction
{
    public function run()
    {
        //test
        header('Content-type: application/json');
        $params = $_GET;

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
            'threadid' => 29080, 'api_v'=> '1'
        ], ConnectorInterface::METHOD_GET);
        var_dump($response);
        die;

    }
}