<?php

/**
 * Created by JetBrains PhpStorm.
 * User: hungld
 * Date: 12/4/16
 * Time: 10:18 AM
 * To change this template use File | Settings | File Templates.
 */
class AndroidInAppPurchaseAction extends CAction {

    public function run() {
        header('Content-type: application/json');
        Yii::log("\n ----------------------Vao ANDROID----------------------------------------------: ");
        $params = $_POST;
        $responseData = isset($params['responseData']) ? $params['responseData'] : null;
        $publicKey = isset($params['publicKey']) ? $params['publicKey'] : null;
        $signature = isset($params['signature']) ? $params['signature'] : null;
        $user_id = isset($params['user_id']) ? $params['user_id'] : null;
        $currency = isset($params['currency']) ? $params['currency'] : 'USD';
        $money = isset($params['money']) ? $params['money'] : 0;
        if ($user_id == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing Params user_id'));
            return;
        }
        if ($responseData == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing Params responseData'));
            return;
        }
        if ($publicKey == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing Params publicKey'));
            return;
        }
        if ($signature == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing Params signature'));
            return;
        }
        if (!Subscriber::model()->exists('id = '. $user_id)){
            echo json_encode(array('code' => 1, 'message' => 'user_id is not exist'));
            return;
        }
        $responseData = str_replace(' ','+',$responseData);
        $publicKey = str_replace(' ','+',$publicKey);
        $signature = str_replace(' ','+',$signature);
//        $responseData = '{"orderId":"GPA.1309-9765-8183-43835","packageName":"com.bkt.hocde","productId":"100_oncash","purchaseTime":1460342608725,"purchaseState":0,"purchaseToken":"lklpegilfdkgcchioafgnihl.AO-J1OwWTMdpH_O9eG4q88H7m6UtQC-nr4HKtS4CTjGJhVrPA4P86CjgGSN-dqY53ia5RxI33ijLjCTBTMXeCfrA6lpBYfYaivVoL6vwLUjrsStkyK_-Nn8"}';
//        $signature = 'D2K+P/QgfQcnZxAFe+/XwhWEZ+aHuK3+TgrSHks4PhZlARiBvwXsL3WGfyDxWkG4dn/L4I/UNL40w4k3O41YASCdoABRvLLx6HMPtTxBqCxeL72BBh2SmZbrsio5Cadv1KkPzudpP/2PE+WCykH8nOaGUA5cOdbFyI7/BozaWdIBq1/rzpte3/4E3N3fm/pSOW/zYx/H9VoAmQjl7ngpf8bfpQQOP8mNQEmWZkjRS3QGswIn2RmJZOBsTO8lFZ2Z0UVUwTohgKYs4YaZUDFVutMgW+0vFJt9lc7Nryu8iS7GEkm4Uny/Lkvi99k58cZa8ISmWY1Ak5xr8aqubzZZLg==';
//        $publicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhhPZwVr21ldjS6bzU17sFnTBmAgdDjUkY1Eptql/sjkUYscNEcM/DaoXgLTsDzHdDdFn2QDb1NLO8L9BrZv7nIKvhz0MyqGqQfxVaNzOtrybItM9/TRRgX2NBYBK0KCSjMRE23ZwccU01A3lec9wmpG7ejjFuJkC/o+0/z+cmRXA+84LPO72y+DVrd2qDUsmS+0xi2QpUscJ0jqLFobpUlNUQUBCWueZLLdIdJd/2MBFcS94wbybkbcUxXwvibF8xYgxPaCIb+UhPSvZTNSUJ66fnSrp+QgpB1O1Pc09eWoKYk3KInX1hcudfr04ihqQbLIsBeIikp8Hf4rYUG1jrQIDAQAB';
        $responseData = trim($responseData);
        $responseData = base64_decode($responseData);
        $signature = trim($signature);
        $response = json_decode($responseData);
        Yii::log("\n responseData----------------------------------------------: " . $responseData);
        Yii::log("\n signature--------------------------------------------------: " . $signature);
        Yii::log("\n publicKey: ------------------------------------------------" . $publicKey);
//    print_r($response);die;
        //Create an RSA key compatible with openssl_verify from our Google Play sig
        $key =	"-----BEGIN PUBLIC KEY-----\n".
		chunk_split($publicKey, 64,"\n").
		'-----END PUBLIC KEY-----';  
        $key = openssl_get_publickey($key);
//    print_r($key);die;
        // Pre-add signature to return array before we decode it
        $retArray = array('signature' => $signature);

        //Signature should be in binary format, but it comes as BASE64.
        $signature = base64_decode($signature);
        
        //Verify the signature
        $result = openssl_verify($responseData, $signature, $key, OPENSSL_ALGO_SHA1);
        $status = (1 === $result) ? 0 : 1;
        if($status == 0){
            $oncash = array_pop(explode('_',$response->productId));
            $subscriber = Subscriber::model()->findByPk($user_id);
            $subscriber->fcoin += $oncash;
            if(!$subscriber->save()){
                echo json_encode(array('code'=>3,'message'=>'Giao dịch thất bại')); return;
            }
            $transaction = $subscriber->newTransaction(PURCHASE_TYPE_NEW, 'APP', 'APP', 'APP', $subscriber['partner_id'], 'APP');
            $transaction->status = 1;
            $transaction->oncash = $oncash;
            $transaction->cost = $money;
            $transaction->currency = $currency;
            $transaction->save();
            echo json_encode(array('code'=>0,'message'=>'Giao dịch thành công')); return;
        }else{
            echo json_encode(array('code'=>3,'message'=>'Giao dịch thất bại')); return;
        }
    }

}
