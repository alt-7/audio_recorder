<?php

declare(strict_types=1);

namespace app\commands;

use app\models\User;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;
use yii\helpers\Console;

class UserController extends Controller
{
    /**
     * Запуск: php yii user/init
     * @throws Exception
     */
    public function actionInit(): int
    {
        $this->stdout("Начинаем создание пользователей...\n");

        $defaultUsers = [
            [
                'username'     => 'admin',
                'password'     => 'admin',
                'access_token' => 'admin-token',
                'role'         => 'Administrator'
            ],
            [
                'username'     => 'api_client',
                'password'     => 'password',
                'access_token' => $_ENV['API_KEY_SECRET'] ?? 'secret-api-key-123',
                'role'         => 'API Client'
            ],
            [
                'username'     => 'manager',
                'password'     => 'manager',
                'access_token' => 'manager-token',
                'role'         => 'Manager'
            ]
        ];

        foreach ($defaultUsers as $userData) {
            $user = User::findByUsername($userData['username']);

            if ($user) {
                $this->stdout("Пользователь '{$userData['username']}' уже существует.\n", Console::FG_YELLOW);
                continue;
            }

            $user = new User();
            $user->username = $userData['username'];
            $user->setPassword($userData['password']);
            $user->generateAuthKey();
            $user->access_token = $userData['access_token'] ?? Yii::$app->security->generateRandomString(32);

            if ($user->save()) {
                $this->stdout("Создан: {$userData['username']} ({$userData['role']})\n", BaseConsole::FG_GREEN);
            } else {
                $this->stderr("Ошибка при создании '{$userData['username']}': " . json_encode($user->errors) . "\n", BaseConsole::FG_RED);
            }
        }

        $this->stdout("Готово!\n");
        return ExitCode::OK;
    }
}