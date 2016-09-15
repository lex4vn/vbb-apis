<?php

class RegisterAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $userName = isset($_POST['username']) ? $_POST['username'] : null;
        $email = isset($_POST['email']) ? $_POST['email'] : null;
        $fullName = isset($_POST['fullname']) ? $_POST['fullname'] : null;
        $password = isset($_POST['password']) ? $_POST['password'] : null;
        $phoneNumber = isset($_POST['phonenumber']) ? $_POST['phonenumber'] : "0912345678";
        $job = isset($_POST['job']) ? $_POST['job'] : 'Nhân viên';
        $wife = isset($_POST['wife']) ? $_POST['wife'] : 'Vợ 2';
        $birthday = isset($_POST['birthday']) ? $_POST['birthday'] : null;

        if ($userName == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params username'));
            return;
        }
        if ($password == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params password'));
            return;
        }
        if ($email == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params email'));
            return;
        }

        if ($fullName == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params fullname'));
            return;
        }

        if ($birthday == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing params birthday'));
            return;
        }

        //Parameters
        $uniqueId = '123123';
        $content = '';

        $apiConfig = new ApiConfig(API_KEY, $uniqueId, CLIENT_NAME, CLIENT_VERSION, PLATFORM_NAME, PLATFORM_VERSION);
        $apiConnector = new GuzzleProvider(API_URL);
        $api = new Api($apiConfig, $apiConnector);

        $response = $api->callRequest('api_init', [
            'clientname' => CLIENT_NAME,
            'clientversion' => CLIENT_VERSION,
            'platformname' => PLATFORM_NAME,
            'platformversion' => PLATFORM_VERSION,
            'uniqueid' => $uniqueId]);

        // var_dump($response);die(1);

        // Get token key
        $accessToken = $response['apiaccesstoken'];

        $apiConfig->setAccessToken($accessToken);
        $api = new Api($apiConfig, $apiConnector);

        $userfield = [];
        $userfield["field5"] = $fullName;
        $userfield["field6"] = $birthday;
        //Nghe nghiep
        $userfield["field7"] = $job;
        $userfield["field8"] = $phoneNumber;

        // Xe - vo 2
        $userfield["field9"] = $wife;
        $userfield["field10"] = "Tổ đội";

        $month = date("m", strtotime($birthday));

        // Get day
        $day = date("d", strtotime($birthday));

        // Get day
        $year = date("Y", strtotime($birthday));

        $birthdate = $day . '/' . $month . '/' . $year;

        $response = $api->callRequest('register_addmember', [
            'agree' => '1',
            'username' => $userName,
            'email' => $email,
            'emailconfirm' => $email,
            'password' => $password,
            'passwordconfirm' => $password,
            'month' => $month,
            'day' => $day,
            'year' => $year,
            'birthdate' => $birthdate,
            'timezoneoptions' => TIME_ZONE_7,
            'userfield' => $userfield
        ]);
        //  var_dump($response);die(1);

        if (!isset($response)) {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }
        //var_dump($response);die(1);
        // var_dump($response['response']);die(1);
        //  if(!isset($response->response->errorlist)){

        if (isset($response['response'])) {
            if (isset($response['response']->errormessage)) {
                $result = $response['response']->errormessage[0];
                if ('registration_complete' == $result) {
                    //var_dump($response);
                    echo json_encode(array('code' => 0,
                        'accessToken' => $accessToken,
                        'message' => 'Dang ky thanh cong',
                        'profile' => $response['response']));
                    return;
                }
            } else {
                $errorList = $response['response']->errorlist;
                //  var_dump($errorList);die(1);
                if (!empty($errorList)) {
                    $messsage = '';
                    foreach ($errorList as $e) {
                       // var_dump($e);
                        if ('usernametaken' == $e[0]) {
                            echo json_encode(array('code' => 2, 'message' => 'Tên tài khoản đã được sử dụng'));
                            return;
                        }
                        if ('emailtaken' == $e[0]) {
                            echo json_encode(array('code' => 2, 'message' => 'Email đã được sử dụng'));
                            return;
                        }

                        if ('regexincorrect' == $e[0]) {
                            echo json_encode(array('code' => 2, 'message' => 'Vui lòng nhập họ và tên'));
                            return;
                        }
                        if ('usernametoolong' == $e[0]) {
                            echo json_encode(array('code' => 2, 'message' => 'Tên tài khoản quá dài'));
                            return;
                        }

                    }

                    echo json_encode(array('code' => 2, 'message' => $messsage));
                    return;
                }
            }
        }
//        if ($user->save()) {
//            $sessionKey = CUtils::generateSessionKey($user->primaryKey);
//            $version = Version::model()->findByPk(2);
//            $profile = array(
//                'id' => $user->primaryKey,
//                'fullName' => $firstName . ' ' . $lastName,
//                'userName' => $userName,
//                'avatar' => $avatar,
//                'phoneNumber' => !empty($user->phone_number) ? (string)$user->phone_number : '',
//                'lastName' => !empty($user->lastname) ? $user->lastname : '',
//                'firstName' => !empty($user->$firstName) ? $user->$firstName : '',
//                'numberMessage' => 0,
//                'versionApp' => '1.0',
//            );
//            echo json_encode(array('code' => 0,
//                'sessionkey' => $sessionKey,
//                'message' => 'Tài khoản của bạn đã đăng ký thành công',
//                'item' => $profile));
//            //Yii::app()->mail->send($message);
//            return;
//        }
    }
}