<?php


class GetBikeTypeAction extends CAction
{
    public function run(){
        header('Content-type: application/json');
        $biketypes = Biketype::model()->findAll();
        $content = array();
        foreach($biketypes as $i => $item){
            $content[$i]['id'] = $item->id;
            $content[$i]['type'] = $item->type;
           // $content[$i]['name'] = $item->name;
        }
        echo json_encode(array('code' => 0,'message' => 'Danh sách dòng xe','items'=>$content));
    }
}