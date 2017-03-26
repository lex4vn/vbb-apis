<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/15/2016
 * Time: 10:43 AM
 */
class AddPostAction extends CAction
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
        if (!isset($params['sessionhash']) || $params['sessionhash'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params sessionhash'));
            return;
        }

        if (!isset($params['message']) || $params['message'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message'));
            return;
        }
        if ((!isset($params['username']) || $params['username'] == '') && $params['type'] == 1) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params username'));
            return;
        }
        if (strlen($params['message']) < 10) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params message too short, min 10 character'));
            return;
        }

        if (!isset($params['subject']) || $params['subject'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params subject'));
            return;
        }
        if (strlen($params['subject']) > 85) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params subject too long, max 85 character'));
            return;
        }

        $bike = isset($params['bike']) ? $params['bike'] : 'Chưa chọn';
        $type = isset($params['type']) ? $params['type'] : 1;
        $price = isset($params['price']) ? $params['price'] : 0;
        $phone = isset($params['phone']) ? $params['phone'] : 'Không có';
        $location = isset($params['location']) ? $params['location'] : 'vui lòng liên hệ';
        $formality = isset($params['formality']) ? $params['formality'] : '0';
        $status = isset($params['status']) ? $params['status'] : '0';
        $message = isset($params['message']) ? $params['message'] : '';
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {

            $post = new Post();
            $post->subject = $params['subject'];
            //$post->title_ascii = mb_strtolower(CVietnameseTools::makeCodeName2($params['subject']));
            $post->bike = $bike;
            $post->price = $price;
            $post->phone = $phone;
            $post->location = $location;
            $post->formality = $formality;
            $post->message = $message;
            $post->status = $status == 'Mới' || $status == '0'? 0: 1;
            $post->create_date = date('Y-m-d H:i:s');
            $post->modify_date = date('Y-m-d H:i:s');
            $post->thumb = 'noimage.png';
            $post->type = $type;
            $post->postuserid = Yii::app()->session['user_id'];
            $post->postusername = Yii::app()->session['username'];
            $post->save();

            Yii::log($post->type);
            if ($post->type == 2) {
                $thumb = '';
                if (isset($params['images'])) {
                    foreach ($params['images'] as $index => $item) {
                        if ($index == 0) {
                            $thumb = IMAGES_PATH . $item['image_name'];
                        }
                        $postImage = PostImages::model()->findByPk($item['id']);
                        $postImage->post_id = $post->id;
                        $postImage->save();
                    }
                }
                $post->thumb = $thumb;

                if (empty($thumb)) {
                    echo json_encode(array('code' => 1, 'message' => 'Bạn cần thêm ít nhất 1 tấm ảnh!'));
                    return;
                }
            }

            if (!$post->save()) {
                echo json_encode(array('code' => 1, 'message' => 'Đã có lỗi khi đăng bài'));
                return;
            }


            echo json_encode(array('code' => 0, 'message' => 'Post successfull.'));
            //$this->send_notification("Your friend: .... just posted a thread: " +  $params['subject'],$sessionhash);
            return;
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
    }

    private function send_notification($sessionhash, $msg)
    {
        $apiConfig = unserialize(base64_decode($sessionhash));
        $api = new Api($apiConfig, new GuzzleProvider(API_URL));
        $response = $api->callRequest('profile_buddylist', [
            'api_v' => '1'
        ], ConnectorInterface::METHOD_POST);
        if (isset($response['response'])) {
            $tokens = array();
            foreach ($response['response']->HTML->buddylist as $buddy) {
                $user = $buddy->user;

                $userid = $user->userid;

                $user = User::model()->findByAttributes(array('userid' => $userid));
                if (isset($user) && isset($user->device_token)) {
                    $tokens[] = $user->device_token;
                }

            }
            CUtils::send_notification($msg, $tokens);
        }

    }
}