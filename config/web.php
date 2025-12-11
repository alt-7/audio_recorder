<?php

use app\factories\RecordingFactory;
use app\interfaces\AudioProcessorInterface;
use app\interfaces\RecordingRepositoryInterface;
use app\interfaces\StorageInterface;
use app\repositories\RecordingRepository;
use yii\symfonymailer\Mailer;
use yii\web\Response;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id'                  => 'basic',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'timeZone'            => 'Asia/Almaty',
    'controllerNamespace' => 'app\controllers',
    'viewPath'            => dirname(__DIR__) . '/app/views',
    'aliases'             => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components'          => [
        'response'       => [
            'class'         => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->format === Response::FORMAT_JSON) {
                    $response->data = \app\components\ResponseFormatter::format($response);
                }
            },
        ],
        'request'        => [
            'cookieValidationKey' => 'audio_records_12345',
            'parsers'             => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache'          => [
            'class' => 'yii\caching\FileCache',
        ],
        'user'           => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler'   => [
            'errorAction' => 'site/error',
        ],
        'mailer'         => [
            'class'            => Mailer::class,
            'viewPath'         => '@app/mail',
            'useFileTransport' => true,
        ],
        'log'            => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db'             => $db,
        'urlManager'     => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
                'POST api/recording/start' => 'audio-record/start',
                'POST api/recording/stop'  => 'audio-record/stop',
                'GET swagger/doc'          => 'swagger/doc',
            ],
        ],
        'audioProcessor' => [
            'class'        => 'app\components\FfmpegComponent',
            'ffmpegBinary' => '/usr/bin/ffmpeg',
        ],
        'fileStorage'    => [
            'class'       => 'app\components\LocalStorageComponent',
            'storagePath' => '@webroot/audio-records',
        ],
    ],
    'container'           => [
        'definitions' => [
            RecordingRepositoryInterface::class => RecordingRepository::class,
            AudioProcessorInterface::class      => function () {
                return Yii::$app->audioProcessor;
            },
            StorageInterface::class             => function () {
                return Yii::$app->fileStorage;
            },
            RecordingFactory::class             => RecordingFactory::class,
        ],
    ],
    'params'              => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class'      => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.*', '192.168.*', '*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.*', '192.168.*', '*'],
    ];
}

return $config;