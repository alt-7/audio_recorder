<?php

declare(strict_types=1);

namespace app\exceptions;

use yii\base\Exception;

class AudioException extends Exception
{
    public function getName(): string
    {
        return 'Ошибка обработки звука';
    }
}