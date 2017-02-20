<?php

/**
 * This is the model class for table "post".
 *
 * The followings are the available columns in table 'post':
 * @property integer $id
 * @property string $subject
 * @property string $message
 * @property string $content
 * @property string $thumb
 * @property double $price
 * @property string $phone
 * @property string $bike
 * @property string $address
 * @property integer $formality
 * @property string $location
 * @property integer $postuserid
 * @property string $postusername
 * @property string $create_date
 * @property string $modify_date
 * @property integer $status
 * @property integer $type
 * @property integer $count_like
 * @property integer $count_comment
 */
class Post extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'post';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('thumb', 'required'),
            array('formality, postuserid, status, type, count_like, count_comment', 'numerical', 'integerOnly' => true),
            array('price', 'numerical'),
            array('subject, bike, address, location', 'length', 'max' => 200),
            array('thumb', 'length', 'max' => 100),
            array('phone', 'length', 'max' => 15),
            array('postusername', 'length', 'max' => 30),
            array('message, content, create_date, modify_date', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, subject, message, content, thumb, price, phone, bike, address, formality, location, postuserid, postusername, create_date, modify_date, status, type, count_like, count_comment', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'subject' => 'Subject',
            'message' => 'Message',
            'content' => 'Content',
            'thumb' => 'Thumb',
            'price' => 'mon',
            'phone' => 'Phone',
            'bike' => 'Bike',
            'address' => 'Address',
            'formality' => 'Formality',
            'location' => 'Location',
            'postuserid' => 'Postuserid',
            'postusername' => 'Postusername',
            'create_date' => 'Create Date',
            'modify_date' => 'Modify Date',
            'status' => '0: chua duoc tra loi, 1: tra loi xong',
            'type' => 'Type',
            'count_like' => 'Count Like',
            'count_comment' => 'Count Comment',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('subject', $this->subject, true);
        $criteria->compare('message', $this->message, true);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('thumb', $this->thumb, true);
        $criteria->compare('price', $this->price);
        $criteria->compare('phone', $this->phone, true);
        $criteria->compare('bike', $this->bike, true);
        $criteria->compare('address', $this->address, true);
        $criteria->compare('formality', $this->formality);
        $criteria->compare('location', $this->location, true);
        $criteria->compare('postuserid', $this->postuserid);
        $criteria->compare('postusername', $this->postusername, true);
        $criteria->compare('create_date', $this->create_date, true);
        $criteria->compare('modify_date', $this->modify_date, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('type', $this->type);
        $criteria->compare('count_like', $this->count_like);
        $criteria->compare('count_comment', $this->count_comment);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Post the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function getPosts($postuserid = 0,$limit = 8, $offset = 0, $type = 0, $status = 1)
    {
        $sql = 't.status=' . $status;
        if($type > 0){
            $sql .= ' and t.type =' . $type;
        }
        if($postuserid > 0){
            $sql .= ' and t.postuserid =' . $postuserid;
        }

        $total = Yii::app()->db->createCommand()
            ->select('*')
            ->from('post t')
            ->where($sql)
            ->queryAll();

        $result = Yii::app()->db->createCommand()
            ->select('t.id, t.content, t.message, t.subject, t.thumb,t.price, t.phone, t.bike, t.formality, t.postusername, t.price, t.postuserid, t.location, t.create_date, t.modify_date, t.status, t.type, t.count_like, t.count_comment')
            ->from('post t')
            ->where($sql)
            ->order('t.modify_date desc')
            ->limit($limit)
            ->offset($offset)
            ->queryAll();
        return array(
            'data' => $result,
            'total'=>count($total)
        );
    }
}
