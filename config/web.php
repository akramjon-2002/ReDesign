<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'telegram' => [
            'class' => 'app\modules\telegram\Module',
        ],
    ],
    'bootstrap' => ['log', 'queue'],
    'container' => [
        'singletons' => [
            'app\services\TextureService' => [
                'class' => 'app\services\TextureService',
            ],
            'app\services\RequestService' => [
                'class' => 'app\services\RequestService',
            ],
            'app\services\TelegramService' => [
                'class' => 'app\services\TelegramService',
            ],
            'app\services\TelegramUpdateService' => [
                'class' => 'app\services\TelegramUpdateService',
            ],
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '5L8uJrSa_dmIPMEOSu4t2VhAp42icaK2',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'queue' => [
            'class' => \yii\queue\db\Queue::class,
            'db' => 'db', // DB connection component or its config
            'tableName' => '{{%queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => \yii\mutex\PgsqlMutex::class, // Mutex used to sync queries
        ],
        'stability' => [
            'class' => 'app\services\StabilityService',
        ],
        'telegramService' => [
            'class' => 'app\services\TelegramService',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
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
                    'logFile' => '@runtime/logs/errors/' . date('Y-m-d') . '.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'trace'],
                    'logFile' => '@runtime/logs/info/' . date('Y-m-d') . '.log',
                    'categories' => ['app\jobs\*', 'app\services\*', 'app\modules\*'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'admin' => 'admin/texture/index',
                'telegram/webhook' => 'telegram/webhook/index',
                'telegram/webapp' => 'telegram/webapp/index',
                'telegram/webapp/upload' => 'telegram/webapp/upload',
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
