<?php

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

        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            //var_dump($apiConfig);die();
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));
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
                echo json_encode(array('code' => 1, 'message' => $response['response']->errormessage));
                return;
            }
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 0, 'message' => 'User logged out'));
            return;
        }
    }
}