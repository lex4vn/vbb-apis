<?php

/**
 * Created by JetBrains PhpStorm.
 * User: hungld
 * Date: 1/7/15
 * Time: 2:18 PM
 * To change this template use File | Settings | File Templates.
 */
class TestAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
//        CUtils::send_notification("Tesstestetsetsete", "d_AzycHgjrk:APA91bFxu34Uhg4U1S0aPlCPdInS-5ezSMOUU82aHID_MCOoA5XOqDYOqYdxyPeAs0-5qur8dcf0KqnzjZwS2hIzMGyemt0q3UgtU12OtIuJszKakogreqfU9WACpsab3hMsMuPfeOdw");
        //day la url cua service, anh cho no thanh constant nhe
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'registration_ids' => array("d_AzycHgjrk:APA91bFxu34Uhg4U1S0aPlCPdInS-5ezSMOUU82aHID_MCOoA5XOqDYOqYdxyPeAs0-5qur8dcf0KqnzjZwS2hIzMGyemt0q3UgtU12OtIuJszKakogreqfU9WACpsab3hMsMuPfeOdw"),
            'data' => array(
                "message" => "Testttttttttt"
            )
        );
        $fields = json_encode($fields);
        $headers = array(
            //day la server key cho ios, anh cho no thanh constant nhe
            'Authorization: key=AAAADBi4nxg:APA91bHUvoNgm3lO6F4Ge1YTLfT9vOboWnQd5dAmRgZHX07AUA1c2OSbnWyfOB3qKSj68E-vRVpw917uT0DaHW2c3YTuGSMA8-ZEV8IwmQRWqxOrbIxSaZ71cy1BLFoN9fGlWWGaRwOo',
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result == false) {
            die("Curl failed: " . curl_error($ch));
        }
        //echo $result;
        echo json_encode(array('code' => 0, 'message' => $result));
    }
}