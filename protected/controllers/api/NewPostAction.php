<?php

class NewPostAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;

        if (!isset($params['sessionhash']) || $params['sessionhash'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params sessionhash'));
            return;
        }

        if (!isset($params['message']) || $params['message'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
            return;
        }

        if (strlen($params['message']) < 10) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message too short, min 10 character'));
            return;
        }

        if (!isset($params['postid']) || $params['postid'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params postid'));
            return;
        }

        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        //var_dump($sessionhash);die();
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            //var_dump($apiConfig);die();
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            $response = $api->callRequest('newreply_postreply', [
                'message' => $params['message'],
                'p' => $params['postid'],
                'api_v' => '1'
            ], ConnectorInterface::METHOD_POST);
            //var_dump($response);
            if (isset($response['response']->errormessage)) {
                if ($response['response']->errormessage == 'redirect_postthanks') {
                    echo json_encode(array('code' => 0, 'message' => 'Post successfull.'));
                    return;
                }
                //redirect_duplicatethread
                if ($response['response']->errormessage == 'redirect_duplicatepost') {
                    echo json_encode(array('code' => 1, 'message' => 'Post duplicate post.'));
                    return;
                }
                //redirect_postthanks_moderate
                if ($response['response']->errormessage == 'redirect_postthanks_moderate') {
                    echo json_encode(array('code' => 0, 'message' => 'Post successfull. Please wait moderate acceptance'));
                    return;
                }
            }
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
        echo json_encode(array('code' => 2, 'message' => 'Forum error'));
    }
}