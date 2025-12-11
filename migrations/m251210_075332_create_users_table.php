<?php

use yii\db\Migration;

class m251210_075332_create_users_table extends Migration
{
    /**
     * @throws \yii\base\Exception
     */
    public function safeUp(): void
    {
        $this->createTable('users', [
            'id'            => $this->primaryKey(),
            'username'      => $this->string()->notNull()->unique(),
            'auth_key'      => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'access_token'  => $this->string()->unique(),
            'created_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->insert('users', [
            'username'      => 'api_client',
            'auth_key'      => Yii::$app->security->generateRandomString(),
            'password_hash' => Yii::$app->security->generatePasswordHash('password'),
            'access_token'  => 'secret-api-key-123'
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('users');
    }
}