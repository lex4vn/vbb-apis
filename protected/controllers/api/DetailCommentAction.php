<?php


class DetailCommentAction extends CAction
{
    public function run(){
        header('Content-type: application/json');
        $params = $_GET;
        $threadId = isset($_GET['threadId']) ? $_GET['threadId'] : null;
        if ($threadId == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params thread id'));
            return;
        }
        $pageNumber = isset($_GET['pageNumber']) ? $_GET['pageNumber'] : null;
        if ($pageNumber == null) {
            $pageNumber = 1;
        }

        $sessionhash = CUtils::getSessionHash($params['sessionhash']);
        if ($sessionhash) {
            $apiConfig = unserialize(base64_decode($sessionhash));
            $api = new Api($apiConfig, new GuzzleProvider(API_URL));

            $response = $api->callRequest('showthread', [
                'threadid' => $threadId, 'api_v'=> '1', 'pagenumber' => $pageNumber
            ], ConnectorInterface::METHOD_GET);

            if (isset($response['response'])) {
                $result = array();
                foreach ( $response["response"]->postbits as $postbit){
                    $item = array(
                        'username' => $postbit->post->username,
                        'avatarurl' => $postbit->post->avatarurl,
                        'onlinestatus' => $postbit->post->onlinestatus,
                        'usertitle' => $postbit->post->usertitle,
                        'message' => $postbit->post->message,
                        'postdate' => $postbit->post->postdate,
                        'isMycomment' => $postbit->post->userid ==  Yii::app()->session['user_id']
                    );
                    array_push($result, $item);
                }
                echo json_encode(array('code' => 0,
                    'message' => 'get detail comment success',
                    'listcomment' => $result
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
// con thieu phan lay email, SĐT, check user hiện tại có phải là người đăng post hay là ban hay ko
    }

}