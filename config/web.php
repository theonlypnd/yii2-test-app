<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],

    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'be0ed9c029feead7da11abe18262917a07a54cd7734c5f951411bc8cb74a42e9',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],

        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'user' => [
            'identityClass'   => 'app\models\Admin',
            'enableAutoLogin' => false,
            'loginUrl'        => null,
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

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules' => [
                '' => 'site/index',
                'assets/<path:.+>' => 'site/asset',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['task'],
                    'pluralize' => true,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'OPTIONS' => 'options',
                    ],
                ],

                '<path:(?!api/).+>' => 'site/index',
            ],
        ],

        'corsFilter' => [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:3000'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Allow-Headers' => ['*'],
            ],
        ],
    ],

    'as cors' => [
        'class' => \yii\filters\Cors::class,
    ],

    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment

    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // 'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // 'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
