<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dangtd
 * Date: 1/7/15
 * Time: 2:18 PM
 * To change this template use File | Settings | File Templates.
 */

class ListAppAction extends CAction{
    public function run(){
        header('Content-type: application/json');

        $page = isset($_GET['page_number']) ? $_GET['page_number'] : 1;
        $limit = isset($_GET['page_size']) ? $_GET['page_size'] : 20;
        $offset = ($page - 1) * $limit;

        $arrApps = IntroduceApp::model()->findAllByAttributes(
            array(
//                'status'=>1,
//                'type'=>1,
            ),
            array(
                'order'=>'id asc',
                'limit' => $limit,
                'offset' => $offset
            )
        );

        $arrApp = array();
        for ($i = 0; $i < count($arrApps); $i++){
            $arrApp[$i]['id'] = $arrApps[$i]['id'];
            $arrApp[$i]['title'] = $arrApps[$i]['title'];
            $arrApp[$i]['url'] = $arrApps[$i]['url'];
            $arrApp[$i]['image_thump'] = IPSERVER . 'web/uploads/'. $arrApps[$i]['image_thump'];
        }
        echo json_encode(array('code' => 0,'ListApp' => array('title' => 'Danh sách Ứng dụng','items'=>$arrApp)));
    }   
}