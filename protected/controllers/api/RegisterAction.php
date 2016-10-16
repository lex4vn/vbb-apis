<?php

class RegisterAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        $userName = isset($params['username']) ? $params['username'] : null;
        $email = isset($params['email']) ? $params['email'] : null;
        $fullName = isset($params['fullname']) ? $params['fullname'] : null;
        $password = isset($params['password']) ? $params['password'] : null;
        $phoneNumber = isset($params['phonenumber']) ? $params['phonenumber'] : "0912345678";
        $job = isset($params['job']) ? $params['job'] : 'Nhân viên';
        $wife = isset($params['wife']) ? $params['wife'] : 'Vợ 2';
        $birthday = isset($params['birthday']) ? $params['birthday'] : null;

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
        $uniqueId = uniqid();
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

        // Get year
        $year = date("Y", strtotime($birthday));

        $birthdate = $day . '/' . $month . '/' . $year;

        $response = $api->callRequest('register_addmember', [
            'agree' => '1',
            'username' => $userName,
            'email' => $email,
            'emailconfirm' => $email,
            'password_md5' => $password,
            'passwordconfirm_md5' => $password,
            'month' => $month,
            'day' => $day,
            'year' => $year,
            'birthdate' => $birthdate,
            'timezoneoptions' => TIME_ZONE_7,
            'userfield' => $userfield,
            'api_v'=> '1'
        ]);

        if (!isset($response)) {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }

        if (!isset($response['response'])) {
            echo json_encode(array('code' => 1, 'message' => 'Forum error'));
            return;
        }

        if (isset($response['response'])) {
            if (isset($response['response']->errormessage)) {
                $result = $response['response']->errormessage[0];
                if ('registration_complete' == $result) {
                    echo json_encode(array('code' => 0,
                        'accessToken' => $accessToken,
                        'message' => 'Register successful',
                        'username' => $userName,
                        'email' => $email,
                        'password' => $password,
                        'birthdate' => $birthdate,
                        'fullName' => $fullName,
                    ));
                    return;
                }
            } else {
                $errorList = $response['response']->errorlist;
                if (!empty($errorList)) {
                    $messsage = '';
                    foreach ($errorList as $e) {
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
    }
}