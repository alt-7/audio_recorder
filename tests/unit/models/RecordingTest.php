<?php

declare(strict_types=1);

namespace tests\unit\models;

use app\models\Recording;
use Codeception\Test\Unit;

class RecordingTest extends Unit
{
    public function testValidationRules(): void
    {
        $model = new Recording();

        verify($model->validate())->false();
        $model->session_id = 'test-uuid-123';
        $model->department = 'sales';
        $model->operator_name = 'ivanov';
        $model->status = Recording::STATUS_RECORDING;
        verify($model->validate())->true();
    }

    public function testTableName(): void
    {
        verify(Recording::tableName())->equals('recording');
    }
}