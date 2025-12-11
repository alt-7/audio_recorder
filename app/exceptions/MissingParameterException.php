<?php

declare(strict_types=1);

namespace app\exceptions;

use yii\web\BadRequestHttpException;

class MissingParameterException extends BadRequestHttpException
{
    public function getName(): string
    {
        return 'Отсутствует необходимый параметр';
    }
}