<?php

return [
    'user1' => [
        'id'            => 100,
        'username'      => 'admin',
        'auth_key'      => 'test100key',
        'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
        'access_token'  => $_ENV['API_KEY_SECRET'],
    ],
    'user2' => [
        'id'            => 101,
        'username'      => 'demo',
        'auth_key'      => 'test101key',
        'password_hash' => Yii::$app->security->generatePasswordHash('demo'),
        'access_token'  => '101-token',
    ],
];