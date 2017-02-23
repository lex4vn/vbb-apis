<?php

class PostAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        $threadId = isset($params['threadId']) ? $params['threadId'] : null;
        $page = isset($params['pagenumber']) ? $params['pagenumber'] : 1;

        if ($threadId == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params threadId'));
            return;
        }

        $sessionhash = CUtils::getSessionHash($params['sessionhash']);
        if ($sessionhash) {

            $query = "select * from post where id = $threadId";
            $connection = Yii::app()->db;
            $command = $connection->createCommand($query);
            $result = $command->queryAll();

            if (count($result) > 0){
                $post = $result[0];
            }else{
                echo json_encode(array('code' => 1, 'message' => 'No data'));
                return;
            }

            $images = array();

            $comments = array();
            // Commments
            $queryComment = "select *, username,avatar from comment left join api_user on api_user.userid = comment.user_id  where post_id = $threadId";
            $commandComment = $connection->createCommand($queryComment);
            $resultComment = $commandComment->queryAll();
            foreach($resultComment as $comment){
                $comments[] = array(
                    'username' => $comment['username'],
                    'userid' => $comment['userid'],
                    'avatarurl' =>  $comment['avatar'],
                    'onlinestatus' => 1,
                    'usertitle' => $comment['username'],
                    'postid' => $comment['post_id'],
                    'postdate' => $comment['create_date'],
                    'message' => $comment['content'],
                );
            }
            $user = User::model()->findByPk($post['postuserid']);
            $user_title = 'User';
            $avatarurl = '';
            if($user == null){
                // sync user from forum
                //echo json_encode(array('code' => 1, 'message' => 'Forum error'));
                //return;
            }else{
                $user_title = $user['username'];// TODO add user_title
                $avatarurl = $user['avatar'];
            }

            $results = array(
                'username' => $post['postusername'],
                'userid' => $post['postuserid'],
                'avatarurl' =>  $avatarurl,
                'onlinestatus' => '',
                'usertitle' =>$post['postusername'],
                'postid' => $post['id'],
                'post_url' => IMAGES_PATH. $post['thumb'],
                'postdate' => $post['create_date'],
                'title' => $post['subject'],
                'bike' => $post['bike'],
                'price' => $post['price'],
                'phone' => $post['phone'],
                'address' => $post['location'],
                'formality' => $post['formality'],
                'status' =>  $post['status'],
                'images' => $images,
                'message' =>  $post['content'],
                'ismypost' => isset($post['userid'])? $post['userid'] == Yii::app()->session['user_id']: false,
                'totalposts' =>  $post['formality'],
                'pagenumber' =>  $post['formality'],
                'perpage' =>  $post['formality'],
                'comments' => $comments
            );

            echo json_encode(array('code' => 0,
                'message' => 'Get detail post success',
                'detailsthread' => $results
            ));
            return;
        } else {
            echo json_encode(array('code' => 2, 'message' => 'User logged out'));
            return;
        }
    }
}