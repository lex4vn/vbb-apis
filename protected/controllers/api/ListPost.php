<?php

class ListPost extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;

        $forumid = isset($params['forumid']) ? $params['forumid'] : null;
        if ($forumid == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params forumid'));
            return;
        }

        $page = isset($params['page_number']) ? $params['page_number'] : 1;
        $limit = isset($params['page_size']) ? $params['page_size'] : 20;

        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {

            $type = $forumid == 69 ? 1 : 2;
            $offset = ($page - 1) * $limit;
            $posts = Post::model()->getPosts(0,$limit, $offset, $type, 1);

            $items = array();
            foreach ($posts['data'] as $post) {
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
                );
                array_push($items, $item);
            }
            echo json_encode(array('code' => 0,
                'message' => 'get list forum success',
                'totalpages' => $posts['total'],
                'listThread' => $items
            ));
            return;

        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }
}

