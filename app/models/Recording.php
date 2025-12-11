<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $session_id
 * @property string $department
 * @property string $operator_name
 * @property string|null $file_path
 * @property int|null $file_size
 * @property float|null $duration
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class Recording extends ActiveRecord
{
    public const STATUS_RECORDING = 'recording';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ERROR = 'error';

    public static function tableName(): string
    {
        return 'recording';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['session_id', 'department', 'operator_name'], 'required'],
            [['status'], 'in', 'range' => [
                self::STATUS_RECORDING,
                self::STATUS_PROCESSING,
                self::STATUS_COMPLETED,
                self::STATUS_ERROR
            ]],
            [['file_path'], 'string', 'max' => 500],
            [['file_size'], 'integer'],
            [['duration'], 'number'],
        ];
    }
}