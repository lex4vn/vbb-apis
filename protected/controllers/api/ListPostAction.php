<?php

class ListPostAction extends CAction
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

            $criteria = new CDbCriteria;
            $criteria->addCondition("type = ".$type);
            //$criteria->order = 'modify_date DESC';
            $offset = ($page - 1) * $limit;
            $criteria->limit = $limit;
            $criteria->offset = $offset;

            $arr_option['criteria'] = $criteria;

            //$posts = new CActiveDataProvider('Post', $arr_option);
            //TODO
            //$count =
            $posts = Yii::app()->db->createCommand()
                ->select('*')
                ->from('post t')
                ->where('t.status=1 and t.type='.$type)
                //->group('m.question_id')
                //->order('t.modify_date desc')
                ->limit($limit)
                ->offset($offset)
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
                'totalpages' => 1,
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

