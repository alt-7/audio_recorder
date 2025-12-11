<?php

declare(strict_types=1);

namespace app\exceptions;

use yii\db\Exception;

class RecordingSaveException extends Exception
{
    public function __construct(array $errors)
    {
        parent::__construct('Ошибка сохранения записи: ' . json_encode($errors));
    }

    public function getName(): string
    {
        return 'Ошибка сохранения базы данных';
    }
}