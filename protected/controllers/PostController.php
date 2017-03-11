<?php

class PostController extends Controller
{
    public function actionIndex()
    {
        $this->titlePage = 'Chợ PKL';
//        if (!Yii::app()->session['user_id']) {
//            $this->redirect(Yii::app()->homeurl);
//        }
        $this->render('post/index', array());
    }
    //**
    //  Load more in page index
    //**//
    public function actionLoadItem()
    {
        $uid = 1;//$_POST['uid'];
        $type = $_POST['tab_item'] == 2 ? 2:1;
        $page = $_POST['page'];
        $page_size = $_POST['page_size'];
        $offset = $page_size * $page;

        $status = 1;

        if ($type == 2) {
            unset(Yii::app()->session['tab_item']);
            Yii::app()->session['tab_item'] = 2;
        } else if($type == 1) {
            unset(Yii::app()->session['tab_item']);
            Yii::app()->session['tab_item'] = 3;
        }else{
            unset(Yii::app()->session['tab_item']);
            Yii::app()->session['tab_item'] = 4;
        }
        unset(Yii::app()->session['page']);
        Yii::app()->session['page'] = $page;

        $html = '';
        if($type == 2){
            $query = "select * from post where type = $type order by modify_date desc limit $offset, $page_size";
        }else{
            $query = "select p.id,p.subject,p.message,p.postusername,p.create_date,p.status,a.expiry_date,avatar from post p
left join authtoken a on a.user_id = p.postuserid
left join api_user on api_user.userid = p.postuserid
where p.type = $type order by p.create_date desc limit $offset, $page_size";
        }
        $connection = Yii::app()->db;
        $command = $connection->createCommand($query);
        $post = $command->queryAll();

        if (count($post) == 0) {
            $html .= '<div class="web_body">';
            $html .= '<div class="listarticle">';
            $html .= '<div class="row ">';
            $html .= '<div class="col-md-12"><div class="row">';
            $html .= '<div class="col-md-6 col-xs-6 avata">';
            $html .= 'Không có kết quả';
            $html .= '</div>';
            $html .= '</div></div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            echo $html;
            die;
        }

        $this->renderPartial('post/autoload', array(
            'post' => $post,
            'user_id' => $uid,
            'tab_item' => $type,
        ));
    }


    public function actionView($id)
    {
        if (!isset($id)) {
            $this->redirect(Yii::app()->homeurl);
        }
        $postCheck = Post::model()->findByPk($id);
        if ($postCheck == null) {
            $this->redirect(Yii::app()->homeurl);
        }

        $query = "select *, images.post_id, p.id, p.status as status_p from post p join images on images.post_id = p.id where p.id = $id";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($query);
        $post = $command->queryRow();

        $user = User::model()->findByPk($postCheck['postuserid']);
        if ($user['avatar'] != null) {

            if ($user['password'] == 'faccebook' || $user['password'] == 'Google') {
                $url_avatar = $user['avatar'];
            } else {
                $url_avatar = $user['avatar'];
            }
        } else {
            $url_avatar = 'http://pkl.vn/vbb-apis/themes/advance/FileManager/avata.png';
        }

        $post['url_avatar'] = $url_avatar;
        $post['check_like'] = true;
        $post['count_like'] = true;
        $this->titlePage = $post['subject'];

        $images = Images::model()->findAllByAttributes(array('post_id' => $post['id']));

        $this->render('post/detail', array
            (
                'post' => $post,
                'images' => $images,
                'checkLike' => 10,
                'success' => 1,
                'subUser' => 1,
                'id' => $id,
                'reply' => 6
            )
        );
    }


    public function actionNotifyPost()
    {
        $this->titlePage = 'Câu hỏi | PKL';
        if (isset($_GET['classId'])) {
            $class_id = $_GET['classId'];
        } else {
            echo 'Url không tồn tại!';
            die;
        }

        $this->render('post/notifyPost', array(
            'class_id' => $class_id,
        ));
    }

    public function actionShowsubject()
    {
        $class_id = $_POST['classId'];
        if (!isset(Yii::app()->session['user_id'])) {
            $this->redirect(Yii::app()->homeurl);
        }
        $user_id = Yii::app()->session['user_id'];
//        $subject1 = SubscriberCheckTest::model()->findAllByAttributes(array('subscriber_id'=>$user_id, 'class_id'=>$class_id), 'point >=18');
        $subject_id = array();
//        $subscriber_subject = SubscriberCheckTest::model()->findAllByAttributes(array('subscriber_id'=>$user_id, 'class_id'=>$class_id), 'point >=18');
//        for ($i=0; $i < count($subscriber_subject); $i++){
//            $id= $subscriber_subject[$i]['subject_id'];
//            array_push($subject_id, $id);
//        }
//        $array_id = implode(",",$subject_id);
//        $query = "select * from subject_category where id in ($array_id) and class_id= $class_id";
        $query = "select * from subject_category where class_id= $class_id and type = 1";
        $connection = Yii::app()->db2;
        $command = $connection->createCommand($query);
        $subject = $command->queryAll();
        $this->renderPartial('post/_showcontent', array(
            'subject' => $subject,
            'class_id' => $class_id,
        ));
    }

    public function actionSaveUpload()
    {
        header("Content-Type: text/html;charset=utf-8");
        if (isset($_POST) && isset($_FILES)) {
            $post = new Post();
            $post->subject = isset($_POST['title']) ? strip_tags($_POST['title']) : null;
            $post->message = isset($_POST['message']) ? $_POST['message'] : null;
            $post->price = isset($_POST['price']) ? $_POST['price'] : null;
            $post->phone = isset($_POST['phone']) ? $_POST['phone'] : null;
            $post->bike = isset($_POST['bike']) ? $_POST['bike'] : null;
            $post->location = isset($_POST['address']) ? $_POST['address'] : null;
            $post->address = isset($_POST['address']) ? $_POST['address'] : null;
            $post->status = isset($_POST['status']) ? $_POST['status'] : null;;
            $post->postuserid = 1;
            $post->postusername = 'test';
            $post->create_date = date('Y-m-d H:i:s');
            $post->modify_date = date('Y-m-d H:i:s');

            if (!$post->save()) {
                echo "<pre>";
                print_r($post->getErrors());
                die;
            } else {
                $files = $_FILES['file'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['name'][$i] != '') {
                        $filename = $files['name'][$i];
                        $array_name = explode('.', $filename);
                        $extention = $array_name[count($array_name) - 1];
                        $new_name = date('YmdHis') . '-' . rand() . '.' . $extention;

                        if (!file_exists(IMAGES_SOURCE)) {
                            mkdir(IMAGES_SOURCE, 0777, true);
                        }
                        Yii::log('forder upload : ' . IMAGES_SOURCE, 'error');
                        move_uploaded_file($files['tmp_name'][$i], IMAGES_SOURCE . '/' . $new_name);

                        // Check to make sure the move result is true before continuing
                        $target_file = IMAGES_SOURCE . '/' . $new_name;
                        $resized_file = IMAGES_SOURCE . '/' . $new_name;
                        $wmax = 1024;
                        $hmax = 720;
                        $this->ak_img_resize($target_file, $resized_file, $wmax, $hmax, $files['type'][$i]);

                        $postImage = new Images();
                        $postImage->title = "title";
                        $postImage->title_ascii = "title";
                        $postImage->post_id = $post->id;
                        $postImage->type = 1;
                        $postImage->status = 1;
                        $postImage->base_url = $new_name;
                        if($i == 0){
                            $post->thumb = $new_name;
                        }
                        if (!$postImage->save()) {
                            $errors = $postImage->getErrors();
                            $status = '';
                            foreach ($errors as $error) {
                                $status = $error[0] . ' ';
                            }
                            echo $status;
                            die;
                        }
                        $size = getimagesize(IMAGES_SOURCE . '/' . $new_name);
                        $postImage->width = $size[0];
                        $postImage->height = $size[1];
                        $postImage->save();
                    }
                }
                if (!isset($postImage->width) || $postImage->width == 0 || !isset($postImage->id)) {
                    echo 2;
                    die;
                }
                $post->status = 1;
                $post->save();
            }
        }
    }


    public function actionInsertComment()
    {
        $user_id = $_POST['uid'];
        $comment_text = $_POST['comment_text'];
        $post_id = $_POST['post_id'];
        $comment = new Comment();
        $comment->user_id = $user_id;
        $comment->content = $comment_text;
        $comment->status = 1;
        $comment->post_id = $post_id;
        $comment->create_date = date('Y-m-d H:i:s');
        $comment->modify_date = date('Y-m-d H:i:s');
        if (!$comment->save()) {
            echo '<pre>';
            print_r($comment->getErrors());
        }

        //$this->insertNotificationComment($post_id, $user_id);
    }


    private function insertNotificationGlobal($content_note, $notificationId, $subscriber_id)
    {
        $notifiForPostSub = NotificationGlobal::model()->findByAttributes(array('notification_id' => $notificationId, 'subscriber_id' => $subscriber_id));
        if (isset($notifiForPostSub)) {
            $notifiForPostSub->is_read = 0;
            $notifiForPostSub->is_new = 1;
            $notifiForPostSub->content_note = $content_note;
            $notifiForPostSub->time_update = date('Y-m-d H:i:s');
            $notifiForPostSub->save();
        } else {
            $notifiForPostSub = new NotificationGlobal();
            $notifiForPostSub->notification_id = $notificationId;
            $notifiForPostSub->is_read = 0;
            $notifiForPostSub->is_new = 1;
            $notifiForPostSub->subscriber_id = $subscriber_id;
            $notifiForPostSub->content_note = $content_note;
            $notifiForPostSub->time_update = date('Y-m-d H:i:s');
            $notifiForPostSub->save();
        }
    }

    private function insertNotificationComment($post_id, $user_id)
    {
        $post = Post::model()->findByPk($post_id);
        $classOfPost = Class1::model()->findByPk($post->class_id);
        $subject = SubjectCategory::model()->findByPk($post->category_id);

        $querySubAnswer = "SELECT subscriber_id FROM  answer
                    WHERE post_id = " . $post_id . " and subscriber_id != " . $user_id . "
                    GROUP by subscriber_id";

        $querySubComment = "SELECT subscriber_id FROM comment
                    WHERE post_id= " . $post_id . " and subscriber_id != " . $user_id . "
                    GROUP by subscriber_id";

        $subNotificationsAnswer = Yii::app()->db->createCommand($querySubAnswer)->queryAll();
        $subNotificationsComment = Yii::app()->db->createCommand($querySubComment)->queryAll();

        //Find comment
        $notification = Notifications::model()->findByAttributes(array('post_id' => $post_id, 'type' => '3'));
        if (isset($notification)) {
            $notification->isread = 0;
            $notification->isnew = 1;
            $notification->time = date('Y-m-d H:i:s');
            $notification->save();
        } else {
            $notification = new Notifications();
            //Type = 3 comment
            $notification->type = 3;
            $notification->post_id = $post_id;
            $notification->class_id = $post->class_id;
            $notification->subject_id = $post->category_id;
            $notification->from_subscriber_id = $user_id;
            $notification->to_subscriber_id = 0;
            $notification->isread = 0;
            $notification->isnew = 1;
            $notification->time = date('Y-m-d H:i:s');
            $notification->save();
        }

        if ($user_id == $post->subscriber_id) {
            // Send to answer, comment
            foreach ($subNotificationsComment as $sub) {
                $content_note = Yii::app()->session['username'] . ' cũng đã bình luận về câu hỏi ' . $subject->subject_name . ', ' . $classOfPost->class_name . '.';
                $this->insertNotificationGlobal($content_note, $notification->id, $sub['subscriber_id']);
            }

            foreach ($subNotificationsAnswer as $sub) {
                $content_note = Yii::app()->session['username'] . ' đã bình luận về câu hỏi ' . $subject->subject_name . ', ' . $classOfPost->class_name . ' mà bạn đã cung cấp câu trả lời.';
                $this->insertNotificationGlobal($content_note, $notification->id, $sub['subscriber_id']);
            }

        } else {
            // Send to post
            $content_note = Yii::app()->session['username'] . ' đã bình luận về câu hỏi ' . $subject->subject_name . ', ' . $classOfPost->class_name . ' của bạn.';
            $this->insertNotificationGlobal($content_note, $notification->id, $post->subscriber_id);

            foreach ($subNotificationsComment as $sub) {
                $content_note = Yii::app()->session['username'] . ' cũng đã bình luận về câu hỏi ' . $subject->subject_name . ', ' . $classOfPost->class_name . '.';
                $this->insertNotificationGlobal($content_note, $notification->id, $sub['subscriber_id']);
            }

            foreach ($subNotificationsAnswer as $sub) {
                $content_note = Yii::app()->session['username'] . ' đã bình luận về câu hỏi ' . $subject->subject_name . ', ' . $classOfPost->class_name . ' mà bạn đã cung cấp câu trả lời.';
                $this->insertNotificationGlobal($content_note, $notification->id, $sub['subscriber_id']);
            }
        }
    }

    public function actionLoadComment()
    {
        $post_id = $_POST['post_id'];
        $query = "select cm.*,
cm.user_id, sub.userid, cm.id, cm.status, sub.avatar, sub.username, sub.password
from comment cm join api_user sub on cm.user_id = sub.userid
where cm.post_id=$post_id and cm.status = 1 order by cm.id desc";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($query);
        $comments = $command->queryAll();
        //var_dump($comments);die();
        $url = Yii::app()->theme->baseUrl;
        $html = '';
        $html .= '<ul>';
        $CUtils = new CUtils();
        foreach ($comments as $comment) {
            if ($comment['modify_date'] != null) {
                $time = $CUtils->formatTime($comment['modify_date']);
            } else {
                $time = $CUtils->formatTime($comment['create_date']);
            }
            if ($comment['avatar'] == '' || $comment['avatar'] == 'noavatar.png') {
                $avata = Yii::app()->theme->baseUrl . '/FileManager/avata.png';
            } else {
                if ($comment['password'] == 'faccebook' || $comment['password'] == 'Google') {
                    $avata = $comment['avatar'];
                } else {
                    $avata = $comment['avatar'];
                }
            }
            $html .= '<li>';
            $html .= '<img src="' . $avata . '"/>';
            $html .= '<div class="comment-answer-list-title">';
            $html .= '<a href="#">' . $comment['username'] . '</a>';
            $html .= '<p>' . $comment['content'] . '</p>';
            $html .= '<p style="color: #b0b0b0;font-family: font-dep1;">' . $time . '</p>';
            $html .= '</a">';
            $html .= '</div">';
            $html .= '</li>';
        }
        $html .= '</ul>';
        echo $html;
    }



    public function actionCheckSocket()
    {
        $test = $_POST['json'];
        if (isset($test['lstid'][3])) {
            echo $test['lstid'][3];
            die;
        } else {
            echo 2;
            die;
        }
    }

    public function actionPostSubject($id)
    {
        $this->titlePage = 'Câu hỏi  | PKL';
//            if(!Yii::app()->session['user_id']){
//                $this->redirect(Yii::app()->homeurl.'/account');
//            }
        if (!isset(Yii::app()->session['user_id'])) {
            $this->redirect(Yii::app()->homeurl);
        }
        $user_id = Yii::app()->session['user_id'];
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $page_size = 10;
        if (isset($_GET['page'])) {
            $offset = $page_size * $page;
        } else {
            $offset = 0;
        }
        $checkExam = SubjectCategory::model()->findByPk($id);
        $subscriber_subject = SubscriberCheckTest::model()->findAllByAttributes(array('subscriber_id' => $user_id, 'class_id' => $checkExam->class_id, 'subject_id' => $id), 'point >=18');
        if (count($subscriber_subject) == 0) {
            $this->redirect(Yii::app()->homeurl . 'postTest/startTest?subjectId=' . $id . '&classId=' . $checkExam->class_id);
            return;
        }
        $query = "select * from post where status = 1 and category_id = $id order by modify_date desc limit $offset, $page_size";
//            $query = "select *, qm.post_id, q.id from post q join post_image qm on qm.post_id = q.id order by q.modify_date desc";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($query);
        $post = $command->queryAll();
//        echo "<pre>";
//        print_r($post);
//        die;
        for ($i = 0; $i < count($post); $i++) {
            $post_id = $post[$i]['id'];
            $class_id = $post[$i]['class_id'];
            $category_id = $post[$i]['category_id'];
            $subcriber_id = $post[$i]['subscriber_id'];
            if (!Yii::app()->session['user_id']) {
                $checkLike = 0;
                $post[$i]['check_like'] = 0;
            } else {
                $checkLike = Like::model()->findByAttributes(
                    array(
                        'post_id' => $post_id,
                        'subscriber_id' => Yii::app()->session['user_id']
                    )
                );
                if (count($checkLike) > 0) {
                    $post[$i]['check_like'] = 1;
                } else {
                    $post[$i]['check_like'] = 0;
                }
            }
            $postImage = PostImage::model()->findByAttributes(array('post_id' => $post_id, 'status' => 1));
            $post[$i]['base_url'] = $postImage['base_url'];
            $class_name = Class1::model()->findByAttributes(array('id' => $class_id, 'status' => 1));
            $subjectCategory = SubjectCategory::model()->findByAttributes(array('id' => $category_id, 'status' => 1));
            $post[$i]['class_name'] = $class_name['class_name'];
            $post[$i]['subject_name'] = $subjectCategory['subject_name'];
            $Subcriber = Subscriber::model()->findByPk($subcriber_id);
            $level = Level::model()->findByPk($post[$i]['level_id']);
            if ($Subcriber['url_avatar'] != null) {
                if ($Subcriber['password'] == 'faccebook' || $Subcriber['password'] == 'Google') {
                    $url_avatar = $Subcriber['url_avatar'];
                } else {
                    $url_avatar = $Subcriber['url_avatar'];
                }
//                $url_avatar = IPSERVER . $Subcriber['url_avatar'];
            } else {
                $url_avatar = '';
            }
            $post[$i]['subscriber_name'] = $Subcriber['lastname'] . ' ' . $Subcriber['firstname'];
            $post[$i]['sub_id'] = $Subcriber['id'];
            $post[$i]['url_avatar'] = $url_avatar;
            $post[$i]['level'] = $level->name;
        }
//        echo "<pre>";
//        print_r($post);
//        die;
        $this->render('post/postSubject', array(
            'posts' => $post,
            'subjectId' => $id,
        ));
    }

    function ak_img_resize($target, $newcopy, $w, $h, $ext)
    {
        Yii::log('--------------1--------------');
        list($w_orig, $h_orig) = getimagesize($target);
        Yii::log('--------------2--------------');
        $scale_ratio = $w_orig / $h_orig;
        if (($w / $h) > $scale_ratio) {
            $w = $h * $scale_ratio;
        } else {
            $h = $w / $scale_ratio;
        }
        $img = "";
        Yii::log('--------------3--------------');
        $ext = strtolower($ext);
        Yii::log('--------------4--------------');
        Yii::log('--------------5--------------' . $ext);
        Yii::log('--------------6--------------' . $target);
        if ($ext == "image/gif") {
            $img = imagecreatefromgif($target);
        } else if ($ext == "image/png") {
            Yii::log('--------------7--------------' . $target);
            $img = imageCreateFromPng($target);
        } else {
            Yii::log('--------------7--------------' . $target);
            $img = imageCreateFromJpeg($target);
        }
        Yii::log('--------------8--------------');
        $tci = imagecreatetruecolor($w, $h);
        Yii::log('--------------9--------------');
        // imagecopyresampled(dst_img, src_img, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)
        imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
        Yii::log('--------------10--------------');
        imagejpeg($tci, $newcopy, 80);
    }

    public function actionList()
    {
        $this->titlePage = 'Danh sách  | PKL';
        $title = isset($_GET['title']) ? $_GET['title'] : '';
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $page_size = 10;
        if (isset($_GET['page'])) {
            $offset = $page_size * $page;
        } else {
            $offset = 0;
        }
        if (!$this->detect->isTablet() || !$this->detect->isMobile()) {
            $criteria = new CDbCriteria;
            $criteria->addCondition("status = 3");
            $criteria->order = 'modify_date DESC';
            $criteria->limit = $page_size;
            $criteria->offset = $offset;

            $arr_option['criteria'] = $criteria;
            if ($page_size) {
                $arr_option['pagination'] = array(
                    'pageSize' => $page_size,
                    'pageVar' => 'page',
                    'route' => 'post/list'
                );
            } else {
                $arr_option['pagination'] = FALSE;
            }
            $post = new CActiveDataProvider('Post', $arr_option);
            $this->render('post/list', array(
                'posts' => $post
            ));
        } else {
            $this->redirect(Yii::app()->user->returnUrl);
        }
    }

    public function actionSell()
    {
        $this->titlePage = 'Đăng tin bán xe | PKL';
        /*if (!Yii::app()->session['user_id']) {
            $this->redirect(Yii::app()->homeurl);
        }*/

        $biketypes = Biketype::model()->findAll();
        //$subject = SubjectCategory::model()->findAllByAttributes(array('status' => 1, 'type' => 1));
        $time = date('Y-m-d H:i:s');
        $criteria = new CDbCriteria;
        $criteria->condition = "is_active = 1 and expiry_date > '$time'";
        $criteria->compare('subscriber_id', Yii::app()->session['user_id']);
        //$usingService = ServiceSubscriberMapping::model()->findAll($criteria);
       // $level = Level::model()->findAll();
        $this->render('post/sell', array(
           'biketypes' => $biketypes,
           'year' => 2000,
          //  'subject' => $subject,
          //  'level' => $level
        ));
    }

    public function actionBuy()
    {
        $this->titlePage = 'Đăng tin mua xe | PKL';
        /*if (!Yii::app()->session['user_id']) {
            $this->redirect(Yii::app()->homeurl);
        }*/

        $biketypes = Biketype::model()->findAll();
        //$subject = SubjectCategory::model()->findAllByAttributes(array('status' => 1, 'type' => 1));
        $time = date('Y-m-d H:i:s');
        $criteria = new CDbCriteria;
        $criteria->condition = "is_active = 1 and expiry_date > '$time'";
        $criteria->compare('subscriber_id', Yii::app()->session['user_id']);
        //$usingService = ServiceSubscriberMapping::model()->findAll($criteria);
        // $level = Level::model()->findAll();
        $this->render('post/buy');
    }
    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }
}
