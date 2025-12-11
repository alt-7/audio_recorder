<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => $_ENV['DB_DSN'] ?? 'pgsql:host=db;port=5432;dbname=audio_record',
    'username' => $_ENV['DB_USER'] ?? 'user',
    'password' => $_ENV['DB_PASSWORD'] ?? 'password',
    'charset' => 'utf8',
];