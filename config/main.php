<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-admin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'admin\controllers',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-admin',
            'enableCsrfValidation' => false
        ],
        'user' => [
            'identityClass' => 'admin\models\admin\Admin',
            'enableAutoLogin' => true,
            'authTimeout' => 7200,
            'identityCookie' => ['name' => '_identity-admin', 'httpOnly' => true],
        ],
        'session' => [
            'name' => 'app-admin',
            'class' => 'yii\redis\Session',
            'keyPrefix'=>'app-admin',
            'redis' => 'redis',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/admin.log',
                    'logVars' => [], //关闭$_SERVER  info
                    'maxFileSize' => 1000000, //1GM
                    'prefix' => function ($message) {
                        $uri = Yii::$app->request->url;
                        return "[$uri]";
                    }
                ]
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'formatter' => [
            'class' => 'admin\components\Formatter',
            'dateFormat' => 'Y-M-d',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => '￥',
            'sizeFormatBase' => 1000,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // 订单管理
                'order/<type:\w+>' => 'order/index',

                // 充值商品管理
                'goods/<type:\w+>'                  => 'goods/index',
                'goods/<type:\w+>/create'           => 'goods/create',
                'goods/<type:\w+>/<id:\d+>/update'  => 'goods/update',
                'goods/<type:\w+>/<id:\d+>/delete'  => 'goods/delete',

                //代理查看用户详情
                '/drp/user/view/<uid:\d+>' => '/drp/user/view',
                '/drp/user/expend/<uid:\d+>' => '/drp/user/expend',
                '/drp/user/order/<uid:\d+>' => '/drp/user/order',
                '/drp/user/read/<uid:\d+>' => '/drp/user/read',
                '/drp/user/vip-order/<uid:\d+>' => '/drp/user/vip-order',

                // 通用路由
                '<controller:[\w-]+>/<id:\d+>/<action:[\w-]+>' => '<controller>/<action>',

                '<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => '<module>/<controller>/<action>',
                '<module:[\w-]+>/<controller:[\w-]+>/<id:\d+>/<action:[\w-]+>' => '<module>/<controller>/<action>',
                '<controller:[\w-]+>/<action:[\w-]+>' => '<controller>/<action>',
            ],
        ],
    ],
    
    'modules' => [
        'app' => [
            'class' => 'admin\modules\app\Module',
        ],
        'drp' => [
            'class' => 'admin\modules\drp\Module',
        ],
        'manager' => [
            'class' => 'admin\modules\manager\Module',
        ],
        'mp' => [
            'class' => 'admin\modules\mp\Module',
        ],
        'cp' => [
            'class' => 'admin\modules\cp\Module'
        ],
        'api' => [
            'class' => 'admin\modules\api\Module'
        ],
        'welfare' => [
            'class' => 'admin\modules\welfare\Module'
        ],
    ],
    
    'as access' => [
        'class'  => 'admin\filters\AccessControl',
        'except' => ['site/captcha', 'site/login', 'site/error', 'drp/wechat/index', 'api/*'],
        'rules'  => [
            [
                'allow' => false,
                'roles' => ['?'],
            ],
            [
                'allow' => true,
                'roles' => ['@'],
            ]
        ]
    ],
    
    'params' => $params,
];
