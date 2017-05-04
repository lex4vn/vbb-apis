<?php

/**
 * This is the model class for table "api_chat".
 *
 * The followings are the available columns in table 'api_chat':
 * @property integer $id
 * @property integer $fromid
 * @property string $fromuser
 * @property integer $status_from
 * @property integer $to
 * @property string $touser
 * @property integer $status_to
 * @property string $message
 * @property integer $read
 * @property string $time
 */
class Chat extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'api_chat';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fromid, fromuser, to, touser, message, read, time', 'required'),
			array('fromid, status_from, to, status_to, read', 'numerical', 'integerOnly'=>true),
			array('fromuser, touser', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, fromid, fromuser, status_from, to, touser, status_to, message, read, time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'fromid' => 'Fromid',
			'fromuser' => 'Fromuser',
			'status_from' => 'Status From',
			'to' => 'To',
			'touser' => 'Touser',
			'status_to' => 'Status To',
			'message' => 'Message',
			'read' => 'Read',
			'time' => 'Time',
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

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('fromid',$this->fromid);
		$criteria->compare('fromuser',$this->fromuser,true);
		$criteria->compare('status_from',$this->status_from);
		$criteria->compare('to',$this->to);
		$criteria->compare('touser',$this->touser,true);
		$criteria->compare('status_to',$this->status_to);
		$criteria->compare('message',$this->message,true);
		$criteria->compare('read',$this->read);
		$criteria->compare('time',$this->time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Chat the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
