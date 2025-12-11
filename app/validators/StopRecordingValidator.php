<?php

declare(strict_types=1);

namespace app\validators;

use yii\base\Model;

class StopRecordingValidator extends Model
{
    public string $session_id;
    public string $department;
    public string $operator_name;
    public string $audio_data;

    public function rules(): array
    {
        return [
            [['session_id', 'department', 'operator_name', 'audio_data'], 'required'],
            [['session_id'], 'trim'],
            [['session_id'], 'string', 'length' => 36],
            [['department', 'operator_name'], 'trim'],
            [['department', 'operator_name'], 'string', 'max' => 50],
            [['department', 'operator_name'], 'match', 'pattern' => '/^[a-z0-9_]+$/i'],
            [['audio_data'], 'string'],
        ];
    }
}