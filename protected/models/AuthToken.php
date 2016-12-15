<?php

/**
 * This is the model class for table "authToken".
 *
 * The followings are the available columns in table 'authToken':
 * @property integer $user_id
 * @property string $token
 * @property string $sessionhash
 * @property integer $expiry_date
 */
class AuthToken extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'authToken';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, token, sessionhash', 'required'),
			array('user_id, expiry_date', 'numerical', 'integerOnly'=>true),
			array('token', 'length', 'max'=>255),
			array('sessionhash', 'length', 'max'=>1000),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('user_id, token, sessionhash, expiry_date', 'safe', 'on'=>'search'),
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
			'user_id' => 'User Id',
			'token' => 'Token',
			'sessionhash' => 'Sessionhash',
			'expiry_date' => 'Expiry Date',
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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('token',$this->token,true);
		$criteria->compare('sessionhash',$this->sessionhash,true);
		$criteria->compare('expiry_date',$this->expiry_date);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AuthToken the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
