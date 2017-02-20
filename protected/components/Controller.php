<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 *///
	public $menu=array();
    public $detect;
    public $tablet = false;
    public $pageTitle;
    public $userName;
    public $titlePage;
    public $title = 'API';
    public $description = 'API';
    public $keywords = 'API';
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

    public function __construct($id, $module)
    {
		$domain = $_SERVER['HTTP_HOST'];
		$this->detect = Yii::app()->mobileDetect;
		if ($this->detect->isTablet()) {
			$this->tablet = TRUE;
			Yii::app()->theme = "advance";
		} else
			if ($this->detect->isMobile()) {
				if ($this->detect->version('Windows Phone')) {
					Yii::app()->theme = 'advance';
				} elseif ($this->detect->is('Opera')) {
					Yii::app()->theme = 'advance';
				} else if ($this->detect->is('AndroidOS')) {
					if ($this->detect->version('Android') < 3.0) {
						Yii::app()->theme = 'advance';
					} else {
						Yii::app()->theme = 'advance';
					}
				} else if ($this->detect->is('iOS')) {
					if ($this->detect->getIOSGrade() === 'B') {
						Yii::app()->theme = 'advance';
					} else {
						Yii::app()->theme = 'advance';
					}
				} else {
					if ($this->detect->mobileGrade() === 'A') {
						Yii::app()->theme = 'advance';
					} else {
						Yii::app()->theme = 'advance';
					}
				}
			} else {
				Yii::app()->theme = 'advance';
			}
    }
}