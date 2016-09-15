<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hungld
 * Date: 1/7/15
 * Time: 2:18 PM
 * To change this template use File | Settings | File Templates.
 */

class TestAction extends CAction{
    public function run(){
        header('Content-type: application/json');

        $apiConfig = new Signes\ApiConfig('mkAUvs6w', '12344567', 'lex', 'vb', 'yii', '898');
        $apiConnector = new Signes\GuzzleProvider('http://localhost/forum/');

        $api = new Api($apiConfig, $apiConnector);

        $response = $api->callRequest('user.fetchByEmail', ['email' => 'test@example.com']);
        $bool = false;
        echo json_encode(array('test'=> $bool));
        return;
    }
}