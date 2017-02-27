<?php

class SearchPostAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        $params = $_GET;
        if (!isset($params['search']) || $params['search'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params search'));
            return;
        }
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {
            $items = array();

            $criteria = new CDbCriteria();
            if(isset($params['search']))
            {
                $q = $params['search'];
                $criteria->compare('subject', $q, true, 'OR');
                $criteria->compare('message', $q, true, 'OR');
                $criteria->compare('content', $q, true, 'OR');
            }
            $results = Post::model()->findAll($criteria);
            foreach ($results as $result) {
                $item = array(
                    'threadid' => $result->id,
                    'threadtitle' => $result->subject,
                    'postuserid' => $result->postuserid,
                    'postusername' => $result->postusername,
                    'preview' => $result->message,
                    'price' => $result->price,
                    'phone' => $result->phone,
                    'bike' => $result->bike,
                    'address' => $result->location,
                    'formality' => $result->formality,
                    'image' => $result->thumb,
                    'status' => $result->status,
                    'type' => $result->type,
                );
                array_push($items, $item);
            }
            echo json_encode(array('code' => 0,
                'message' => 'search success',
                'listThread' => $items
            ));
            return;
        }else{
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }
    }


}