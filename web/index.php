<?php

function dd(...$input)
{
    foreach ($input as $i) {
        var_dump($i);
    }
    die;
}

function jd(...$input)
{
    die(json_encode($input));
}

function ed($input)
{
    var_export($input);
    die;
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__));
defined('VENDOR_PATH') or define('VENDOR_PATH', BASE_PATH . '/vendor');
defined('APP_NAME') or define('APP_NAME', 'وبـلاگ فروشـگاهـی اکــرز');

require VENDOR_PATH . '/autoload.php';
require VENDOR_PATH . '/yiisoft/yii2/Yii.php';

$params = require(__DIR__ . '/../config/params.php');

$config = [
    'id' => 'basic',
    'name' => APP_NAME,
    'language' => 'fa-IR',
    'bootstrap' => [
        'log',
    ],
    'basePath' => BASE_PATH,
    'vendorPath' => VENDOR_PATH,
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'controllerNamespace' => 'app\controllers',
    'components' => [
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js' => [
                        YII_ENV !== 'prod' ? 'jquery.js' : 'jquery.min.js'
                    ]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [
                        YII_ENV !== 'prod' ? 'css/bootstrap.css' : 'css/bootstrap.min.css',
                    ]
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => [
                        YII_ENV !== 'prod' ? 'js/bootstrap.js' : 'js/bootstrap.min.js',
                    ]
                ]
            ],
        ],
        'db' => $params['db'],
        'dbLog' => $params['dbLog'],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-app',
            'cookieValidationKey' => $params['cookieValidationKey'],
            'baseUrl' => $params['baseUrl'],
        ],
        'session' => [
            'name' => 'akrez-app',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-app', 'httpOnly' => true],
        ],
        'customerApi' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Customer',
            'enableSession' => false,
            'enableAutoLogin' => false,
            'loginUrl' => null,
            'returnUrl' => null,
        ],
        'blog' => [
            'class' => 'app\components\BlogContainer',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'site/gallery/<type:\w+>/<whq>/<name:[\w\.]+>' => 'site/gallery',
                //
                '<controller:(site)>/<action>' => '<controller>/<action>',
                '<controller:(site)>' => '<controller>/index',
                //
                '<module:(api)>/<controller>/<action>' => '<module>/<controller>/<action>',
                //
                '<controller>/<_blog>/<action>/<id:\d+>' => '<controller>/<action>',
                '<controller>/<_blog>/<action>' => '<controller>/<action>',
                '<controller>/<_blog>/' => '<controller>/index',
                //
                '' => 'site/index',
            ],
        ],
        'formatter' => [
            'class' => 'app\components\Formatter',
        ],
        'mailer' => $params['mailer'],
    ],
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
    ],
    'params' => $params['params'],
];

if (YII_ENV == 'dev') {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

(new yii\web\Application($config))->run();
