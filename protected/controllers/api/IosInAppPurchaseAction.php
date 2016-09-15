<?php

/**
 * Created by JetBrains PhpStorm.
 * User: hungld
 * Date: 12/4/16
 * Time: 10:18 AM
 * To change this template use File | Settings | File Templates.
 */
class IosInAppPurchaseAction extends CAction {

    public function run() {
        header('Content-type: application/json');
        Yii::log("\n ----------------------Vao IOS----------------------------------------------: ");
        $params = $_POST;
        $receipt = isset($params['receipt']) ? $params['receipt'] : null;
        $user_id = isset($params['user_id']) ? $params['user_id'] : null;
        $tranID = isset($params['tran_id']) ? $params['tran_id'] : null;
        $currency = isset($params['currency']) ? $params['currency'] : 'USD';
        $money_full = isset($params['money']) ? $params['money'] : 0;
        $type = isset($params['type']) ? $params['type'] : 0;// 0: link test, 1: link that
        if ($receipt == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing Params receipt'));
            return;
        }
        if ($user_id == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing Params user_id'));
            return;
        }
        if ($tranID == null) {
            echo json_encode(array('code' => 5, 'message' => 'Missing Params tran_id'));
            return;
        }
        if (!Subscriber::model()->exists('id = '. $user_id)){
            echo json_encode(array('code' => 1, 'message' => 'user_id is not exist'));
            return;
        }
        if($type == 0){
            $endpoint = itunesReceiptValidator::SANDBOX_URL;
        }else if($type == 1){
            $endpoint = itunesReceiptValidator::PRODUCTION_URL;
        }
        Yii::log("\n tranID----------------------------------------------: " . $tranID);
        Yii::log("\n endpoint----------------------------------------------: " . $endpoint);
        try {
            $rv = new itunesReceiptValidator($endpoint, $receipt);
            $info = $rv->validateReceipt();
//            echo '<pre>'; var_dump($info); return;
            if($info->status == 0 && $info->receipt->transaction_id == $tranID){
                $money = array_pop(explode('_',$info->receipt->product_id));
                $oncash = '';
                switch (intval($money)){
                    case 1: $oncash = 100; break;
                    case 2: $oncash = 200; break;
                    case 3: $oncash = 300; break;
                    case 4: $oncash = 400; break;
                    case 5: $oncash = 500; break;
                    case 6: $oncash = 600; break;
                    case 7: $oncash = 700; break;
                    case 8: $oncash = 800; break;
                }
                $subscriber = Subscriber::model()->findByPk($user_id);
                $subscriber->fcoin += $oncash;
                if(!$subscriber->save()){
                    echo json_encode(array('code'=>3,'message'=>'Giao dịch thất bại')); return;
                }
                $transaction = $subscriber->newTransaction(PURCHASE_TYPE_NEW, 'APP', 'APP', 'APP', $subscriber['partner_id'], 'APP');
                $transaction->oncash = $oncash;
                $transaction->cost = $money_full;
                $transaction->currency = $currency;
                $transaction->status = 1;
                $transaction->save();
                echo json_encode(array('code'=>0,'message'=>'Giao dịch thành công')); return;
            }
            echo json_encode(array('code'=>3,'message'=>'Giao dịch thất bại')); return;
        } catch (Exception $ex) {
            echo json_encode(array('code'=>3,'message'=>'Giao dịch thất bại')); return;
        }
    }

}
