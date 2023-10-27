<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'timeZone' => 'Europe/Moscow',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'auth' => [
            'class' => \app\modules\auth\Module::class
        ],
        'admin' => [
            'class' => \app\modules\admin\Module::class
        ],
        'user' => [
            'class' => \app\modules\user\Module::class
        ],
        'catalog' => [
            'class' => \app\modules\catalog\Module::class
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'hJgCBMxIog1iGOtiJowSO_4VFaxA3dDO',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\modules\auth\models\Identity',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
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
        'db' => $db,
        'jwt' => [
            'class' => \kaabar\jwt\Jwt::class,
            'key' => 'U}MpK|~WBNd4iDCDCZOXlppS%xcbZV65apbC$GR27TOwsvU57FP*UYK}By3Qrz34',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => ['admin/auth' => 'auth/auth'],
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'OPTIONS' => 'options',
                        'OPTIONS login' => 'options',
                        'OPTIONS refresh-token' => 'options',
                        '' => 'options',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => ['admin/category' => 'admin/category'],
                    'extraPatterns' => [
                        'PUT,PATCH {id}' => 'update',
                        'DELETE {id}' => 'delete',
                        'GET,HEAD {id}' => 'view',
                        'POST' => 'create',
                        'GET,HEAD' => 'index',
                        'OPTIONS' => 'options',
                        '' => 'options',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => ['admin/item' => 'admin/items'],
                    'extraPatterns' => [
                        'PUT,PATCH {id}' => 'update',
                        'DELETE {id}' => 'delete',
                        'GET,HEAD {id}' => 'view',
                        'POST' => 'create',
                        'GET,HEAD' => 'index',
                        'OPTIONS' => 'options',
                        '' => 'options',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => ['catalog/category' => 'catalog/category'],
                    'extraPatterns' => [
                        'GET,HEAD {id}' => 'view',
                        'GET,HEAD' => 'index',
                        'OPTIONS' => 'options',
                        '' => 'options',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => [
                        'catalog/items' => 'catalog/items',
                    ],
                    'extraPatterns' => [
                        'GET,HEAD {id}' => 'index',
                        'OPTIONS' => 'options',
                        '' => 'options',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => [
                        'catalog' => 'catalog/items',
                    ],
                    'extraPatterns' => [
                        'GET,HEAD {id}' => 'view',
                        'OPTIONS' => 'options',
                        '' => 'options',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => ['admin/user' => 'user/user'],
                    'extraPatterns' => [
                        'PUT,PATCH {id}' => 'update',
                        'DELETE {id}' => 'delete',
                        'GET,HEAD {id}' => 'view',
                        'POST' => 'create',
                        'GET,HEAD' => 'index',
                        'OPTIONS' => 'options',
                        '' => 'options',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => ['' => 'site'],
                    'extraPatterns' => [
                        'GET' => 'index',
                        'GET json-schema' => 'json-schema',
                        'OPTIONS' => 'options',
                        '' => 'options',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
