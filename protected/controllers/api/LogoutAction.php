<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 12/14/2016
 * Time: 8:05 PM
 */
class LogoutAction extends CAction
{
    public function run()
    {

        header('Content-type: application/json');
        if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        if (!isset($params['sessionhash']) || $params['sessionhash'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params sessionhash'));
            return;
        }
        $uniqueId = uniqid();

        $apiConfig = new ApiConfig(API_KEY, $uniqueId, CLIENT_NAME, CLIENT_VERSION, PLATFORM_NAME, PLATFORM_VERSION);
        $apiConnector = new GuzzleProvider(API_URL);
        $api = new Api($apiConfig, $apiConnector);

        $response = $api->callRequest('api_init', [
            'clientname' => CLIENT_NAME,
            'clientversion' => CLIENT_VERSION,
            'platformname' => PLATFORM_NAME,
            'platformversion' => PLATFORM_VERSION,
            'uniqueid' => $uniqueId]);
        // Get token key
        $accessToken = $response['apiaccesstoken'];

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);
        $response = $api->callRequest('login_logout', [
            'sessionhash' => $params['sessionhash'] , 'api_v' => '1'
        ], ConnectorInterface::METHOD_POST);
        if (isset($response['response'])) {
            if (isset($response['response']->errormessage)) {
                $result = $response['response']->errormessage[0];
                if ('cookieclear' == $result ) {
                    CUtils::deleteSessionHash(($params['sessionhash']));
                    echo json_encode(array('code' => 0,
                        'message' => 'logout successful.'
                    ));
                    return;
                }
            }
        }else{
            echo json_encode(array('code' => 1, 'result' =>  false,'message' => 'Forum error'));
            return;
        }

    }
}