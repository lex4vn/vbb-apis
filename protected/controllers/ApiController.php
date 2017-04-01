<?php

class ApiController extends CController
{
    const  API_KEY = 'voice_20131227';

    public function filters()
    {
        return array();
    }

    public function beforeAction($action)
    {
        Yii::log('=============================================================='.$action->id);
        //Yii::log('======================GET============'.var_dump($_GET));
        //Yii::log('======================POST============'.var_dump($_POST));
        // TODO check exist in array
        if (
            $action->id != "listReport"
            && $action->id != "loginGoogleAndroid"
            && $action->id != "loginGoogle"
            && $action->id != "test"
            && $action->id != "test2"
            && $action->id != "test3"
            && $action->id != "loginFaceBook"
            && $action->id != "listCommentInPost"
            && $action->id != "resetPass"
            && $action->id != "login"
            && $action->id != "register"
            && $action->id != "debug"
            && $action->id != "getBikeType"
            && $action->id != "getPass"
            && $action->id != "searchUser"
            && $action->id != "showThread"
            && $action->id != "newThread"
            && $action->id != "getForumsByPage"
            && $action->id != "showThreadByPage"
            && $action->id != "loginFace"
            && $action->id != "detailComments"
            && $action->id != "uploadImages"
            && $action->id != "logout"
            && $action->id != "addDevicetoken"

        ) {
            $sessionKey = isset($_POST['sessionhash']) ? $_POST['sessionhash'] : null;
            if ($sessionKey == null) {
                $sessionKey = isset($_GET['sessionhash']) ? $_GET['sessionhash'] : null;
            }
            if ($sessionKey == null && empty($_POST)) {
                $params = json_decode(file_get_contents('php://input'), true);
                $sessionKey = isset($params['sessionhash']) ? $params['sessionhash'] : null;
            }

            if($sessionKey == null){
                echo json_encode(array('code' => 5, 'message' => 'Missing params sessionhash'));
                return;
            }
            //$sessionKey = str_replace(' ', '+', $sessionKey);
            //Yii::log("\n Session key:" . $sessionKey);
            return true;// CUtils::checkAuthSessionKey($sessionKey);
        } else {
            return true;
        }
        return parent::beforeAction($action);
    }

    public function actions()
    {
        // Sort by ASC
        return array(
            'accessKey' => 'protected.controllers.api.AccessSessionKeyAction',

            'addFriend1' => 'protected.controllers.api.AddFriendAction',
            'listProfileBuddy1' => 'protected.controllers.api.ListProfileBuddyAction',
            'detailFriend1' => 'protected.controllers.api.DetailFriendAction',

            'addFriend' => 'protected.controllers.api.FriendAddAction',
            'listProfileBuddy' => 'protected.controllers.api.FriendListAction',
            'detailFriend' => 'protected.controllers.api.FriendDetailAction',

            'addDevicetoken' => 'protected.controllers.api.AddDeviceToken',
            'androidInAppPurchase' => 'protected.controllers.api.AndroidInAppPurchaseAction',
            'cancelService' => 'protected.controllers.api.CancelserivceAction',
            'class' => 'protected.controllers.api.ClassAction',
            'confilmLogin' => 'protected.controllers.api.ConfilmLoginAction',
            'confirmLevel' => 'protected.controllers.api.ConfirmLevelAction',
            'confirmLogin' => 'protected.controllers.api.ConfirmLoginAction',
            'confirmRegisterService' => 'protected.controllers.api.ConfirmRegisterserivceAction',
            'changeLevel' => 'protected.controllers.api.ChangeLevelAction',
            'changepassword' => 'protected.controllers.api.ChangePassAction',
            'debug' => 'protected.controllers.api.DebugAction',
            'getBikeType' => 'protected.controllers.api.GetBikeTypeAction',
            'getDeviceToken' => 'protected.controllers.api.GetdevicetokenAction',

            // 1 profile thread tested
            'profileThreads1' => 'protected.controllers.api.ProfileThreadsAction',
            'profileThreads' => 'protected.controllers.api.ProfilePostAction',

            //2 getForum tested
            'getForumv1' => 'protected.controllers.api.GetForum',
            'getForum' => 'protected.controllers.api.ListPostAction',

            // 3. detailPost
            'detailPost1' => 'protected.controllers.api.DetailPostAction',
            'detailPost' => 'protected.controllers.api.PostAction',

            'getForumPage' => 'protected.controllers.api.GetForumByPage',
            'getNotifi' => 'protected.controllers.api.GetNotifiAction',
            'getPass' => 'protected.controllers.api.GetPassAction',
            'gettrans' => 'protected.controllers.api.TransactionDetailAction',
            'history' => 'protected.controllers.api.HistoryAction',
            'holdQuestion' => 'protected.controllers.api.HoldQuestionAction',
            'htmlContent' => 'protected.controllers.api.HtmlcontentAction',
            'uploadImages' => 'protected.controllers.api.ImageAction',
            'insertAvatar' => 'protected.controllers.api.InsertAvatarAction',
            'insertComment' => 'protected.controllers.api.InsertCommentAction',
            'iosInAppPurchase' => 'protected.controllers.api.IosInAppPurchaseAction',
            'listCommentInPost' => 'protected.controllers.api.ListCommentInPost',
            'listApp' => 'protected.controllers.api.ListAppAction',
            'listBlog' => 'protected.controllers.api.ListBlogAction',
            'listCategoryBlog' => 'protected.controllers.api.CategoryBlogAction',
            'listChapterVideo' => 'protected.controllers.api.ListChapterVideoAction',

            'listquestion' => 'protected.controllers.api.ListquestionAction',
            'listReport' => 'protected.controllers.api.ListReportAction',
            'login' => 'protected.controllers.api.LoginAction',
            'logout' => 'protected.controllers.api.LogoutAction',
            'loginGoogle' => 'protected.controllers.api.LoginGoogleAction',
            'messages' => 'protected.controllers.api.MessagesAction',
            'messagesBox' => 'protected.controllers.api.MessagesBoxAction',
            'messagesHistory' => 'protected.controllers.api.MessagesHistoryAction',
            'chat' => 'protected.controllers.api.ChatAction',
            'messageSend' => 'protected.controllers.api.MessageSendAction',
            'profile' => 'protected.controllers.api.ProfileAction',
            'profilePost' => 'protected.controllers.api.ProfilePostAction',


            'register' => 'protected.controllers.api.RegisterAction',
            'registerService' => 'protected.controllers.api.RegisterserivceAction',
            'report' => 'protected.controllers.api.ReportAction',
            'resetPass' => 'protected.controllers.api.ResetPassAction',
            'searchUser' => 'protected.controllers.api.SearchUserAction',
            'statusComment' => 'protected.controllers.api.StatusCommentAction',
            'subject' => 'protected.controllers.api.SubjectAction',
            'test' => 'protected.controllers.api.TestAction',
            'test2' => 'protected.controllers.api.Test2Action',
            'test3' => 'protected.controllers.api.Test3Action',
            'typeAccount' => 'protected.controllers.api.TypeAccountAction',
            'updateProfile' => 'protected.controllers.api.UpdateProfileAction',


            'postViewed' => 'protected.controllers.api.PostViewAction',
            'updatePost' => 'protected.controllers.api.UpdatePostAction',
            // 4. new thread
            'newThread1' => 'protected.controllers.api.NewThreadAction',
            'newThread' => 'protected.controllers.api.AddPostAction',
            // 5. new comment
            'newPost1' => 'protected.controllers.api.NewPostAction',
            'newPost' => 'protected.controllers.api.AddCommentAction',

            'getForumsByPage' => 'protected.controllers.api.GetForumsByPage',
            'showThreadByPage' => 'protected.controllers.api.ShowThreadByPage',
            'loginFace' => 'protected.controllers.api.LoginFacebook',




            'detailComments' => 'protected.controllers.api.DetailCommentAction',
            //7
            'advancedSearch1' => 'protected.controllers.api.AdvancedSearchAction',
            'advancedSearch' => 'protected.controllers.api.AdvancedSearchPostAction',
            //6
            'search1' => 'protected.controllers.api.SearchAction',
            'search' => 'protected.controllers.api.SearchPostAction',

            'searchResult' => 'protected.controllers.api.SearchResult',
            'updateProfilePic' => 'protected.controllers.api.UpdateProfilePic',
        );
    }

    public static function getStatusCodeMessage($status)
    {
        $codes = array(
            200 => 'OK',
            400 => 'ERROR: Bad request. API does not exist OR request failed due to some reason.',
        );

        return (isset($codes[$status])) ? $codes[$status] : null;
    }

    public static function sendResponse($status = 200, $body = '', $content_type = 'application/json')
    {
        header('HTTP/1.1 ' . $status . ' ' . self::getStatusCodeMessage($status));
        header('Content-type: ' . $content_type);
        if (trim($body) != '') echo $body;
        Yii::app()->end();
    }
}