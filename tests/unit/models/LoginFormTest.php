<?php

declare(strict_types=1);

namespace tests\unit\models;

use app\models\LoginForm;
use Codeception\Test\Unit;
use tests\unit\fixtures\UserFixture;
use Yii;

class LoginFormTest extends Unit
{
    private $model;

    public function _fixtures(): array
    {
        return [
            'users' => [
                'class' => UserFixture::class,
            ],
        ];
    }

    protected function _after(): void
    {
        Yii::$app->user->logout();
    }

    public function testLoginNoUser(): void
    {
        $this->model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        verify($this->model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
    }

    public function testLoginWrongPassword(): void
    {
        $this->model = new LoginForm([
            'username' => 'demo',
            'password' => 'wrong_password',
        ]);

        verify($this->model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
        verify($this->model->errors)->arrayHasKey('password');
    }

    public function testLoginCorrect(): void
    {
        $this->model = new LoginForm([
            'username' => 'demo',
            'password' => 'demo',
        ]);

        verify($this->model->login())->true();
        verify(Yii::$app->user->isGuest)->false();
        verify($this->model->errors)->arrayHasNotKey('password');
    }
}