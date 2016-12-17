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
        // TODO check exist in array
        if (
            $action->id != "listReport"
            && $action->id != "htmlContent"
            && $action->id != "level"
            && $action->id != "class"
            && $action->id != "loginGoogleAndroid"
            && $action->id != "loginGoogle"
            && $action->id != "test"
            && $action->id != "test2"
            && $action->id != "test3"
            && $action->id != "partner"
            && $action->id != "loginFaceBook"
            && $action->id != "listCommentInPost"
            && $action->id != "resetPass"
            && $action->id != "login"
            && $action->id != "register"
            && $action->id != "debug"
            && $action->id != "listSubjectVideo"
            && $action->id != "listChapterVideo"
            && $action->id != "adsOncash"
            && $action->id != "getBikeType"
            && $action->id != "getSchool"
            && $action->id != "getPass"
            && $action->id != "searchUser"
            && $action->id != "showThread"
            && $action->id != "newThread"
            && $action->id != "getForumsByPage"
            && $action->id != "showThreadByPage"
            && $action->id != "loginFace"
            && $action->id != "detailComments"
            && $action->id != "detailPost"
            && $action->id != "uploadImages"
            && $action->id != "logout"
        ) {
            //var_dump($_POST['sessionhash']);die();
            $sessionKey = isset($_POST['sessionhash']) ? $_POST['sessionhash'] : null;
            if ($sessionKey == null) {
                $sessionKey = isset($_GET['sessionhash']) ? $_GET['sessionhash'] : null;
            }
            if ($sessionKey == null && empty($_POST)) {
                $_POST = json_decode(file_get_contents('php://input'), true);
            }
            $params = $_POST;
			//fix tam da abc
            $sessionKey = isset($params['sessionhash']) ? $params['sessionhash'] : $_GET['sessionhash'];
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
            'adsOncash' => 'protected.controllers.api.AdsOncashAction',
            'androidInAppPurchase' => 'protected.controllers.api.AndroidInAppPurchaseAction',
            'answer' => 'protected.controllers.api.AnswerAction',
            'banner' => 'protected.controllers.api.BannerAction',
            'cancelService' => 'protected.controllers.api.CancelserivceAction',
            'class' => 'protected.controllers.api.ClassAction',
            'confilmLogin' => 'protected.controllers.api.ConfilmLoginAction',
            'confirmLevel' => 'protected.controllers.api.ConfirmLevelAction',
            'confirmLogin' => 'protected.controllers.api.ConfirmLoginAction',
            'confirmRegisterService' => 'protected.controllers.api.ConfirmRegisterserivceAction',
            'changeLevel' => 'protected.controllers.api.ChangeLevelAction',
            'changepassword' => 'protected.controllers.api.ChangePassAction',
            'debug' => 'protected.controllers.api.DebugAction',
            'detailBlog' => 'protected.controllers.api.DetailBlogAction',
            'detailquestion' => 'protected.controllers.api.DetailquestionAction',
            'detailQuestionBank' => 'protected.controllers.api.DetailQuestionBankAction',
            'detailquestionBk' => 'protected.controllers.api.DetailquestionBkAction',
            'detailVideo' => 'protected.controllers.api.DetailVideoAction',
            'exam' => 'protected.controllers.api.ExamAction',
            'getBikeType' => 'protected.controllers.api.GetBikeTypeAction',
            'getDeviceToken' => 'protected.controllers.api.GetdevicetokenAction',
            'getForum' => 'protected.controllers.api.GetForum',
            'getNotifi' => 'protected.controllers.api.GetNotifiAction',
            'getPass' => 'protected.controllers.api.GetPassAction',
            'getSchool' => 'protected.controllers.api.GetSchoolAction',
            'gettrans' => 'protected.controllers.api.TransactionDetailAction',
            'history' => 'protected.controllers.api.HistoryAction',
            'holdQuestion' => 'protected.controllers.api.HoldQuestionAction',
            'htmlContent' => 'protected.controllers.api.HtmlcontentAction',
            'uploadImages' => 'protected.controllers.api.ImageAction',
            'insertAvatar' => 'protected.controllers.api.InsertAvatarAction',
            'insertComment' => 'protected.controllers.api.InsertCommentAction',
            'iosInAppPurchase' => 'protected.controllers.api.IosInAppPurchaseAction',
            'level' => 'protected.controllers.api.LevelAction',
            'likeQuestion' => 'protected.controllers.api.LikequestionAction',
            'listCommentInPost' => 'protected.controllers.api.ListCommentInPost',
            'listApp' => 'protected.controllers.api.ListAppAction',
            'listBlog' => 'protected.controllers.api.ListBlogAction',
            'listCategoryBlog' => 'protected.controllers.api.CategoryBlogAction',
            'listChapterVideo' => 'protected.controllers.api.ListChapterVideoAction',
            'listProfileBuddy' => 'protected.controllers.api.ListProfileBuddyAction',
            'listquestion' => 'protected.controllers.api.ListquestionAction',
            'listQuestionBank' => 'protected.controllers.api.ListQuestionBankAction',
            'listReport' => 'protected.controllers.api.ListReportAction',
            'listservice' => 'protected.controllers.api.ListserviceAction',
            'listSubjectVideo' => 'protected.controllers.api.ListSubjectVideoAction',
            'listVideo' => 'protected.controllers.api.ListVideoAction',
            'login' => 'protected.controllers.api.LoginAction',
            'logout' => 'protected.controllers.api.LogoutAction',
            'loginFaceBook' => 'protected.controllers.api.LoginFaceBookAction',
            'loginFaceBookDemo' => 'protected.controllers.api.LoginFacebookDemo',
            'loginGoogle' => 'protected.controllers.api.LoginGoogleAction',
            'loginGoogleAndroid' => 'protected.controllers.api.LoginGoogleAndroidAction',
            'partner' => 'protected.controllers.api.PartnerAction',
            'pintop' => 'protected.controllers.api.PinTopAction',
            'profile' => 'protected.controllers.api.ProfileAction',
            'profilePost' => 'protected.controllers.api.ProfilePostAction',
            'profileThreads' => 'protected.controllers.api.ProfileThreadsAction',
            'question' => 'protected.controllers.api.QuestionAction',
            'questionPoint' => 'protected.controllers.api.QuestionPointAction',
            'questionServer' => 'protected.controllers.api.QuestionServerAction',
            'questionTeacher' => 'protected.controllers.api.QuestionTeacherAction',
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
            'updateStatus' => 'protected.controllers.api.UpdateStatusAction',
            'useCard' => 'protected.controllers.api.UseCardAction',
            'useCardNet2e' => 'protected.controllers.api.UseCardNet2EAction',
            'showThread' => 'protected.controllers.api.ShowThreadAction',
            'newThread' => 'protected.controllers.api.NewThreadAction',
            'getForumsByPage' => 'protected.controllers.api.GetForumsByPage',
            'showThreadByPage' => 'protected.controllers.api.ShowThreadByPage',
            'loginFace' => 'protected.controllers.api.LoginFacebook',
            'detailPost' => 'protected.controllers.api.DetailPostAction',
            'detailComments' => 'protected.controllers.api.DetailCommentAction',
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