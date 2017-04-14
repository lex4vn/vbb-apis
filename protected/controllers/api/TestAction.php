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
        CUtils::send_notification("Tesstestetsetsete", "d_AzycHgjrk:APA91bFxu34Uhg4U1S0aPlCPdInS-5ezSMOUU82aHID_MCOoA5XOqDYOqYdxyPeAs0-5qur8dcf0KqnzjZwS2hIzMGyemt0q3UgtU12OtIuJszKakogreqfU9WACpsab3hMsMuPfeOdw");
        echo json_encode(array('code' => 0, 'message' => 'OK'));
    }
}