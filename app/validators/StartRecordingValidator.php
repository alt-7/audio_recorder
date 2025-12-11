<?php

declare(strict_types=1);

namespace app\validators;

use yii\base\Model;

class StartRecordingValidator extends Model
{
    public string $department;
    public string $operator_name;

    public function rules(): array
    {
        return [
            [['department', 'operator_name'], 'required'],
            [['department', 'operator_name'], 'string', 'max' => 50],
            [['department', 'operator_name'], 'trim'],
            [['department', 'operator_name'], 'match', 'pattern' => '/^[a-z0-9_]+$/i'],
        ];
    }
}