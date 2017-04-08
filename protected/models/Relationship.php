<?php

/**
 * This is the model class for table "relationship".
 *
 * The followings are the available columns in table 'relationship':
 * @property string $user_one_id
 * @property string $user_two_id
 * @property integer $status
 * @property string $action_user_id
 */
class Relationship extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'relationship';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_one_id, user_two_id, action_user_id', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('user_one_id, user_two_id, action_user_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('user_one_id, user_two_id, status, action_user_id', 'safe', 'on'=>'search'),
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
			'user_one_id' => 'User One',
			'user_two_id' => 'User Two',
			'status' => 'Status',
			'action_user_id' => 'Action User',
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

		$criteria->compare('user_one_id',$this->user_one_id,true);
		$criteria->compare('user_two_id',$this->user_two_id,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('action_user_id',$this->action_user_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Relationship the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function friendsBelong($uid)
	{
		return Yii::app()->db->createCommand()
			->select('u.device_token')
			->from('relationship r')
			->join('api_user u', 'u.userid=r.user_two_id')
			->where('(r.user_two_id=:id AND r.status = 1)', array(':id'=>$uid))
			->queryAll();
	}

	public function friendList($uid,$limit,$offset)
	{
		return Yii::app()->db->createCommand()
			->select('u.userid, u.username, u.avatar, u.phonenumber, u.password as email,t.expiry_date')
			->from('relationship r')
			->join('api_user u', 'u.userid=r.user_two_id')
			->join('authtoken t', 't.user_id=r.user_two_id')
			->where('(r.user_one_id=:id AND r.status = 1)', array(':id'=>$uid))
			->limit($limit)
			->offset($offset)
			->order('t.expiry_date desc')
			->queryAll();
	}
	public function friendDetail($uid)
	{
		return Yii::app()->db->createCommand()
			->select('u.userid, u.username, u.avatar, u.phonenumber, u.password as email,t.expiry_date')
			->from('api_user u')
			->join('authtoken t', 't.user_id=u.userid')
			->where('(u.userid=:id)', array(':id'=>$uid))
			->queryRow();
	}
}
