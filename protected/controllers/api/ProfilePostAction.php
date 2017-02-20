<?php

class ProfilePostAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        // var_dump($_POST['sessionhash']);die();
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            //$type = $forumid == 69 ? 1 : 2;
            //$offset = ($page - 1) * $limit;
            $posts = Post::model()->getPosts(Yii::app()->session['user_id']);

            $items = array();
            if (count($posts['data'])) {
                foreach ($posts['data'] as $post) {
                    $item = array(
                        'threadid' => $post['id'],
                        'threadtitle' => $post['subject'],
                        'postuserid' => $post['postuserid'],
                        'postusername' => $post['postusername'],
                       // 'post_url' => API_URL . $post['post_url'],
                        'preview' => $post['message'],
                        'price' => $post['price'],
                        'phone' => $post['phone'],
                        'bike' => empty($post['bike']) ? 'Khác': $post['bike'],
                        'address' => $post['location'],
                        'formality' => $post['formality'],
                        'image' => IMAGES_PATH.$post['thumb'],
                        'status' => $post['status'],
                        'type' => $post['type'],
                    );
                    array_push($items, $item);
                }
                echo json_encode(array('code' => 0,
                    'message' => 'Get list post successful',
                    'totalpages' => $posts['total'],
                    'posts' => $items
                ));
                return;
            }
            echo json_encode(array('code' => 1, 'message' => 'No results'));
            return;
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}