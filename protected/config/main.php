<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.

if (!isset($root_dir)) $root_dir = dirname(dirname(dirname(dirname(__FILE__))));
Yii::setPathOfAlias('protected', $root_dir . '/vbb-apis/protected/');

return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'API',

    // preloading 'log' component
    'preload' => array('log'),

    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.vendor.*',
        'application.extensions.yii-mail.*',
	'application.extensions.*',
	'application.extensions.select2.*',
    ),

    'modules' => array(
        // uncomment the following to enable the Gii tool
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => 'admin',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            //'ipFilters'=>array('127.0.1.1','::1'),
        ),
    ),

    // application components
    'components' => array(
        'yii-mail' => array(
            'class' => 'application.extensions.yii-mail.YiiMailMessage',
            'delivery' => 'php', //Will use the php mailing function.
            //May also be set to 'debug' to instead dump the contents of the email into the view
        ),

        'mail' => array(
            'class' => 'application.extensions.yii-mail.YiiMail',
            'transportType' => 'smtp',
            'transportOptions' => array(
                'host' => 'smtp.gmail.com',
                'encryption' => 'ssl',
                'username' => 'smartkids211@gmail.com',
                'password' => '123456',
                'port' => '465',
            ),
            'viewPath' => 'application.views.mail',
            'logging' => true,
            'dryRun' => false,
        ),
        'user' => array(
            // enable cookie-based authentication
            'allowAutoLogin' => true,
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager' => array(
            'urlFormat' => 'path',
            'rules' => array(
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        'db' => array(
            'connectionString' => 'mysql:host=localhost;dbname=test',
            'class' => 'application.extensions.PHPPDO.CPdoDbConnection',
            'pdoClass' => 'PHPPDO',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ),

        'errorHandler' => array(
            'errorAction' => 'site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning, info',
                    'maxFileSize' => 102400,
                    'maxLogFiles' => 20,
                ),
            ),
        ),
        'mobileDetect' => array(
            'class' => 'application.extensions.mobileDetect.MobileDetect'
        ),
    ),

    'params' => array(
        // this is used in contact page
        'adminEmail' => 'webmaster@example.com',
        'voucher' => array(
            'partnerCode' => 'test',
            'password' => '123456',
            'secretKey' => 'test_sk',
        ),

        'ipRanges' => array(
            '127.0.0.1/1',
        ),

    ),
);
