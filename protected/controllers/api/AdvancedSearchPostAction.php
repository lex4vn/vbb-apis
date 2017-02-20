<?php

class AdvancedSearchPostAction extends CAction
{
    public function run()
    {
        header('Content-type: application/json');
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $params = $_POST;
        if (!isset($params['type']) || $params['type'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params type'));
            return;
        }
        if (!($params['type'] == 1 || $params['type'] == 2)) {
            echo json_encode(array('code' => 5, 'message' => 'Params type can be 1 or 2. Therein 1 is need to buy. 2 is need to sell.'));
            return;
        }

        if (!isset($params['bike']) || $params['bike'] == '') {
            echo json_encode(array('code' => 5, 'message' => 'Missing params bike'));
            return;
        }

        //var_dump($params);die();
        $sessionhash = CUtils::getSessionHash(($params['sessionhash']));
        if ($sessionhash) {

            $criteria = new CDbCriteria();
            if(isset($params['search']))
            {
                $q = $params['search'];
                $criteria->compare('subject', $q, true, 'OR');
                //$criteria->compare('price', $params['price_min'], true, 'OR');
                //$criteria->compare('price', $params['price_max'], true, 'OR');
                $criteria->compare('bike', $params['bike'], true, 'OR');
            }

            if (isset($response['response']) && 'search' == $response['response']->errormessage) {
                $searchid = $response['show']->searchid;
            } else {
                echo json_encode(array('code' => 1, 'message' => 'No results'));
                return;
            }

            //var_dump($response);die();
            $items = array();
            $results = $dataProvider=new CActiveDataProvider("post", array('criteria'=>$criteria));
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
                    'type' => $result->type,
                    'status' => $result->status,
                );
                //var_dump($item);die();

                array_push($items, $item);
            }
           // var_dump($items);
           // die();
            if ($items) {
                echo json_encode(array('code' => 0,
                    'message' => 'Search successful',
                    // 'totalpages' => $response["response"]->pagenav->totalpages,
                    'listThread' => $items
                ));
                return;
            } else {
                echo json_encode(array('code' => 1, 'message' => 'No results'));
                return;
            }

        } else {
            // Sessionhash is empty
            echo json_encode(array('code' => 10, 'message' => 'User logged out'));
            return;
        }

    }
}