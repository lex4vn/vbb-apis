<?php

class PostViewAction extends CAction
{
    public function run()
    {
        // http://pkl.vn/vbb-apis/api/getForum?forumid=69
        header('Content-type: application/json');
        $params = $_GET;

        $page = isset($params['pageNumber']) ? $params['pageNumber'] : 1;
        $limit = isset($params['page_size']) ? $params['page_size'] : 20;
        if($page == 0){
            $page = 1;
        }
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {

            $offset = ($page - 1) * $limit;

            $posts = Yii::app()->db->createCommand()
                ->select('p.*, t.modified_date')
                ->from('post_view t')
                ->where('t.user_id ='.Yii::app()->session['user_id'])
                ->join('post p','t.post_id = p.id')
                //->group('m.question_id')
                //->order('t.modify_date desc')
                ->limit($limit)
                ->offset($offset)
                ->order('modify_date')
                ->queryAll();


            $items = array();
            //var_dump($posts);die;
            foreach ($posts as $post) {
                $item = array(
                    'threadid' => $post['id'],
                    'threadtitle' => $post['subject'],
                    'postuserid' => $post['postuserid'],
                    'postusername' => $post['postusername'],
                    'post_url' => API_URL . $post['id'],
                    'postdate' => $post['create_date'],
                    'preview' => $post['message'],
                    'price' => $post['price'],
                    'phone' => $post['phone'],
                    'bike' => $post['bike'],
                    'address' => $post['location'],
                    'formality' => $post['formality'],
                    'image' => $post['thumb'],
                    'status' => $post['status'],
                    'onlinestatus' => 'online',
                    'modified_date' => $post['modified_date'],
                );
                array_push($items, $item);
            }
            echo json_encode(array('code' => 0,
                'message' => 'get list forum success',
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

