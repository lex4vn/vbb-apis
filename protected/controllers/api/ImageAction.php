<?php
class ImageAction extends CAction{
    public function run(){
        header('Content-type: application/json');
        if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        if(!isset($params["images"])){
            echo json_encode(array('code' => 5, 'message' => 'Missing params images'));
            return;
        }
////        if(!isset($params["number_image"])){
//            echo json_encode(array('code' => 5, 'message' => 'Missing params number_image'));
//            return;
//        }
//        if($params['number_image'] < 1){
//            echo json_encode(array('code' => 5, 'message' => 'Param number_image must be more than 0'));
//            return;
//        }
        $images = array();
        $number_image = count($params['images']);
        //var_dump($params['images']);

        for ($i = 0; $i < $number_image; $i++){
            //var_dump($params['images'][$i]);
            $item = $params['images'][$i];
            if(!isset($params['images'][$i]["width"])){
                echo json_encode(array('code' => 5, 'message' => 'Missing params width_'.$i));
                return;
            }
            if(!isset($params['images'][$i]["height"])){
                echo json_encode(array('code' => 5, 'message' => 'Missing params height_'.$i));
                return;
            }
            if(!isset($params['images'][$i]["extension"])){
                echo json_encode(array('code' => 5, 'message' => 'Missing params extension_'.$i));
                return;
            }
            if(!isset($params['images'][$i]["image_base64"])){
                echo json_encode(array('code' => 5, 'message' => 'Missing params image_base64'.$i));
                return;
            }
//            $array_image = array(
//                'extension' => $params["extension_$i"],
//                'image_base64' => $params["image_" . $i . "_base64"],
//                'width' => $params["width_$i"],
//                'height' => $params["height_$i"],
//            );
//            array_push($images, $array_image);
        }
        $image_results = [];
        foreach ($params['images'] as $item){
            $extension = $item['extension'];
            $image_base64 = $item['image_base64'];

            if (!file_exists(IMAGES_SOURCE)) {
                mkdir(IMAGES_SOURCE, 0777, true);
            }

            $binary = base64_decode($image_base64);
            header('Content-Type: bitmap; charset=utf-8');

            $file_name = date('YmdHis') . '-' . rand() . '.' . $extension;
            $path = fopen(IMAGES_SOURCE . '/' . $file_name, 'w+');

            //write file
            fwrite($path, $binary);
            //close file stream
            fclose($path);

            $post_image = new Images();
            $post_image->type = 1;
            $post_image->status = 1;
            $post_image->width = $item['width'];
            $post_image->height = $item['height'];
            $post_image->base_url = $file_name;
            if (!$post_image->save()){
                echo json_encode(array('code' => 5, 'message' => 'Cannot upload images file'));
                return;
            }

            //$size = getimagesize(IMAGES_SOURCE.$file_name);
            //$post_image->width = $size[0];
           // $post_image->height = $size[1];
            $post_image->save();
            $image_result = array(
                'id' => $post_image->id,
                'image_name' => $post_image->base_url,
                'image_url' => IMAGES_SOURCE .$post_image->base_url,
            );
            $image_results[] = $image_result;
        }

        echo json_encode(array('code' => 0, 'message' => 'Upload successfully', 'images'=>$image_results));
    }
}
