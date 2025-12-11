<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%recording}}`.
 */
class m251209_171347_create_recording_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%recording}}', [
            'id'            => $this->primaryKey(),
            'session_id'    => $this->string(36)->notNull()->unique(),
            'department'    => $this->string(100)->notNull(),
            'operator_name' => $this->string(100)->notNull(),
            'file_path'     => $this->string(500)->defaultValue(null),
            'file_size'     => $this->integer()->defaultValue(null),
            'duration'      => $this->float()->defaultValue(null),
            'status'        => $this->string(20)->defaultValue('recording'),
            'created_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_dept_oper', 'recording', ['department', 'operator_name']);
        $this->createIndex('idx_created_at', 'recording', 'created_at');

        $this->execute("ALTER TABLE recording ADD CONSTRAINT check_recording_status CHECK (status IN ('recording', 'processing', 'completed', 'error'))");
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%recording}}');
    }
}
