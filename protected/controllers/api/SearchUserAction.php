<?php

class SearchUserAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        if (isset($params['email'])) {
            if ($params['email'] == '') {
                echo json_encode(array('code' => 5, 'message' => 'Missing params email'));
                return;
            }
        } else if (isset($params['username'])) {
            if ($params['username'] == '') {
                echo json_encode(array('code' => 5, 'message' => 'Missing params username'));
                return;
            }
        } else {
            echo json_encode(array('code' => 5, 'message' => 'Missing params email or username'));
            return;
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
            'uniqueid' => $uniqueId]);

        // Get token key
        $accessToken = $response['apiaccesstoken'];

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);
        if (!empty($params['email'])) {
            $response = $api->callRequest('api_emailsearch', [
                'fragment' => $params['email'],'api_v'=> '1'
            ]);

            if (count($response) >= 3) {
                echo json_encode(array('code' => 0,
                    'message' => 'Successful',
                    'userid' => $response[0],
                    'username' => $response[1],
                    'email' => $response[2],
                ));
                return;
            } else {
                echo json_encode(array('code' => 1, 'message' => 'Email is not registered.'));
                return;
            }
        } else if (!empty($params['username'])) {
            $response = $api->callRequest('api_usersearch', [
                'fragment' => $params['username'],'api_v'=> '1'
            ]);

            if (count($response) >= 3) {
                echo json_encode(array('code' => 0,
                    'message' => 'Successful',
                    'userid' => $response[0],
                    'username' => $response[1],
                    'email' => $response[2],
                ));
                return;
            }else{
                echo json_encode(array('code' => 1, 'message' => 'Username is not registered.'));
                return;
            }

        } else {
            echo json_encode(array('code' => 5, 'message' => 'Missing params email or username'));
            return;
        }



    }
}