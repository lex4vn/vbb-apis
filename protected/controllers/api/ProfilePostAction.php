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
            $page = isset($params['pageNumber']) ? $params['pageNumber'] : 1;
            $limit = isset($params['page_size']) ? $params['page_size'] : 20;

            //$type = $forumid == 69 ? 1 : 2;
            $offset = ($page - 1) * $limit;
            $posts = Post::model()->findPosts(Yii::app()->session['user_id'], $limit, $offset);
            $items = array();

            foreach ($posts as $post) {
                $item = array(
                    'threadid' => $post['id'],
                    'threadtitle' => $post['subject'],
                    'postuserid' => $post['postuserid'],
                    'postusername' => $post['postusername'],
                    'post_url' => API_URL . $post['id'],
                    'preview' => $post['message'],
                    'price' => $post['price'],
                    'phone' => $post['phone'],
                    'bike' => $post['bike'],
                    'address' => $post['location'],
                    'formality' => $post['formality'],
                    'image' => $post['thumb'],
                    'status' => $post['status'],
                    'type' => $post['type'],
                );
                array_push($items, $item);
            }
            echo json_encode(array('code' => 0,
                'message' => 'Get list post successful',
                'totalpages' => 1,
                'listThread' => $items
            ));
            return;
        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 101, 'message' => 'User logged out'));
            return;
        }
    }
}