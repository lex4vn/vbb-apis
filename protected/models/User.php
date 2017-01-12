<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $userid
 * @property string $username
 * @property string $password
 * @property string $device_token
 * @property integer $device_type
 * @property string $googleid
 * @property string $gg_access_token
 * @property string $facebookid
 * @property string $fb_access_token
 * @property integer $status
 * @property string $create_date
 * @property string $modify_date
 */
class User extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'vpi_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('userid', 'required'),
			array('userid, device_type, status', 'numerical', 'integerOnly'=>true),
			array('username', 'length', 'max'=>50),
			array('password, device_token', 'length', 'max'=>100),
			array('googleid, facebookid', 'length', 'max'=>30),
			array('gg_access_token, fb_access_token, create_date, modify_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('userid, username, password, device_token, device_type, googleid, gg_access_token, facebookid, fb_access_token, status, create_date, modify_date', 'safe', 'on'=>'search'),
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
			'userid' => 'Userid',
			'username' => 'Username',
			'password' => 'Password',
			'device_token' => 'Device Token',
			'device_type' => '1 ios, 2 android',
			'googleid' => 'Googleid',
			'gg_access_token' => 'Gg Access Token',
			'facebookid' => 'Facebookid',
			'fb_access_token' => 'Fb Access Token',
			'status' => 'Status',
			'create_date' => 'Create Date',
			'modify_date' => 'Modify Date',
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

		$criteria->compare('userid',$this->userid);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('device_token',$this->device_token,true);
		$criteria->compare('device_type',$this->device_type);
		$criteria->compare('googleid',$this->googleid,true);
		$criteria->compare('gg_access_token',$this->gg_access_token,true);
		$criteria->compare('facebookid',$this->facebookid,true);
		$criteria->compare('fb_access_token',$this->fb_access_token,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('create_date',$this->create_date,true);
		$criteria->compare('modify_date',$this->modify_date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
